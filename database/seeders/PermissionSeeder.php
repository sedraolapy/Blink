<?php

namespace Database\Seeders;

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (PermissionEnum::cases() as $permission) {
            Permission::firstOrCreate([
                'name' => $permission->value,
                'guard_name' => 'web',
            ]);
        }


        $superAdmin = Role::findByName(
            RoleEnum::SUPER_ADMIN->value,
            'web'
        );

        $admin = Role::findByName(
            RoleEnum::ADMIN->value,
            'web'
        );

        $flexOfficer = Role::findByName(
            RoleEnum::FLEX_BOOKING_OFFICER->value,
            'web'
        );

        $screenOfficer = Role::findByName(
            RoleEnum::SCREEN_BOOKING_OFFICER->value,
            'web'
        );

        $externalOfficer = Role::findByName(
            RoleEnum::EXTERNAL_BOOKING_OFFICER->value,
            'web'
        );

        $salesCoordinator = Role::findByName(
            RoleEnum::SALES_COORDINATOR->value,
            'web'
        );

        $salesManager = Role::findByName(
            RoleEnum::SALES_MANAGER->value,
            'web'
        );


        $superAdmin->syncPermissions(
            collect(PermissionEnum::cases())
                ->map(
                    fn (PermissionEnum $permission) =>
                        $permission->value
                )
                ->all()
        );


        $admin->syncPermissions([
            PermissionEnum::ACCESS_DASHBOARD->value,
        ]);


        $flexOfficer->syncPermissions([
            PermissionEnum::CREATE_FLEX_BOOKING->value,
        ]);


        $screenOfficer->syncPermissions([
            PermissionEnum::CREATE_SCREEN_BOOKING->value,
        ]);


        $externalOfficer->syncPermissions([
            PermissionEnum::CREATE_EXTERNAL_BOOKING->value,
        ]);


        $salesCoordinator->syncPermissions([
            PermissionEnum::MANAGE_ORDERS->value,
            PermissionEnum::MANAGE_REPORTS->value,
        ]);


        $salesManager->syncPermissions([
            PermissionEnum::CREATE_FLEX_BOOKING->value,
            PermissionEnum::CREATE_SCREEN_BOOKING->value,
            PermissionEnum::CREATE_EXTERNAL_BOOKING->value,

            PermissionEnum::UPDATE_BOOKING->value,
            PermissionEnum::DELETE_BOOKING->value,

            PermissionEnum::UPDATE_CUSTOMER->value,

            PermissionEnum::UPLOAD_CONTRACT->value,
            PermissionEnum::EXPORT_QUOTATION->value,

            PermissionEnum::MANAGE_ORDERS->value,
            PermissionEnum::MANAGE_REPORTS->value,
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}