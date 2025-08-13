<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PhotoResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'path' => $this->path,
            'caption_ja' => $this->caption_ja,
            'caption_en' => $this->caption_en,
            'shot_at' => $this->shot_at,
            'is_cover' => (bool) $this->is_cover,
        ];
    }
}