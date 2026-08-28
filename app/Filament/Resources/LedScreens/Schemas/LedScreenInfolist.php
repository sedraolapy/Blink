<?php

namespace App\Filament\Resources\LedScreens\Schemas;

use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class LedScreenInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('code')
                    ->label(__('filament/admin/led_screen_resource.code'))
                    ->badge()
                    ->columnSpanFull(),

                TextEntry::make('network.location_name')
                    ->label(__('filament/admin/led_screen_resource.network'))
                    ->placeholder('-'),

                TextEntry::make('location_name')
                    ->label(__('filament/admin/led_screen_resource.location_name')),

                TextEntry::make('area.name')
                    ->label(__('filament/admin/led_screen_resource.area')),

                TextEntry::make('area.governorate.name')
                    ->label(__('filament/admin/led_screen_resource.governorate')),

                TextEntry::make('latitude')
                    ->label(__('filament/admin/led_screen_resource.latitude')),

                TextEntry::make('longitude')
                    ->label(__('filament/admin/led_screen_resource.longitude')),

                TextEntry::make('width')
                    ->label(__('filament/admin/led_screen_resource.width')),

                TextEntry::make('height')
                    ->label(__('filament/admin/led_screen_resource.height')),

                TextEntry::make('width_px')
                    ->label(__('filament/admin/led_screen_resource.width_px')),

                TextEntry::make('height_px')
                    ->label(__('filament/admin/led_screen_resource.height_px')),

                TextEntry::make('local_price_display')
                    ->label(__('filament/admin/led_screen_resource.local_price'))
                    ->state(fn ($record) =>
                        $record->network_id
                            ? $record->network?->local_price
                            : $record->local_price
                    )
                    ->numeric()
                    ->placeholder('-'),

                TextEntry::make('foreign_price_display')
                    ->label(__('filament/admin/led_screen_resource.foreign_price'))
                    ->state(fn ($record) =>
                        $record->network_id
                            ? $record->network?->foreign_price
                            : $record->foreign_price
                    )
                    ->numeric()
                    ->placeholder('-'),

                TextEntry::make('created_at')
                    ->label(__('filament/admin/led_screen_resource.created_at'))
                    ->dateTime(),

                TextEntry::make('updated_at')
                    ->label(__('filament/admin/led_screen_resource.updated_at'))
                    ->dateTime(),

                Action::make('view_video')
                    ->label(__('filament/admin/led_screen_resource.view_video'))
                    ->icon('heroicon-o-play-circle')
                    ->color('primary')
                    ->url(
                        fn ($record) => $record->getFirstMediaUrl('led_screen_video')
                    )
                    ->openUrlInNewTab()
                    ->visible(
                        fn ($record) => $record->hasMedia('led_screen_video')
                    ),
            ]);
    }
}