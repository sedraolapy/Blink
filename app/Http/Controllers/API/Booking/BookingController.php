<?php

namespace App\Http\Controllers\API\Booking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\UpdateBookingAdvertiserTypeRequest;
use App\Http\Resources\Booking\UpdateBookingAdvertiserTypeResource;
use App\Models\Booking;
use App\Services\Booking\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function updateAdvertiserType(Booking $booking,UpdateBookingAdvertiserTypeRequest $request,BookingService $service) 
    {
        $booking = $service->updateAdvertiserType(
            $booking,
            $request->validated('advertiser_type')
        );
    
        return sendResponse(
            __('messages.booking.updated'),
            new UpdateBookingAdvertiserTypeResource($booking)
        );
    }
}
