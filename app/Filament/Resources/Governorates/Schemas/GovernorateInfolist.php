<?php

namespace App\Filament\Resources\Governorates\Schemas;

use App\Enums\DisplayGroupEnum;
use App\Enums\InstallationDayEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class GovernorateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label(__('filament/admin/governorate_resource.name')),

                TextEntry::make('display_group')
                    ->label(__('filament/admin/governorate_resource.display_group'))
                    ->badge()
                    ->formatStateUsing(
                        fn ($state): string =>
                            $state instanceof DisplayGroupEnum
                                ? $state->label()
                                : (DisplayGroupEnum::tryFrom($state)?->label() ?? $state)
                    ),

                TextEntry::make('installation_day')
                    ->label(__('filament/admin/governorate_resource.installation_day'))
                    ->badge()
                    ->formatStateUsing(
                        fn ($state): string =>
                            $state instanceof InstallationDayEnum
                                ? $state->label()
                                : (InstallationDayEnum::tryFrom($state)?->label() ?? $state)
                    ),

                TextEntry::make('created_at')
                    ->label(__('filament/admin/governorate_resource.created_at'))
                    ->dateTime(),

                TextEntry::make('updated_at')
                    ->label(__('filament/admin/governorate_resource.updated_at'))
                    ->dateTime(),
            ]);
    }
}