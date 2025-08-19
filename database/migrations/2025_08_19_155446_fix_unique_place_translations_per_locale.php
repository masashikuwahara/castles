<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('place_translations', function (Blueprint $table) {
            // 旧: slug_localized 単独ユニークを削除
            // インデックス名は Laravel 既定: place_translations_slug_localized_unique
            $table->dropUnique('place_translations_slug_localized_unique');

            // 新: (locale, slug_localized) をユニークに
            $table->unique(['locale','slug_localized'], 'pt_locale_slug_unique');

            // ついでに (place_id, locale) もユニークにして重複翻訳を防止（未作成なら）
            $table->unique(['place_id','locale'], 'pt_place_locale_unique');
        });
    }

    public function down(): void
    {
        Schema::table('place_translations', function (Blueprint $table) {
            // 追加した複合ユニークを削除
            $table->dropUnique('pt_locale_slug_unique');
            $table->dropUnique('pt_place_locale_unique');

            // 旧ルールに戻す（slug_localized 単独ユニーク）
            $table->unique('slug_localized', 'place_translations_slug_localized_unique');
        });
    }
};
