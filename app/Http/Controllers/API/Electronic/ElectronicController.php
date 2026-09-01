<?php

namespace App\Http\Controllers\API\Electronic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Electronic\ElectronicIndexRequest;
use App\Http\Resources\Electronic\ElectronicNetworkDetailsResource;
use App\Http\Resources\Electronic\ElectronicResource;
use App\Http\Resources\Electronic\ElectronicScreenDetailsResource;
use App\Services\Electronic\ElectronicService;
use Illuminate\Http\Request;

class ElectronicController extends Controller
{
    public function __construct(private readonly ElectronicService $electronicService) {}

    public function index(ElectronicIndexRequest $request)
    {
        $data = $request->validated();

        $result = $this->electronicService->index($data);

        return sendResponse(
            __('messages.electronic.index_success'),
            [
                'summary' => $result['summary'],
                'items' => ElectronicResource::collection(
                    $result['items']->getCollection()
                ),
            ],
            200,
            $result['items']
        );
    }

    public function showScreen(int $id)
    {
        $result = $this->electronicService->showScreen($id);

        return sendResponse(
            __('messages.electronic.screen_retrieved'),
            new ElectronicScreenDetailsResource($result)
        );
    }

    public function showNetwork(int $id)
    {
        $result = $this->electronicService->showNetwork($id);

        return sendResponse(
            __('messages.electronic.network_retrieved'),
            new ElectronicNetworkDetailsResource($result)
        );
    }
}
