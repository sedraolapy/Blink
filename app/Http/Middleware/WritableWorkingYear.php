<?php

namespace App\Http\Middleware;

use App\Services\WorkingYear\WorkingYearContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WritableWorkingYear
{
    public function __construct(private readonly WorkingYearContext $workingYearContext)
    {
    }

    public function handle(Request $request,Closure $next): Response
    {
        $year = $this->workingYearContext->get();

        if ($year < now()->year) {
            return sendError(
                __('messages.working_year_read_only'),
                403
            );
        }

        return $next($request);
    }
}