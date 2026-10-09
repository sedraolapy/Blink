<?php

namespace App\Services\Dashboard;

use App\Enums\ExternalAssetTypeEnum;
use App\Models\ExternalAsset;
use App\Models\FlexBillboard;
use App\Models\LedBookingItem;
use App\Models\LedNetwork;
use App\Models\LedScreen;
use App\Services\AdvertisingPeriod\AdvertisingPeriodService;
use App\Services\WorkingYear\WorkingYearContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;

class DashboardService
{
    public function __construct(
        private readonly WorkingYearContext $workingYearContext,
        private readonly AdvertisingPeriodService $advertisingPeriodService
    ) {}

    public function index(): array
    {
        $year = $this->workingYearContext->get();

        $assetCounts = $this->getAssetCounts();

        return [
            'total_assets' => $assetCounts['total'],
            'flex_current_period' => $this->getFlexCurrentPeriod($year, $assetCounts['flex']),
            'top_requested_asset' => $this->getTopRequestedAsset($year),
            'asset_distribution' => $this->getAssetDistribution($assetCounts),
        ];
    }

    private function getAssetCounts(): array
    {
        $counts = [
            'flex' => FlexBillboard::query()->count(),

            'electronic' => LedScreen::query()->count(),

            ExternalAssetTypeEnum::MURAL->value =>
                ExternalAsset::query()->type(ExternalAssetTypeEnum::MURAL)->count(),

            ExternalAssetTypeEnum::ROOFTOP->value =>
                ExternalAsset::query()->type(ExternalAssetTypeEnum::ROOFTOP)->count(),

            ExternalAssetTypeEnum::TUNNEL->value =>
                ExternalAsset::query()->type(ExternalAssetTypeEnum::TUNNEL)->count(),

            ExternalAssetTypeEnum::BRIDGE->value =>
                ExternalAsset::query()->type(ExternalAssetTypeEnum::BRIDGE)->count(),

            ExternalAssetTypeEnum::UNIPOLE->value =>
                ExternalAsset::query()->type(ExternalAssetTypeEnum::UNIPOLE)->count(),
        ];

        $counts['total'] = array_sum($counts);

        return $counts;
    }

    private function getFlexCurrentPeriod(int $year,int $totalFlex): array
    {
        $period = $this->advertisingPeriodService->getCurrentPeriodForYear($year);

        $bookedCount = FlexBillboard::query()
            ->whereHas('bookingItems',
                fn (Builder $query) =>
                    $query
                        ->confirmedForYear($year)
                        ->forAdvertisingPeriod($period->id)
            )
            ->count();

        return [
            'period' => [
                'id' => $period->id,
                'number' => $period->number,
            ],

            'booked_count' => $bookedCount,
            'unbooked_count' => $totalFlex - $bookedCount,
        ];
    }

    public function topRequestedAssets(string $type): array
    {
        $year = $this->workingYearContext->get();

        return [
            'type' => $type,

            'items' => $this
                ->getRequestedAssets(
                    $type,
                    $year,
                    10
                )
                ->map(
                    fn (array $item) =>
                        Arr::except($item, ['type'])
                )
                ->values()
                ->all(),
        ];
    }

    private function getTopRequestedAsset(int $year): ?array
    {
        return collect([
            $this
                ->getFlexRequestedAssets($year, 1)
                ->first(),

            $this
                ->getElectronicRequestedAssets($year, 1)
                ->first(),

            $this
                ->getExternalRequestedAssets($year,null,1)
                ->first(),
        ])
            ->filter()
            ->sortByDesc('booking_occurrences_count')
            ->first();
    }

    private function getRequestedAssets(string $type,int $year,int $limit): Collection
    {
        return match ($type) {
            'flex' => $this->getFlexRequestedAssets($year,$limit),
            'electronic' => $this->getElectronicRequestedAssets($year,$limit),
            default => $this->getExternalRequestedAssets($year,ExternalAssetTypeEnum::from($type),$limit),
        };
    }

    private function getFlexRequestedAssets(int $year,int $limit): Collection
    {
        return FlexBillboard::query()
            ->whereHas(
                'bookingItems',
                fn (Builder $query) =>
                    $query->confirmedForYear($year)
            )
            ->withCount([
                'bookingItems as booking_occurrences_count' =>
                    fn (Builder $query) =>
                        $query->confirmedForYear($year),
            ])
            ->orderByDesc('booking_occurrences_count')
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->map(
                fn (FlexBillboard $asset) =>
                    $this->formatAsset(
                        $asset,
                        'flex'
                    )
            );
    }

    private function getElectronicRequestedAssets(int $year,int $limit): Collection
    {
        return $this
            ->getElectronicScreens($year, $limit)
            ->merge(
                $this->getElectronicNetworks($year,$limit)
            )
            ->sortByDesc(
                'booking_occurrences_count'
            )
            ->take($limit)
            ->values();
    }

    private function getElectronicScreens(int $year,int $limit): Collection
    {
        return LedScreen::query()
            ->whereNull('network_id')
            ->whereHas(
                'bookingItems',
                fn (Builder $query) =>
                    $query
                        ->whereNull('led_network_id')
                        ->confirmedForYear($year)
            )
            ->withCount([
                'bookingItems as booking_occurrences_count' =>
                    fn (Builder $query) =>
                        $query
                            ->whereNull('led_network_id')
                            ->confirmedForYear($year),
            ])
            ->orderByDesc('booking_occurrences_count')
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->map(
                fn (LedScreen $screen) =>
                    $this->formatAsset(
                        $screen,
                        'electronic',
                        'screen'
                    )
            );
    }

    private function getElectronicNetworks(int $year,int $limit): Collection
    {
        $occurrences = LedBookingItem::query()
            ->confirmedForYear($year)
            ->whereNotNull('led_network_id')
            ->get([
                'led_network_id',
                'led_booking_period_id',
            ])
            ->groupBy('led_network_id')
            ->map(
                fn (Collection $items) =>
                    $items
                        ->pluck('led_booking_period_id')
                        ->unique()
                        ->count()
            )
            ->sortDesc()
            ->take($limit);

        if ($occurrences->isEmpty()) {
            return collect();
        }

        $networks = LedNetwork::query()
            ->whereIn('id',$occurrences->keys())
            ->get()
            ->keyBy('id');

        return $occurrences
            ->map(
                function (
                    int $count,
                    int|string $networkId
                ) use ($networks) {
                    $network = $networks->get(
                        (int) $networkId
                    );

                    if (! $network) {
                        return null;
                    }

                    $network->setAttribute('booking_occurrences_count', $count);

                    return $this->formatAsset(
                        $network,
                        'electronic',
                        'network'
                    );
                }
            )
            ->filter()
            ->values();
    }

    private function getExternalRequestedAssets(int $year,?ExternalAssetTypeEnum $type,int $limit): Collection
    {
        $query = ExternalAsset::query();

        if ($type !== null) {
            $query->type($type);
        }

        return $query
            ->whereHas(
                'bookingItems',
                fn (Builder $query) =>
                    $query->confirmedForYear($year)
            )
            ->withCount([
                'bookingItems as booking_occurrences_count' =>
                    fn (Builder $query) =>
                        $query->confirmedForYear($year),
            ])
            ->orderByDesc('booking_occurrences_count' )
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->map(
                fn (ExternalAsset $asset) =>
                    $this->formatAsset(
                        $asset,
                        $asset->type->value
                    )
            );
    }

    private function formatAsset(Model $asset,string $type,?string $electronicType = null): array
    {
        $data = [
            'id' => $asset->id,
            'type' => $type,
            'code' => $asset->code ?? null,
            'name' => $asset->location_name,
            'booking_occurrences_count' => (int) $asset->booking_occurrences_count,
        ];

        if ($electronicType !== null) {
            $data['electronic_type'] =
                $electronicType;
        }

        return $data;
    }


    private function getAssetDistribution(array $counts): array
    {
        $types = [
            'flex',
            'electronic',
            ExternalAssetTypeEnum::MURAL->value,
            ExternalAssetTypeEnum::ROOFTOP->value,
            ExternalAssetTypeEnum::TUNNEL->value,
            ExternalAssetTypeEnum::BRIDGE->value,
            ExternalAssetTypeEnum::UNIPOLE->value,
        ];

        return collect($types)
            ->map(
                fn (string $type) => [
                    'type' => $type,
                    'count' => $counts[$type],
                    'percentage' =>
                        $counts['total'] > 0
                            ? round(
                                (
                                    $counts[$type]
                                    / $counts['total']
                                ) * 100,
                                2
                            )
                            : 0,
                ]
            )
            ->values()
            ->all();
    }
}