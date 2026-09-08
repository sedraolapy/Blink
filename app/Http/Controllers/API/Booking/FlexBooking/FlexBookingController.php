<?php

namespace App\Http\Controllers\Api\Booking\FlexBooking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\FlexBooking\StoreFlexBookingRequest;
use App\Http\Resources\Booking\FlexBooking\StoreFlexBookingResource;
use App\Services\Booking\FlexBooking\FlexBookingService;

class FlexBookingController extends Controller
{
    public function __construct(private readonly FlexBookingService $service) {}

    public function store(StoreFlexBookingRequest $request)
    {
        $data = $request->validated();
        $result = $this->service->store($data);

        return sendResponse(
            __('messages.flex_booking.saved'),
            new StoreFlexBookingResource($result),
            201
        );
    }
}