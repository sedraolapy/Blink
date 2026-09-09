<?php

namespace App\Http\Controllers\Api\Booking\FlexBooking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\FlexBooking\StoreFlexBookingRequest;
use App\Http\Requests\Booking\FlexBooking\UpdateFlexBookingRequest;
use App\Http\Resources\Booking\FlexBooking\ShowFlexBookingResource;
use App\Http\Resources\Booking\FlexBooking\StoreFlexBookingResource;
use App\Http\Resources\Booking\FlexBooking\UpdateFlexBookingResource;
use App\Services\Booking\FlexBooking\FlexBookingService;

class FlexBookingController extends Controller
{
    public function __construct(private readonly FlexBookingService $service) {}

    public function show(int $bookingId)
    {
        $flexBooking = $this->service->show($bookingId);

        return sendResponse(
            __('messages.flex_booking.show'),
            new ShowFlexBookingResource($flexBooking)
        );
    }

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

    public function update(int $bookingId,UpdateFlexBookingRequest $request)
    {
        $data =  $request->validated();
        $result = $this->service->update($bookingId,$data);

        return sendResponse(
            __('messages.flex_booking.updated'),
            new UpdateFlexBookingResource($result)
        );
    }
}