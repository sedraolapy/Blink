<?php

namespace App\Services\Customer;

use App\Enums\SubscriptionTypeEnum;
use App\Models\Booking;
use App\Models\Contract;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerService
{
    public function index(array $filters): array
    {
        $query = Customer::query()
            ->search($filters['search'] ?? null)
            ->subscriptionType($filters['subscription_type'] ?? null)
            ->latestContractStatus($filters['contract_status'] ?? null);

        $totalCustomers = (clone $query)->count();

        $customers = $query
            ->addSelect([
                'latest_contract_status' => Contract::query()
                    ->select('contracts.status')
                    ->join(
                        'bookings',
                        'bookings.id',
                        '=',
                        'contracts.booking_id'
                    )
                    ->whereColumn(
                        'bookings.customer_id',
                        'customers.id'
                    )
                    ->orderByDesc('contracts.created_at')
                    ->orderByDesc('contracts.id')
                    ->limit(1),
            ])
            ->orderBy('id')
            ->paginate(24);

        return [
            'summary' => [
                'total_customers' => $totalCustomers,
            ],

            'customers' => $customers,
        ];
    }

    public function create(array $data): Customer
    {
        $data['subscription_type'] = SubscriptionTypeEnum::BRONZE->value;

        return Customer::create($data);
    }

    public function show(int $id): Customer
    {
        return Customer::query()
            ->withCount('bookings')
            ->addSelect([
                'latest_contract_status' => Contract::query()
                    ->select('contracts.status')
                    ->join(
                        'bookings',
                        'bookings.id',
                        '=',
                        'contracts.booking_id'
                    )
                    ->whereColumn(
                        'bookings.customer_id',
                        'customers.id'
                    )
                    ->orderByDesc('contracts.created_at')
                    ->orderByDesc('contracts.id')
                    ->limit(1),
            ])
            ->findOrFail($id);
    }

    public function update(Customer $customer, array $data): Customer
    {
        $customer->update($data);

        return $customer->refresh();
    }


    public function bookings(int $customerId, array $filters)
    {
        Customer::query()->findOrFail($customerId);

        return Booking::query()
            ->where('customer_id', $customerId)
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
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->paginate(24);
    }
}