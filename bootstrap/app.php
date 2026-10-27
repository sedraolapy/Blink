<?php

use App\Http\Middleware\CampaignReportPeriodMiddleware;
use App\Http\Middleware\Localization;
use App\Http\Middleware\WorkingYear;
use App\Http\Middleware\WritableWorkingYear;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(Localization::class);

        $middleware->statefulApi();

        $middleware->redirectGuestsTo(
            fn (Request $request) =>
                $request->is('api/*')
                    ? null
                    : (
                        \Illuminate\Support\Facades\Route::has('login')
                            ? route('login')
                            : null
                    )
        );

        $middleware->alias([
            'localization' => Localization::class,
            'working.year' => WorkingYear::class,
            'working.year.writable' => WritableWorkingYear::class,
            'campaign.report.period' => CampaignReportPeriodMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, \Throwable $e): bool =>
                $request->is('api/*') || $request->expectsJson()
        );


        $exceptions->render(
            function (
                AuthenticationException $exception,
                Request $request
            ) {
                if (! $request->is('api/*')) {
                    return null;
                }

                return sendError(
                    __('messages.unauthenticated'),
                    401
                );
            }
        );


        $exceptions->render(
            function (
                NotFoundHttpException $exception,
                Request $request
            ) {
                if (! $request->is('api/*')) {
                    return null;
                }

                return sendResponse(
                    __('messages.not_found'),
                    null,
                    404
                );
            }
        );
        $exceptions->render(
            function (
                AccessDeniedHttpException $exception,
                Request $request
            ) {
                if (! $request->is('api/*')) {
                    return null;
                }

                return sendResponse(
                    __('messages.forbidden'),
                    null,
                    403
                );
            }
        );
        $exceptions->render(
            function (
                ValidationException $exception,
                Request $request
            ) {
                if (! $request->is('api/*')) {
                    return null;
                }

                return sendError(
                    __('messages.validation_failed'),
                    422,
                    $exception->errors()
                );
            }
        );
    })
    ->create();