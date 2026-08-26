<?php

namespace Database\Seeders;

use App\Enums\DisplayGroupEnum;
use App\Enums\InstallationDayEnum;
use App\Models\Governorate;
use Illuminate\Database\Seeder;

class GovernorateSeeder extends Seeder
{
    public function run(): void
    {
        $governorates = [
            [
                'name' => [
                    'ar' => 'دمشق',
                    'en' => 'Damascus',
                ],
                'display_group' => DisplayGroupEnum::DAMASCUS_DARAA_SWEIDA->value,
                'installation_day' => InstallationDayEnum::THURSDAY->value,
            ],
            [
                'name' => [
                    'ar' => 'درعا',
                    'en' => 'Daraa',
                ],
                'display_group' => DisplayGroupEnum::DAMASCUS_DARAA_SWEIDA->value,
                'installation_day' => InstallationDayEnum::FRIDAY->value,
            ],
            [
                'name' => [
                    'ar' => 'السويداء',
                    'en' => 'Sweida',
                ],
                'display_group' => DisplayGroupEnum::DAMASCUS_DARAA_SWEIDA->value,
                'installation_day' => InstallationDayEnum::FRIDAY->value,
            ],

            [
                'name' => [
                    'ar' => 'ريف دمشق',
                    'en' => 'Rif Dimashq',
                ],
                'display_group' => DisplayGroupEnum::OTHERS->value,
                'installation_day' => InstallationDayEnum::SATURDAY->value,
            ],
            [
                'name' => [
                    'ar' => 'حلب',
                    'en' => 'Aleppo',
                ],
                'display_group' => DisplayGroupEnum::OTHERS->value,
                'installation_day' => InstallationDayEnum::SATURDAY->value,
            ],
            [
                'name' => [
                    'ar' => 'حمص',
                    'en' => 'Homs',
                ],
                'display_group' => DisplayGroupEnum::OTHERS->value,
                'installation_day' => InstallationDayEnum::SATURDAY->value,
            ],
            [
                'name' => [
                    'ar' => 'حماة',
                    'en' => 'Hama',
                ],
                'display_group' => DisplayGroupEnum::OTHERS->value,
                'installation_day' => InstallationDayEnum::SATURDAY->value,
            ],
            [
                'name' => [
                    'ar' => 'اللاذقية',
                    'en' => 'Latakia',
                ],
                'display_group' => DisplayGroupEnum::OTHERS->value,
                'installation_day' => InstallationDayEnum::SATURDAY->value,
            ],
            [
                'name' => [
                    'ar' => 'طرطوس',
                    'en' => 'Tartous',
                ],
                'display_group' => DisplayGroupEnum::OTHERS->value,
                'installation_day' => InstallationDayEnum::SATURDAY->value,
            ],
            [
                'name' => [
                    'ar' => 'إدلب',
                    'en' => 'Idlib',
                ],
                'display_group' => DisplayGroupEnum::OTHERS->value,
                'installation_day' => InstallationDayEnum::SATURDAY->value,
            ],
            [
                'name' => [
                    'ar' => 'دير الزور',
                    'en' => 'Deir ez-Zor',
                ],
                'display_group' => DisplayGroupEnum::OTHERS->value,
                'installation_day' => InstallationDayEnum::SATURDAY->value,
            ],
            [
                'name' => [
                    'ar' => 'الرقة',
                    'en' => 'Raqqa',
                ],
                'display_group' => DisplayGroupEnum::OTHERS->value,
                'installation_day' => InstallationDayEnum::SATURDAY->value,
            ],
            [
                'name' => [
                    'ar' => 'الحسكة',
                    'en' => 'Al-Hasakah',
                ],
                'display_group' => DisplayGroupEnum::OTHERS->value,
                'installation_day' => InstallationDayEnum::SATURDAY->value,
            ],
            [
                'name' => [
                    'ar' => 'القنيطرة',
                    'en' => 'Quneitra',
                ],
                'display_group' => DisplayGroupEnum::OTHERS->value,
                'installation_day' => InstallationDayEnum::SATURDAY->value,
            ],
        ];

        foreach ($governorates as $governorate) {
            Governorate::updateOrCreate(
                [
                    'name->en' => $governorate['name']['en'],
                ],
                $governorate
            );
        }
    }
}