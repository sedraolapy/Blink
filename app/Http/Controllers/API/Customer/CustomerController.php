<?php

namespace App\Http\Controllers\API\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Http\Resources\Customer\CustomerResource;
use App\Models\Customer;
use App\Services\Customer\CustomerService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(private readonly CustomerService $customerService) {}

    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 15);
        $customers = $this->customerService->getAll($perPage);

        return sendResponse(
            __('messages.customers_retrieved'),
            CustomerResource::collection($customers->items())->resolve($request),
            200,
            $customers
        );
    }

    public function store(StoreCustomerRequest $request)
    {
        $data = $request->validated();
        $customer = $this->customerService->create($data);

        return sendResponse(
            __('messages.customer_created'),
            new CustomerResource($customer),
            201
        );
    }

    public function show(Customer $customer)
    {
        $customer = $this->customerService->show($customer);
        return sendResponse(
            __('messages.customer_retrieved'),
            new CustomerResource($customer)
        );
    }

    public function update(UpdateCustomerRequest $request,Customer $customer)
    {
        $data =  $request->validated();
        $customer = $this->customerService->update($customer,$data);

        return sendResponse(
            __('messages.customer_updated'),
            new CustomerResource($customer)
        );
    }
}
