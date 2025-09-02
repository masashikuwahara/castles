<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ImportMedia extends Command
{
    // 使い方の例は下にまとめています
    protected $signature = 'media:import
        {--model= : place|cultural|auto}
        {--slug= : スラッグ（カンマ区切りOK）。例: matsumoto,sannai-maruyama}
        {--all : ディレクトリ配下の全スラッグを対象にする}
        {--dir= : ベースディレクトリ（省略時は place=castles / cultural=cultural）}
        {--disk=public : ディスク名}
        {--clear : 取り込み前に既存の photos コレクションを削除}
        {--cover= : カバー判定に使う正規表現（例: cover|main|^0*1[._-]}}
        {--sidecar=captions.json : ファイル名→キャプションを持つJSON名}
        {--dry : 書き込みせずドライラン}';

    protected $description = 'フォルダの画像を Spatie MediaLibrary に一括登録（Place/CulturalSite両対応）';

    public function handle(): int
    {
        $modelOpt = strtolower((string)$this->option('model'));
        $disk     = (string)$this->option('disk') ?: 'public';
        $dirOpt   = (string)$this->option('dir') ?: null;
        $clear    = (bool)$this->option('clear');
        $dry      = (bool)$this->option('dry');
        $coverRe  = (string)($this->option('cover') ?: 'cover|main|^0*1[._-]');
        $sidecar  = (string)($this->option('sidecar') ?: 'captions.json');

        // モデルの判定
        $modelMap = [
            'place'    => \App\Models\Place::class,
            'cultural' => \App\Models\CulturalSite::class,
        ];

        if (!in_array($modelOpt, ['place','cultural','auto'], true)) {
            $this->error('--model は place|cultural|auto のいずれかにしてください');
            return self::FAILURE;
        }

        // スラッグ配列
        $slugs = [];
        if ($this->option('slug')) {
            $slugs = collect(explode(',', (string)$this->option('slug')))
                ->map(fn($s) => trim($s))
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        $all = (bool)$this->option('all');
        if (!$all && empty($slugs)) {
            $this->error('対象がありません。--slug=... または --all を指定してください。');
            return self::FAILURE;
        }

        // モデル別のデフォルトディレクトリ
        $defaultDir = fn($m) => $m === 'place' ? 'castles' : 'cultural';

        // auto モードは、--dir 配下の直下フォルダ名をスラッグとみなして
        // Place が見つからなければ CulturalSite を試す運用
        if ($modelOpt === 'auto' && !$dirOpt) {
            $this->warn('auto モードでは --dir を指定してください（例: storage/app/public/castles 相当の "castles"）');
            return self::FAILURE;
        }

        // 対象スラッグの決定（--all の場合はディレクトリ配下を列挙）
        if ($all) {
            $baseDir = $dirOpt ?: $defaultDir($modelOpt === 'auto' ? 'place' : $modelOpt);
            $dirs = Storage::disk($disk)->directories($baseDir);
            $slugs = collect($dirs)
                ->map(fn($p) => basename($p))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (empty($slugs)) {
                $this->warn("ディレクトリにスラッグっぽいフォルダが見つかりません: disk={$disk} dir={$baseDir}");
                return self::SUCCESS;
            }
        }

        // 取り込み処理本体
        foreach ($slugs as $slug) {
            $modelsToTry = [];

            if ($modelOpt === 'auto') {
                $modelsToTry = ['place', 'cultural'];
            } else {
                $modelsToTry = [$modelOpt];
            }

            $imported = false;

            foreach ($modelsToTry as $which) {
                $class = $modelMap[$which];
                /** @var \Illuminate\Database\Eloquent\Model|\Spatie\MediaLibrary\HasMedia|null $model */
                $model = $class::where('slug', $slug)->first();

                if (!$model) {
                    // 該当モデルでは未ヒット。auto の場合は次を試す
                    if ($modelOpt === 'auto') continue;
                    $this->warn("{$which}: slug={$slug} のレコードが見つかりません。スキップ");
                    continue;
                }

                $baseDir = $dirOpt ?: $defaultDir($which);
                $folder  = "$baseDir/$slug";
                if (!Storage::disk($disk)->exists($folder)) {
                    $this->warn("{$which}: フォルダがありません: disk={$disk} path={$folder}");
                    // 次モデルを試さず終了（明示指定時）
                    if ($modelOpt !== 'auto') break;
                    continue;
                }

                if ($clear && !$dry) {
                    $model->clearMediaCollection('photos');
                    $this->info("{$which}: 既存の photos をクリアしました: slug={$slug}");
                }

                // サイドカー JSON（キャプション/カバー指定）
                $metaByFile = [];
                $sidecarPath = "$folder/$sidecar";
                if (Storage::disk($disk)->exists($sidecarPath)) {
                    $json = json_decode(Storage::disk($disk)->get($sidecarPath), true) ?: [];
                    // 期待形式: { "filename.jpg": { "ja": "...", "en": "...", "cover": true } }
                    $metaByFile = is_array($json) ? $json : [];
                }

                // 画像列挙
                $files = Storage::disk($disk)->files($folder);
                $files = array_values(array_filter($files, fn($p) => preg_match('/\.(jpe?g|png|webp)$/i', $p)));

                if (empty($files)) {
                    $this->warn("{$which}: 画像が見つかりません: $folder");
                    // 明示モデル時は終了、auto時は次モデルへ
                    if ($modelOpt !== 'auto') break;
                    continue;
                }

                // 既存の original_path を収集（重複スキップ用）
                $existing = method_exists($model, 'getMedia')
                    ? $model->getMedia('photos')->mapWithKeys(function ($m) {
                        return [$m->getCustomProperty('original_path') => true];
                    })->filter()->all()
                    : [];

                $added = 0;
                foreach ($files as $i => $path) {
                    $bn = basename($path);

                    // サイドカー優先でカバー判定
                    $side = $metaByFile[$bn] ?? [];
                    $isCover = isset($side['cover'])
                        ? (bool)$side['cover']
                        : (bool)preg_match('/(' . $coverRe . ')/i', $bn) || $i === 0;

                    $captionJa = $side['ja'] ?? null;
                    $captionEn = $side['en'] ?? null;

                    // 重複スキップ（original_path を比較）
                    if (!empty($existing[$path])) {
                        $this->line("  - skip (duplicate): $path");
                        continue;
                    }

                    $this->line("  + add: $path" . ($isCover ? " [COVER]" : "") . ($dry ? " (dry)" : ""));

                    if ($dry) {
                        $added++;
                        continue;
                    }

                    $model->addMediaFromDisk($path, $disk)
                        ->withCustomProperties([
                            'caption_ja'    => $captionJa,
                            'caption_en'    => $captionEn,
                            'is_cover'      => $isCover,
                            'original_path' => $path,
                        ])
                        ->toMediaCollection('photos');

                    $added++;
                }

                $imported = true;
                $this->info("{$which}: slug={$slug} 取り込み完了（追加 {$added} 件）");
                // auto の場合、どちらかで成功したら次の slug へ
                if ($modelOpt === 'auto') break;
            }

            if (!$imported) {
                $this->warn("slug={$slug} は取り込み対象が見つかりませんでした（モデル不一致 or ディレクトリ無し）");
            }
        }

        $this->newLine();
        $this->info('完了。非同期変換を使っていない場合は即反映されます。必要なら `php artisan media-library:regenerate` を。');

        return self::SUCCESS;
    }
}
