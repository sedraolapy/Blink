<?php

namespace App\Services\Map;

use App\Models\ExternalAsset;
use App\Models\FlexBillboard;
use App\Models\LedScreen;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MapService
{
    public function index(array $filters): array
    {
        $type = $filters['type'] ?? null;
        $search = $filters['search'] ?? null;

        $result = [
            'flex' => collect(),
            'electronic' => collect(),
            'murals' => collect(),
            'rooftops' => collect(),
            'tunnels' => collect(),
            'bridges' => collect(),
            'unipoles' => collect(),
        ];

        if (! $type || $type === 'flex') {
            $result['flex'] = $this->getFlex($search);
        }

        if (! $type || $type === 'electronic') {
            $result['electronic'] = $this->getElectronic($search);
        }

        $externalTypes = [
            'mural' => 'murals',
            'rooftop' => 'rooftops',
            'tunnel' => 'tunnels',
            'bridge' => 'bridges',
            'unipole' => 'unipoles',
        ];

        foreach ($externalTypes as $externalType => $key) {
            if (! $type || $type === $externalType) {
                $result[$key] = $this->getExternal(
                    $externalType,
                    $search
                );
            }
        }

        return $result;
    }

    private function getFlex(?string $search): Collection
    {
        return FlexBillboard::query()
            ->with([
                'area.governorate',
                'media',
            ])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->when(
                $search,
                fn (Builder $query) =>
                    $this->applySearch($query, $search)
            )
            ->get();
    }

    private function getElectronic(?string $search): Collection
    {
        return LedScreen::query()
            ->with([
                'area.governorate',
                'network',
                'media',
            ])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->when(
                $search,
                fn (Builder $query) =>
                    $this->applySearch($query, $search)
            )
            ->get();
    }

    private function getExternal(string $type,?string $search): Collection
    {
        return ExternalAsset::query()
            ->with([
                'area.governorate',
                'media',
            ])
            ->where('type', $type)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->when(
                $search,
                fn (Builder $query) =>
                    $this->applySearch($query, $search)
            )
            ->get();
    }

    private function applySearch(Builder $query,string $search): Builder
    {
        $locale = app()->getLocale();

        return $query->where(
            function (Builder $query) use ($search, $locale) {
                $query
                    ->where(
                        "location_name->{$locale}",
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'code',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhereHas(
                        'area',
                        function (Builder $query) use (
                            $search,
                            $locale
                        ) {
                            $query->where(
                                "name->{$locale}",
                                'like',
                                "%{$search}%"
                            );
                        }
                    );
            }
        );
    }
}