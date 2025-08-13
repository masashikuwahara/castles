<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prefecture extends Model
{
    protected $fillable = ['name_ja','name_en','code'];

    public function places()
    {
        return $this->hasMany(Place::class);
    }
}

