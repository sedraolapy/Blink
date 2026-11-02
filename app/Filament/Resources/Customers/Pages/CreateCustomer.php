<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Enums\SubscriptionTypeEnum;
use App\Filament\Resources\Customers\CustomerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    private ?string $subscriptionType = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->subscriptionType = $data['subscription_type']
            ?? SubscriptionTypeEnum::BRONZE->value;

        unset($data['subscription_type']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->subscriptions()->updateOrCreate(
            [
                'year' => now()->year,
            ],
            [
                'subscription_type' => $this->subscriptionType,
            ]
        );
    }
}
