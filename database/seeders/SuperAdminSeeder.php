<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            [
                'email' => 'superadmin@blink.com',
            ],
            [
                'name' => [
                    'en' => 'Super Admin',
                    'ar' => 'المدير العام للنظام',
                ],
                'password' => 'password123',
            ]
        );

        $user->syncRoles([
            RoleEnum::SUPER_ADMIN->value,
        ]);
    }
}