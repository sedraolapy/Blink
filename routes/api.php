<?php

use App\Http\Controllers\API\AdvertisingPeriod\AdvertisingPeriodController;
use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\Booking\BookingController;
use App\Http\Controllers\Api\Booking\ElectronicBooking\BookingOptions\ElectronicBookingOptionsController;
use App\Http\Controllers\API\Booking\ElectronicBooking\ElectronicBookingController;
use App\Http\Controllers\API\Booking\ExternalBooking\ExternalBookingController;
use App\Http\Controllers\Api\Booking\FlexBooking\BookingOptions\FlexBookingOptionsController;
use App\Http\Controllers\Api\Booking\FlexBooking\FlexBookingController;
use App\Http\Controllers\API\Contract\ContractController;
use App\Http\Controllers\API\Customer\CustomerController;
use App\Http\Controllers\API\Electronic\ElectronicController;
use App\Http\Controllers\API\FlexBillboard\FlexController;
use App\Http\Controllers\Api\Governorate\GovernorateController;
use App\Http\Controllers\Api\Map\MapController;
use App\Http\Controllers\API\Outdoor\OutdoorController;
use App\Http\Controllers\API\Quotation\QuotationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('localization')->group(function () {
    Route::post('/login', [LoginController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [LoginController::class, 'me']);
        Route::post('/logout', [LoginController::class, 'logout']);

        // Customers - no working year needed
        Route::apiResource('customers', CustomerController::class)->only(['store', 'update']);

        Route::get('/governorates', [GovernorateController::class, 'index']);
        Route::get('/map', [MapController::class, 'index']);

        Route::middleware('working.year')->group(function () {
            // Customers
            Route::apiResource('customers', CustomerController::class)->only(['index', 'show']);
            Route::get('/customers/{id}/bookings',[CustomerController::class, 'bookings']);

            // Advertising periods
            Route::get('/periods',[AdvertisingPeriodController::class, 'index']);

            // Flex assets
            Route::get('/flex', [FlexController::class, 'index']);
            Route::get('/flex/{id}', [FlexController::class, 'show']);

            // Electronic assets
            Route::get('/electronic',[ElectronicController::class, 'index']);
            Route::get('/electronic/screens/{id}',[ElectronicController::class, 'showScreen']);
            Route::get('/electronic/networks/{id}',[ElectronicController::class, 'showNetwork']);

            // Outdoor assets
            Route::prefix('outdoor')->group(function () {
                Route::get('/murals',[OutdoorController::class, 'murals']);
                Route::get('/rooftops',[OutdoorController::class, 'rooftops']);
                Route::get('/tunnels',[OutdoorController::class, 'tunnels']);
                Route::get('/bridges',[OutdoorController::class, 'bridges']);
                Route::get('/unipoles',[OutdoorController::class, 'unipoles']);
                Route::get('/{id}',[OutdoorController::class, 'show']);
            });

            // Bookings
            Route::get('/bookings/{bookingId}',[BookingController::class, 'show']);

            // Flex bookings
            Route::get('/booking-options/flex/periods',[FlexBookingOptionsController::class, 'periods']);
            Route::post('/assets-available/flex/options-booking',[FlexBookingOptionsController::class, 'availableAssets']);
            Route::get('/bookings/{bookingId}/flex',[FlexBookingController::class, 'show']);

            // Electronic bookings
            Route::get('/booking-options/electronic/assets',[ElectronicBookingOptionsController::class, 'assets']);
            Route::get('/bookings/{booking_id}/electronic',[ElectronicBookingController::class, 'show']);

            // Outdoor bookings
            Route::post('/booking-options/outdoor/available-assets',[ExternalBookingController::class, 'availableAssets']);
            Route::get('/bookings/{booking_id}/outdoor/{type}',[ExternalBookingController::class, 'show']);

            // Quotation
            Route::get('/bookings/{bookingId}/quotation',[QuotationController::class, 'show']);

            // Contract
            Route::get('/bookings/{bookingId}/contract',[ContractController::class, 'show']);

            Route::middleware('working.year.writable')->group(function () {
                // Bookings
                Route::patch('/bookings/{booking}',[BookingController::class, 'updateAdvertiserType']);

                // Flex bookings
                Route::post('/bookings/flex',[FlexBookingController::class, 'store']);
                Route::put('/bookings/{bookingId}/flex',[FlexBookingController::class, 'update']);

                // Electronic bookings
                Route::post('/bookings/electronic',[ElectronicBookingController::class, 'store']);
                Route::put('/bookings/{booking_id}/electronic',[ElectronicBookingController::class, 'update']);

                // Outdoor bookings
                Route::post('/bookings/outdoor',[ExternalBookingController::class, 'store']);
                Route::put('/bookings/{booking_id}/outdoor/{type}',[ExternalBookingController::class, 'update']);

                // Qoutation
                Route::post('/bookings/{bookingId}/quotation/issue',[QuotationController::class, 'issue']);

                // Contract
                Route::post('/bookings/{bookingId}/contract',[ContractController::class, 'store']);
                Route::put('/bookings/{bookingId}/contract',[ContractController::class, 'update']);
            });
        });
    });
});