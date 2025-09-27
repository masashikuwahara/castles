<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CulturalSiteResource;
use App\Models\CulturalSite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CulturalSiteController extends Controller
{
    // public function index(Request $req, string $locale)
    // {
    //     $q = CulturalSite::query()
    //         ->with([
    //             'translations' => fn($t) => $t->where('locale', $locale),
    //             'prefecture', 
    //             'tags',
    //             'media' => fn($m)=>$m->where('collection_name','photos'),
    //         ]);

    //     if ($kw = trim((string)$req->query('q'))) {
    //         $q->whereHas('translations', function (Builder $t) use ($kw, $locale) {
    //             $t->where('locale', $locale)
    //               ->where(function ($w) use ($kw) {
    //                   $w->whereFullText(['name','summary'], $kw)
    //                     ->orWhere('name', 'like', "%{$kw}%")
    //                     ->orWhere('summary', 'like', "%{$kw}%");
    //               });
    //         });
    //     }

    //     // フィルタ
    //     if ($pref = $req->query('pref'))    $q->where('prefecture_id', $pref);
    //     if ($period = $req->query('period')) $q->where('period', $period);
    //     if ($type = $req->query('site_type')) $q->where('site_type', $type);
    //     if ($tags = $req->query('tags')) {
    //         $slugs = array_filter(explode(',', $tags));
    //         $q->whereHas('tags', fn($t) => $t->whereIn('slug', $slugs));
    //     }

    //     $sort = $req->query('sort', '-rating');
    //     match ($sort) {
    //         'rating'   => $q->orderBy('rating'),
    //         '-rating'  => $q->orderByDesc('rating'),
    //         default    => $q->latest('id'),
    //     };

    //     if ($tagsStr = $req->query('tags')) {
    //         $slugs = collect(explode(',', $tagsStr))->filter()->unique()->values();
    //         foreach ($slugs as $slug) {
    //             $q->whereHas('tags', fn($t)=>$t->where('slug', $slug));
    //         }
    //     }
        
    //     $sort = $req->query('sort', 'recommended');
    //     switch ($sort) {
    //         case 'name':
    //             $q->leftJoin('cultural_site_translations as tr', function($j) use ($locale){
    //                 $j->on('tr.cultural_site_id', '=', 'cultural_sites.id')
    //                 ->where('tr.locale', $locale);
    //             })
    //             ->select('cultural_sites.*')
    //             ->orderBy('tr.name');
    //             break;
    //         case 'new':
    //             $q->orderByDesc('cultural_sites.created_at')->orderByDesc('cultural_sites.id');
    //             break;
    //         case 'recommended':
    //         default:
    //             $q->orderByRaw('rating IS NULL')->orderByDesc('rating')->orderBy('cultural_sites.id');
    //             break;
    //     }

    //     return CulturalSiteResource::collection(
    //         $q->paginate(24)->appends($req->query())
    //     );
    // }

    public function index(Request $req, string $locale)
    {
        $q = CulturalSite::query()
            ->with([
                'translations' => fn($t) => $t->where('locale', $locale),
                'prefecture','tags',
                'media' => fn($m)=>$m->where('collection_name','photos'),
            ]);

        // 検索
        if ($kw = trim((string)$req->query('q'))) {
            $q->whereHas('translations', function (Builder $t) use ($kw, $locale) {
                $t->where('locale', $locale)
                ->where(function ($w) use ($kw) {
                    $w->where('name', 'like', "%{$kw}%")
                        ->orWhere('summary', 'like', "%{$kw}%");
                });
            });
        }

        if ($pref = $req->query('pref'))      $q->where('prefecture_id', $pref);
        if ($period = $req->query('period'))  $q->where('period', $period);
        if ($type = $req->query('site_type')) $q->where('site_type', $type);

        if ($tagsStr = $req->query('tags')) {
            foreach (collect(explode(',', $tagsStr))->filter() as $slug) {
                $q->whereHas('tags', fn($t)=>$t->where('slug', $slug));
            }
        }

        $sort = $req->query('sort', 'recommended');
        switch ($sort) {
            case 'name':
                $q->leftJoin('cultural_site_translations as tr', function($j) use ($locale){
                    $j->on('tr.cultural_site_id', '=', 'cultural_sites.id')
                    ->where('tr.locale', $locale);
                })
                ->select('cultural_sites.*')
                ->distinct('cultural_sites.id')
                ->orderBy('tr.name')
                ->orderBy('cultural_sites.id');
                break;

            case 'new':
                $q->orderByDesc('cultural_sites.created_at')
                ->orderByDesc('cultural_sites.id');
                break;

            case 'recommended':
            default:
                $q->orderByRaw('cultural_sites.rating IS NULL')
                ->orderByDesc('cultural_sites.rating')
                ->orderByDesc('cultural_sites.updated_at')
                ->orderBy('cultural_sites.id');
                break;
        }

        return CulturalSiteResource::collection(
            $q->paginate(24)->withQueryString()
        );
    }

    public function show(Request $req, string $locale, string $slug)
    {
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
