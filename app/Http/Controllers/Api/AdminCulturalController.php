<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CulturalSiteResource;
use App\Models\CulturalSite;
use App\Models\CulturalSiteTranslation;
use App\Models\Tag;
use Illuminate\Http\Request;

class AdminCulturalController extends Controller
{
    public function store(Request $req)
    {
        $v = $req->validate([
            'slug' => 'required|alpha_dash|unique:cultural_sites,slug',
            'prefecture_id' => 'required|exists:prefectures,id',
            'city' => 'nullable|string',
            'lat'  => 'nullable|numeric',
            'lng'  => 'nullable|numeric',

            'designated_heritage' => 'nullable|string',
            'site_type' => 'nullable|string',
            'period'    => 'nullable|string',
            'rating'    => 'nullable|integer|min:0|max:5',

            'managing_agency' => 'nullable|string',
            'official_url'    => 'nullable|string',
            'meta'            => 'nullable|array',

            // 翻訳
            't_ja.name'           => 'required|string',
            't_ja.slug_localized' => 'nullable|string',
            't_ja.summary'        => 'nullable|string',

            't_en.name'           => 'nullable|string',
            't_en.slug_localized' => 'nullable|string',
            't_en.summary'        => 'nullable|string',

            // タグ
            'tags'   => 'array',
            'tags.*' => 'string|distinct',
        ]);

        // 作成
        $site = CulturalSite::create([
            'prefecture_id' => $v['prefecture_id'],
            'slug' => $v['slug'],
            'city' => $v['city'] ?? null,
            'lat'  => $v['lat'] ?? null,
            'lng'  => $v['lng'] ?? null,

            'designated_heritage' => $v['designated_heritage'] ?? null,
            'site_type' => $v['site_type'] ?? null,
            'period'    => $v['period'] ?? null,
            'rating'    => $v['rating'] ?? 0,

            'managing_agency' => $v['managing_agency'] ?? null,
            'official_url'    => $v['official_url'] ?? null,
            'meta'            => $v['meta'] ?? null,
        ]);

        // 翻訳 JA
        $ja = $v['t_ja'];
        CulturalSiteTranslation::create([
            'cultural_site_id' => $site->id,
            'locale' => 'ja',
            'name'   => $ja['name'],
            'slug_localized' => $ja['slug_localized'] ?: $site->slug,
            'summary' => $ja['summary'] ?? null,
        ]);

        // 翻訳 EN（任意）
        if (!empty($v['t_en']['name'])) {
            $en = $v['t_en'];
            CulturalSiteTranslation::create([
                'cultural_site_id' => $site->id,
                'locale' => 'en',
                'name'   => $en['name'],
                'slug_localized' => $en['slug_localized'] ?: $site->slug,
                'summary' => $en['summary'] ?? null,
            ]);
        }

        // タグ同期（slugベース・なければ作成）
        $slugs = collect($v['tags'] ?? [])->filter()->unique();
        if ($slugs->isNotEmpty()) {
            $found = Tag::whereIn('slug', $slugs)->get()->keyBy('slug');
            $missing = $slugs->diff($found->keys());
            foreach ($missing as $slug) {
                $found[$slug] = Tag::create(['slug' => $slug, 'name' => $slug]);
            }
            $site->tags()->sync($found->pluck('id'));
        }

        return (new CulturalSiteResource(
            $site->load(['translations','prefecture','tags','media' => fn($m)=>$m->where('collection_name','photos')])
        ))->response();
    }

    public function addPhoto(Request $req, CulturalSite $cultural)
    {
        $v = $req->validate([
            'file' => 'required|image',
            'caption_ja' => 'nullable|string',
            'caption_en' => 'nullable|string',
            'is_cover'   => 'nullable|boolean',
        ]);

        $media = $cultural->addMediaFromRequest('file')
            ->usingFileName(time().'_'.$req->file('file')->getClientOriginalName())
            ->withCustomProperties([
                'caption_ja' => $v['caption_ja'] ?? null,
                'caption_en' => $v['caption_en'] ?? null,
                'is_cover'   => (bool)($v['is_cover'] ?? false),
            ])->toMediaCollection('photos');

        return response()->json(['id' => $media->id], 201);
    }
}
