<?php

namespace App\Filament\Resources\LedScreens\Pages;

use App\Filament\Resources\LedScreens\LedScreenResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditLedScreen extends EditRecord
{
    protected static string $resource = LedScreenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
    public function getTitle(): string
    {
        return __('filament/admin/edit_led_screen.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/edit_led_screen.title');
    }

}
