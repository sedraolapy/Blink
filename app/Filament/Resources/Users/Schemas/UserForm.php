<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\RoleEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Tabs::make('name_translations')
                    ->tabs([
                        Tab::make('العربية')
                            ->schema([
                                TextInput::make('name.ar')
                                    ->label(__('filament/admin/user_resource.name'))
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        Tab::make('English')
                            ->schema([
                                TextInput::make('name.en')
                                    ->label(__('filament/admin/user_resource.name'))
                                    ->required()
                                    ->maxLength(255),
                            ]),
                    ])
                    ->columnSpanFull(),

                TextInput::make('email')
                    ->label(__('filament/admin/user_resource.email'))
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                TextInput::make('password')
                    ->label(__('filament/admin/user_resource.password'))
                    ->password()
                    ->revealable()
                    ->required(
                        fn (string $operation): bool =>
                            $operation === 'create'
                    )
                    ->dehydrated(
                        fn (?string $state): bool =>
                            filled($state)
                    )
                    ->maxLength(255),

                Select::make('roles')
                    ->label(__('filament/admin/user_resource.roles'))
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->getOptionLabelFromRecordUsing(
                        fn (Role $record): string =>
                            RoleEnum::tryFrom($record->name)?->label()
                            ?? $record->name
                    )
                    ->required(),
            ]);
    }
}