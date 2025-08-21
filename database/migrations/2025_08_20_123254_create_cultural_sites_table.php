<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cultural_sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prefecture_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('slug')->unique();                 // ベースslug（英数）
            $table->string('city')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();

            // 文化財向けフィールド（必要に応じて増やせます）
            $table->string('designated_heritage')->nullable(); // 例：国指定史跡/特別史跡
            $table->string('site_type')->nullable();           // 例：貝塚/古墳/寺院跡…
            $table->string('period')->nullable();              // 例：縄文/弥生/古墳…（将来enumでもOK）
            $table->unsignedTinyInteger('rating')->nullable(); // おすすめ度
            $table->string('managing_agency')->nullable();     // 管理主体（任意）
            $table->string('official_url')->nullable();        // 公式サイトURL（任意）
            $table->json('meta')->nullable();                  // 予備

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cultural_sites');
    }
};
