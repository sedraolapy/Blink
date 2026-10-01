<?php

namespace App\Console\Commands;

use App\Services\Customer\CustomerSubscriptionService;
use Illuminate\Console\Command;

class UpdateCustomerSubscriptions extends Command
{
    protected $signature = 'customers:update-subscriptions';

    protected $description =
        'Update customer subscription types for the current year';

    public function __construct(
        private readonly CustomerSubscriptionService $service
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $year = now()->year;

        $this->service->assignSubscriptionTypes($year);

        $this->info(
            "Customer subscription types updated successfully for {$year}."
        );

        return self::SUCCESS;
    }
}