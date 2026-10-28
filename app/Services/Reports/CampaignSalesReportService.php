<?php

namespace App\Services\Reports;

use App\Enums\BookingItemStatusEnum;
use App\Enums\BookingStatusEnum;
use App\Models\FlexBookingItem;
use App\Models\Governorate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LogicException;

class CampaignSalesReportService
{
    public function buildReport(array $period): array
    {
        $year = (int) $period['year'];
        $periodId = (int) $period['id'];

        $damascusId = (int) Governorate::query()
            ->where('name->en', 'Damascus')
            ->firstOrFail()
            ->id;

        $bookingItems = $this->getBookingItems($year,$periodId);
        $rows = $this->buildItems($bookingItems,$damascusId);
        $summary = $this->buildSummary($rows,$damascusId);

        return [
            'period' => $period,
            'items' => $rows,
            'summary' => $summary,
        ];
    }

    private function getBookingItems(int $year,int $periodId): Collection
    {
        $items = FlexBookingItem::query()
            ->forAdvertisingPeriod($periodId)
            ->whereIn('status', [
                BookingItemStatusEnum::BOOKED->value,
                BookingItemStatusEnum::UNCONFIRMED->value,
            ])
            ->whereHas(
                'period.flexBooking.booking',
                fn ($query) => $query
                    ->where('year', $year)
                    ->whereIn('status', [
                        BookingStatusEnum::CONFIRMED->value,
                        BookingStatusEnum::UNCONFIRMED->value,
                    ])
            )
            ->with([
                'period.flexBooking.booking.customer',
                'billboard.area.governorate',
            ])
            ->get();

        $items = $items->filter(function ($item) {
            $bookingStatus = $item
                ->period
                ->flexBooking
                ->booking
                ->status;

            return match ($bookingStatus) {
                BookingStatusEnum::CONFIRMED =>
                    $item->status === BookingItemStatusEnum::BOOKED,

                BookingStatusEnum::UNCONFIRMED =>
                    $item->status === BookingItemStatusEnum::UNCONFIRMED,

                default => false,
            };
        });

        if (
            $items->pluck('flex_billboard_id')
                ->unique()
                ->count() !== $items->count()
        ) {
            throw new LogicException(
                "Duplicate flex billboard reservations "
                . "found in year {$year}, "
                . "period {$periodId}."
            );
        }

        return $items->values();
    }

    private function buildItems(Collection $bookingItems,int $damascusId): Collection
    {
        return $bookingItems
            ->groupBy(function ($item) {
                $booking = $item
                    ->period
                    ->flexBooking
                    ->booking;

                return $booking->customer_id
                    . ':' . $booking->status->value;
            })
            ->map(function ($items) use ($damascusId) {
                $first = $items->first();

                $booking = $first
                    ->period
                    ->flexBooking
                    ->booking;

                $damascusCount = $items
                    ->filter(
                        fn ($item) =>
                            (int) $item
                                ->billboard
                                ->area
                                ->governorate_id
                            === $damascusId
                    )
                    ->count();

                $otherCount = $items->count()
                    - $damascusCount;

                return [
                    'customer' => $booking->customer,
                    'booking_status' =>$booking->status->value,
                    'damascus_boards_count' => $damascusCount,
                    'other_governorates_boards_count' => $otherCount,
                    'total_boards_count' => $items->count(),
                ];
            })
            ->sortBy(fn (array $row) => sprintf(
                '%010d-%d',
                $row['customer']->id,
                $row['booking_status'] === 'confirmed'
                    ? 0
                    : 1
            ))
            ->values();
    }

    private function buildSummary(Collection $rows,int $damascusId): array
    {
        $inventory = DB::table('flex_billboards as fb')
            ->join(
                'areas as a',
                'a.id',
                '=',
                'fb.area_id'
            )
            ->select('a.governorate_id')
            ->selectRaw('COUNT(*) as boards_count')
            ->groupBy('a.governorate_id')
            ->pluck(
                'boards_count',
                'governorate_id'
            );

        $damascusInventory = (int) $inventory->get($damascusId, 0);
        $otherInventory = (int) $inventory->sum()  - $damascusInventory;

        $confirmed = $rows->where( 'booking_status',BookingStatusEnum::CONFIRMED->value);
        $unconfirmed = $rows->where('booking_status', BookingStatusEnum::UNCONFIRMED->value);

        $damascusConfirmed = (int) $confirmed->sum('damascus_boards_count');
        $damascusUnconfirmed = (int) $unconfirmed->sum('damascus_boards_count');
        $otherConfirmed = (int) $confirmed->sum('other_governorates_boards_count');
        $otherUnconfirmed = (int) $unconfirmed->sum('other_governorates_boards_count');

        $damascusBooked = $damascusConfirmed + $damascusUnconfirmed;
        $otherBooked = $otherConfirmed + $otherUnconfirmed;

        $damascusRemaining = $damascusInventory - $damascusConfirmed;
        $otherRemaining = $otherInventory - $otherConfirmed;

        return [
            'damascus' => [
                'inventory_boards_count' => $damascusInventory,
                'booked_boards_count' => $damascusBooked,
                'confirmed_boards_count' => $damascusConfirmed,
                'unconfirmed_boards_count' => $damascusUnconfirmed,
                'remaining_boards_count' => $damascusRemaining,
            ],

            'other_governorates' => [
                'inventory_boards_count' => $otherInventory,
                'booked_boards_count' => $otherBooked,
                'confirmed_boards_count' => $otherConfirmed,
                'unconfirmed_boards_count' => $otherUnconfirmed,
                'remaining_boards_count' => $otherRemaining,
            ],

            'total' => [
                'booked_boards_count' =>  $damascusBooked + $otherBooked,
                'confirmed_boards_count' => $damascusConfirmed + $otherConfirmed,
                'unconfirmed_boards_count' => $damascusUnconfirmed + $otherUnconfirmed,
                'remaining_boards_count' => $damascusRemaining + $otherRemaining,
            ],
        ];
    }
}
