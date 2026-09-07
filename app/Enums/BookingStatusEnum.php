<?php

namespace App\Enums;

enum BookingStatusEnum: string
{
    case CONFIRMED = 'confirmed';
    case UNCONFIRMED = 'unconfirmed';

    public function label(): string
    {
        return __("enums.booking_statuses.{$this->value}");
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status) => [
                $status->value => $status->label(),
            ])
            ->toArray();
    }
}