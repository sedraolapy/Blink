<?php

namespace App\Services\Governorate;

use App\Models\Governorate;
use Illuminate\Database\Eloquent\Collection;

class GovernorateService
{
    public function getAll(): Collection
    {
        return Governorate::query()
            ->orderBy('id')
            ->get();
    }
}