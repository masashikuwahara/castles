<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PlaceController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\CulturalSiteController;
use App\Http\Controllers\Api\Admin\PlaceAdminController;
use App\Http\Controllers\Api\Admin\CulturalSiteAdminController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\AdminPlaceController;
use App\Http\Controllers\Api\AdminCulturalController;

Route::pattern('locale', 'ja|en');
Route::get('/ping', function ($locale) {
    return response()->json(['ok' => true, 'locale' => $locale]);
});

Route::prefix('{locale}')->group(function () {
    Route::get('/places', [PlaceController::class, 'index']);        // 一覧 + 検索/フィルタ
    Route::get('/places/{slug}', [PlaceController::class, 'show']);  // 詳細（slug_localized優先）
    Route::get('/tags', [TagController::class, 'index']);            // タグ一覧
    Route::get('/quiz', [QuizController::class, 'random']);
    Route::get('/cultural-sites', [CulturalSiteController::class, 'index']);
    Route::get('/cultural-sites/{slug}', [CulturalSiteController::class, 'show']);
    Route::get('/cultural-sites/{slug}', [CulturalSiteController::class,'show'])
    ->name('api.cultural-sites.show');
    Route::get('/cultural/tags', [TagController::class, 'cultural']);
});

Route::middleware(['auth:sanctum','can:admin'])->prefix('admin/{locale}')->group(function () {
    // 城
    Route::post('/places', [PlaceAdminController::class, 'store']);     // 新規
    // 必要に応じて：update/delete も後で追加

    // 文化財
    Route::post('/culturals', [CulturalSiteAdminController::class, 'store']);

    // マスタ用
    Route::get('/tags', fn() => \App\Models\Tag::select('id','name','slug')->orderBy('name')->get());
    Route::get('/prefectures', fn() => \App\Models\Prefecture::select('id','name_ja','name_en')->orderBy('id')->get());
});

Route::post('/login',  [AuthApiController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me',     [AuthApiController::class, 'me']);
    Route::post('/logout',[AuthApiController::class, 'logout']);
    Route::post('/admin/places',   [AdminPlaceController::class, 'store']);
    Route::post('/admin/culturals',[AdminCulturalController::class, 'store']);
    Route::post('/admin/places/{place}/photos',   [AdminPlaceController::class, 'addPhoto']);
    Route::post('/admin/culturals/{site}/photos', [AdminCulturalController::class, 'addPhoto']);
});