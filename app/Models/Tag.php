<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends Model
{
    protected $fillable = ['name','slug'];

    public function places()
    {
        return $this->morphedByMany(Place::class, 'taggable');
    }

    public function culturalSites(): MorphToMany
    {
        return $this->morphedByMany(\App\Models\CulturalSite::class, 'taggable');
    }
}

