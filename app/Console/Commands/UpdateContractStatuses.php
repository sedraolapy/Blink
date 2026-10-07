<?php

namespace App\Console\Commands;

use App\Enums\ContractStatusEnum;
use App\Models\Contract;
use Illuminate\Console\Command;

class UpdateContractStatuses extends Command
{
    protected $signature = 'contracts:update-statuses';

    protected $description = 'Update contract statuses based on their start and end dates';

    public function handle(): int
    {
        $today = today()->toDateString();

        $waitingStart = Contract::query()
            ->whereDate('start_date', '>', $today)
            ->where(
                'status',
                '!=',
                ContractStatusEnum::WAITING_START->value
            )
            ->update([
                'status' => ContractStatusEnum::WAITING_START->value,
            ]);

        $inProgress = Contract::query()
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->where(
                'status',
                '!=',
                ContractStatusEnum::IN_PROGRESS->value
            )
            ->update([
                'status' => ContractStatusEnum::IN_PROGRESS->value,
            ]);

        $finished = Contract::query()
            ->whereDate('end_date', '<', $today)
            ->where(
                'status',
                '!=',
                ContractStatusEnum::FINISHED->value
            )
            ->update([
                'status' => ContractStatusEnum::FINISHED->value,
            ]);

        $this->info(
            "Contract statuses updated: "
            . "waiting_start={$waitingStart}, "
            . "in_progress={$inProgress}, "
            . "finished={$finished}"
        );

        return self::SUCCESS;
    }
}