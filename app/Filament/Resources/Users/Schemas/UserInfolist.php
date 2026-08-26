<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\RoleEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label(__('filament/admin/user_resource.name')),

                TextEntry::make('email')
                    ->label(__('filament/admin/user_resource.email')),

                TextEntry::make('roles.name')
                    ->label(__('filament/admin/user_resource.roles'))
                    ->badge()
                    ->formatStateUsing(
                        fn (string $state): string =>
                            RoleEnum::tryFrom($state)?->label() ?? $state
                    ),

                TextEntry::make('created_at')
                    ->label(__('filament/admin/user_resource.created_at'))
                    ->dateTime(),

                TextEntry::make('updated_at')
                    ->label(__('filament/admin/user_resource.updated_at'))
                    ->dateTime(),
            ]);
    }
}