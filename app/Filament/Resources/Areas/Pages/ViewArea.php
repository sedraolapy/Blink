<?php

namespace App\Filament\Resources\Areas\Pages;

use App\Filament\Resources\Areas\AreaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewArea extends ViewRecord
{
    protected static string $resource = AreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
    public function getTitle(): string
    {
        return __('filament/admin/view_area.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/view_area.title');
    }

}
