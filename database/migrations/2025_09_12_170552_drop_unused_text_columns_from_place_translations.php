<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('place_translations', function (Blueprint $table) {
            $table->dropColumn([
                'castle_structure_text',
                'tenshu_structure_text',
                'designated_heritage_text',
                'remains_text',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('place_translations', function (Blueprint $table) {
            $table->text('castle_structure_text')->nullable();
            $table->text('tenshu_structure_text')->nullable();
            $table->text('designated_heritage_text')->nullable();
            $table->text('remains_text')->nullable();
        });
    }
};

