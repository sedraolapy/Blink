<?php

namespace App\Services\Orders\Electronic;

use App\Enums\BookingStatusEnum;
use App\Models\Booking;
use App\Models\LedBookingItem;
use App\Services\WorkingYear\WorkingYearContext;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ElectronicOrderIssueService
{
    public function __construct(
        private readonly ElectronicOrderCandidatesService $candidatesService,
        private readonly WorkingYearContext $workingYearContext
    ) {}

    public function issue(array $data, callable $generatePdf): string
    {
        $this->validateDates($data);

        return DB::transaction(function () use ($data, $generatePdf) {

            $booking = Booking::query()
                ->where('customer_id', (int) $data['customer_id'])
                ->where('year', $this->workingYearContext->get())
                ->where('status', BookingStatusEnum::CONFIRMED->value)
                ->whereHas(
                    'ledBooking.periods',
                    fn ($query) => $query->whereKey(
                        (int) $data['period_id']
                    )
                )
                ->lockForUpdate()
                ->firstOrFail();

            $ledBooking = $booking->ledBooking()
                ->lockForUpdate()
                ->firstOrFail();

            $period = $ledBooking->periods()
                ->whereKey((int) $data['period_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $items = LedBookingItem::query()
                ->where('led_booking_period_id', $period->id)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $candidates = $this->candidatesService->getCandidates(
                (int) $data['customer_id'],
                (int) $period->id
            );

            $selection = $this->validateSelection($data,$candidates);
            $screenIds = $this->getPhysicalScreenIds($selection);
            $selectedItems = $items->whereIn('led_screen_id',$screenIds);

            if (
                $selectedItems->count() !== count($screenIds)
                || $selectedItems->contains(
                    fn (LedBookingItem $item) =>
                        $item->operation_order
                )
            ) {
                throw ValidationException::withMessages([
                    'selected_screen_ids' => [
                        __('validation.electronic_orders.invalid_selection'),
                    ],
                ]);
            }

            $pdf = $generatePdf($data, $selection);

            if (
                ! is_string($pdf)
                || ! str_starts_with($pdf, '%PDF-')
            ) {
                throw new RuntimeException(
                    'Electronic order PDF generation failed.'
                );
            }

            LedBookingItem::query()
                ->where('led_booking_period_id', $period->id)
                ->whereIn('led_screen_id', $screenIds)
                ->update([
                    'operation_order' => true,
                ]);

            $ledBooking->update([
                'operation_order' => true,
            ]);

            return $pdf;
        });
    }

    private function validateSelection(array $data,array $candidates)
    {
        $screenIds = array_map(
            'intval',
            $data['selected_screen_ids'] ?? []
        );

        $networkIds = array_map(
            'intval',
            $data['selected_network_ids'] ?? []
        );

        if (empty($screenIds) && empty($networkIds)) {
            throw ValidationException::withMessages([
                'selected_screen_ids' => [
                    __('validation.electronic_orders.empty_selection'),
                ],
            ]);
        }

        $eligibleScreens = collect($candidates['screens'])->keyBy('id');
        $eligibleNetworks = collect($candidates['networks'])->keyBy('id');

        if (
            collect($screenIds)->contains(
                fn (int $id) => ! $eligibleScreens->has($id)
            )
        ) {
            throw ValidationException::withMessages([
                'selected_screen_ids' => [
                    __('validation.electronic_orders.invalid_screens'),
                ],
            ]);
        }

        if (
            collect($networkIds)->contains(
                fn (int $id) => ! $eligibleNetworks->has($id)
            )
        ) {
            throw ValidationException::withMessages([
                'selected_network_ids' => [
                    __('validation.electronic_orders.invalid_networks'),
                ],
            ]);
        }

        return [
            'customer' => $candidates['customer'],
            'period' => $candidates['period'],

            'screens' => collect($screenIds)
                ->map(fn (int $id) => $eligibleScreens->get($id))
                ->all(),

            'networks' => collect($networkIds)
                ->map(fn (int $id) => $eligibleNetworks->get($id))
                ->all(),
        ];
    }

    private function getPhysicalScreenIds(array $selection): array
    {
        $screenIds = collect($selection['screens'])
            ->pluck('id')
            ->merge(
                collect($selection['networks'])
                    ->flatMap(
                        fn (array $network) =>
                            collect($network['screens'])->pluck('id')
                    )
            )
            ->map(fn ($id) => (int) $id)
            ->all();

        if (count($screenIds) !== count(array_unique($screenIds))) {
            throw ValidationException::withMessages([
                'selected_screen_ids' => [
                    __('validation.electronic_orders.invalid_selection'),
                ],
            ]);
        }

        return $screenIds;
    }


    private function validateDates(array $data)
    {
        $year = $this->workingYearContext->get();

        if (
            CarbonImmutable::parse($data['issue_date'])->year !== $year
        ) {
            throw ValidationException::withMessages([
                'issue_date' => [
                    __('validation.electronic_orders.issue_date_outside_year'),
                ],
            ]);
        }

        if (
            CarbonImmutable::parse($data['execution_deadline'])->year !== $year
        ) {
            throw ValidationException::withMessages([
                'execution_deadline' => [
                    __('validation.electronic_orders.deadline_outside_year'),
                ],
            ]);
        }
    }

}
