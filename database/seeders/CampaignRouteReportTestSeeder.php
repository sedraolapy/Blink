<?php

namespace Database\Seeders;

use App\Enums\BookingItemStatusEnum;
use App\Enums\BookingStatusEnum;
use App\Enums\BookingTypeEnum;
use App\Models\AdvertisingPeriod;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\FlexBillboard;
use App\Models\FlexBooking;
use App\Models\FlexBookingItem;
use App\Models\FlexBookingPeriod;
use App\Models\FlexDesign;
use App\Services\Reports\CampaignReportPeriodService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CampaignRouteReportTestSeeder extends Seeder
{
    private const COMPANIES = [
        'alpha' => [
            'phone' => 'CR-REPORT-TEST-A',
            'ar' => 'شركة اختبار مسار الحملة ألفا',
            'en' => 'Campaign Route Test Alpha',
        ],
        'beta' => [
            'phone' => 'CR-REPORT-TEST-B',
            'ar' => 'شركة اختبار مسار الحملة بيتا',
            'en' => 'Campaign Route Test Beta',
        ],
        'gamma' => [
            'phone' => 'CR-REPORT-TEST-C',
            'ar' => 'شركة اختبار مسار الحملة غاما',
            'en' => 'Campaign Route Test Gamma',
        ],
    ];

    // [customer-key, design-key]
    // null reservation = no booking.
    // null design-key = booking without design.
    private const SCENARIOS = [
        [
            'case' => 'same_design',
            'governorate_id' => 1,
            'previous' => ['alpha', 'shared'],
            'current' => ['alpha', 'shared'],
        ],
        [
            'case' => 'changed_design',
            'governorate_id' => 1,
            'previous' => ['alpha', 'old'],
            'current' => ['alpha', 'new'],
        ],
        [
            'case' => 'current_without_design',
            'governorate_id' => 1,
            'previous' => null,
            'current' => ['gamma', null],
        ],
        [
            'case' => 'changed_customer',
            'governorate_id' => 5,
            'previous' => ['alpha', 'brand'],
            'current' => ['beta', 'brand'],
        ],
        [
            'case' => 'new_booking',
            'governorate_id' => 5,
            'previous' => null,
            'current' => ['gamma', 'campaign'],
        ],
        [
            'case' => 'previous_only',
            'governorate_id' => 6,
            'previous' => ['beta', 'old'],
            'current' => null,
        ],
        [
            'case' => 'previous_without_design',
            'governorate_id' => 6,
            'previous' => ['alpha', null],
            'current' => ['alpha', 'new'],
        ],
    ];

    public function run(): void
    {
        $this->assertLocal();

        $workingYear = (int) now('Asia/Damascus')->year;

        $current = app(CampaignReportPeriodService::class)
            ->getAvailablePeriod($workingYear);

        if ($current === null) {
            throw new RuntimeException(
                'No campaign report period is available today.'
            );
        }

        $currentYear = (int) $current['year'];
        $currentPeriodId = (int) $current['id'];

        $previousNumber = (int) $current['number'] === 1
            ? 26
            : (int) $current['number'] - 1;

        $previousYear = (int) $current['number'] === 1
            ? $currentYear - 1
            : $currentYear;

        $previousPeriodId = (int) AdvertisingPeriod::query()
            ->where('number', $previousNumber)
            ->firstOrFail()
            ->id;

        DB::transaction(function () use (
            $previousYear,
            $previousPeriodId,
            $currentYear,
            $currentPeriodId
        ): void {

            // Remove only old data from this test seeder.
            $this->deleteTestCustomers();

            $required = collect(self::SCENARIOS)
                ->countBy('governorate_id');

            // Exclude boards already used in either period.
            $occupied = DB::table('flex_booking_items as i')
                ->join(
                    'flex_booking_periods as p',
                    'p.id',
                    '=',
                    'i.flex_booking_period_id'
                )
                ->join(
                    'flex_bookings as fb',
                    'fb.id',
                    '=',
                    'p.flex_booking_id'
                )
                ->join(
                    'bookings as b',
                    'b.id',
                    '=',
                    'fb.booking_id'
                )
                ->where(function ($query) use (
                    $previousYear,
                    $previousPeriodId,
                    $currentYear,
                    $currentPeriodId
                ) {
                    $query->where(
                        function ($query) use (
                            $previousYear,
                            $previousPeriodId
                        ) {
                            $query
                                ->where(
                                    'p.advertising_period_id',
                                    $previousPeriodId
                                )
                                ->where('b.year', $previousYear);
                        }
                    )->orWhere(
                        function ($query) use (
                            $currentYear,
                            $currentPeriodId
                        ) {
                            $query
                                ->where(
                                    'p.advertising_period_id',
                                    $currentPeriodId
                                )
                                ->where('b.year', $currentYear);
                        }
                    );
                })
                ->pluck('i.flex_billboard_id')
                ->unique()
                ->all();

            $available = FlexBillboard::query()
                ->whereNotIn('id', $occupied)
                ->whereHas(
                    'area',
                    fn ($query) =>
                        $query->whereIn(
                            'governorate_id',
                            $required->keys()->all()
                        )
                )
                ->with('area')
                ->orderBy('id')
                ->get()
                ->groupBy(
                    fn (FlexBillboard $board) =>
                        (int) $board->area->governorate_id
                )
                ->map(
                    fn ($boards) => $boards->values()->all()
                )
                ->all();

            // Validate free inventory before creating anything.
            foreach ($required as $governorateId => $count) {
                $stock = count(
                    $available[$governorateId] ?? []
                );

                if ($stock < $count) {
                    throw new RuntimeException(
                        "Governorate {$governorateId}: "
                        . "need {$count} free boards, "
                        . "found {$stock}."
                    );
                }
            }

            $contexts = [];

            // Prepare customers and confirmed bookings.
            foreach (self::COMPANIES as $key => $company) {

                $customer = Customer::query()->create([
                    'phone' => $company['phone'],
                    'name' => [
                        'ar' => $company['ar'],
                        'en' => $company['en'],
                    ],
                ]);

                foreach (
                    array_unique([$previousYear, $currentYear])
                    as $year
                ) {
                    $booking = Booking::query()->forceCreate([
                        'customer_id' => $customer->id,
                        'year' => $year,
                        'booking_type' =>
                            BookingTypeEnum::LOCAL->value,
                        'status' =>
                            BookingStatusEnum::CONFIRMED->value,
                    ]);

                    $contexts[$key][$year] =
                        FlexBooking::query()->create([
                            'booking_id' => $booking->id,
                        ]);
                }
            }

            // Create each scenario in previous/current periods.
            foreach (self::SCENARIOS as $scenario) {

                $governorateId = $scenario['governorate_id'];

                $billboard = array_shift(
                    $available[$governorateId]
                );

                $this->createPeriodItem(
                    $scenario['previous'],
                    $contexts,
                    $previousYear,
                    $previousPeriodId,
                    $billboard
                );

                $this->createPeriodItem(
                    $scenario['current'],
                    $contexts,
                    $currentYear,
                    $currentPeriodId,
                    $billboard
                );
            }
        });

    }

    private function createPeriodItem(
        ?array $reservation,
        array $contexts,
        int $year,
        int $periodId,
        FlexBillboard $billboard
    ): void {

        if ($reservation === null) {
            return;
        }

        [$companyKey, $designKey] = $reservation;

        $flexBooking = $contexts[$companyKey][$year];

        $period = FlexBookingPeriod::query()
            ->firstOrCreate([
                'flex_booking_id' => $flexBooking->id,
                'advertising_period_id' => $periodId,
            ]);

        $design = $designKey === null
            ? null
            : FlexDesign::query()->firstOrCreate([
                'flex_booking_id' => $flexBooking->id,
                'name' => 'Campaign Route Test - ' . $designKey,
            ]);

        FlexBookingItem::query()->create([
            'flex_booking_period_id' => $period->id,
            'flex_billboard_id' => $billboard->id,
            'design_id' => $design?->id,
            'has_dykat' => false,
            'is_gift' => false,
            'status' => BookingItemStatusEnum::BOOKED->value,
            'order_status' => null,
        ]);
    }

    public function cleanup(): void
    {
        $this->assertLocal();

        DB::transaction(
            fn () => $this->deleteTestCustomers()
        );
    }

    private function deleteTestCustomers(): void
    {
        foreach (self::COMPANIES as $company) {
            Customer::query()
                ->where('phone', $company['phone'])
                ->where('name->en', $company['en'])
                ->get()
                ->each(
                    fn (Customer $customer) =>
                        $customer->delete()
                );
        }
    }

    private function assertLocal(): void
    {
        if (! app()->environment('local')) {
            throw new RuntimeException(
                'Test seeder is allowed only in APP_ENV=local.'
            );
        }
    }
}
