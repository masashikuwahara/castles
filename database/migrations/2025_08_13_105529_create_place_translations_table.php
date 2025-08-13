<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('place_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('place_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 2); // 'ja' | 'en'
            $table->string('name');
            $table->string('slug_localized')->unique(); // 言語別スラッグ → /ja/places/松本城, /en/places/matsumoto-castle など
            $table->text('summary')->nullable(); // 概要
            // 表示向けの詳細文を分けたい場合のフィールド（任意）
            $table->text('castle_structure_text')->nullable();
            $table->text('tenshu_structure_text')->nullable();
            $table->text('designated_heritage_text')->nullable();
            $table->text('remains_text')->nullable();

            $table->timestamps();
            $table->unique(['place_id','locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('place_translations');
    }
};
