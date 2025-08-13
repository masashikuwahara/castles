<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\{Place,PlaceTranslation,Prefecture,Tag,Photo};

class PlaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    $prefNagano = Prefecture::where('name_en','Nagano')->first();
    $prefHyogo  = Prefecture::where('name_en','Hyogo')->first();
    $prefAichi  = Prefecture::where('name_en','Aichi')->first();
    $prefGifu   = Prefecture::where('name_en','Gifu')->first();

    // 1) 松本城
    $matsumoto = Place::create([
        'type'=>'castle','slug'=>'matsumoto',
        'prefecture_id'=>$prefNagano->id,'city'=>'松本市',
        'lat'=>36.2381,'lng'=>137.9707,
        'built_year'=>1504,'castle_structure'=>'平城','tenshu_structure'=>'入母屋破風',
        'founder'=>'小笠原氏','main_renovators'=>null,'main_lords'=>'石川数正など',
        'designated_heritage'=>'国宝','remains'=>'天守・石垣・堀',
        'rating'=>5,'is_top100'=>true
    ]);
    PlaceTranslation::insert([
        [
            'place_id'=>$matsumoto->id,'locale'=>'ja','name'=>'松本城',
            'slug_localized'=>'松本城',
            'summary'=>'日本最古級の五重六階の天守をもつ国宝の城。'
        ],
        [
            'place_id'=>$matsumoto->id,'locale'=>'en','name'=>'Matsumoto Castle',
            'slug_localized'=>'matsumoto-castle',
            'summary'=>'A National Treasure with a striking black keep (donjon).'
        ],
    ]);
    $matsumoto->tags()->attach(Tag::whereIn('slug',['hirajiro','irimoya-hafu','genzon-tenshu','kokuhou','ishigaki','hori'])->pluck('id'));
    Photo::create(['place_id'=>$matsumoto->id,'path'=>'/storage/photos/matsumoto01.webp','is_cover'=>true]);

    // 2) 姫路城
    $himeji = Place::create([
        'type'=>'castle','slug'=>'himeji',
        'prefecture_id'=>$prefHyogo->id,'city'=>'姫路市',
        'lat'=>34.8394,'lng'=>134.6939,
        'built_year'=>1609,'castle_structure'=>'平山城','tenshu_structure'=>'入母屋破風',
        'founder'=>'赤松貞範','main_lords'=>'池田輝政など',
        'designated_heritage'=>'国宝','remains'=>'天守・櫓・石垣・堀',
        'rating'=>5,'is_top100'=>true
    ]);
    PlaceTranslation::insert([
        ['place_id'=>$himeji->id,'locale'=>'ja','name'=>'姫路城','slug_localized'=>'姫路城','summary'=>'白鷺城の名で知られる世界遺産・国宝。'],
        ['place_id'=>$himeji->id,'locale'=>'en','name'=>'Himeji Castle','slug_localized'=>'himeji-castle','summary'=>'World Heritage and National Treasure, famed as the White Heron Castle.'],
    ]);
    $himeji->tags()->attach(Tag::whereIn('slug',['hirayamajiro','irimoya-hafu','kokuhou','ishigaki','hori'])->pluck('id'));
    Photo::create(['place_id'=>$himeji->id,'path'=>'/storage/photos/himeji01.webp','is_cover'=>true]);

    // 3) 犬山城（現存天守）
    $inuyama = Place::create([
        'type'=>'castle','slug'=>'inuyama',
        'prefecture_id'=>$prefAichi->id,'city'=>'犬山市',
        'lat'=>35.3876,'lng'=>136.9396,
        'built_year'=>1537,'castle_structure'=>'平山城','tenshu_structure'=>'入母屋破風',
        'founder'=>'織田信康','main_lords'=>'成瀬氏',
        'designated_heritage'=>'国宝','remains'=>'天守・石垣',
        'rating'=>4,'is_top100'=>true
    ]);
    PlaceTranslation::insert([
        ['place_id'=>$inuyama->id,'locale'=>'ja','name'=>'犬山城','slug_localized'=>'犬山城','summary'=>'木曽川沿いの国宝天守で知られる城。'],
        ['place_id'=>$inuyama->id,'locale'=>'en','name'=>'Inuyama Castle','slug_localized'=>'inuyama-castle','summary'=>'A National Treasure keep overlooking the Kiso River.'],
    ]);
    $inuyama->tags()->attach(Tag::whereIn('slug',['hirayamajiro','irimoya-hafu','genzon-tenshu','kokuhou','ishigaki'])->pluck('id'));

    // 4) 岐阜城（山城）
    $gifu = Place::create([
        'type'=>'castle','slug'=>'gifu',
        'prefecture_id'=>$prefGifu->id,'city'=>'岐阜市',
        'lat'=>35.4339,'lng'=>136.7847,
        'built_year'=>1201,'castle_structure'=>'山城',
        'founder'=>'二階堂行政','main_lords'=>'斎藤道三・織田信長',
        'designated_heritage'=>null,'remains'=>'石垣',
        'rating'=>4,'is_top100'=>true
    ]);
    PlaceTranslation::insert([
        ['place_id'=>$gifu->id,'locale'=>'ja','name'=>'岐阜城','slug_localized'=>'岐阜城','summary'=>'金華山の山頂に築かれた山城。'],
        ['place_id'=>$gifu->id,'locale'=>'en','name'=>'Gifu Castle','slug_localized'=>'gifu-castle','summary'=>'A mountaintop castle on Mt. Kinka.'],
    ]);
    $gifu->tags()->attach(Tag::whereIn('slug',['yamajiro','ishigaki'])->pluck('id'));

    // 5) 文化財（例：犬山城天守を文化財としても登録例）
    // 実際は寺社や史跡など別物を入れる想定
    $cp = Place::create([
        'type'=>'cultural_property','slug'=>'some-cultural-prop',
        'prefecture_id'=>$prefAichi->id,'city'=>'犬山市',
        'rating'=>3
    ]);
    PlaceTranslation::insert([
        ['place_id'=>$cp->id,'locale'=>'ja','name'=>'例：史跡サンプル','slug_localized'=>'史跡サンプル','summary'=>'文化財ページ用のサンプル。'],
        ['place_id'=>$cp->id,'locale'=>'en','name'=>'Sample Cultural Site','slug_localized'=>'sample-cultural-site','summary'=>'Sample record for cultural properties.'],
    ]);
}
}
