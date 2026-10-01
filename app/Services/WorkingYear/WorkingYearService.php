<?php

namespace App\Services\WorkingYear;

use App\Models\WorkingYear;
use Illuminate\Database\Eloquent\Collection;

class WorkingYearService
{
    public function getAvailableYears(): Collection
    {
        $this->ensureCurrentYearIsAvailable();

        return WorkingYear::query()
            ->orderBy('year')
            ->get();
    }

    public function isAvailable(int $year): bool
    {
        $this->ensureCurrentYearIsAvailable();

        return WorkingYear::query()
            ->where('year', $year)
            ->exists();
    }

    private function ensureCurrentYearIsAvailable(): void
    {
        WorkingYear::query()->firstOrCreate([
            'year' => now()->year,
        ]);
    }
}