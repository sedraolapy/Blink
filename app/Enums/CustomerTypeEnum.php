<?php

namespace App\Enums;

enum CustomerTypeEnum: string
{
    case LOCAL = 'local';
    case FOREIGN = 'foreign';
}