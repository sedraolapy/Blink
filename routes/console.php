<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::command('customers:update-subscriptions')
    ->dailyAt('00:05')
    ->when(function () {
        $startDate = Carbon::parse('2026-09-08');

        return today()->gte($startDate)
            && $startDate->diffInDays(today()) % 14 === 0;
    })
    ->withoutOverlapping();

Schedule::command('contracts:update-statuses')
    ->dailyAt('00:10')
    ->withoutOverlapping();