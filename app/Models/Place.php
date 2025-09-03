<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Place extends Model implements HasMedia
{
    use InteractsWithMedia;
    protected $fillable = [
        'type','slug','prefecture_id','city','lat','lng',
        'built_year','abolished_year','castle_structure','tenshu_structure',
        'founder','main_renovators','main_lords','designated_heritage','remains',
        'rating','is_top100','is_top100_continued','meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function translations()
    {
        return $this->hasMany(PlaceTranslation::class);
    }
    public function prefecture()
    {
        return $this->belongsTo(Prefecture::class);
    }
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        if ($media instanceof Media) { }

        // サーバがWebPを作れるならwebp、無理ならjpgに自動フォールバック
        $format = function_exists('imagewebp') ? 'webp' : 'jpg';

        $this->addMediaConversion('thumb-webp')
            ->format($format)->width(240)->nonQueued();

        $this->addMediaConversion('card-webp')
            ->format($format)->width(600)->nonQueued();

        $this->addMediaConversion('cover-webp')
            ->format($format)->width(1200)->nonQueued();
    }
}

