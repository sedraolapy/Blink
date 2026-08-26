<?php

namespace App\Enums;

enum DisplayGroupEnum: string
{
    case DAMASCUS_DARAA_SWEIDA = 'damascus_daraa_sweida';
    case OTHERS = 'others';

    public function label(): string
    {
        return __("enums.display_groups.{$this->value}");
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $group) => [
                $group->value => $group->label(),
            ])
            ->toArray();
    }
}