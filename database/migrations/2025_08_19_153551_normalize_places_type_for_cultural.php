<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1) いったん可変長に広げる
        DB::statement("ALTER TABLE places MODIFY `type` VARCHAR(20) NOT NULL DEFAULT 'castle'");

        // 2) 不正値を正規化（NULL/空/不明値は 'castle' に寄せる。必要なら map を増やす）
        DB::statement("
            UPDATE places
            SET `type` = 'castle'
            WHERE `type` IS NULL
               OR TRIM(`type`) = ''
               OR TRIM(`type`) NOT IN ('castle','cultural')
        ");

        // 3) ENUM に戻す（'castle','cultural'）
        DB::statement("ALTER TABLE places MODIFY `type` ENUM('castle','cultural') NOT NULL DEFAULT 'castle'");
    }

    public function down(): void
    {
        // 元に戻す（必要に応じて調整）
        DB::statement("ALTER TABLE places MODIFY `type` ENUM('castle') NOT NULL DEFAULT 'castle'");
    }
};
