<?php

namespace App\Services\Customer;

use App\Enums\SubscriptionTypeEnum;
use App\Models\Booking;
use App\Models\Customer;
use App\Services\WorkingYear\WorkingYearContext;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    public function __construct(private readonly WorkingYearContext $workingYearContext)
    {}

    public function index(array $filters): array
    {
        $year = $this->workingYearContext->get();

        $query = Customer::query()
            ->withLatestContractStatus($year)
            ->search($filters['search'] ?? null)
            ->subscriptionType(
                $filters['subscription_type'] ?? null,
                $year
            )
            ->latestContractStatus(
                $filters['contract_status'] ?? null,
                $year
            );

        $totalCustomers = (clone $query)->count();

        $customers = $query
            ->with([
                'subscriptions' => fn ($query) => $query->where('year', $year),
            ])
            ->orderByDesc('id')
            ->paginate(24);

        return [
            'summary' => [
                'total_customers' => $totalCustomers,
            ],

            'customers' => $customers,
        ];
    }

    public function create(array $data)
    {
        $year = now()->year;

        return DB::transaction(function () use ($data, $year) {
            $customer = Customer::query()->create($data);

            $customer->subscriptions()->create([
                'year' => $year,
                'subscription_type' => SubscriptionTypeEnum::BRONZE->value,
            ]);

            return $customer->load([
                'subscriptions' => fn ($query) => $query->where('year', $year),
            ]);
        });
    }

    public function show(int $id)
    {
        $year = $this->workingYearContext->get();

        return Customer::query()
            ->withLatestContractStatus($year)
            ->with([
                'subscriptions' => fn ($query) => $query->where('year', $year),
            ])
            ->withCount([
                'bookings' => fn ($query) => $query->where('year', $year),
            ])
            ->findOrFail($id);
    }

    public function update(Customer $customer, array $data)
    {
        $customer->update($data);

        $year = now()->year;

        return Customer::query()
            ->withLatestContractStatus($year)
            ->with([
                'subscriptions' => fn ($query) => $query->where('year', $year),
            ])
            ->findOrFail($customer->id);
    }

    public function bookings(int $customerId, array $filters)
    {
        $year = $this->workingYearContext->get();

        Customer::query()->findOrFail($customerId);

        return Booking::query()
            ->where('customer_id', $customerId)
            ->where('year', $year)
            ->dateRange(
                $filters['from_date'] ?? null,
                $filters['to_date'] ?? null
            )
            ->with([
                'contract.media',
                'quotation',
                'flexBooking',
                'ledBooking',
                'externalBooking',
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(24);
    }
}