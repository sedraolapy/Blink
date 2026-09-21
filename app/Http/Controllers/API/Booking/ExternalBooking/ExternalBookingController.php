<?php

namespace App\Http\Controllers\API\Booking\ExternalBooking;

use App\Enums\ExternalAssetTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\ExternalBooking\ExternalAvailableAssetsRequest;
use App\Http\Requests\Booking\ExternalBooking\StoreExternalBookingRequest;
use App\Http\Requests\Booking\ExternalBooking\UpdateExternalBookingRequest;
use App\Http\Resources\Booking\ExternalBooking\ExternalAvailableAssetsResource;
use App\Http\Resources\Booking\ExternalBooking\ShowExternalBookingResource;
use App\Services\Booking\ExternalBooking\ExternalBookingService;
use App\Http\Resources\Booking\ExternalBooking\StoreExternalBookingResource;
use App\Http\Resources\Booking\ExternalBooking\UpdateExternalBookingResource;

class ExternalBookingController extends Controller
{
    public function __construct(private readonly ExternalBookingService $service) {}

    public function availableAssets(ExternalAvailableAssetsRequest $request)
    {
        $data = $request->validated();
        $result = $this->service->getAvailableAssets($data);

        return sendResponse(
            __('messages.external_booking.available_assets_retrieved'),
            new ExternalAvailableAssetsResource($result)
        );
    }

    public function show(int $bookingId, string $type)
    {
        $externalType = ExternalAssetTypeEnum::from($type);

        $externalBookingType = $this->service->show($bookingId, $externalType);

        return sendResponse(
            __('messages.external_booking.show'),
            new ShowExternalBookingResource($externalBookingType)
        );
    }

    public function store(StoreExternalBookingRequest $request)
    {
        $data = $request->validated();
        $externalBooking = $this->service->store($data);

        return sendResponse(
            __('messages.external_booking.saved'),
            new StoreExternalBookingResource($externalBooking)
        );
    }

    public function update(UpdateExternalBookingRequest $request,int $bookingId,string $type)
    {
        $externalType = ExternalAssetTypeEnum::from($type);

        $data = $request->validated();
        $externalBookingType = $this->service->update($bookingId, $externalType, $data);

        return sendResponse(
            __('messages.external_booking.updated'),
            new UpdateExternalBookingResource($externalBookingType)
        );
    }
}
