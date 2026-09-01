<?php

namespace App\Http\Controllers\API\Outdoor;

use App\Enums\ExternalAssetTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Outdoor\OutdoorIndexRequest;
use App\Http\Resources\Outdoor\OutdoorResource;
use App\Http\Resources\Outdoor\OutdoorDetailsResource;
use App\Services\Outdoor\OutdoorService;
use Illuminate\Http\Request;

class OutdoorController extends Controller
{
    public function __construct(
        private readonly OutdoorService $outdoorService) {}

    public function murals(OutdoorIndexRequest $request)
    {
        return $this->indexByType(ExternalAssetTypeEnum::MURAL,$request);
    }

    public function rooftops(OutdoorIndexRequest $request)
    {
        return $this->indexByType(ExternalAssetTypeEnum::ROOFTOP,$request);
    }

    public function tunnels(OutdoorIndexRequest $request)
    {
        return $this->indexByType(ExternalAssetTypeEnum::TUNNEL,$request);
    }

    public function bridges(OutdoorIndexRequest $request)
    {
        return $this->indexByType(ExternalAssetTypeEnum::BRIDGE,$request);
    }

    public function unipoles(OutdoorIndexRequest $request)
    {
        return $this->indexByType(ExternalAssetTypeEnum::UNIPOLE,$request);
    }

    private function indexByType(ExternalAssetTypeEnum $type,OutdoorIndexRequest $request)
    {
        $data = $request->validated();
        $result = $this->outdoorService->index($type,$data);

        return sendResponse(
            __('messages.outdoor.index_success'),
            [
                'summary' => $result['summary'],
                'items' => OutdoorResource::collection(
                    $result['assets']->getCollection()
                ),
            ],
            200,
            $result['assets']
        );
    }

    public function show(int $id)
    {
        $result = $this->outdoorService->show($id);

        return sendResponse(
            __('messages.outdoor.show_success'),
            new OutdoorDetailsResource($result)
        );
    }
}
