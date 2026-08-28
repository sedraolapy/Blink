<?php

namespace App\Filament\Resources\ExternalAssets\Pages;

use App\Filament\Resources\ExternalAssets\ExternalAssetResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditExternalAsset extends EditRecord
{
    protected static string $resource = ExternalAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
    public function getTitle(): string
    {
        return __('filament/admin/edit_external_asset.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/edit_external_asset.title');
    }

}
