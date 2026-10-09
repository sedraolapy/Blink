<?php

namespace App\Http\Controllers\API\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\TopRequestedAssetsRequest;
use App\Services\Dashboard\DashboardService;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService)
    {}

    public function index()
    {
        $data = $this->dashboardService->index();
        return sendResponse(
            __('messages.dashboard.index_success'),
            $data
        );
    }

    public function topRequestedAssets(TopRequestedAssetsRequest $request)
    {
        $data = $request->validated();

        return sendResponse(
            __('messages.dashboard.top_requested_assets_success'),
            $this->dashboardService->topRequestedAssets($data['type'])
        );
    }
}