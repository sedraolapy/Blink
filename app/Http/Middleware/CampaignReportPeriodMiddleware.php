<?php

namespace App\Http\Middleware;

use App\Services\Reports\CampaignReportPeriodService;
use App\Services\WorkingYear\WorkingYearContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CampaignReportPeriodMiddleware
{
    public function __construct(
        private readonly CampaignReportPeriodService $periodService,
        private readonly WorkingYearContext $workingYearContext
    ) {}

    public function handle(Request $request,Closure $next): Response
    {
        $workingYear = $this->workingYearContext->get();
        $period = $this->periodService->getAvailablePeriod($workingYear);

        if ($period === null) {
            return sendResponse(
                __('messages.campaign_report_period_not_available'),
                null,
                404
            );
        }

        $request->attributes->set('campaign_report_period',$period);

        return $next($request);
    }
}
