<?php

namespace App\Enums;

enum RoleEnum : String
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';

    case FLEX_BOOKING_OFFICER = 'flex_booking_officer';
    case SCREEN_BOOKING_OFFICER = 'screen_booking_officer';
    case UNIPOLE_BOOKING_OFFICER = 'unipole_booking_officer';

    case SALES_COORDINATOR = 'sales_coordinator';
    case SALES_MANAGER = 'sales_manager';


    public function label(): string
    {
        return __("enums.roles.{$this->value}");
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $role) => [
                $role->value => $role->label(),
            ])
            ->toArray();
    }
}

