<?php

namespace App\Enums;

enum BookingServiceTypeEnum: string
{
    case FLEX = 'flex';
    case ELECTRONIC = 'electronic';
    case EXTERNAL = 'external';

    public function label(): string
    {
        return __("enums.booking_service_types.{$this->value}");
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