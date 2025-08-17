<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\{Place, Photo};
use Illuminate\Support\Facades\Storage;

class ImportPhotosToMedia extends Command
{
    protected $signature = 'photos:import-to-media {--dry}';
    protected $description = 'Import existing Photo records into medialibrary';

    public function handle(): int
    {
        $dry = $this->option('dry');

        $bar = $this->output->createProgressBar(Photo::count());
        $bar->start();

        Photo::orderBy('id')->chunk(200, function($chunk) use ($dry, $bar) {
            foreach ($chunk as $photo) {
                /** @var \App\Models\Place $place */
                $place = $photo->place;
                if (!$place) { $bar->advance(); continue; }

                // 例: /storage/photos/matsumoto01.webp → public ディスク基準に直す
                $p = ltrim($photo->path, '/');
                if (str_starts_with($p, 'storage/')) {
                    $p = substr($p, strlen('storage/')); // "photos/xxx.webp"
                }

                if (!Storage::disk('public')->exists($p)) {
                    $this->warn(" missing: {$p}");
                    $bar->advance();
                    continue;
                }

                if (!$dry) {
                    // 同一ファイルの重複取り込みを避けたい場合は簡易チェック
                    $already = $place->getMedia('photos')->firstWhere('file_name', basename($p));
                    if (!$already) {
                        $place->addMediaFromDisk($p, 'public')->toMediaCollection('photos');
                    }
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info($dry ? 'DRY RUN done.' : 'Imported. Now run: php artisan media-library:regenerate');

        return self::SUCCESS;
    }
}
