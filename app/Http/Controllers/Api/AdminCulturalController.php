<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlaceResource;
use App\Models\Place;
use App\Models\PlaceTranslation;
use Illuminate\Http\Request;

class AdminCulturalController extends Controller
{
    public function store(Request $req)
    {
        $v = $req->validate([
          'slug' => 'required|alpha_dash|unique:places,slug',
          'prefecture_id' => 'required|exists:prefectures,id',
          'city' => 'nullable|string',
          'type' => 'in:castle|cultural', // 運用は castle 前提
          'name_ja' => 'required|string',
          'summary_ja' => 'nullable|string',
          'name_en' => 'nullable|string',
          'summary_en' => 'nullable|string',
        ]);

        $place = Place::create([
          'type' => $req->input('type','castle'),
          'slug' => $v['slug'],
          'prefecture_id' => $v['prefecture_id'],
          'city' => $v['city'] ?? null,
        ]);

        // 翻訳
        PlaceTranslation::create([
          'place_id' => $place->id,'locale'=>'ja',
          'name'=>$v['name_ja'],'summary'=>$v['summary_ja'] ?? null,
          'slug_localized'=>$v['slug'], // ja は同じ slug でOK
        ]);
        if ($req->filled('name_en')) {
          PlaceTranslation::create([
            'place_id'=>$place->id,'locale'=>'en',
            'name'=>$v['name_en'],'summary'=>$v['summary_en'] ?? null,
            'slug_localized'=>$place->slug.'-en',
          ]);
        }

        return (new PlaceResource($place->load(['translations','prefecture','tags','media'])))->response();
    }

    public function addPhoto(Request $req, Place $place)
    {
        $v = $req->validate([
          'file' => 'required|image',
          'caption_ja' => 'nullable|string',
          'caption_en' => 'nullable|string',
          'is_cover'   => 'nullable|boolean',
        ]);

        $media = $place->addMediaFromRequest('file')
          ->usingFileName(time().'_'.$req->file('file')->getClientOriginalName())
          ->withCustomProperties([
            'caption_ja' => $v['caption_ja'] ?? null,
            'caption_en' => $v['caption_en'] ?? null,
            'is_cover'   => (bool)($v['is_cover'] ?? false),
          ])->toMediaCollection('photos');

        return response()->json(['id'=>$media->id], 201);
    }
}
