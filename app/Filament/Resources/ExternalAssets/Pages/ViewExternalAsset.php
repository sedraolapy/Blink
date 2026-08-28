<?php

namespace App\Filament\Resources\ExternalAssets\Pages;

use App\Filament\Resources\ExternalAssets\ExternalAssetResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewExternalAsset extends ViewRecord
{
    protected static string $resource = ExternalAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
    public function getTitle(): string
    {
        return __('filament/admin/view_external_asset.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/view_external_asset.title');
    }

}
