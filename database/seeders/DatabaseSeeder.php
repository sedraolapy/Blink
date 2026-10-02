<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            SuperAdminSeeder::class,
            GovernorateSeeder::class,
            AreaSeeder::class,
            AdvertisingPeriodSeeder::class,
            FlexBillboardSeeder::class,
            LedScreenSeeder::class,
            ExternalAssetSeeder::class,
            BookingApiTestSeeder::class,
            ElectronicSlidesTestSeeder::class,
            OutdoorMultiplePeriodsTestSeeder::class,
            FlexPriceGroupSeeder::class,
            MixedWorkingYearsBookingsSeeder::class,
        ]);

        Customer::factory()->count(20)->create();
    }
}
