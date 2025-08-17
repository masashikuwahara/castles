<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Schema;

class PlaceResource extends JsonResource
{
    public function toArray($request)
    {
        // 呼び出し側で locale に絞った translations を preload している想定
        $t = $this->translations->first();

        return [
            'id' => $this->id,
            'type' => $this->type,
            'slug' => $this->slug,
            'slug_localized' => optional($t)->slug_localized,
            'name' => optional($t)->name,
            'summary' => optional($t)->summary,
            'prefecture' => [
                'id' => optional($this->prefecture)->id,
                'name_ja' => optional($this->prefecture)->name_ja,
                'name_en' => optional($this->prefecture)->name_en,
            ],
            'city' => $this->city,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'built_year' => $this->built_year,
            'abolished_year' => $this->abolished_year,
            'castle_structure' => $this->castle_structure,
            'tenshu_structure' => $this->tenshu_structure,
            'founder' => $this->founder,
            'main_renovators' => $this->main_renovators,
            'main_lords' => $this->main_lords,
            'designated_heritage' => $this->designated_heritage,
            'remains' => $this->remains,
            'rating' => (int) $this->rating,
            'is_top100' => (bool) $this->is_top100,
            'is_top100_continued' => (bool) $this->is_top100_continued,
            'tags' => $this->whenLoaded('tags', fn() =>
                $this->tags->map(fn($tag)=>['name'=>$tag->name,'slug'=>$tag->slug])
            ),
            // 'cover_photo' => $this->whenLoaded('photos', function () {
            //     $cover = $this->photos->firstWhere('is_cover', true) ?? $this->photos->first();
            //     return $cover ? new PhotoResource($cover) : null;
            'cover_photo' => $this->whenLoaded('photos', function () {
            // 既存Photoテーブルのカバー（is_cover）を優先。なければ最初のMedia
            $coverPhoto = $this->photos->firstWhere('is_cover', true) ?? $this->photos->first();
            $media = $this->getFirstMedia('photos'); // MediaLibraryの先頭（取り込み済み前提）

            if (!$media) {
                // Media未移行時は従来のpathだけ返す（フォールバック）
                return $coverPhoto ? [
                    'src' => $coverPhoto->path,
                    'width' => null, 'height' => null,
                    'srcset' => null, 'sizes' => null,
                    'format' => 'orig',
                ] : null;
            }

            if (!Schema::hasTable('media')) {
                $coverPhoto = $this->photos->firstWhere('is_cover', true) ?? $this->photos->first();
                return $coverPhoto ? [
                    'src' => $coverPhoto->path,
                    'srcset' => null, 'sizes' => null,
                    'width' => null, 'height' => null,
                    'format' => 'orig', 'original' => $coverPhoto->path,
                ] : null;
            }

            // 生成済みURL（publicディスク → /storage/...）
            $webp = [
                'thumb' => $media->getUrl('thumb-webp'),
                'card'  => $media->getUrl('card-webp'),
                'cover' => $media->getUrl('cover-webp'),
            ];
            // AVIFを有効化したら↓も追加
            // $avif = [
            //     'thumb' => $media->getUrl('thumb-avif'),
            //     'card'  => $media->getUrl('card-avif'),
            //     'cover' => $media->getUrl('cover-avif'),
            // ];

            return [
                // 既定のsrcは中サイズ
                'src'    => $webp['card'],
                'srcset' => [
                    'webp' => "{$webp['thumb']} 240w, {$webp['card']} 600w, {$webp['cover']} 1200w",
                    // 'avif' => "{$avif['thumb']} 240w, {$avif['card']} 600w, {$avif['cover']} 1200w",
                ],
                'sizes'  => '(min-width:1024px) 25vw, (min-width:768px) 33vw, 50vw',
                // 任意：想定サイズ（レイアウトシフト防止・概算でOK）
                'width'  => 600,
                'height' => 400,
                'format' => 'webp',
                'original' => $media->getUrl(),
            ];
            }),
        ];
    }
}
