<?php

namespace App\Http\Controllers\Api\Map;

use App\Http\Controllers\Controller;
use App\Http\Requests\Map\MapIndexRequest;
use App\Http\Resources\Map\MapAssetResource;
use App\Services\Map\MapService;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function __construct(private readonly MapService $mapService) {}

    public function index(MapIndexRequest $request)
    {
        $data = $request->validated();
        $result = $this->mapService->index($data);

        return sendResponse(
            __('messages.map.index_success'),
            [
                'flex' => MapAssetResource::collection(
                    $result['flex']
                ),

                'electronic' => MapAssetResource::collection(
                    $result['electronic']
                ),

                'murals' => MapAssetResource::collection(
                    $result['murals']
                ),

                'rooftops' => MapAssetResource::collection(
                    $result['rooftops']
                ),

                'tunnels' => MapAssetResource::collection(
                    $result['tunnels']
                ),

                'bridges' => MapAssetResource::collection(
                    $result['bridges']
                ),

                'unipoles' => MapAssetResource::collection(
                    $result['unipoles']
                ),
            ]
        );
    }
}
