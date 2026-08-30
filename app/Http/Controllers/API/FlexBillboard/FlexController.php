<?php

namespace App\Http\Controllers\API\FlexBillboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\FlexBillboard\FlexIndexRequest;
use App\Http\Resources\FlexBillboard\FlexBillboardDetailsResource;
use App\Http\Resources\FlexBillboard\FlexBillboardResource;
use App\Services\FlexBillboard\FlexAvailabilityService;
use Illuminate\Http\Request;

class FlexController extends Controller
{
    public function __construct(private readonly FlexAvailabilityService $flexAvailabilityService) {}

    public function index(FlexIndexRequest $request)
    {
        $data = $request->validated();
        $result = $this->flexAvailabilityService->index($data);

        return sendResponse(
            __('messages.flex_billboards_retrieved'),
            [
                'summary' => $result['summary'],
                'items' => FlexBillboardResource::collection(
                    $result['billboards']->getCollection()
                ),
            ],
            200,
            $result['billboards']
        );
    }

    public function show(int $id)
    {
        $result = $this->flexAvailabilityService->show($id);

        return sendResponse(
            __('messages.flex_billboard_retrieved'),
            new FlexBillboardDetailsResource($result)
        );
    }
}
