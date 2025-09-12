<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlaceResource;
use App\Models\Place;
use App\Models\PlaceTranslation;
use Illuminate\Http\Request;
use App\Models\Tag;

class AdminPlaceController extends Controller
{
      public function store(Request $req)
    {
        // 1) 単一の validate に統合（tags も含める）
        $v = $req->validate([
            'type' => 'nullable|in:castle,cultural',
            'slug' => 'required|alpha_dash|unique:places,slug',
            'prefecture_id' => 'required|exists:prefectures,id',
            'city' => 'nullable|string',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'built_year' => 'nullable|integer',
            'abolished_year' => 'nullable|integer',
            'castle_structure' => 'nullable|string',
            'tenshu_structure' => 'nullable|string',
            'founder' => 'nullable|string',
            'main_renovators' => 'nullable|string',
            'main_lords' => 'nullable|string',
            'designated_heritage' => 'nullable|string',
            'remains' => 'nullable|string',
            'rating' => 'nullable|integer|min:0|max:5',
            'is_top100' => 'boolean',
            'is_top100_continued' => 'boolean',
            'meta' => 'nullable|array',

            // 翻訳
            't_ja.name' => 'required|string',
            't_ja.slug_localized' => 'nullable|string',
            't_ja.summary' => 'nullable|string',
            't_ja.castle_structure_text' => 'nullable|string',
            't_ja.tenshu_structure_text' => 'nullable|string',
            't_ja.designated_heritage_text' => 'nullable|string',
            't_ja.remains_text' => 'nullable|string',

            't_en.name' => 'nullable|string',
            't_en.slug_localized' => 'nullable|string',
            't_en.summary' => 'nullable|string',
            't_en.castle_structure_text' => 'nullable|string',
            't_en.tenshu_structure_text' => 'nullable|string',
            't_en.designated_heritage_text' => 'nullable|string',
            't_en.remains_text' => 'nullable|string',

            // タグ（slugの配列）
            'tags'   => ['array'],
            'tags.*' => ['string','distinct'],
        ]);

        // 2) Place を先に作成
        $place = Place::create([
            'type' => $req->input('type', 'castle'),
            'slug' => $v['slug'],
            'prefecture_id' => $v['prefecture_id'],
            'city' => $v['city'] ?? null,
            'lat'  => $v['lat'] ?? null,
            'lng'  => $v['lng'] ?? null,
            'built_year' => $v['built_year'] ?? null,
            'abolished_year' => $v['abolished_year'] ?? null,
            'castle_structure' => $v['castle_structure'] ?? null,
            'tenshu_structure' => $v['tenshu_structure'] ?? null,
            'founder' => $v['founder'] ?? null,
            'main_renovators' => $v['main_renovators'] ?? null,
            'main_lords' => $v['main_lords'] ?? null,
            'designated_heritage' => $v['designated_heritage'] ?? null,
            'remains' => $v['remains'] ?? null,
            'rating' => $v['rating'] ?? 0,
            'meta' => $v['meta'] ?? null,
            'is_top100' => (bool)$req->boolean('is_top100'),
            'is_top100_continued' => (bool)$req->boolean('is_top100_continued'),
        ]);

        // 3) 翻訳：ja
        $ja = $v['t_ja'];
        PlaceTranslation::create([
            'place_id' => $place->id,
            'locale'   => 'ja',
            'name'     => $ja['name'],
            'slug_localized' => $ja['slug_localized'] ?: $place->slug,
            'summary'  => $ja['summary'] ?? null,
            'castle_structure_text' => $ja['castle_structure_text'] ?? null,
            'tenshu_structure_text' => $ja['tenshu_structure_text'] ?? null,
            'designated_heritage_text' => $ja['designated_heritage_text'] ?? null,
            'remains_text' => $ja['remains_text'] ?? null,
        ]);

        // 翻訳：en（任意）
        if (!empty($v['t_en']['name'])) {
            $en = $v['t_en'];
            PlaceTranslation::create([
                'place_id' => $place->id,
                'locale'   => 'en',
                'name'     => $en['name'],
                'slug_localized' => $en['slug_localized'] ?: ($place->slug.'-castle'),
                'summary'  => $en['summary'] ?? null,
                'castle_structure_text' => $en['castle_structure_text'] ?? null,
                'tenshu_structure_text' => $en['tenshu_structure_text'] ?? null,
                'designated_heritage_text' => $en['designated_heritage_text'] ?? null,
                'remains_text' => $en['remains_text'] ?? null,
            ]);
        }

        // 4) タグ同期（Place 作成後）
        $slugs = collect($v['tags'] ?? [])->filter()->unique();
        if ($slugs->isNotEmpty()) {
            $found = Tag::whereIn('slug', $slugs)->get()->keyBy('slug');
            $missing = $slugs->diff($found->keys());
            foreach ($missing as $slug) {
                $found[$slug] = Tag::create(['slug' => $slug, 'name' => $slug]);
            }
            $place->tags()->sync($found->pluck('id'));
        }

        // 応答
        return (new PlaceResource(
            $place->load(['translations','prefecture','tags','media'])
        ))->response()->setStatusCode(201);
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
