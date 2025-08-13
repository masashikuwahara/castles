<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PrefectureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name_ja'=>'長野県','name_en'=>'Nagano','code'=>'NAG'],
            ['name_ja'=>'兵庫県','name_en'=>'Hyogo','code'=>'HYG'],
            ['name_ja'=>'愛知県','name_en'=>'Aichi','code'=>'AIC'],
            ['name_ja'=>'岐阜県','name_en'=>'Gifu','code'=>'GIF'],
            ['name_ja'=>'青森県','name_en'=>'Aomori','code'=>'AOM'],
            // 必要に応じて増やします（本番は47件）
        ];
        \App\Models\Prefecture::upsert($data,['code'],['name_ja','name_en']);
    }
}
