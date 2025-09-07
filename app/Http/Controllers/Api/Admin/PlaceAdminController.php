<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlaceResource;
use App\Models\Place;
use App\Models\PlaceTranslation;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlaceAdminController extends Controller
{
    public function store(Request $req, string $locale)
    {
        $data = $req->validate([
            // 本体
            'slug' => ['required','string','max:255','unique:places,slug'],
            'prefecture_id' => ['required','exists:prefectures,id'],
            'city' => ['nullable','string','max:255'],
            'lat'  => ['nullable','numeric'],
            'lng'  => ['nullable','numeric'],

            // 城向け属性
            'built_year' => ['nullable','integer'],
            'abolished_year' => ['nullable','integer'],
            'castle_structure' => ['nullable','string','max:255'],
            'tenshu_structure' => ['nullable','string','max:255'],
            'founder' => ['nullable','string','max:255'],
            'main_renovators' => ['nullable','string','max:255'],
            'main_lords' => ['nullable','string','max:255'],
            'designated_heritage' => ['nullable','string','max:255'],
            'remains' => ['nullable','string','max:255'],
            'rating' => ['nullable','integer','min:1','max:5'],
            'is_top100' => ['boolean'],
            'is_top100_continued' => ['boolean'],

            // 翻訳（ja/en の name/summary/slug_localized）
            'translations' => ['required','array'],
            'translations.ja.name' => ['required','string','max:255'],
            'translations.ja.summary' => ['nullable','string'],
            'translations.ja.slug_localized' => ['nullable','string','max:255',
                Rule::unique('place_translations','slug_localized')],
            'translations.en.name' => ['nullable','string','max:255'],
            'translations.en.summary' => ['nullable','string'],
            'translations.en.slug_localized' => ['nullable','string','max:255',
                Rule::unique('place_translations','slug_localized')],

            // タグ
            'tags' => ['array'],
            'tags.*' => ['string'], // slug で受ける

            // 画像（任意）
            'photos' => ['array'],
            'photos.*' => ['file','image','max:8192'], // 8MB
            'cover_index' => ['nullable','integer','min:0'],
            'captions_ja' => ['array'],
            'captions_en' => ['array'],
        ]);

        $place = Place::create(array_merge($data, ['type' => 'castle']));

        // translations
        foreach (['ja','en'] as $loc) {
            if (!empty($data['translations'][$loc])) {
                PlaceTranslation::create([
                    'place_id' => $place->id,
                    'locale' => $loc,
                    'name' => $data['translations'][$loc]['name'] ?? $place->slug,
                    'summary' => $data['translations'][$loc]['summary'] ?? null,
                    'slug_localized' => $data['translations'][$loc]['slug_localized'] ?? null,
                ]);
            }
        }

        // タグ
        if (!empty($data['tags'])) {
            $tagIds = Tag::whereIn('slug',$data['tags'])->pluck('id')->all();
            $place->tags()->sync($tagIds);
        }

        // 画像
        if ($req->hasFile('photos')) {
            $files = $req->file('photos');
            foreach ($files as $i => $file) {
                $media = $place->addMedia($file)
                    ->withCustomProperties([
                        'caption_ja' => $req->input("captions_ja.$i"),
                        'caption_en' => $req->input("captions_en.$i"),
                        'is_cover'   => (int)$req->input('cover_index') === $i,
                    ])
                    ->toMediaCollection('photos');
            }
        }

        // 応答
        $place->load([
            'translations' => fn($q)=>$q->where('locale',$locale),
            'prefecture','tags','media' => fn($m)=>$m->where('collection_name','photos')
        ]);

        return new PlaceResource($place);
    }
}

