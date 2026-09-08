<?php

namespace App\Services\Customer;

use App\Enums\SubscriptionTypeEnum;
use App\Models\Booking;
use App\Models\Customer;

class CustomerService
{
    public function index(array $filters): array
    {
        $query = Customer::query()
            ->search($filters['search'] ?? null)
            ->subscriptionType(
                $filters['subscription_type'] ?? null
            )
            ->latestContractStatus(
                $filters['contract_status'] ?? null
            );

        $totalCustomers = (clone $query)->count();

        $customers = $query
            ->with('latestContract')
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
        $data['subscription_type'] = SubscriptionTypeEnum::BRONZE->value;

        return Customer::query()->create($data);
    }

    public function show(int $id)
    {
        return Customer::query()
            ->with('latestContract')
            ->withCount('bookings')
            ->findOrFail($id);
    }

    public function update(Customer $customer,array $data)
    {
        $customer->update($data);

        return $customer->refresh();
    }

    public function bookings(int $customerId,array $filters)
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
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(24);
    }
}