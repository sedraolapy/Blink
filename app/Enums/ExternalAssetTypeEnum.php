<?php

namespace App\Enums;

enum ExternalAssetTypeEnum: string
{
    case UNIPOLE = 'unipole';
    case BRIDGE = 'bridge';
    case TUNNEL = 'tunnel';
    case MURAL = 'mural';
    case ROOFTOP = 'rooftop';

    public function label(): string
    {
        return __("enums.external_asset_types.{$this->value}");
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type) => [
                $type->value => $type->label(),
            ])
            ->toArray();
    }
}