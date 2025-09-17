<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CulturalSiteTranslation extends Model
{
    protected $fillable = ['cultural_site_id','locale','name','summary','slug_localized'];

    public function site(): BelongsTo
    {
        return $this->belongsTo(CulturalSite::class, 'cultural_site_id');
    }
}
