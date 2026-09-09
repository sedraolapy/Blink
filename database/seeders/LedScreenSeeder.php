<?php

namespace Database\Seeders;

use App\Models\Governorate;
use App\Models\LedNetwork;
use App\Models\LedScreen;
use Database\Seeders\Concerns\GeneratesDemoCoordinates;
use Illuminate\Database\Seeder;

class LedScreenSeeder extends Seeder
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
            $this->command?->error(
                'No governorates with areas found. Run geography seeders first.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Independent Screens
        |--------------------------------------------------------------------------
        */

        $independentScreensCount = 200;

        for (
            $i = 1;
            $i <= $independentScreensCount;
            $i++
        ) {
            $area =
                $this->distributedArea(
                    $governorates,
                    $i - 1
                );

            $governorate =
                $area->governorate;

            $areaCenter =
                $this->areaCenter(
                    $area
                );

            $coordinates =
                $this->pointNear(
                    $areaCenter['latitude'],
                    $areaCenter['longitude'],
                    80,
                    1000,
                    "independent-led-{$i}"
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

            $governorateNameAr =
                $governorate
                    ->getTranslation(
                        'name',
                        'ar'
                    );

            $governorateNameEn =
                $governorate
                    ->getTranslation(
                        'name',
                        'en'
                    );

            LedScreen::updateOrCreate(
                [
                    'code' => sprintf(
                        'LED-IND-%04d',
                        $i
                    ),
                ],
                [
                    'area_id' =>
                        $area->id,

                    'network_id' =>
                        null,

                    'location_name' => [
                        'ar' =>
                            "شاشة إلكترونية مستقلة {$governorateNameAr} - {$areaNameAr} {$i}",

                        'en' =>
                            "Independent LED Screen {$governorateNameEn} - {$areaNameEn} {$i}",
                    ],

                    'latitude' =>
                        $coordinates['latitude'],

                    'longitude' =>
                        $coordinates['longitude'],

                    'width' =>
                        4 + ($i % 3),

                    'height' =>
                        3 + ($i % 2),

                    'width_px' =>
                        1920,

                    'height_px' =>
                        1080,

                    'local_price' =>
                        1500
                        + (($i % 30) * 50),

                    'foreign_price' =>
                        2000
                        + (($i % 30) * 50),
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | LED Networks
        |--------------------------------------------------------------------------
        |
        | Network واحدة لكل محافظة.
        | كل Network فيها 2 إلى 5 شاشات.
        |
        */

        foreach (
            $governorates->values()
            as $index => $governorate
        ) {
            $networkNumber =
                $index + 1;

            $areas =
                $governorate
                    ->areas
                    ->values();

            $area =
                $areas->get(
                    $index
                    % $areas->count()
                );

            $area->setRelation(
                'governorate',
                $governorate
            );

            $governorateNameAr =
                $governorate
                    ->getTranslation(
                        'name',
                        'ar'
                    );

            $governorateNameEn =
                $governorate
                    ->getTranslation(
                        'name',
                        'en'
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

            $networkNameEn =
                "Test LED Network {$networkNumber} - {$governorateNameEn}";

            $network =
                LedNetwork::updateOrCreate(
                    [
                        'location_name->en' =>
                            $networkNameEn,
                    ],
                    [
                        'location_name' => [
                            'ar' =>
                                "شبكة شاشات {$governorateNameAr} - {$areaNameAr}",

                            'en' =>
                                $networkNameEn,
                        ],

                        'local_price' =>
                            3000
                            + ($networkNumber * 100),

                        'foreign_price' =>
                            4000
                            + ($networkNumber * 100),
                    ]
                );

            $areaCenter =
                $this->areaCenter(
                    $area
                );

            /*
             * مكان مركزي خاص بالشبكة.
             */
            $networkCenter =
                $this->pointNear(
                    $areaCenter['latitude'],
                    $areaCenter['longitude'],
                    100,
                    700,
                    "network-center-{$networkNumber}"
                );

            /*
             * 2، 3، 4، 5 ثم يرجع يعيد.
             */
            $screensCount =
                2 + (
                    ($networkNumber - 1)
                    % 4
                );

            for (
                $screenNumber = 1;
                $screenNumber <= $screensCount;
                $screenNumber++
            ) {
                $coordinates =
                    $this->pointNear(
                        $networkCenter['latitude'],
                        $networkCenter['longitude'],
                        30,
                        180,
                        "network-{$networkNumber}-screen-{$screenNumber}"
                    );

                LedScreen::updateOrCreate(
                    [
                        'code' => sprintf(
                            'LED-NET-%02d-%02d',
                            $networkNumber,
                            $screenNumber
                        ),
                    ],
                    [
                        'area_id' =>
                            $area->id,

                        'network_id' =>
                            $network->id,

                        'location_name' => [
                            'ar' =>
                                "الشاشة {$screenNumber} ضمن شبكة {$governorateNameAr}",

                            'en' =>
                                "{$governorateNameEn} Network {$networkNumber} Screen {$screenNumber}",
                        ],

                        'latitude' =>
                            $coordinates['latitude'],

                        'longitude' =>
                            $coordinates['longitude'],

                        'width' =>
                            5,

                        'height' =>
                            3,

                        'width_px' =>
                            1920,

                        'height_px' =>
                            1080,

                        'local_price' =>
                            null,

                        'foreign_price' =>
                            null,
                    ]
                );
            }
        }
    }
}