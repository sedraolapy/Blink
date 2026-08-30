<?php

namespace App\Filament\Resources\ExternalAssets\Schemas;

use App\Enums\ExternalAssetTypeEnum;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ExternalAssetInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('code')
                    ->label(__('filament/admin/external_asset_resource.code'))
                    ->badge()
                    ->columnSpanFull(),

                TextEntry::make('type')
                    ->label(__('filament/admin/external_asset_resource.type'))
                    ->formatStateUsing(
                        fn ($state) => $state instanceof ExternalAssetTypeEnum
                            ? $state->label()
                            : ExternalAssetTypeEnum::from($state)->label()
                    ),

                TextEntry::make('location_name')
                    ->label(__('filament/admin/external_asset_resource.location_name')),

                TextEntry::make('area.name')
                    ->label(__('filament/admin/external_asset_resource.area')),

                TextEntry::make('area.governorate.name')
                    ->label(__('filament/admin/external_asset_resource.governorate')),

                TextEntry::make('latitude')
                    ->label(__('filament/admin/external_asset_resource.latitude')),

                TextEntry::make('longitude')
                    ->label(__('filament/admin/external_asset_resource.longitude')),

                TextEntry::make('width')
                    ->label(__('filament/admin/external_asset_resource.width')),

                TextEntry::make('height')
                    ->label(__('filament/admin/external_asset_resource.height')),

                TextEntry::make('local_price')
                    ->label(__('filament/admin/external_asset_resource.local_price'))
                    ->numeric(),

                TextEntry::make('foreign_price')
                    ->label(__('filament/admin/external_asset_resource.foreign_price'))
                    ->numeric(),

                TextEntry::make('created_at')
                    ->label(__('filament/admin/external_asset_resource.created_at'))
                    ->dateTime(),

                TextEntry::make('updated_at')
                    ->label(__('filament/admin/external_asset_resource.updated_at'))
                    ->dateTime(),
                    
                Action::make('view_video')
                    ->label(__('filament/admin/external_asset_resource.view_video'))
                    ->icon('heroicon-o-play-circle')
                    ->color('primary')
                    ->url(
                        fn ($record) => $record->getFirstMediaUrl('external_asset_video')
                    )
                    ->openUrlInNewTab()
                    ->visible(
                        fn ($record) => $record->hasMedia('external_asset_video')
                    ),
            ]);
    }
}