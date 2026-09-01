<?php

use App\Http\Controllers\API\AdvertisingPeriod\AdvertisingPeriodController;
use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\Customer\CustomerController;
use App\Http\Controllers\API\Electronic\ElectronicController;
use App\Http\Controllers\API\FlexBillboard\FlexController;
use App\Http\Controllers\Api\Governorate\GovernorateController;
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

        Route::apiResource('customers', CustomerController::class)->only(['index', 'store', 'show', 'update']);

        Route::get('/periods', [AdvertisingPeriodController::class, 'index']);
        Route::get('/governorates', [GovernorateController::class, 'index']);

        Route::get('/flex', [FlexController::class, 'index']);
        Route::get('/flex/{id}', [FlexController::class, 'show']);

        Route::get('/electronic', [ElectronicController::class, 'index']);
        Route::get('/electronic/screens/{id}',[ElectronicController::class, 'showScreen']);
        Route::get('/electronic/networks/{id}',[ElectronicController::class, 'showNetwork']);
    });

});