<?php

namespace App\Http\Controllers\API\Booking\ElectronicBooking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\ElectronicBooking\StoreElectronicBookingRequest;
use App\Http\Requests\Booking\ElectronicBooking\UpdateElectronicBookingRequest;
use App\Http\Resources\Booking\ElectronicBooking\ShowElectronicBookingResource;
use App\Http\Resources\Booking\ElectronicBooking\StoreElectronicBookingResource;
use App\Services\Booking\ElectronicBooking\ElectronicBookingService;
use Illuminate\Http\Request;

class ElectronicBookingController extends Controller
{
    public function __construct(private readonly ElectronicBookingService $service) {}

    public function show(int $bookingId)
    {
        $electronicBooking = $this->service->show($bookingId);

        return sendResponse(
            __('messages.electronic_booking.show'),
            new ShowElectronicBookingResource($electronicBooking)
        );
    }

    public function store(StoreElectronicBookingRequest $request)
    {
        $data = $request->validated();
        $result = $this->service->store($data);

        return sendResponse(
            __('messages.electronic_booking.store_success'),
            new StoreElectronicBookingResource($result),
            201
        );
    }

    public function update(UpdateElectronicBookingRequest $request,int $booking_id)
    {
        $data = $request->validated();
        $result = $this->service->update($booking_id,$data);

        return sendResponse(
            __('messages.electronic_booking.update_success'),
            new StoreElectronicBookingResource($result)
        );
    }
}
