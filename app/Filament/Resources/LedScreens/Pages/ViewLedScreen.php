<?php

namespace App\Filament\Resources\LedScreens\Pages;

use App\Filament\Resources\LedScreens\LedScreenResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLedScreen extends ViewRecord
{
    protected static string $resource = LedScreenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
    public function getTitle(): string
    {
        return __('filament/admin/view_led_screen.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/view_led_screen.title');
    }

}
