<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cultural_site_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cultural_site_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 2)->index();
            $table->string('name');
            $table->text('summary')->nullable();
            $table->string('slug_localized')->nullable();

            // 言語ごとに同じslug_localizedが使えるよう複合ユニーク
            $table->unique(['locale', 'slug_localized'], 'cst_locale_slug_unique');
            // 同じ文化財IDに同じ言語の翻訳は1つだけ
            $table->unique(['cultural_site_id', 'locale'], 'cst_site_locale_unique');

            $table->timestamps();
        });

        // （任意）全文検索の下地
        DB::statement('ALTER TABLE cultural_site_translations ADD FULLTEXT fulltext_name_summary (name, summary)');
    }

    public function down(): void
    {
        Schema::dropIfExists('cultural_site_translations');
    }
};
