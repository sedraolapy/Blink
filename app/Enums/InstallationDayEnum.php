<?php

namespace App\Enums;

enum InstallationDayEnum: string
{
    case THURSDAY = 'thursday';
    case FRIDAY = 'friday';
    case SATURDAY = 'saturday';

    public function label(): string
    {
        return __("enums.installation_days.{$this->value}");
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $day) => [
                $day->value => $day->label(),
            ])
            ->toArray();
    }
}