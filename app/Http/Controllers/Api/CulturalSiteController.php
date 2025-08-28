<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CulturalSiteResource;
use App\Models\CulturalSite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CulturalSiteController extends Controller
{
    public function index(Request $req, string $locale)
    {
        $q = CulturalSite::query()
            ->with([
                'translations' => fn($t) => $t->where('locale', $locale),
                'prefecture', 
                'tags',
                'media' => fn($m)=>$m->where('collection_name','photos'),
            ]);

        // 検索
        if ($kw = trim((string)$req->query('q'))) {
            $q->whereHas('translations', function (Builder $t) use ($kw, $locale) {
                $t->where('locale', $locale)
                  ->where(function ($w) use ($kw) {
                      $w->whereFullText(['name','summary'], $kw)
                        ->orWhere('name', 'like', "%{$kw}%")
                        ->orWhere('summary', 'like', "%{$kw}%");
                  });
            });
        }

        // フィルタ
        if ($pref = $req->query('pref'))    $q->where('prefecture_id', $pref);
        if ($period = $req->query('period')) $q->where('period', $period);
        if ($type = $req->query('site_type')) $q->where('site_type', $type);
        if ($tags = $req->query('tags')) {
            $slugs = array_filter(explode(',', $tags));
            $q->whereHas('tags', fn($t) => $t->whereIn('slug', $slugs));
        }

        // 並び
        $sort = $req->query('sort', '-rating');
        match ($sort) {
            'rating'   => $q->orderBy('rating'),
            '-rating'  => $q->orderByDesc('rating'),
            default    => $q->latest('id'),
        };

        // ★ タグ（カンマ区切り / AND で絞る）
        if ($tagsStr = $req->query('tags')) {
            $slugs = collect(explode(',', $tagsStr))->filter()->unique()->values();
            foreach ($slugs as $slug) {
                $q->whereHas('tags', fn($t)=>$t->where('slug', $slug));
            }
        }

        return CulturalSiteResource::collection(
            $q->paginate(24)->appends($req->query())
        );
    }

    public function show(Request $req, string $locale, string $slug)
    {
        // まず翻訳slug_localized で、無ければベースslug で
        $site = CulturalSite::query()
            ->with([
                'translations' => fn($t) => $t->where('locale', $locale),
                'prefecture', 'tags'
            ])
            ->whereHas('translations', fn($t) => $t->where('locale', $locale)->where('slug_localized', $slug))
            ->first()
            ?? CulturalSite::with(['translations' => fn($t) => $t->where('locale',$locale),'prefecture','tags'])
                ->where('slug', $slug)->first();

        abort_if(!$site, 404);

        return CulturalSiteResource::make($site);
    }
}
