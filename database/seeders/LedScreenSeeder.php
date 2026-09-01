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
        $areas = Area::query()
            ->with('governorate')
            ->get();

        if ($areas->isEmpty()) {
            $this->command?->error(
                'No areas found. Run AreaSeeder first.'
            );

            return;
        }

        for ($i = 1; $i <= 30; $i++) {

            $area = $areas[($i - 1) % $areas->count()];

            LedScreen::updateOrCreate(
                [
                    'code' => sprintf('LED-IND-%03d', $i),
                ],
                [
                    'area_id' => $area->id,
                    'network_id' => null,

                    'location_name' => [
                        'ar' => "شاشة إلكترونية مستقلة {$i}",
                        'en' => "Independent LED Screen {$i}",
                    ],

                    'latitude' => 33.5000000 + ($i * 0.001),
                    'longitude' => 36.2000000 + ($i * 0.001),

                    'width' => 4 + ($i % 3),
                    'height' => 3,

                    'width_px' => 1920,
                    'height_px' => 1080,

                    'local_price' => 1500 + ($i * 50),
                    'foreign_price' => 2000 + ($i * 50),
                ]
            );
        }

        for ($i = 1; $i <= 8; $i++) {

            $area = $areas[($i - 1) % $areas->count()];

            $network = LedNetwork::updateOrCreate(
                [
                    'location_name->en' => "Test LED Network {$i}",
                ],
                [
                    'location_name' => [
                        'ar' => "شبكة شاشات تجريبية {$i}",
                        'en' => "Test LED Network {$i}",
                    ],

                    'local_price' => 3000 + ($i * 100),
                    'foreign_price' => 4000 + ($i * 100),
                ]
            );

            LedScreen::updateOrCreate(
                [
                    'code' => sprintf(
                        'LED-NET-%02d-01',
                        $i
                    ),
                ],
                [
                    'area_id' => $area->id,
                    'network_id' => $network->id,

                    'location_name' => [
                        'ar' => "الشاشة الأولى ضمن الشبكة {$i}",
                        'en' => "Network {$i} Screen 1",
                    ],

                    'latitude' => 33.6000000 + ($i * 0.001),
                    'longitude' => 36.3000000 + ($i * 0.001),

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
                    'code' => sprintf(
                        'LED-NET-%02d-02',
                        $i
                    ),
                ],
                [
                    'area_id' => $area->id,
                    'network_id' => $network->id,

                    'location_name' => [
                        'ar' => "الشاشة الثانية ضمن الشبكة {$i}",
                        'en' => "Network {$i} Screen 2",
                    ],

                    'latitude' => 33.7000000 + ($i * 0.001),
                    'longitude' => 36.4000000 + ($i * 0.001),

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
}