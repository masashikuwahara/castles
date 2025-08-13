<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            ['name'=>'山城','slug'=>'yamajiro'],
            ['name'=>'平山城','slug'=>'hirayamajiro'],
            ['name'=>'平城','slug'=>'hirajiro'],
            ['name'=>'入母屋破風','slug'=>'irimoya-hafu'],
            ['name'=>'現存天守','slug'=>'genzon-tenshu'],
            ['name'=>'石垣','slug'=>'ishigaki'],
            ['name'=>'堀','slug'=>'hori'],
            ['name'=>'国宝','slug'=>'kokuhou'],
            ['name'=>'重要文化財','slug'=>'juyou-bunkazai'],
        ];
        \App\Models\Tag::upsert($tags,['slug'],['name']);
    }
}
