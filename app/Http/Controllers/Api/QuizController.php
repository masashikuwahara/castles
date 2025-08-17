<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class QuizController extends Controller
{
    public function random(Request $req, string $locale)
    {
        $choicesCount = max(2, min(6, (int) $req->query('choices', 4)));

        $hasMediaTable = \Illuminate\Support\Facades\Schema::hasTable('media');
        $hasAnyMedia   = $hasMediaTable &&
        \Spatie\MediaLibrary\MediaCollections\Models\Media::query()
        ->where('collection_name', 'photos')->exists();

        // 画像がある Place をベースにランダム取得
        $base = Place::query()
            ->with([
                'translations' => fn($q) => $q->where('locale', $locale),
                'tags',
                'prefecture',
                'photos' => fn($q) => $q->where('is_cover', true)->orWhereNull('is_cover'),
            ])
            ->where(function ($q) use ($hasAnyMedia) {
              if ($hasAnyMedia) {
                $q->whereHas('media', fn($m) => $m->where('collection_name', 'photos'));
                }
                $q->orWhereHas('photos');
                });

        $correct = (clone $base)->inRandomOrder()->first();
        if (!$correct) {
            return response()->json(['message' => 'No quiz data'], 404);
        }

        $decoys = (clone $base)
            ->where('id', '<>', $correct->id)
            ->where('type', $correct->type) // 同タイプから選ぶと難易度が適度に
            ->inRandomOrder()
            ->limit($choicesCount - 1)
            ->get();

        $pool = collect([$correct])->merge($decoys)->shuffle()->values();

        // 画像情報（PlaceResource と同じ形に近づける）
        $image = $this->buildCoverImagePayload($correct);

        $choices = $pool->map(function ($p) {
            $t = $p->translations->first();
            return [
                'id'   => $p->id,
                'name' => $t?->name ?? $p->slug,
                'slug' => $t?->slug_localized ?? $p->slug,
            ];
        });

        $t = $correct->translations->first();
        return response()->json([
            'data' => [
                'question' => [
                    'place_id'   => $correct->id,
                    'image'      => $image,
                    'choices'    => $choices,
                    'correct_id' => $correct->id, // クライアントで判定（シンプル重視）
                ],
                'explain' => [
                    'name'    => $t?->name ?? null,
                    'summary' => $t?->summary ?? null,
                    'slug'    => $t?->slug_localized ?? $correct->slug,
                ],
            ]
        ]);
    }

    private function buildCoverImagePayload(Place $place): ?array
    {
        // MediaLibrary 優先、なければ photos.path を返す
        if (Schema::hasTable('media')) {
            $media = $place->getFirstMedia('photos');
            if ($media) {
                $webp = [
                    'thumb' => $media->getUrl('thumb-webp'),
                    'card'  => $media->getUrl('card-webp'),
                    'cover' => $media->getUrl('cover-webp'),
                ];
                return [
                    'src'    => $webp['card'],
                    'srcset' => [
                        'webp' => "{$webp['thumb']} 240w, {$webp['card']} 600w, {$webp['cover']} 1200w",
                    ],
                    'sizes'  => '(min-width:1024px) 50vw, 100vw',
                    'width'  => 1200,
                    'height' => 800,
                    'format' => 'webp',
                ];
            }
        }
        // フォールバック（photos.path）
        $p = $place->photos->first();
        if ($p) {
            return [
                'src'    => $p->path,
                'srcset' => null,
                'sizes'  => null,
                'width'  => null,
                'height' => null,
                'format' => 'orig',
            ];
        }
        return null;
    }
}
