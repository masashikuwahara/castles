<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlaceResource;
use App\Models\Place;
use App\Models\PlaceTranslation;
use Illuminate\Http\Request;

class PlaceController extends Controller
{
    public function index(Request $req, string $locale)
{
    $q = \App\Models\Place::query()
        ->with([
            'translations' => fn($t)=>$t->where('locale',$locale),
            'prefecture','tags',
            'media' => fn($m)=>$m->where('collection_name','photos'),
            // 'photos',
        ]);

    // フリーテキスト
    if ($s = trim((string)$req->query('q',''))) {
        $q->whereHas('translations', function($t) use ($s,$locale){
            $t->where('locale',$locale)
              ->where(function($w) use ($s){
                  $w->where('name','like',"%{$s}%")
                    ->orWhere('summary','like',"%{$s}%");
              });
        });
    }

    // 100名城/続100/その他
    if ($req->boolean('top100'))  $q->where('is_top100', true);
    if ($req->boolean('top100c')) $q->where('is_top100_continued', true);
    if ($req->boolean('others'))  $q->where('is_top100', false)->where('is_top100_continued', false);

    // タグ（AND）
    if ($tagsStr = $req->query('tags')) {
        foreach (collect(explode(',', $tagsStr))->filter() as $slug) {
            $q->whereHas('tags', fn($t)=>$t->where('slug',$slug));
        }
    }

    $p = $q->paginate(24)->withQueryString();
    // 念のため null モデルは捨てる（ほぼ出ませんが堅牢化）
    $p->setCollection($p->getCollection()->filter());

    return \App\Http\Resources\PlaceResource::collection($p);
}



    public function show(string $locale, string $slug)
    {
        $place = Place::query()
        ->with([
            'translations' => fn($t) => $t->where('locale',$locale),
            'prefecture','tags',
            'media' => fn($m) => $m->where('collection_name','photos'),
            ])
        ->where(function($q) use ($slug, $locale) {
            $q->whereHas('translations', fn($t)=>$t->where('locale',$locale)->where('slug_localized',$slug))
            ->orWhere('slug', $slug);
        })
        ->firstOrFail();

        return new PlaceResource($place);
    }
}