<?php

namespace App\Http\Controllers\API\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CustomerBookingsRequest;
use App\Http\Requests\Customer\CustomerIndexRequest;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Http\Resources\Customer\CustomerBookingResource;
use App\Http\Resources\Customer\CustomerDetailsResource;
use App\Http\Resources\Customer\CustomerResource;
use App\Models\Customer;
use App\Services\Customer\CustomerService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(private readonly CustomerService $customerService) {}

    public function index(CustomerIndexRequest $request)
    {
        $data =    $request->validated();
        $result = $this->customerService->index($data);

        return sendResponse(
            __('messages.customers_retrieved'),
            [
                'summary' => $result['summary'],
                'items' => CustomerResource::collection(
                    $result['customers']->getCollection()
                ),
            ],
            200,
            $result['customers']
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

    public function show(int $id)
    {
        $customer = $this->customerService->show($id);

        return sendResponse(
            __('messages.customer_retrieved'),
            new CustomerDetailsResource($customer)
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

    public function bookings(int $id,CustomerBookingsRequest $request)
    {
        $data = $request->validated();
        $bookings = $this->customerService->bookings($id,$data);

        return sendResponse(
            __('messages.bookings_success'),
            [
                'items' => CustomerBookingResource::collection(
                    $bookings->getCollection()
                ),
            ],
            200,
            $bookings
        );
    }
}
