<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PlaceController;
use App\Http\Controllers\Api\TagController;

Route::pattern('locale', 'ja|en');
Route::get('/ping', function ($locale) {
    return response()->json(['ok' => true, 'locale' => $locale]);
});

Route::prefix('{locale}')->group(function () {
    Route::get('/places', [PlaceController::class, 'index']);        // 一覧 + 検索/フィルタ
    Route::get('/places/{slug}', [PlaceController::class, 'show']);  // 詳細（slug_localized優先）
    Route::get('/tags', [TagController::class, 'index']);            // タグ一覧
});