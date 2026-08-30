<?php

namespace Database\Seeders;

use App\Enums\ExternalAssetTypeEnum;
use App\Models\Area;
use App\Models\ExternalAsset;
use Illuminate\Database\Seeder;

class ExternalAssetSeeder extends Seeder
{
    public function run(): void
    {
        $area = Area::query()->first();

        if (! $area) {
            $this->command?->error('No areas found. Run AreaSeeder first.');

            return;
        }

        $assets = [
            [
                'code' => 'EXT-UNI-001',
                'type' => ExternalAssetTypeEnum::UNIPOLE,
                'location_name' => [
                    'ar' => 'يوني بول عند المدخل الرئيسي',
                    'en' => 'Unipole at Main Entrance',
                ],
                'latitude' => 33.5138,
                'longitude' => 36.2765,
                'width' => 14,
                'height' => 4,
                'local_price' => 5000000,
                'foreign_price' => 400,
            ],
            [
                'code' => 'EXT-BRG-001',
                'type' => ExternalAssetTypeEnum::BRIDGE,
                'location_name' => [
                    'ar' => 'إعلان على الجسر الرئيسي',
                    'en' => 'Main Bridge Advertisement',
                ],
                'latitude' => 33.5162,
                'longitude' => 36.2810,
                'width' => 12,
                'height' => 3,
                'local_price' => 4500000,
                'foreign_price' => 350,
            ],
            [
                'code' => 'EXT-TUN-001',
                'type' => ExternalAssetTypeEnum::TUNNEL,
                'location_name' => [
                    'ar' => 'إعلان مدخل النفق',
                    'en' => 'Tunnel Entrance Advertisement',
                ],
                'latitude' => 33.5105,
                'longitude' => 36.2702,
                'width' => 10,
                'height' => 3,
                'local_price' => 4000000,
                'foreign_price' => 320,
            ],
            [
                'code' => 'EXT-MUR-001',
                'type' => ExternalAssetTypeEnum::MURAL,
                'location_name' => [
                    'ar' => 'جدارية على الشارع الرئيسي',
                    'en' => 'Main Street Mural',
                ],
                'latitude' => 33.5190,
                'longitude' => 36.2850,
                'width' => 15,
                'height' => 8,
                'local_price' => 6000000,
                'foreign_price' => 500,
            ],
            [
                'code' => 'EXT-ROO-001',
                'type' => ExternalAssetTypeEnum::ROOFTOP,
                'location_name' => [
                    'ar' => 'سطحية على المبنى الرئيسي',
                    'en' => 'Main Building Rooftop',
                ],
                'latitude' => 33.5210,
                'longitude' => 36.2890,
                'width' => 10,
                'height' => 5,
                'local_price' => 5500000,
                'foreign_price' => 450,
            ],
        ];

        foreach ($assets as $asset) {
            ExternalAsset::updateOrCreate(
                ['code' => $asset['code']],
                [
                    'type' => $asset['type'],
                    'area_id' => $area->id,
                    'location_name' => $asset['location_name'],
                    'latitude' => $asset['latitude'],
                    'longitude' => $asset['longitude'],
                    'width' => $asset['width'],
                    'height' => $asset['height'],
                    'local_price' => $asset['local_price'],
                    'foreign_price' => $asset['foreign_price'],
                ]
            );
        }
    }
}