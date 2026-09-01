<?php

namespace Database\Seeders;

use App\Enums\ExternalAssetTypeEnum;
use App\Models\Area;
use App\Models\ExternalAsset;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExternalAssetSeeder extends Seeder
{
    public function run(): void
    {
        $areas = Area::query()
            ->with('governorate')
            ->get();

        if ($areas->isEmpty()) {
            return;
        }

        $types = [
            [
                'type' => ExternalAssetTypeEnum::MURAL,
                'code' => 'MUR',
                'ar' => 'جدارية إعلانية',
                'en' => 'Advertising Mural',
            ],
            [
                'type' => ExternalAssetTypeEnum::ROOFTOP,
                'code' => 'ROO',
                'ar' => 'سطحية إعلانية',
                'en' => 'Advertising Rooftop',
            ],
            [
                'type' => ExternalAssetTypeEnum::TUNNEL,
                'code' => 'TUN',
                'ar' => 'إعلان نفق',
                'en' => 'Tunnel Advertisement',
            ],
            [
                'type' => ExternalAssetTypeEnum::BRIDGE,
                'code' => 'BRG',
                'ar' => 'إعلان جسر',
                'en' => 'Bridge Advertisement',
            ],
            [
                'type' => ExternalAssetTypeEnum::UNIPOLE,
                'code' => 'UNI',
                'ar' => 'يوني بول',
                'en' => 'Unipole',
            ],
        ];

        foreach ($types as $data) {
            for ($i = 1; $i <= 30; $i++) {
                $area = $areas[
                    ($i - 1) % $areas->count()
                ];

                $governorateName = $area
                    ->governorate
                    ->getTranslation('name', 'en');

                $governorateCode = Str::upper(
                    Str::substr($governorateName, 0, 3)
                );

                $code = sprintf(
                    '%s-%s-%03d',
                    $data['code'],
                    $governorateCode,
                    $i
                );

                ExternalAsset::updateOrCreate(
                    [
                        'code' => $code,
                    ],
                    [
                        'type' => $data['type'],
                        'area_id' => $area->id,
                        'location_name' => [
                            'ar' => "{$data['ar']} {$i}",
                            'en' => "{$data['en']} {$i}",
                        ],
                        'latitude' =>33.4000000 + ($i * 0.001),
                        'longitude' =>36.2000000 + ($i * 0.001),
                        'width' =>8 + ($i % 8),
                        'height' =>3 + ($i % 4),
                        'local_price' =>3000000 + ($i * 100000),
                        'foreign_price' =>250 + ($i * 10),
                    ]
                );
            }
        }

    }
}