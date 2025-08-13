<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    protected $fillable = ['place_id','path','caption_ja','caption_en','shot_at','is_cover'];

    public function place()
    {
        return $this->belongsTo(Place::class);
    }
}

