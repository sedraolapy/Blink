<?php

namespace Database\Seeders;

use App\Enums\BookingItemStatusEnum;
use App\Enums\BookingStatusEnum;
use App\Enums\BookingTypeEnum;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\FlexBillboard;
use App\Models\FlexBooking;
use App\Models\FlexBookingItem;
use App\Models\FlexBookingPeriod;
use App\Models\FlexDesign;
use App\Services\Reports\CampaignReportPeriodService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CampaignStartReportTestSeeder extends Seeder
{
    private const COMPANIES = [
        [
            'phone' => 'CS-REPORT-TEST-A',
            'ar' => 'شركة اختبار الحملة ألفا',
            'en' => 'Campaign Test Alpha',
            'installation' => [1, 1, 5, 6],
            'extension' => [],
        ],
        [
            'phone' => 'CS-REPORT-TEST-B',
            'ar' => 'شركة اختبار الحملة بيتا',
            'en' => 'Campaign Test Beta',
            'installation' => [],
            'extension' => [1, 5, 5, 7],
        ],
        [
            'phone' => 'CS-REPORT-TEST-C',
            'ar' => 'شركة اختبار الحملة غاما',
            'en' => 'Campaign Test Gamma',
            'installation' => [6, 8],
            'extension' => [7, 8],
        ],
    ];

    public function run(): void
    {
        $this->assertLocal();

        $currentYear = (int) now('Asia/Damascus')->year;

        $period = app(CampaignReportPeriodService::class)
        ->getAvailablePeriod($currentYear);

        if ($period === null) {
            throw new RuntimeException(
                'No campaign-start report period is available today.'
            );
        }

        DB::transaction(function () use ($period): void {

            // Remove only previous data created by this seeder.
            $this->deleteTestCustomers();

            $year = (int) $period['year'];
            $periodId = (int) $period['id'];

            $required = collect(self::COMPANIES)
                ->flatMap(fn (array $company) => array_merge(
                    $company['installation'],
                    $company['extension']
                ))
                ->countBy();

            // Use existing billboards that are not used
            // in this period and year by any booking.
            $available = FlexBillboard::query()
                ->whereHas(
                    'area',
                    fn (Builder $query) =>
                        $query->whereIn(
                            'governorate_id',
                            $required->keys()->all()
                        )
                )
                ->whereDoesntHave(
                    'bookingItems',
                    fn (Builder $query) =>
                        $query
                            ->forAdvertisingPeriod($periodId)
                            ->whereHas(
                                'period.flexBooking.booking',
                                fn (Builder $booking) =>
                                    $booking->where('year', $year)
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
                    fn (Collection $boards) =>
                        $boards->pluck('id')->all()
                )
                ->all();

            // Verify inventory availability before creating data.
            foreach ($required as $governorateId => $count) {

                $availableCount = count(
                    $available[$governorateId] ?? []
                );

                if ($availableCount < $count) {
                    throw new RuntimeException(
                        "Not enough free flex boards in governorate "
                        . "{$governorateId}. Required: {$count}, "
                        . "available: {$availableCount}"
                    );
                }
            }

            foreach (self::COMPANIES as $company) {

                $customer = Customer::query()->create([
                    'phone' => $company['phone'],
                    'name' => [
                        'ar' => $company['ar'],
                        'en' => $company['en'],
                    ],
                ]);

                $booking = Booking::query()->forceCreate([
                    'customer_id' => $customer->id,
                    'year' => $year,
                    'booking_type' => BookingTypeEnum::LOCAL->value,
                    'status' => BookingStatusEnum::CONFIRMED->value,
                ]);

                $flexBooking = FlexBooking::query()->create([
                    'booking_id' => $booking->id,

                    'installation_order' =>
                        count($company['installation']) > 0,

                    'extension_order' =>
                        count($company['extension']) > 0,
                ]);

                $bookingPeriod = FlexBookingPeriod::query()
                    ->create([
                        'flex_booking_id' => $flexBooking->id,
                        'advertising_period_id' => $periodId,
                    ]);

                $design = FlexDesign::query()->create([
                    'flex_booking_id' => $flexBooking->id,
                    'name' => 'Campaign Start Report Test',
                ]);

                foreach (['installation', 'extension'] as $type) {

                    foreach ($company[$type] as $governorateId) {

                        $boardId = array_shift(
                            $available[$governorateId]
                        );

                        FlexBookingItem::query()->create([
                            'flex_booking_period_id' =>
                                $bookingPeriod->id,

                            'flex_billboard_id' => $boardId,

                            'design_id' => $design->id,

                            'status' =>
                                BookingItemStatusEnum::BOOKED->value,

                            'order_status' => $type,

                            'has_dykat' => false,
                            'is_gift' => false,
                        ]);
                    }
                }
            }
        });

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
        Customer::query()
            ->whereIn(
                'phone',
                array_column(self::COMPANIES, 'phone')
            )
            ->get()
            ->each(
                fn (Customer $customer) =>
                    $customer->delete()
            );
    }

    private function assertLocal(): void
    {
        if (! app()->environment('local')) {
            throw new RuntimeException(
                'CampaignStartReportTestSeeder is only '
                . 'allowed in APP_ENV=local.'
            );
        }
    }
}
