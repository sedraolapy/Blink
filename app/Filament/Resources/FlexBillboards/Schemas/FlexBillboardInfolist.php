<?php

namespace App\Filament\Resources\FlexBillboards\Schemas;

use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
class FlexBillboardInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('code')
                    ->label(__('filament/admin/flex_billboard_resource.code')),

                TextEntry::make('location_name')
                    ->label(__('filament/admin/flex_billboard_resource.location_name')),

                TextEntry::make('area.name')
                    ->label(__('filament/admin/flex_billboard_resource.area')),

                TextEntry::make('area.governorate.name')
                    ->label(__('filament/admin/flex_billboard_resource.governorate')),

                TextEntry::make('latitude')
                    ->label(__('filament/admin/flex_billboard_resource.latitude')),

                TextEntry::make('longitude')
                    ->label(__('filament/admin/flex_billboard_resource.longitude')),

                TextEntry::make('width')
                    ->label(__('filament/admin/flex_billboard_resource.width')),

                TextEntry::make('height')
                    ->label(__('filament/admin/flex_billboard_resource.height')),

                TextEntry::make('local_price')
                    ->label(__('filament/admin/flex_billboard_resource.local_price'))
                    ->numeric(),

                TextEntry::make('foreign_price')
                    ->label(__('filament/admin/flex_billboard_resource.foreign_price'))
                    ->numeric(),

                TextEntry::make('created_at')
                    ->label(__('filament/admin/flex_billboard_resource.created_at'))
                    ->dateTime(),

                TextEntry::make('updated_at')
                    ->label(__('filament/admin/flex_billboard_resource.updated_at'))
                    ->dateTime(),

                    Action::make('view_video')
                    ->label(__('filament/admin/flex_billboard_resource.view_video'))
                    ->icon('heroicon-o-play-circle')
                    ->color('primary')
                    ->url(
                        fn ($record) => $record->getFirstMediaUrl('flex_billboard_video')
                    )
                    ->openUrlInNewTab()
                    ->visible(
                        fn ($record) => $record->hasMedia('flex_billboard_video')
                    ),
            ]);
    }
}