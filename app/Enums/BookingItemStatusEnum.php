<?php

namespace App\Enums;

enum BookingItemStatusEnum: string
{
    case UNCONFIRMED = 'unconfirmed';
    case BOOKED = 'booked';

    public function label(): string
    {
        return __("enums.flex_booking_item_statuses.{$this->value}");
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