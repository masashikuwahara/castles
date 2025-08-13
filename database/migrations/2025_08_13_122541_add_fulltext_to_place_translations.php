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
        Schema::table('place_translations', function (Blueprint $table) {
            $table->fullText(['name', 'summary'], 'fulltext_name_summary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('place_translations', function (Blueprint $table) {
            $table->dropFullText('fulltext_name_summary');
        });
    }
};
