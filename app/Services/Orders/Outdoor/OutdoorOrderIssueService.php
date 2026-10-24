<?php

namespace App\Services\Orders\Outdoor;

use App\Enums\BookingItemStatusEnum;
use App\Enums\BookingStatusEnum;
use App\Models\Booking;
use App\Services\WorkingYear\WorkingYearContext;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class OutdoorOrderIssueService
{
    public function __construct(
        private readonly OutdoorOrderCandidatesService $candidatesService,
        private readonly WorkingYearContext $workingYearContext
    ) {}

    public function issue(array $data,callable $generatePdf)
    {
        $this->validateDates($data);

        return DB::transaction(function () use ($data, $generatePdf) {

            $booking = Booking::query()
                ->where('customer_id', (int) $data['customer_id'])
                ->where('year', $this->workingYearContext->get())
                ->where(
                    'status',
                    BookingStatusEnum::CONFIRMED->value
                )
                ->whereHas(
                    'externalBooking.types.periods',
                    fn (Builder $query) =>
                        $query->whereKey(
                            (int) $data['period_id']
                        )
                )
                ->lockForUpdate()
                ->firstOrFail();

            $externalBooking = $booking
                ->externalBooking()
                ->lockForUpdate()
                ->firstOrFail();

            $outdoorType = $externalBooking
                ->types()
                ->where('type', $data['outdoor_type'])
                ->lockForUpdate()
                ->firstOrFail();

            $period = $outdoorType
                ->periods()
                ->whereKey((int) $data['period_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $items = $period
                ->items()
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $candidates = $this->candidatesService
                ->getCandidates(
                    $data['outdoor_type'],
                    (int) $data['customer_id'],
                    (int) $data['period_id']
                );

            $selection = $this->validateSelection(
                $data,
                $items,
                $candidates
            );

            $pdf = $generatePdf($data, $selection);

            if (
                ! is_string($pdf)
                || ! str_starts_with($pdf, '%PDF-')
            ) {
                throw new RuntimeException(
                    'Outdoor order PDF generation failed.'
                );
            }

            $updated = $period
                ->items()
                ->whereIn(
                    'external_asset_id',
                    $data['selected_asset_ids']
                )
                ->where(
                    'status',
                    BookingItemStatusEnum::BOOKED->value
                )
                ->whereNull('order_status')
                ->update([
                    'order_status' => $data['type'],
                ]);

            if ($updated !== count($data['selected_asset_ids'])) {
                throw ValidationException::withMessages([
                    'selected_asset_ids' => [
                        __('validation.outdoor_orders.invalid_selected'),
                    ],
                ]);
            }

            return $pdf;
        });
    }

    private function validateSelection(array $data,Collection $items,array $candidates)
    {
        $selectedIds = array_map(
            'intval',
            $data['selected_asset_ids']
        );

        $excludedIds = array_map(
            'intval',
            $data['excluded_asset_ids']
        );

        if (! empty(array_intersect($selectedIds, $excludedIds))) {
            throw ValidationException::withMessages([
                'selected_asset_ids' => [
                    __('validation.outdoor_orders.selection_overlap'),
                ],
            ]);
        }

        $eligibleItems = $items
            ->filter(
                fn ($item) =>
                    $item->status === BookingItemStatusEnum::BOOKED
                    && $item->order_status === null
            )
            ->keyBy('external_asset_id');

        $eligibleIds = $eligibleItems
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->all();

        if (! empty(array_diff($selectedIds, $eligibleIds))) {
            throw ValidationException::withMessages([
                'selected_asset_ids' => [
                    __('validation.outdoor_orders.invalid_selected'),
                ],
            ]);
        }

        if (! empty(array_diff($excludedIds, $eligibleIds))) {
            throw ValidationException::withMessages([
                'excluded_asset_ids' => [
                    __('validation.outdoor_orders.invalid_excluded'),
                ],
            ]);
        }

        foreach ($selectedIds as $assetId) {
            if ($eligibleItems->get($assetId)->design_id === null) {
                throw ValidationException::withMessages([
                    'selected_asset_ids' => [
                        __('validation.outdoor_orders.design_required'),
                    ],
                ]);
            }
        }

        $candidateItems = collect($candidates['items'])
            ->keyBy('asset.id');

        return [
            'customer' => $candidates['customer'],
            'period' => $candidates['period'],
            'outdoor_type' => $data['outdoor_type'],

            'selected_items' => collect($selectedIds)
                ->map(fn (int $id) => $candidateItems->get($id))
                ->all(),

            'excluded_items' => collect($excludedIds)
                ->map(fn (int $id) => $candidateItems->get($id))
                ->all(),
        ];
    }

    private function validateDates(array $data): void
    {
        $year = $this->workingYearContext->get();

        if (
            CarbonImmutable::parse($data['issue_date'])->year !== $year
        ) {
            throw ValidationException::withMessages([
                'issue_date' => [
                    __('validation.outdoor_orders.issue_date_outside_year'),
                ],
            ]);
        }

        if (
            CarbonImmutable::parse($data['execution_deadline'])->year !== $year
        ) {
            throw ValidationException::withMessages([
                'execution_deadline' => [
                    __('validation.outdoor_orders.deadline_outside_year'),
                ],
            ]);
        }
    }


}
