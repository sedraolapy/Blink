<?php

namespace App\Filament\Resources\ExternalAssets\Pages;

use App\Filament\Resources\ExternalAssets\ExternalAssetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExternalAssets extends ListRecords
{
    protected static string $resource = ExternalAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
    public function getTitle(): string
    {
        return __('filament/admin/list_external_assets.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/list_external_assets.title');
    }

}
