<?php

namespace App\Console\Commands;

use App\Services\Customer\CustomerSubscriptionService;
use Illuminate\Console\Command;

class UpdateCustomerSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customers:update-subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update customer subscription types based on booking items ranking';

    public function __construct(private readonly CustomerSubscriptionService $service) 
    {
        parent::__construct();
    }


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->service->assignSubscriptionTypes();
        $this->info('Customer subscription types updated successfully.');

        return self::SUCCESS;
    }
}
