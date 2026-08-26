<?php

namespace App\Services\Customer;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerService
{
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return Customer::query()
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Customer
    {
        return Customer::create($data);
    }

    public function show(Customer $customer): Customer
    {
        return $customer;
    }

    public function update(Customer $customer, array $data): Customer
    {
        $customer->update($data);

        return $customer->refresh();
    }
}