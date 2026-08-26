<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Enums\SubscriptionTypeEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label(__('filament/admin/customer_resource.name'))
                    ->placeholder('-')
                    ->columnSpanFull(),

                TextEntry::make('phone')
                    ->label(__('filament/admin/customer_resource.phone'))
                    ->placeholder('-'),

                TextEntry::make('subscription_type')
                    ->label(__('filament/admin/customer_resource.subscription_type'))
                    ->badge()
                    ->formatStateUsing(
                        fn ($state): string =>
                            $state instanceof SubscriptionTypeEnum
                                ? $state->label()
                                : (SubscriptionTypeEnum::tryFrom($state)?->label() ?? $state)
                    ),

                TextEntry::make('created_at')
                    ->label(__('filament/admin/customer_resource.created_at'))
                    ->dateTime()
                    ->placeholder('-'),

                TextEntry::make('updated_at')
                    ->label(__('filament/admin/customer_resource.updated_at'))
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}