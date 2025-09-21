<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PrefectureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $rows = [
            ['id'=>1,  'name_ja'=>'北海道',   'name_en'=>'Hokkaido',  'code'=>'HOK', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>2,  'name_ja'=>'青森県',   'name_en'=>'Aomori',    'code'=>'AOM', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>3,  'name_ja'=>'岩手県',   'name_en'=>'Iwate',     'code'=>'IWA', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>4,  'name_ja'=>'宮城県',   'name_en'=>'Miyagi',    'code'=>'MYG', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>5,  'name_ja'=>'秋田県',   'name_en'=>'Akita',     'code'=>'AKI', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>6,  'name_ja'=>'山形県',   'name_en'=>'Yamagata',  'code'=>'YGT', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>7,  'name_ja'=>'福島県',   'name_en'=>'Fukushima', 'code'=>'FKS', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>8,  'name_ja'=>'茨城県',   'name_en'=>'Ibaraki',   'code'=>'IBR', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>9,  'name_ja'=>'栃木県',   'name_en'=>'Tochigi',   'code'=>'TCG', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>10, 'name_ja'=>'群馬県',   'name_en'=>'Gunma',     'code'=>'GNM', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>11, 'name_ja'=>'埼玉県',   'name_en'=>'Saitama',   'code'=>'STM', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>12, 'name_ja'=>'千葉県',   'name_en'=>'Chiba',     'code'=>'CHB', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>13, 'name_ja'=>'東京都',   'name_en'=>'Tokyo',     'code'=>'TKY', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>14, 'name_ja'=>'神奈川県', 'name_en'=>'Kanagawa',  'code'=>'KNG', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>15, 'name_ja'=>'新潟県',   'name_en'=>'Niigata',   'code'=>'NGT', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>16, 'name_ja'=>'富山県',   'name_en'=>'Toyama',    'code'=>'TYM', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>17, 'name_ja'=>'石川県',   'name_en'=>'Ishikawa',  'code'=>'ISK', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>18, 'name_ja'=>'福井県',   'name_en'=>'Fukui',     'code'=>'FKI', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>19, 'name_ja'=>'山梨県',   'name_en'=>'Yamanashi', 'code'=>'YMN', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>20, 'name_ja'=>'長野県',   'name_en'=>'Nagano',    'code'=>'NGN', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>21, 'name_ja'=>'岐阜県',   'name_en'=>'Gifu',      'code'=>'GIF', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>22, 'name_ja'=>'静岡県',   'name_en'=>'Shizuoka',  'code'=>'SZK', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>23, 'name_ja'=>'愛知県',   'name_en'=>'Aichi',     'code'=>'AIC', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>24, 'name_ja'=>'三重県',   'name_en'=>'Mie',       'code'=>'MIE', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>25, 'name_ja'=>'滋賀県',   'name_en'=>'Shiga',     'code'=>'SHG', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>26, 'name_ja'=>'京都府',   'name_en'=>'Kyoto',     'code'=>'KYO', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>27, 'name_ja'=>'大阪府',   'name_en'=>'Osaka',     'code'=>'OSK', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>28, 'name_ja'=>'兵庫県',   'name_en'=>'Hyogo',     'code'=>'HYG', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>29, 'name_ja'=>'奈良県',   'name_en'=>'Nara',      'code'=>'NAR', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>30, 'name_ja'=>'和歌山県', 'name_en'=>'Wakayama',  'code'=>'WKY', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>31, 'name_ja'=>'鳥取県',   'name_en'=>'Tottori',   'code'=>'TTR', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>32, 'name_ja'=>'島根県',   'name_en'=>'Shimane',   'code'=>'SMN', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>33, 'name_ja'=>'岡山県',   'name_en'=>'Okayama',   'code'=>'OKY', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>34, 'name_ja'=>'広島県',   'name_en'=>'Hiroshima', 'code'=>'HRS', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>35, 'name_ja'=>'山口県',   'name_en'=>'Yamaguchi', 'code'=>'YMG', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>36, 'name_ja'=>'徳島県',   'name_en'=>'Tokushima', 'code'=>'TKS', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>37, 'name_ja'=>'香川県',   'name_en'=>'Kagawa',    'code'=>'KAG', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>38, 'name_ja'=>'愛媛県',   'name_en'=>'Ehime',     'code'=>'EHM', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>39, 'name_ja'=>'高知県',   'name_en'=>'Kochi',     'code'=>'KOC', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>40, 'name_ja'=>'福岡県',   'name_en'=>'Fukuoka',   'code'=>'FKO', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>41, 'name_ja'=>'佐賀県',   'name_en'=>'Saga',      'code'=>'SAG', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>42, 'name_ja'=>'長崎県',   'name_en'=>'Nagasaki',  'code'=>'NGS', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>43, 'name_ja'=>'熊本県',   'name_en'=>'Kumamoto',  'code'=>'KUM', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>44, 'name_ja'=>'大分県',   'name_en'=>'Oita',      'code'=>'OIT', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>45, 'name_ja'=>'宮崎県',   'name_en'=>'Miyazaki',  'code'=>'MYZ', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>46, 'name_ja'=>'鹿児島県', 'name_en'=>'Kagoshima', 'code'=>'KGS', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>47, 'name_ja'=>'沖縄県',   'name_en'=>'Okinawa',   'code'=>'OKN', 'created_at'=>$now, 'updated_at'=>$now],
        ];

        DB::table('prefectures')->insert($rows);
    }
}
