<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Enums\SubscriptionTypeEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('name_translations')
                    ->tabs([
                        Tab::make(__('filament/admin/customer_resource.arabic'))
                            ->schema([
                                TextInput::make('name.ar')
                                    ->label(__('filament/admin/customer_resource.name.ar'))
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        Tab::make(__('filament/admin/customer_resource.english'))
                            ->schema([
                                TextInput::make('name.en')
                                    ->label(__('filament/admin/customer_resource.name.en'))
                                    ->required()
                                    ->maxLength(255),
                            ]),
                    ])
                    ->columnSpanFull(),

                TextInput::make('phone')
                    ->label(__('filament/admin/customer_resource.phone'))
                    ->tel()
                    ->maxLength(50),

                Select::make('subscription_type')
                    ->label(__('filament/admin/customer_resource.subscription_type'))
                    ->options(SubscriptionTypeEnum::options()),
            ]);
    }
}