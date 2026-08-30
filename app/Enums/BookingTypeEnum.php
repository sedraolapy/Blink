<?php

namespace App\Enums;

enum BookingTypeEnum: string
{
    case INTERNAL = 'internal';
    case EXTERNAL = 'external';

    public function label(): string
    {
        return __("enums.booking_types.{$this->value}");
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type) => [
                $type->value => $type->label(),
            ])
            ->toArray();
    }
}