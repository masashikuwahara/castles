<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlaceResource;
use App\Models\Place;
use App\Models\PlaceTranslation;
use Illuminate\Http\Request;

class PlaceController extends Controller
{
    public function index(string $locale, Request $req)
    {
        $q = Place::query()
            ->with([
                'translations' => fn($t)=>$t->where('locale', $locale),
                'prefecture',
                'tags',
                'photos' => fn($p)=>$p->where('is_cover', true),
            ])
            ->when($req->type, fn($x,$v)=>$x->where('type',$v)) // castle|cultural_property
            ->when($req->boolean('top100'), fn($x)=>$x->where('is_top100',true))
            ->when($req->boolean('top100c'), fn($x)=>$x->where('is_top100_continued',true))
            ->when($req->boolean('others'), fn($x) =>$x->where('type','castle')->where('is_top100', false)->where('is_top100_continued', false))
            ->when($req->pref, fn($x,$v)=>$x->where('prefecture_id',$v))
            ->when($req->tags, function($x,$v){
                $tags = array_filter(explode(',', $v));
                $x->whereHas('tags', fn($q)=>$q->whereIn('slug',$tags)->orWhereIn('name',$tags));
            })
            ->when($req->q, function($x,$v) use ($locale) {
                // PlaceTranslation に FULLTEXT を貼ると高速化可（後述）
                $x->whereHas('translations', function($t) use ($v,$locale){
                    $t->where('locale',$locale)
                      ->where(function($w) use ($v){
                          $w->where('name','like',"%{$v}%")
                            ->orWhere('summary','like',"%{$v}%");
                      });
                });
            });

        // ソート：人気順/新着/名前
        $sort = $req->get('sort','rating_desc');
        match ($sort) {
            'name_asc'   => $q->orderBy(PlaceTranslation::select('name')->whereColumn('place_id','places.id')->where('locale',$locale)),
            'created_desc' => $q->orderBy('places.created_at','desc'),
            default      => $q->orderBy('rating','desc'),
        };

        return PlaceResource::collection($q->paginate(24));
    }

    public function show(string $locale, string $slug)
    {
        // 1) 言語別スラッグ一致を優先
        $pt = PlaceTranslation::where('locale',$locale)->where('slug_localized',$slug)->first();
        $place = $pt?->place()->with([
            'translations'=>fn($t)=>$t->where('locale',$locale),
            'prefecture','tags','photos'
        ])->first();

        // 2) 見つからなければ共通 slug で検索
        if (!$place) {
            $place = Place::where('slug',$slug)->with([
                'translations'=>fn($t)=>$t->where('locale',$locale),
                'prefecture','tags','photos'
            ])->firstOrFail();
        }

        return new PlaceResource($place);
    }
}