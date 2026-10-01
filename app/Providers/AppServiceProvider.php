<?php

namespace App\Providers;

use App\Enums\RoleEnum;
use App\Models\Booking;
use App\Observers\BookingObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Services\WorkingYear\WorkingYearContext;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(
            WorkingYearContext::class,
            fn () => new WorkingYearContext()
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Booking::observe(BookingObserver::class);

        Gate::before(function ($user, string $ability) {
            if ($user->hasRole(RoleEnum::SUPER_ADMIN->value)) {
                return true;
            }

            return null;
        });
    }
}
