<?php

namespace App\Services\Orders\Flex;

use App\Enums\BookingItemStatusEnum;
use App\Models\AdvertisingPeriod;
use App\Models\FlexBillboard;
use App\Models\FlexBooking;
use App\Models\FlexBookingItem;
use App\Services\WorkingYear\WorkingYearContext;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class FlexOrderIssueService
{
    public function __construct(
        private readonly FlexOrderCandidatesService $candidatesService,
        private readonly WorkingYearContext $workingYearContext
    ) {}

    public function validateSelection(array $data): array
    {
        $periodId = (int) $data['period_id'];
        $type = $data['type'];

        $candidates = $this->candidatesService->getCandidates($periodId);

        $eligibleItems = collect($candidates['items'])
            ->filter(
                fn (array $item) => in_array(
                    $item['classification'],
                    [$type, 'unclassified'],
                    true
                )
            )
            ->keyBy(fn (array $item) => (int) $item['flex']['id']);

        $eligibleIds = $eligibleItems
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->all();

        $selectedIds = array_map(
            'intval',
            $data['selected_flex_ids']
        );

        $excludedIds = array_map(
            'intval',
            $data['excluded_flex_ids']
        );

        if (! empty(array_intersect($selectedIds, $excludedIds))) {
            throw ValidationException::withMessages([
                'selected_flex_ids' => [
                    __('validation.flex_orders.selection_overlap'),
                ],
            ]);
        }

        if (! empty(array_diff($selectedIds, $eligibleIds))) {
            throw ValidationException::withMessages([
                'selected_flex_ids' => [__('validation.flex_orders.invalid_selected'),],
            ]);
        }

        if (! empty(array_diff($excludedIds, $eligibleIds))) {
            throw ValidationException::withMessages([
                'excluded_flex_ids' => [__('validation.flex_orders.invalid_excluded'),],
            ]);
        }

        $submittedIds = array_merge($selectedIds,$excludedIds);

        if (
            count($submittedIds) !== count($eligibleIds)
            || ! empty(array_diff($eligibleIds, $submittedIds))
        ) {
            throw ValidationException::withMessages([
                'selected_flex_ids' => [__('validation.flex_orders.incomplete_selection'),],
            ]);
        }

        return [
            'period' => $candidates['period'],
            'type' => $type,

            'selected_items' => collect($selectedIds)
                ->map(fn (int $id) => $eligibleItems->get($id))
                ->all(),

            'excluded_items' => collect($excludedIds)
                ->map(fn (int $id) => $eligibleItems->get($id))
                ->all(),
        ];
    }


    public function validateDates(array $data,array $selection): void
    {
        $year = $this->workingYearContext->get();

        $issueDate = $data['issue_date'];
        $executionDeadline = $data['execution_deadline'];

        if (
            CarbonImmutable::parse($issueDate)->year !== $year
        ) {
            throw ValidationException::withMessages([
                'issue_date' => [
                    __('validation.flex_orders.issue_date_outside_year'),
                ],
            ]);
        }

        if (
            CarbonImmutable::parse($executionDeadline)->year !== $year
        ) {
            throw ValidationException::withMessages([
                'execution_deadline' => [
                    __('validation.flex_orders.deadline_outside_year'),
                ],
            ]);
        }

        $period = AdvertisingPeriod::query()
            ->with('ranges')
            ->findOrFail((int) $data['period_id']);

        $selectedIds = collect($selection['selected_items'])
            ->pluck('flex.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $billboards = FlexBillboard::query()
            ->with('area.governorate')
            ->whereIn('id', $selectedIds)
            ->get();

        if ($billboards->count() !== count($selectedIds)) {
            throw ValidationException::withMessages([
                'selected_flex_ids' => [
                    __('validation.flex_orders.invalid_selected'),
                ],
            ]);
        }

        foreach ($billboards as $billboard) {
            $displayGroup = $billboard
                ->area
                ?->governorate
                ?->display_group;

            $range = $period->ranges->first(
                fn ($range) =>
                    $range->display_group === $displayGroup
            );

            if (! $range || $displayGroup === null) {
                throw ValidationException::withMessages([
                    'execution_deadline' => [
                        __('validation.flex_orders.period_range_missing'),
                    ],
                ]);
            }

            $periodStart = CarbonImmutable::create(
                $year,
                (int) $range->start_month,
                (int) $range->start_day
            )->toDateString();

            $periodEnd = CarbonImmutable::create(
                $year,
                (int) $range->end_month,
                (int) $range->end_day
            )->toDateString();

            if (
                $executionDeadline < $periodStart
                || $executionDeadline > $periodEnd
            ) {
                throw ValidationException::withMessages([
                    'execution_deadline' => [
                        __('validation.flex_orders.deadline_outside_period'),
                    ],
                ]);
            }
        }
    }


    private function lockPeriodForIssuance(int $periodId): void
    {
        $year = $this->workingYearContext->get();

        AdvertisingPeriod::query()
            ->whereKey($periodId)
            ->lockForUpdate()
            ->firstOrFail();

        FlexBookingItem::query()
            ->forAdvertisingPeriod($periodId)
            ->confirmedForYear($year)
            ->where(
                'status',
                BookingItemStatusEnum::BOOKED->value
            )
            ->orderBy('id')
            ->lockForUpdate()
            ->get(['id']);
    }


    private function runValidatedIssuance(array $data,callable $operation): mixed
    {
        return DB::transaction(function () use ($data, $operation) {

            $periodId = (int) $data['period_id'];
            $this->lockPeriodForIssuance($periodId);
            $selection = $this->validateSelection($data);
            $this->validateDates($data, $selection);
            return $operation($data, $selection);
        });
    }


    private function applyIssuanceStatuses(array $data,array $selection): void
    {
        $year = $this->workingYearContext->get();
        $periodId = (int) $data['period_id'];
        $type = $data['type'];

        $selectedIds = collect($selection['selected_items'])
            ->pluck('flex.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $excludedIds = collect($selection['excluded_items'])
            ->pluck('flex.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $allIds = array_merge($selectedIds, $excludedIds);

        $rows = FlexBookingItem::query()
            ->forAdvertisingPeriod($periodId)
            ->confirmedForYear($year)
            ->where(
                'status',
                BookingItemStatusEnum::BOOKED->value
            )
            ->whereIn('flex_billboard_id', $allIds)
            ->with('period')
            ->get();

        if (
            $rows->count() !== count($allIds)
            || $rows->pluck('flex_billboard_id')->unique()->count()
                !== count($allIds)
        ) {
            throw ValidationException::withMessages([
                'selected_flex_ids' => [
                    __('validation.flex_orders.invalid_selected'),
                ],
            ]);
        }

        $items = $rows->keyBy('flex_billboard_id');

        foreach ($selectedIds as $flexId) {
            $items->get($flexId)->update([
                'order_status' => $type,
            ]);
        }

        foreach ($excludedIds as $flexId) {
            $items->get($flexId)->update([
                'order_status' => 'unclassified',
            ]);
        }

        $flexBookingIds = $rows
            ->pluck('period.flex_booking_id')
            ->unique();

        foreach ($flexBookingIds as $flexBookingId) {

            $hasInstallation = FlexBookingItem::query()
                ->whereHas('period', function ($query) use ($flexBookingId) {
                    $query->where('flex_booking_id', $flexBookingId);
                })
                ->where('order_status', 'installation')
                ->exists();

            $hasExtension = FlexBookingItem::query()
                ->whereHas('period', function ($query) use ($flexBookingId) {
                    $query->where('flex_booking_id', $flexBookingId);
                })
                ->where('order_status', 'extension')
                ->exists();

            FlexBooking::query()
                ->whereKey($flexBookingId)
                ->update([
                    'installation_order' => $hasInstallation,
                    'extension_order' => $hasExtension,
                ]);
        }
    }


    public function issue(array $data,callable $generatePdf): string
    {
        return $this->runValidatedIssuance(
            $data,
            function (array $validatedData,array $selection)
                use ($generatePdf): string
            {

                $pdf = $generatePdf($validatedData,$selection);

                if (
                    ! is_string($pdf)
                    || ! str_starts_with($pdf, '%PDF-')
                ) {
                    throw new RuntimeException('Flex order PDF generation failed.');
                }

                $this->applyIssuanceStatuses($validatedData,$selection);

                return $pdf;
            }
        );
    }





}
