<?php

use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\Customer\CustomerController;
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
    });

});