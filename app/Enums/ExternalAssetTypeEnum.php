<?php

namespace App\Enums;

enum ExternalAssetTypeEnum: string
{
    case UNIPOLE = 'unipole';
    case BRIDGE = 'bridge';
    case TUNNEL = 'tunnel';
    case MURAL = 'mural';
    case ROOFTOP = 'rooftop';
}