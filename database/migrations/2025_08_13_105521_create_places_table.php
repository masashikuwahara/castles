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
        Schema::create('places', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['castle','cultural_property']); // 種別
            $table->string('slug')->unique();                      // 言語に依存しない識別子
            $table->unsignedBigInteger('prefecture_id')->nullable();
            $table->string('city')->nullable();

            $table->decimal('lat',10,7)->nullable();
            $table->decimal('lng',10,7)->nullable();

            $table->smallInteger('built_year')->nullable();
            $table->smallInteger('abolished_year')->nullable();

            // 記号的情報（後でテキスト説明は translations にも持たせます）
            $table->string('castle_structure')->nullable();  // 山城/平山城 など
            $table->string('tenshu_structure')->nullable();  // 入母屋破風 など
            $table->string('founder')->nullable();           // 築城主
            $table->string('main_renovators')->nullable();   // 主な改修者
            $table->string('main_lords')->nullable();        // 主な城主
            $table->string('designated_heritage')->nullable(); // 指定文化財（短い表記）
            $table->string('remains')->nullable();           // 遺構（短い表記）

            $table->tinyInteger('rating')->default(0);       // おすすめ度(1〜5)
            $table->boolean('is_top100')->default(false);
            $table->boolean('is_top100_continued')->default(false);
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('places');
    }
};
