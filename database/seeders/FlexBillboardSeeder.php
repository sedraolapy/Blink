<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\FlexBillboard;
use App\Models\Governorate;
use Database\Seeders\Concerns\GeneratesDemoCoordinates;
use Illuminate\Database\Seeder;

class FlexBillboardSeeder extends Seeder
{
    use GeneratesDemoCoordinates;

    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | اللوحات الأساسية
        |--------------------------------------------------------------------------
        |
        | منخليهم أول شي حتى تبقى IDs القديمة ثابتة
        | للحجوزات والاختبارات الحالية.
        |
        */

        $billboards = [
            [
                'code' => 'FLX-DAM-001',

                'area_en' => 'Al-Rabwah',

                'location_name' => [
                    'ar' => 'الربوة مقابل الشادروان',
                    'en' => 'Al-Rabwah Opposite Al-Shadrawan',
                ],

                'latitude' => 33.5138,
                'longitude' => 36.2706,

                'width' => 6,
                'height' => 3,

                'local_price' => 2500000,
                'foreign_price' => 200,
            ],

            [
                'code' => 'FLX-DAM-002',

                'area_en' => 'Al-Mezzeh',

                'location_name' => [
                    'ar' => 'المزة سور المدينة الجامعية',
                    'en' => 'Al-Mezzeh University City Wall',
                ],

                'latitude' => 33.5052,
                'longitude' => 36.2519,

                'width' => 5,
                'height' => 3,

                'local_price' => 2200000,
                'foreign_price' => 180,
            ],

            [
                'code' => 'FLX-ALE-001',

                'area_en' => 'New Aleppo',

                'location_name' => [
                    'ar' => 'حلب الجديدة دوار قرطبة',
                    'en' => 'New Aleppo Cordoba Roundabout',
                ],

                'latitude' => 36.2155,
                'longitude' => 37.1182,

                'width' => 6,
                'height' => 3,

                'local_price' => 1800000,
                'foreign_price' => 150,
            ],
        ];

        foreach ($billboards as $data) {
            $area = Area::query()
                ->where(
                    'name->en',
                    $data['area_en']
                )
                ->firstOrFail();

            FlexBillboard::updateOrCreate(
                [
                    'code' => $data['code'],
                ],
                [
                    'area_id' => $area->id,

                    'location_name' =>
                        $data['location_name'],

                    'latitude' =>
                        $data['latitude'],

                    'longitude' =>
                        $data['longitude'],

                    'width' =>
                        $data['width'],

                    'height' =>
                        $data['height'],

                    'local_price' =>
                        $data['local_price'],

                    'foreign_price' =>
                        $data['foreign_price'],
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | لوحات موزعة على كل المحافظات
        |--------------------------------------------------------------------------
        */

        $governorates = Governorate::query()
            ->whereHas('areas')
            ->with([
                'areas' => fn ($query) =>
                    $query->orderBy('id'),
            ])
            ->orderBy('id')
            ->get();

        foreach ($governorates as $governorate) {
            $areas = $governorate
                ->areas
                ->values();

            $governorateNameAr =
                $governorate->getTranslation(
                    'name',
                    'ar'
                );

            $governorateNameEn =
                $governorate->getTranslation(
                    'name',
                    'en'
                );

            for ($i = 1; $i <= 2; $i++) {
                $area = $areas->get(
                    ($i - 1) % $areas->count()
                );

                $areaNameAr = $area
                    ->getTranslation(
                        'name',
                        'ar'
                    );

                $areaNameEn = $area
                    ->getTranslation(
                        'name',
                        'en'
                    );

                $areaCenter = $this->areaCenter(
                    $area
                );

                $coordinates = $this->pointNear(
                    $areaCenter['latitude'],
                    $areaCenter['longitude'],
                    80,
                    800,
                    "flex-{$governorate->id}-{$i}"
                );

                $code = sprintf(
                    'FLX-%s-%02d-%02d',
                    $this->governorateCode(
                        $governorate
                    ),
                    $governorate->id,
                    $i
                );

                FlexBillboard::updateOrCreate(
                    [
                        'code' => $code,
                    ],
                    [
                        'area_id' => $area->id,

                        'location_name' => [
                            'ar' =>
                                "لوحة فليكس {$governorateNameAr} - {$areaNameAr} {$i}",

                            'en' =>
                                "{$governorateNameEn} {$areaNameEn} Flex Billboard {$i}",
                        ],

                        'latitude' =>
                            $coordinates['latitude'],

                        'longitude' =>
                            $coordinates['longitude'],

                        'width' =>
                            5 + ($i % 2),

                        'height' => 3,

                        'local_price' =>
                            1800000
                            + ($governorate->id * 100000)
                            + ($i * 50000),

                        'foreign_price' =>
                            150
                            + ($governorate->id * 5)
                            + ($i * 10),
                    ]
                );
            }
        }
    }
}