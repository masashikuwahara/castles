<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TagResource;
use Illuminate\Http\Request;
use App\Models\Tag;

class TagController extends Controller
{
    public function index()
    {
        return TagResource::collection(Tag::orderBy('name')->get());
    }

    public function cultural(Request $req, string $locale)
    {
        $tags = Tag::query()
            ->whereHas('culturalSites')
            ->withCount(['culturalSites as count'])
            ->orderBy('name')
            ->get()
            ->map(fn($t)=>[
                'name'  => $t->name,
                'slug'  => $t->slug,
                'count' => $t->count,
            ]);

        return response()->json(['data' => $tags]);
    }

}

