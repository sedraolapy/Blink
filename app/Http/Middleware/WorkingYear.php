<?php

namespace App\Http\Middleware;

use App\Services\WorkingYear\WorkingYearContext;
use App\Services\WorkingYear\WorkingYearService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WorkingYear
{
    public function __construct(
        private readonly WorkingYearService $workingYearService,
        private readonly WorkingYearContext $workingYearContext,
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('X-Working-Year');

        if (
            $header === null
            || ! preg_match('/^\d{4}$/', $header)
        ) {
            return sendError(
                __('messages.working_year_required'),
                422
            );
        }

        $year = (int) $header;

        if (! $this->workingYearService->isAvailable($year)) {
            return sendError(
                __('messages.working_year_not_available'),
                422
            );
        }

        $this->workingYearContext->set($year);

        return $next($request);
    }
}