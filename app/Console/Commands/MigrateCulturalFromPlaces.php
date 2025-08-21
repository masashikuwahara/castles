<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\{Place, CulturalSite, CulturalSiteTranslation, Tag};
use Illuminate\Support\Facades\DB;
use Throwable;

class MigrateCulturalFromPlaces extends Command
{
    protected $signature = 'cultural:migrate-from-places {--dry : ドライラン(書き込みなし)}';
    protected $description = 'places(type=cultural) を cultural_sites へ移行（翻訳・タグ・画像含む）。再実行可。';

    public function handle(): int
    {
        $dry = (bool)$this->option('dry');

        $count = Place::where('type','cultural')->count();
        if ($count === 0) {
            $this->info('移行対象の Place（type=cultural）がありません。');
            return self::SUCCESS;
        }
        $this->info("対象件数: {$count}");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        Place::with(['translations','tags','media','prefecture'])
            ->where('type','cultural')
            ->orderBy('id')
            ->chunkById(50, function ($rows) use ($bar, $dry) {
                foreach ($rows as $p) {
                    try {
                        if ($dry) {
                            $this->line("\n[DRY] {$p->id} {$p->slug}");
                            $bar->advance();
                            continue;
                        }

                        DB::transaction(function () use ($p) {
                            // 1) 本体 upsert
                            $cs = CulturalSite::updateOrCreate(
                                ['slug' => $p->slug],
                                [
                                    'prefecture_id'      => $p->prefecture_id,
                                    'city'               => $p->city,
                                    'lat'                => $p->lat,
                                    'lng'                => $p->lng,
                                    'designated_heritage'=> $p->designated_heritage,
                                    'site_type'          => null,
                                    'period'             => null,
                                    'rating'             => $p->rating,
                                    'managing_agency'    => null,
                                    'official_url'       => null,
                                ]
                            );

                            // 2) 翻訳 upsert
                            foreach ($p->translations as $t) {
                                CulturalSiteTranslation::updateOrCreate(
                                    ['cultural_site_id' => $cs->id, 'locale' => $t->locale],
                                    [
                                        'name' => $t->name,
                                        'summary' => $t->summary,
                                        'slug_localized' => $t->slug_localized ?: $p->slug,
                                    ]
                                );
                            }

                            // 3) タグ紐付け（重複を避けて追加）
                            $cs->tags()->syncWithoutDetaching($p->tags->pluck('id')->all());

                            // 4) 画像コピー（MediaLibrary）
                            foreach ($p->getMedia('photos') as $m) {
                                // 既に同名ファイルを持つか軽くチェック（雑にnameで確認）
                                $dup = $cs->media()->where('file_name', $m->file_name)->exists();
                                if (!$dup) {
                                    $m->copy($cs, 'photos'); // 同じディスクに複製
                                }
                            }
                        });
                    } catch (Throwable $e) {
                        $this->error("\n[ERROR id={$p->id} slug={$p->slug}] ".$e->getMessage());
                    }
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine(2);
        $this->info('移行処理が完了しました。必要なら派生画像を再生成してください: php artisan media-library:regenerate');

        return self::SUCCESS;
    }
}
