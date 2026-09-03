<?php

namespace App\Enums;

enum ContractStatusEnum: string
{
    case PENDING = 'pending';
    case WAITING_START = 'waiting_start';
    case IN_PROGRESS = 'in_progress';
    case FINISHED = 'finished';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('enums.contract_status.pending'),
            self::WAITING_START => __('enums.contract_status.waiting_start'),
            self::IN_PROGRESS => __('enums.contract_status.in_progress'),
            self::FINISHED => __('enums.contract_status.finished'),
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(
                fn (self $status) => [
                    $status->value => $status->label(),
                ]
            )
            ->toArray();
    }
}