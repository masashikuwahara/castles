<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    protected $fillable = [
        'type','slug','prefecture_id','city','lat','lng',
        'built_year','abolished_year','castle_structure','tenshu_structure',
        'founder','main_renovators','main_lords','designated_heritage','remains',
        'rating','is_top100','is_top100_continued',
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
}

