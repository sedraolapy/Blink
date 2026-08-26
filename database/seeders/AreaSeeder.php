<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Governorate;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [

            // Damascus
            'Damascus' => [
                ['ar' => 'استراد الفيحاء', 'en' => 'Al-Fayhaa Highway'],
                ['ar' => 'استراد جديدة', 'en' => 'Jdeideh Highway'],
                ['ar' => 'البرامكة', 'en' => 'Al-Baramkeh'],
                ['ar' => 'الحلبوني', 'en' => 'Al-Halbouni'],
                ['ar' => 'الربوة', 'en' => 'Al-Rabwah'],
                ['ar' => 'الروضة', 'en' => 'Al-Rawda'],
                ['ar' => 'الزاهرة', 'en' => 'Al-Zahira'],
                ['ar' => 'الزبلطاني', 'en' => 'Al-Zablatani'],
                ['ar' => 'السويقة', 'en' => 'Al-Suweika'],
                ['ar' => 'العدوي', 'en' => 'Al-Adawi'],

                ['ar' => 'الفحامة', 'en' => 'Al-Fahhama'],
                ['ar' => 'القصاع', 'en' => 'Al-Qassaa'],
                ['ar' => 'المالكي', 'en' => 'Al-Malki'],
                ['ar' => 'المتحلق', 'en' => 'Al-Mutahalleq'],
                ['ar' => 'المزة', 'en' => 'Al-Mezzeh'],
                ['ar' => 'المزرعة', 'en' => 'Al-Mazraa'],
                ['ar' => 'المنطقة الصناعية', 'en' => 'Industrial Area'],
                ['ar' => 'الميدان', 'en' => 'Al-Midan'],
                ['ar' => 'أبو رمانة', 'en' => 'Abu Rummaneh'],
                ['ar' => 'باب توما', 'en' => 'Bab Touma'],
                ['ar' => 'باب شرقي', 'en' => 'Bab Sharqi'],
                ['ar' => 'جرمانا', 'en' => 'Jaramana'],
                ['ar' => 'جسر الحرية', 'en' => 'Jisr Al-Hurriya'],
                ['ar' => 'دمر البلد', 'en' => 'Dummar Al-Balad'],
                ['ar' => 'ركن الدين', 'en' => 'Rukn Al-Din'],
                ['ar' => 'شارع الثورة', 'en' => 'Al-Thawra Street'],
                ['ar' => 'شارع النصر', 'en' => 'Al-Nasr Street'],

                ['ar' => 'شارع بغداد', 'en' => 'Baghdad Street'],
                ['ar' => 'كراجات العباسيين', 'en' => 'Abbasiyin Garages'],
                ['ar' => 'كفرسوسة', 'en' => 'Kafr Sousa'],
                ['ar' => 'مشروع دمر', 'en' => 'Mashrou Dummar'],
                ['ar' => 'شارع بيروت', 'en' => 'Beirut Street'],
            ],

            // Aleppo
            'Aleppo' => [
                ['ar' => 'البولمان', 'en' => 'Al-Bolman'],
                ['ar' => 'الجميلية', 'en' => 'Al-Jamiliyah'],
                ['ar' => 'الشهباء', 'en' => 'Al-Shahbaa'],
                ['ar' => 'الفيض', 'en' => 'Al-Fayd'],
                ['ar' => 'المارتيني', 'en' => 'Al-Martini'],
                ['ar' => 'الموكامبو', 'en' => 'Al-Mokambo'],
                ['ar' => 'الميدان', 'en' => 'Al-Midan'],
                ['ar' => 'حلب الجديدة', 'en' => 'New Aleppo'],
                ['ar' => 'ساحة سعد الله الجابري', 'en' => 'Saadallah Al-Jabiri Square'],
                ['ar' => 'فرقان', 'en' => 'Furqan'],
            ],

            // Latakia
            'Latakia' => [
                ['ar' => 'الكورنيش الغربي', 'en' => 'Western Corniche'],
                ['ar' => 'القدسي', 'en' => 'Al-Qudsi'],
                ['ar' => '6 تشرين', 'en' => '6 Tishreen'],
                ['ar' => 'المارتقلا', 'en' => 'Al-Martaqla'],
                ['ar' => 'الشاطئ الأزرق', 'en' => 'Blue Beach'],
            ],

            // Tartous
            'Tartous' => [
                ['ar' => 'المنطقة الحرة', 'en' => 'Free Zone'],
                ['ar' => 'الكورنيش', 'en' => 'Corniche'],
                ['ar' => 'الكراجات القديمة', 'en' => 'Old Garages'],
                ['ar' => 'فندق طرطوس الكبير', 'en' => 'Tartous Grand Hotel'],
                ['ar' => 'مقابل بيت المحافظ', 'en' => 'Opposite Governor House'],
            ],

            // Homs
            'Homs' => [
                ['ar' => 'الكورنيش', 'en' => 'Corniche'],
                ['ar' => 'دوار الجوية', 'en' => 'Al-Jawiya Roundabout'],
                ['ar' => 'الحاج عاطف', 'en' => 'Al-Hajj Atef'],
                ['ar' => 'الإنشاءات', 'en' => 'Al-Inshaat'],
                ['ar' => 'شارع طرابلس', 'en' => 'Tripoli Street'],
                ['ar' => 'دوار النزهة', 'en' => 'Al-Nuzha Roundabout'],
                ['ar' => 'دوار فرع الحزب', 'en' => 'Party Branch Roundabout'],
                ['ar' => 'دوار النش', 'en' => 'Al-Nash Roundabout'],
                ['ar' => 'شارع نزار قباني', 'en' => 'Nizar Qabbani Street'],
                ['ar' => 'شارع الحضارة', 'en' => 'Al-Hadara Street'],
            ],

            // Hama
            'Hama' => [
                ['ar' => 'سور الدفاع المدني', 'en' => 'Civil Defense Wall'],
                ['ar' => 'المرابط', 'en' => 'Al-Murabit'],
                ['ar' => 'فرع المرور', 'en' => 'Traffic Branch'],
            ],
        ];

        foreach ($areas as $governorateName => $governorateAreas) {

            $governorate = Governorate::query()
                ->where('name->en', $governorateName)
                ->firstOrFail();

            foreach ($governorateAreas as $area) {

                $existingArea = Area::query()
                    ->where('governorate_id', $governorate->id)
                    ->where('name->en', $area['en'])
                    ->first();

                if ($existingArea) {
                    $existingArea->setTranslations('name', [
                        'ar' => $area['ar'],
                        'en' => $area['en'],
                    ]);

                    $existingArea->save();

                    continue;
                }

                Area::create([
                    'governorate_id' => $governorate->id,
                    'name' => [
                        'ar' => $area['ar'],
                        'en' => $area['en'],
                    ],
                ]);
            }
        }
    }
}