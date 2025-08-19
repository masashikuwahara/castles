<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Place, Tag, Prefecture};

class CulturalPlaceSeeder extends Seeder
{
    public function run(): void
    {
        $pref = Prefecture::where('name_ja','like','%青森%')->first();
        $p = Place::updateOrCreate(
            ['slug' => 'sannai-maruyama'],
            [
                'type'=>'cultural','prefecture_id'=>$pref->id ?? 1,'city'=>'青森市',
                'lat'=>40.8222,'lng'=>140.6940,
                'designated_heritage'=>'特別史跡','remains'=>'竪穴住居跡・盛土など',
                'rating'=>5,'is_top100'=>false,'is_top100_continued'=>false,
                'castle_structure'=>null,'tenshu_structure'=>null,'founder'=>null,
                'main_renovators'=>null,'main_lords'=>null,'built_year'=>null,'abolished_year'=>null,
            ]
        );

        $p->translations()->updateOrCreate(
            ['locale'=>'ja'], ['name'=>'三内丸山遺跡','summary'=>'縄文時代の大規模集落跡。','slug_localized'=>'sannai-maruyama-site']
        );
        $p->translations()->updateOrCreate(
            ['locale'=>'en'], ['name'=>'Sannai-Maruyama Site','summary'=>'Large Jomon-period settlement.','slug_localized'=>'sannai-maruyama-site']
        );

        $tagIds = Tag::whereIn('slug',['shiseki','joumon','iseki'])->pluck('id');
        $p->tags()->syncWithoutDetaching($tagIds);
    }
}
