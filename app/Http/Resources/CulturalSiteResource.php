<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CulturalSiteResource extends JsonResource
{
    public function toArray($request)
    {
        $t = $this->translations->first();

        // is_cover 優先
        $coverMedia = $this->media()
            ->where('collection_name','photos')
            ->whereJsonContains('custom_properties->is_cover', true)->first()
            ?? $this->getFirstMedia('photos');

        $cover = null;
        if ($coverMedia) {
            $srcset = [];
            if ($coverMedia->hasGeneratedConversion('thumb-webp')) $srcset[] = $coverMedia->getUrl('thumb-webp').' 240w';
            if ($coverMedia->hasGeneratedConversion('card-webp'))  $srcset[] = $coverMedia->getUrl('card-webp').' 600w';
            if ($coverMedia->hasGeneratedConversion('cover-webp')) $srcset[] = $coverMedia->getUrl('cover-webp').' 1200w';

            $cover = [
                'src'        => $coverMedia->hasGeneratedConversion('cover-webp') ? $coverMedia->getUrl('cover-webp') : $coverMedia->getUrl(),
                'srcset'     => ['webp' => $srcset ? implode(', ', $srcset) : null],
                'sizes'      => '(min-width:1024px) 50vw, 100vw',
                'caption_ja' => $coverMedia->getCustomProperty('caption_ja'),
                'caption_en' => $coverMedia->getCustomProperty('caption_en'),
                'is_cover'   => (bool)$coverMedia->getCustomProperty('is_cover'),
                'original'   => $coverMedia->getUrl(),
                'full'       => $coverMedia->hasGeneratedConversion('cover-webp') ? $coverMedia->getUrl('cover-webp') : $coverMedia->getUrl(),
            ];
        }

        // ギャラリー
        $photos = $this->getMedia('photos')->map(function ($m) {
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
            'id' => $this->id,
            'type' => 'cultural',
            'slug' => $this->slug,
            'slug_localized' => $t?->slug_localized ?? $this->slug,
            'name' => $t?->name ?? $this->slug,
            'summary' => $t?->summary,
            'prefecture' => [
                'id' => $this->prefecture?->id,
                'name_ja' => $this->prefecture?->name_ja,
                'name_en' => $this->prefecture?->name_en,
            ],
            'city' => $this->city,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'designated_heritage' => $this->designated_heritage,
            'site_type' => $this->site_type,
            'period' => $this->period,
            'rating' => $this->rating,
            'managing_agency' => $this->managing_agency,
            'official_url' => $this->official_url,
            'meta' => $this->meta,
            'tags' => $this->tags->map(fn($t) => ['name' => $t->name, 'slug' => $t->slug])->values(),
            'cover_photo' => $cover,
            // 'photos'      => $photos,
            'photos' => $this->when(
                $request->routeIs('api.cultural-sites.show'), // ルートに名前を付けておく
                $photos
            ),
        ];
    }
}
