<?php

namespace Database\Seeders;

use App\Enums\ExternalAssetTypeEnum;
use App\Models\ExternalAsset;
use App\Models\Governorate;
use Database\Seeders\Concerns\GeneratesDemoCoordinates;
use Illuminate\Database\Seeder;

class ExternalAssetSeeder extends Seeder
{
    use GeneratesDemoCoordinates;

    public function run(): void
    {
        $governorates = Governorate::query()
            ->whereHas('areas')
            ->with([
                'areas' => fn ($query) =>
                    $query->orderBy('id'),
            ])
            ->orderBy('id')
            ->get();

        if ($governorates->isEmpty()) {
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

        foreach ($types as $typeIndex => $data) {
            for ($i = 1; $i <= 30; $i++) {

                /*
                 * كل record يروح لمحافظة مختلفة بالتناوب.
                 *
                 * typeIndex * 30 حتى كل نوع يبدأ
                 * من توزيع مختلف شوي.
                 */
                $distributionIndex =
                    ($typeIndex * 30)
                    + ($i - 1);

                $area = $this->distributedArea(
                    $governorates,
                    $distributionIndex
                );

                $governorate =
                    $area->governorate;

                $governorateCode =
                    $this->governorateCode(
                        $governorate
                    );

                $code = sprintf(
                    '%s-%s-%03d',
                    $data['code'],
                    $governorateCode,
                    $i
                );

                $areaCenter =
                    $this->areaCenter(
                        $area
                    );

                $coordinates =
                    $this->pointNear(
                        $areaCenter['latitude'],
                        $areaCenter['longitude'],
                        100,
                        1200,
                        "external-{$data['code']}-{$i}"
                    );

                $areaNameAr =
                    $area->getTranslation(
                        'name',
                        'ar'
                    );

                $areaNameEn =
                    $area->getTranslation(
                        'name',
                        'en'
                    );

                ExternalAsset::updateOrCreate(
                    [
                        'code' => $code,
                    ],
                    [
                        'type' => $data['type'],

                        'area_id' => $area->id,

                        'location_name' => [
                            'ar' =>
                                "{$data['ar']} - {$areaNameAr} {$i}",

                            'en' =>
                                "{$data['en']} - {$areaNameEn} {$i}",
                        ],

                        'latitude' =>
                            $coordinates['latitude'],

                        'longitude' =>
                            $coordinates['longitude'],

                        'width' =>
                            8 + ($i % 8),

                        'height' =>
                            3 + ($i % 4),

                        'local_price' =>
                            3000000
                            + ($i * 100000),

                        'foreign_price' =>
                            250
                            + ($i * 10),
                    ]
                );
            }
        }
    }
}