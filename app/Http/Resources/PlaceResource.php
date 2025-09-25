<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PlaceResource extends JsonResource
{
    public function toArray($request)
    {
        $t = $this->relationLoaded('translations')
        ? $this->translations->first()
        : null;

        $cover = null;
        $photoItems = collect();
        $collection = $this->resource->getMedia('photos');

            /** @var Media|null $coverMedia */
            $coverMedia = $collection->first(fn($m) => (bool)$m->getCustomProperty('is_cover')) 
            ?? $collection->first();

            if ($coverMedia) {
                $srcset = [];
                if ($coverMedia->hasGeneratedConversion('thumb-webp')) $srcset[] = $coverMedia->getUrl('thumb-webp').' 240w';
                if ($coverMedia->hasGeneratedConversion('card-webp'))  $srcset[] = $coverMedia->getUrl('card-webp').' 600w';
                if ($coverMedia->hasGeneratedConversion('cover-webp')) $srcset[] = $coverMedia->getUrl('cover-webp').' 1200w';

                $cover = [
                    'src'        => $coverMedia->hasGeneratedConversion('cover-webp') ? $coverMedia->getUrl('cover-webp') : $coverMedia->getUrl(),
                    'srcset'     => ['webp' => $srcset ? implode(', ', $srcset) : null],
                    'sizes'      => '(min-width:1024px) 60vw, 100vw',
                    'caption_ja' => $coverMedia->getCustomProperty('caption_ja'),
                    'caption_en' => $coverMedia->getCustomProperty('caption_en'),
                    'is_cover'   => (bool)$coverMedia->getCustomProperty('is_cover'),
                    // ライトボックス用
                    'id'       => $coverMedia->id,
                    'original' => $coverMedia->getUrl(),
                    'full'     => $coverMedia->hasGeneratedConversion('cover-webp') ? $coverMedia->getUrl('cover-webp') : $coverMedia->getUrl(),
                ];
            }

            $photoItems = $collection
                ->when($cover, fn($col) => $col->reject(fn($m) => $m->id === $cover['id']))
                ->map(function (Media $m) {
                    $srcset = [];
                    if ($m->hasGeneratedConversion('thumb-webp')) $srcset[] = $m->getUrl('thumb-webp').' 240w';
                    if ($m->hasGeneratedConversion('card-webp'))  $srcset[] = $m->getUrl('card-webp').' 600w';
                    return [
                        'id'         => $m->id,
                        'src'        => $m->hasGeneratedConversion('card-webp') ? $m->getUrl('card-webp') : $m->getUrl(),
                        'srcset'     => ['webp' => $srcset ? implode(', ', $srcset) : null],
                        'caption_ja' => $m->getCustomProperty('caption_ja'),
                        'caption_en' => $m->getCustomProperty('caption_en'),
                        'is_cover'   => (bool)$m->getCustomProperty('is_cover'),
                        'original'   => $m->getUrl(),
                        'full'       => $m->hasGeneratedConversion('cover-webp') ? $m->getUrl('cover-webp') : $m->getUrl(),
                    ];
                })->values();

        return [
            'id'   => $this->id,
            'type' => 'castle',
            'slug' => $this->slug,
            'slug_localized' => optional($t)->slug_localized ?? $this->slug,
            'name'    => optional($t)->name ?? $this->slug,
            'summary' => optional($t)->summary,
            'prefecture' => [
                'id' => optional($this->prefecture)->id,
                'name_ja' => optional($this->prefecture)->name_ja,
                'name_en' => optional($this->prefecture)->name_en,
            ],
            'city' => $this->city,
            'lat'  => $this->lat,
            'lng'  => $this->lng,
            'built_year'        => $this->built_year,
            'abolished_year'    => $this->abolished_year,
            'castle_structure'  => $this->castle_structure,
            'tenshu_structure'  => $this->tenshu_structure,
            'founder'           => $this->founder,
            'main_renovators'   => $this->main_renovators,
            'main_lords'        => $this->main_lords,
            'designated_heritage' => $this->designated_heritage,
            'remains'           => $this->remains,
            'official_url' => $this->official_url,
            'rating'            => $this->rating,
            'is_top100'         => (bool) $this->is_top100,
            'is_top100_continued' => (bool) $this->is_top100_continued,
            'tags' => $this->when(
                $this->relationLoaded('tags'),
                fn() => $this->tags->map(fn($tag) => ['name' => $tag->name, 'slug' => $tag->slug])->values(),
                []
            ),
            'cover_photo' => $cover,
            'photos'      => $photoItems,
            'meta'        => $this->meta ?? [],
        ];
    }
}
