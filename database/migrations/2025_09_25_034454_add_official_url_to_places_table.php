<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('places', function (Blueprint $t) {
      $t->string('official_url')->nullable()->after('remains'); // 位置は任意
    });
  }
  
  public function down(): void {
    Schema::table('places', function (Blueprint $t) {
      $t->dropColumn('official_url');
    });
  }
};
