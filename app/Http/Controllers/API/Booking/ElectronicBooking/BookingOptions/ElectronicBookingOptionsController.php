<?php

namespace App\Http\Controllers\Api\Booking\ElectronicBooking\BookingOptions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\ElectronicBooking\ElectronicAssetsRequest;
use App\Http\Resources\Booking\ElectronicBooking\BookingOptions\ElectronicBookingOptionsResource;
use App\Services\Booking\ElectronicBooking\BookingOptions\ElectronicBookingOptionsService;

class ElectronicBookingOptionsController extends Controller
{
    public function __construct(private readonly ElectronicBookingOptionsService $service) {}

    public function assets(ElectronicAssetsRequest $request)
    {
        $data = $request->validated();
        $result = $this->service->assets($data);

        return sendResponse(
            __('messages.electronic_booking_options.assets_success'),
            new ElectronicBookingOptionsResource($result)
        );
    }
}