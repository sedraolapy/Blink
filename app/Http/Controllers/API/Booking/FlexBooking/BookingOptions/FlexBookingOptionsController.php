<?php

namespace App\Http\Controllers\Api\Booking\FlexBooking\BookingOptions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\FlexBooking\BookingOptions\FlexAvailableAssetsRequest;
use App\Http\Requests\Booking\FlexBooking\BookingOptions\FlexPeriodOptionsRequest;
use App\Http\Resources\Booking\FlexBooking\BookingOptions\FlexAvailableAssetsResource;
use App\Http\Resources\Booking\FlexBooking\BookingOptions\FlexPeriodOptionResource;
use App\Services\Booking\FlexBooking\BookingOptions\FlexBookingOptionsService;
use Illuminate\Http\JsonResponse;

class FlexBookingOptionsController extends Controller
{
    public function __construct(private readonly FlexBookingOptionsService $service) {}

    public function periods(FlexPeriodOptionsRequest $request)
    {
        $bookingId = $request->integer('booking_id') ?: null;
        $periods = $this->service->getPeriods($bookingId);

        return sendResponse(
            __('messages.periods_retrieved'),
            FlexPeriodOptionResource::collection($periods)
        );
    }

    public function availableAssets(FlexAvailableAssetsRequest $request)
    {
        $data =  $request->validated();
        $result = $this->service->getAvailableAssets($data);

        return sendResponse(
            __('messages.flex_booking.available_assets_retrieved'),
            new FlexAvailableAssetsResource($result)
        );
    }
}