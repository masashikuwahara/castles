<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, MorphToMany};
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CulturalSite extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'prefecture_id','slug','city','lat','lng',
        'designated_heritage','site_type','period','rating',
        'managing_agency','official_url','meta',
    ];

    protected $casts = [
        'lat' => 'float', 'lng' => 'float',
        'meta' => 'array',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(CulturalSiteTranslation::class);
    }

    public function prefecture(): BelongsTo
    {
        return $this->belongsTo(Prefecture::class);
    }

    public function tags(): MorphToMany
    {
        // 既存の polymorphic taggables を流用
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
      $canWebp = extension_loaded('imagick') || function_exists('imagewebp');
      $format  = $canWebp ? 'webp' : 'jpg';
      
      $this->addMediaConversion('thumb-webp')
        ->format($format)
        ->width(240)
        ->performOnCollections('photos')
        ->nonQueued();

      $this->addMediaConversion('card-webp')
        ->format($format)
        ->width(600)
        ->performOnCollections('photos')
        ->nonQueued();

      $this->addMediaConversion('cover-webp')
        ->format($format)
        ->width(1200)
        ->performOnCollections('photos')
        ->nonQueued();
    }
}
