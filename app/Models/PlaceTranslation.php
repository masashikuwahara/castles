<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaceTranslation extends Model
{
    protected $fillable = [
        'place_id','locale','name','slug_localized','summary',
        'castle_structure_text','tenshu_structure_text',
        'designated_heritage_text','remains_text'
    ];

    public function place()
    {
        return $this->belongsTo(Place::class);
    }
}

