<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\FlexBillboard;
use Illuminate\Database\Seeder;

class FlexBillboardSeeder extends Seeder
{
    public function run(): void
    {
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
                ->where('name->en', $data['area_en'])
                ->firstOrFail();

            FlexBillboard::updateOrCreate(
                [
                    'code' => $data['code'],
                ],
                [
                    'area_id' => $area->id,
                    'location_name' => $data['location_name'],
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'width' => $data['width'],
                    'height' => $data['height'],
                    'local_price' => $data['local_price'],
                    'foreign_price' => $data['foreign_price'],
                ]
            );
        }
    }
}