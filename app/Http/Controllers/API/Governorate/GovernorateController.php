<?php

namespace App\Http\Controllers\Api\Governorate;

use App\Http\Controllers\Controller;
use App\Http\Resources\Governorate\GovernorateResource;
use App\Services\Governorate\GovernorateService;
use Illuminate\Http\Request;

class GovernorateController extends Controller
{
    public function __construct(private readonly GovernorateService $governorateService) {}

    public function index()
    {
        $governorates = $this->governorateService->getAll();

        return sendResponse(
            __('messages.governorates_retrieved'),
            GovernorateResource::collection($governorates)
        );
    }
}
