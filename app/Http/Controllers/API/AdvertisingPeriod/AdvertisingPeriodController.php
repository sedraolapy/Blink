<?php

namespace App\Http\Controllers\API\AdvertisingPeriod;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdvertisingPeriod\AdvertisingPeriodResource;
use App\Services\AdvertisingPeriod\AdvertisingPeriodService;
use Illuminate\Http\Request;

class AdvertisingPeriodController extends Controller
{
    public function __construct(private readonly AdvertisingPeriodService $advertisingPeriodService) {}

    public function index()
    {
        $periods = $this->advertisingPeriodService->getAllWithCurrent();

        return sendResponse(
            __('messages.advertising_periods_retrieved'),
            AdvertisingPeriodResource::collection($periods)
        );
    }
}
