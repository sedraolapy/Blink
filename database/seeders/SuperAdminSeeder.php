<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'email' => 'superadmin@blink.com',
                'name' => [
                    'en' => 'Super Admin',
                    'ar' => 'المدير العام للنظام',
                ],
                'role' => RoleEnum::SUPER_ADMIN,
            ],

            [
                'email' => 'admin@blink.com',
                'name' => [
                    'en' => 'Admin',
                    'ar' => 'مدير النظام',
                ],
                'role' => RoleEnum::ADMIN,
            ],

            [
                'email' => 'flex.officer@blink.com',
                'name' => [
                    'en' => 'Flex Booking Officer',
                    'ar' => 'موظف حجوزات الفليكس',
                ],
                'role' => RoleEnum::FLEX_BOOKING_OFFICER,
            ],

            [
                'email' => 'screen.officer@blink.com',
                'name' => [
                    'en' => 'Screen Booking Officer',
                    'ar' => 'موظف حجوزات الشاشات',
                ],
                'role' => RoleEnum::SCREEN_BOOKING_OFFICER,
            ],

            [
                'email' => 'external.officer@blink.com',
                'name' => [
                    'en' => 'External Booking Officer',
                    'ar' => 'موظف الحجوزات الخارجية',
                ],
                'role' => RoleEnum::EXTERNAL_BOOKING_OFFICER,
            ],

            [
                'email' => 'sales.coordinator@blink.com',
                'name' => [
                    'en' => 'Sales Coordinator',
                    'ar' => 'منسق المبيعات',
                ],
                'role' => RoleEnum::SALES_COORDINATOR,
            ],

            [
                'email' => 'sales.manager@blink.com',
                'name' => [
                    'en' => 'Sales Manager',
                    'ar' => 'مدير المبيعات',
                ],
                'role' => RoleEnum::SALES_MANAGER,
            ],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                [
                    'email' => $data['email'],
                ],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password123'),
                ]
            );

            $user->syncRoles([
                $data['role']->value,
            ]);
        }
    }
}