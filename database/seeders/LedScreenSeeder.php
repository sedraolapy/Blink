<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\LedNetwork;
use App\Models\LedScreen;
use Illuminate\Database\Seeder;

class LedScreenSeeder extends Seeder
{
    public function run(): void
    {
        $area = Area::query()->first();

        if (! $area) {
            $this->command?->error('No areas found. Run AreaSeeder first.');
            return;
        }


        LedScreen::updateOrCreate(
            [
                'code' => 'LED-001',
            ],
            [
                'area_id' => $area->id,
                'network_id' => null,

                'location_name' => [
                    'ar' => 'شاشة إلكترونية مستقلة',
                    'en' => 'Independent LED Screen',
                ],

                'latitude' => 33.5138000,
                'longitude' => 36.2765000,

                'width' => 4,
                'height' => 3,

                'width_px' => 1920,
                'height_px' => 1080,

                'local_price' => 1500,
                'foreign_price' => 2000,
            ]
        );

        $network = LedNetwork::updateOrCreate(
            [
                'location_name->en' => 'Central LED Network',
            ],
            [
                'location_name' => [
                    'ar' => 'شبكة الشاشات المركزية',
                    'en' => 'Central LED Network',
                ],

                'local_price' => 3000,
                'foreign_price' => 4000,
            ]
        );

        LedScreen::updateOrCreate(
            [
                'code' => 'LED-NET-001',
            ],
            [
                'area_id' => $area->id,
                'network_id' => $network->id,

                'location_name' => [
                    'ar' => 'الشاشة الأولى ضمن الشبكة',
                    'en' => 'Network Screen 1',
                ],

                'latitude' => 33.5145000,
                'longitude' => 36.2773000,

                'width' => 5,
                'height' => 3,

                'width_px' => 1920,
                'height_px' => 1080,

                'local_price' => null,
                'foreign_price' => null,
            ]
        );

        LedScreen::updateOrCreate(
            [
                'code' => 'LED-NET-002',
            ],
            [
                'area_id' => $area->id,
                'network_id' => $network->id,

                'location_name' => [
                    'ar' => 'الشاشة الثانية ضمن الشبكة',
                    'en' => 'Network Screen 2',
                ],

                'latitude' => 33.5152000,
                'longitude' => 36.2781000,

                'width' => 5,
                'height' => 3,

                'width_px' => 1920,
                'height_px' => 1080,

                'local_price' => null,
                'foreign_price' => null,
            ]
        );
    }
}