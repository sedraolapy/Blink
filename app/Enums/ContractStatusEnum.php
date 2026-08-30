<?php

namespace App\Enums;

enum ContractStatusEnum: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';

    public function label(): string
    {
        return __("enums.contract_statuses.{$this->value}");
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