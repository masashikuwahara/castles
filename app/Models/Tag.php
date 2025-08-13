<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['name','slug'];

    public function places()
    {
        return $this->morphedByMany(Place::class, 'taggable');
    }
}

