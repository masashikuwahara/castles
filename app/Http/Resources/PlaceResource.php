<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'cover_photo' => $this->whenLoaded('photos', function () {
                $cover = $this->photos->firstWhere('is_cover', true) ?? $this->photos->first();
                return $cover ? new PhotoResource($cover) : null;
            }),
        ];
    }
}
