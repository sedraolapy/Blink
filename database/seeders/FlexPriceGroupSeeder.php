<?php

namespace Database\Seeders;

use App\Models\FlexPriceGroup;
use App\Models\Governorate;
use Illuminate\Database\Seeder;

class FlexPriceGroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            'Damascus' => [
                'billboards_count' => 6,
                'local_price' => 2400,
                'foreign_price' => 3600,
            ],

            'Daraa' => [
                'billboards_count' => 4,
                'local_price' => 900,
                'foreign_price' => 1400,
            ],

            'Sweida' => [
                'billboards_count' => 4,
                'local_price' => 850,
                'foreign_price' => 1300,
            ],

            'Rif Dimashq' => [
                'billboards_count' => 5,
                'local_price' => 1600,
                'foreign_price' => 2400,
            ],

            'Aleppo' => [
                'billboards_count' => 5,
                'local_price' => 1800,
                'foreign_price' => 2700,
            ],

            'Homs' => [
                'billboards_count' => 5,
                'local_price' => 1200,
                'foreign_price' => 1800,
            ],

            'Hama' => [
                'billboards_count' => 4,
                'local_price' => 1000,
                'foreign_price' => 1500,
            ],

            'Latakia' => [
                'billboards_count' => 5,
                'local_price' => 1500,
                'foreign_price' => 2250,
            ],

            'Tartous' => [
                'billboards_count' => 5,
                'local_price' => 1300,
                'foreign_price' => 1950,
            ],

            'Idlib' => [
                'billboards_count' => 4,
                'local_price' => 950,
                'foreign_price' => 1450,
            ],

            'Deir ez-Zor' => [
                'billboards_count' => 3,
                'local_price' => 800,
                'foreign_price' => 1200,
            ],

            'Raqqa' => [
                'billboards_count' => 3,
                'local_price' => 750,
                'foreign_price' => 1150,
            ],

            'Al-Hasakah' => [
                'billboards_count' => 3,
                'local_price' => 700,
                'foreign_price' => 1100,
            ],

            'Quneitra' => [
                'billboards_count' => 4,
                'local_price' => 850,
                'foreign_price' => 1250,
            ],
        ];

        foreach ($groups as $governorateName => $group) {
            $governorate = Governorate::query()
                ->where('name->en', $governorateName)
                ->firstOrFail();

            FlexPriceGroup::query()->updateOrCreate(
                [
                    'governorate_id' => $governorate->id,
                ],
                $group
            );
        }
    }
}