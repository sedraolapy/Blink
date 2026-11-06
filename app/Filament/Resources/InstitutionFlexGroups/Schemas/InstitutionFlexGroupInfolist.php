<?php

namespace App\Filament\Resources\InstitutionFlexGroups\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class InstitutionFlexGroupInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema

            ->components([
                TextEntry::make('name')
                    ->label(__('filament/admin/institution_flex_group_resource.name'))
                    ->getStateUsing(
                        fn ($record): string =>
                            $record->getTranslation('name', app()->getLocale())
                    ),

                TextEntry::make('billboards_count')
                    ->label(__('filament/admin/institution_flex_group_resource.billboards_count'))
                    ->getStateUsing(
                        fn ($record): int => $record->billboards()->count()
                    )
                    ->numeric(),

                TextEntry::make('created_at')
                    ->label(__('filament/admin/institution_flex_group_resource.created_at'))
                    ->dateTime(),

                TextEntry::make('updated_at')
                    ->label(__('filament/admin/institution_flex_group_resource.updated_at'))
                    ->dateTime(),
            ]);

    }
}
