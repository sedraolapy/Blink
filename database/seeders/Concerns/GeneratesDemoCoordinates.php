<?php

namespace Database\Seeders\Concerns;

use App\Models\Area;
use App\Models\Governorate;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait GeneratesDemoCoordinates
{
    private function distributedArea(
        Collection $governorates,
        int $index
    ): Area {
        $governorates = $governorates->values();

        $governoratesCount = $governorates->count();

        $governorate = $governorates->get(
            $index % $governoratesCount
        );

        $areas = $governorate->areas->values();

        $round = intdiv(
            $index,
            $governoratesCount
        );

        $area = $areas->get(
            $round % $areas->count()
        );

        $area->setRelation(
            'governorate',
            $governorate
        );

        return $area;
    }

    private function areaCenter(Area $area): array
    {
        $governorate = $area->governorate;

        $center = $this->governorateCenter(
            $governorate
        );

        $areaName = $area->getTranslation(
            'name',
            'en'
        );

        return $this->pointNear(
            $center['latitude'],
            $center['longitude'],
            400,
            3500,
            "area-{$area->id}-{$areaName}"
        );
    }

    private function pointNear(
        float $latitude,
        float $longitude,
        float $minRadiusMeters,
        float $maxRadiusMeters,
        string $seed
    ): array {
        $radiusRandom = $this->seededUnit(
            "{$seed}-radius"
        );

        $angleRandom = $this->seededUnit(
            "{$seed}-angle"
        );

        $radius = sqrt(
            (
                $radiusRandom
                * (
                    ($maxRadiusMeters ** 2)
                    - ($minRadiusMeters ** 2)
                )
            )
            + ($minRadiusMeters ** 2)
        );

        $angle = 2 * pi() * $angleRandom;

        $latitudeOffset =
            ($radius / 111320)
            * cos($angle);

        $longitudeOffset =
            (
                $radius
                / (
                    111320
                    * cos(deg2rad($latitude))
                )
            )
            * sin($angle);

        return [
            'latitude' => round(
                $latitude + $latitudeOffset,
                7
            ),

            'longitude' => round(
                $longitude + $longitudeOffset,
                7
            ),
        ];
    }

    private function seededUnit(
        string $seed
    ): float {
        $unsigned = (float) sprintf(
            '%u',
            crc32($seed)
        );

        return $unsigned / 4294967295;
    }

    private function governorateCenter(
        Governorate $governorate
    ): array {
        $name = Str::lower(
            Str::ascii(
                $governorate->getTranslation(
                    'name',
                    'en'
                )
            )
        );

        [$latitude, $longitude] = match (true) {
            Str::contains(
                $name,
                [
                    'rif dimashq',
                    'rural damascus',
                    'damascus countryside',
                ]
            ) => [
                33.5167,
                36.7500,
            ],

            Str::contains(
                $name,
                [
                    'damascus',
                    'dimashq',
                ]
            ) => [
                33.5138,
                36.2765,
            ],

            Str::contains(
                $name,
                'aleppo'
            ) => [
                36.2021,
                37.1343,
            ],

            Str::contains(
                $name,
                'homs'
            ) => [
                34.7324,
                36.7137,
            ],

            Str::contains(
                $name,
                'hama'
            ) => [
                35.1318,
                36.7578,
            ],

            Str::contains(
                $name,
                [
                    'latakia',
                    'lattakia',
                ]
            ) => [
                35.5317,
                35.7901,
            ],

            Str::contains(
                $name,
                [
                    'tartus',
                    'tartous',
                ]
            ) => [
                34.8890,
                35.8866,
            ],

            Str::contains(
                $name,
                'idlib'
            ) => [
                35.9306,
                36.6339,
            ],

            Str::contains(
                $name,
                [
                    'daraa',
                    "dar'a",
                ]
            ) => [
                32.6189,
                36.1021,
            ],

            Str::contains(
                $name,
                [
                    'sweida',
                    'suwayda',
                    'suweyda',
                ]
            ) => [
                32.7089,
                36.5695,
            ],

            Str::contains(
                $name,
                [
                    'quneitra',
                    'qunaytira',
                ]
            ) => [
                33.1259,
                35.8246,
            ],

            Str::contains(
                $name,
                [
                    'deir',
                    'dayr',
                ]
            ) => [
                35.3359,
                40.1408,
            ],

            Str::contains(
                $name,
                [
                    'raqqa',
                    'raqqah',
                ]
            ) => [
                35.9500,
                39.0100,
            ],

            Str::contains(
                $name,
                [
                    'hasakah',
                    'hassakeh',
                    'hassakah',
                ]
            ) => [
                36.5024,
                40.7477,
            ],

            default =>
                $this->fallbackGovernorateCenter(
                    $governorate
                ),
        };

        return [
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];
    }

    private function fallbackGovernorateCenter(
        Governorate $governorate
    ): array {
        $latitude = 32.7 + (
            $this->seededUnit(
                "governorate-lat-{$governorate->id}"
            ) * 3.8
        );

        $longitude = 35.8 + (
            $this->seededUnit(
                "governorate-lng-{$governorate->id}"
            ) * 4.8
        );

        return [
            $latitude,
            $longitude,
        ];
    }

    private function governorateCode(
        Governorate $governorate
    ): string {
        $name = Str::lower(
            Str::ascii(
                $governorate->getTranslation(
                    'name',
                    'en'
                )
            )
        );

        return match (true) {
            Str::contains(
                $name,
                [
                    'rif dimashq',
                    'rural damascus',
                    'damascus countryside',
                ]
            ) => 'RIF',

            Str::contains(
                $name,
                [
                    'damascus',
                    'dimashq',
                ]
            ) => 'DAM',

            Str::contains(
                $name,
                'aleppo'
            ) => 'ALE',

            Str::contains(
                $name,
                'homs'
            ) => 'HOM',

            Str::contains(
                $name,
                'hama'
            ) => 'HAM',

            Str::contains(
                $name,
                [
                    'latakia',
                    'lattakia',
                ]
            ) => 'LAT',

            Str::contains(
                $name,
                [
                    'tartus',
                    'tartous',
                ]
            ) => 'TAR',

            Str::contains(
                $name,
                'idlib'
            ) => 'IDL',

            Str::contains(
                $name,
                'daraa'
            ) => 'DAR',

            Str::contains(
                $name,
                [
                    'sweida',
                    'suwayda',
                    'suweyda',
                ]
            ) => 'SWE',

            Str::contains(
                $name,
                [
                    'quneitra',
                    'qunaytira',
                ]
            ) => 'QUN',

            Str::contains(
                $name,
                [
                    'deir',
                    'dayr',
                ]
            ) => 'DEZ',

            Str::contains(
                $name,
                [
                    'raqqa',
                    'raqqah',
                ]
            ) => 'RAQ',

            Str::contains(
                $name,
                [
                    'hasakah',
                    'hassakeh',
                    'hassakah',
                ]
            ) => 'HAS',

            default => sprintf(
                'G%02d',
                $governorate->id
            ),
        };
    }
}