<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CulturalSiteResource;
use App\Models\CulturalSite;
use App\Models\CulturalSiteTranslation;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CulturalSiteAdminController extends Controller
{
    public function store(Request $req, string $locale)
    {
        $data = $req->validate([
            'slug' => ['required','string','max:255','unique:cultural_sites,slug'],
            'prefecture_id' => ['required','exists:prefectures,id'],
            'city' => ['nullable','string','max:255'],
            'lat'  => ['nullable','numeric'],
            'lng'  => ['nullable','numeric'],
            'designated_heritage' => ['nullable','string','max:255'],
            'site_type' => ['nullable','string','max:255'],
            'period' => ['nullable','string','max:255'],
            'rating' => ['nullable','integer','min:1','max:5'],
            'managing_agency' => ['nullable','string','max:255'],
            'official_url' => ['nullable','string','max:255'],
            'meta' => ['nullable','array'],

            'translations' => ['required','array'],
            'translations.ja.name' => ['required','string','max:255'],
            'translations.ja.summary' => ['nullable','string'],
            'translations.ja.slug_localized' => ['nullable','string','max:255',
                Rule::unique('cultural_site_translations','slug_localized')],
            'translations.en.name' => ['nullable','string','max:255'],
            'translations.en.summary' => ['nullable','string'],
            'translations.en.slug_localized' => ['nullable','string','max:255',
                Rule::unique('cultural_site_translations','slug_localized')],

            'tags' => ['array'],
            'tags.*' => ['string'],

            'photos' => ['array'],
            'photos.*' => ['file','image','max:8192'],
            'cover_index' => ['nullable','integer','min:0'],
            'captions_ja' => ['array'],
            'captions_en' => ['array'],
        ]);

        $site = CulturalSite::create($data);

        foreach (['ja','en'] as $loc) {
            if (!empty($data['translations'][$loc])) {
                CulturalSiteTranslation::create([
                    'cultural_site_id' => $site->id,
                    'locale' => $loc,
                    'name' => $data['translations'][$loc]['name'],
                    'summary' => $data['translations'][$loc]['summary'] ?? null,
                    'slug_localized' => $data['translations'][$loc]['slug_localized'] ?? null,
                ]);
            }
        }

        if (!empty($data['tags'])) {
            $tagIds = Tag::whereIn('slug',$data['tags'])->pluck('id')->all();
            $site->tags()->sync($tagIds);
        }

        if ($req->hasFile('photos')) {
            foreach ($req->file('photos') as $i => $file) {
                $site->addMedia($file)
                    ->withCustomProperties([
                        'caption_ja' => $req->input("captions_ja.$i"),
                        'caption_en' => $req->input("captions_en.$i"),
                        'is_cover'   => (int)$req->input('cover_index') === $i,
                    ])
                    ->toMediaCollection('photos');
            }
        }

        $site->load([
            'translations' => fn($q)=>$q->where('locale',$locale),
            'prefecture','tags','media' => fn($m)=>$m->where('collection_name','photos')
        ]);

        return new CulturalSiteResource($site);
    }
}
