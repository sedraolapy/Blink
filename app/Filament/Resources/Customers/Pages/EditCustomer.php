<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Enums\SubscriptionTypeEnum;
use App\Filament\Resources\Customers\CustomerResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    private ?string $subscriptionType = null;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $subscription = $this->getRecord()
            ->currentYearSubscription;

        $data['subscription_type'] =
            $subscription?->subscription_type?->value
            ?? SubscriptionTypeEnum::BRONZE->value;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->subscriptionType = $data['subscription_type'];

        unset($data['subscription_type']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->getRecord()
            ->subscriptions()
            ->updateOrCreate(
                [
                    'year' => now()->year,
                ],
                [
                    'subscription_type' => $this->subscriptionType,
                ]
            );
    }

    public function getTitle(): string
    {
        return __('filament/admin/edit_customer.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/edit_customer.title');
    }
}
