<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CulturalSiteResource extends JsonResource
{
    public function toArray($request)
    {
        $t = $this->translations->first();
        $cover = null;
        if (method_exists($this, 'getFirstMedia')) {
            $m = $this->getFirstMedia('photos');
            if ($m) {
                $cover = [
                    'src'    => $m->getUrl('card-webp'),
                    'srcset' => ['webp' => $m->getUrl('thumb-webp').' 240w, '.$m->getUrl('card-webp').' 600w, '.$m->getUrl('cover-webp').' 1200w'],
                    'sizes'  => '(min-width:1024px) 50vw, 100vw',
                    'width'  => 1200, 'height' => 800, 'format' => 'webp',
                ];
            }
        }

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
            'tags' => $this->tags->map(fn($t) => ['name' => $t->name, 'slug' => $t->slug])->values(),
            'cover_photo' => $cover,
        ];
    }
}
