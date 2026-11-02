<?php

use App\Services\Notification\Generator\ContractNotificationGenerator;
use App\Services\Notification\Generator\ElectronicNotificationGenerator;
use App\Services\Notification\Generator\FlexNotificationGenerator;
use App\Services\Notification\Generator\OutdoorNotificationGenerator;
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


    Schedule::call(function () {
        app(ContractNotificationGenerator::class)->generate();
    })
        ->name('notifications:contracts')
        // ->dailyAt('08:00')
        ->everyMinute()
        ->timezone('Asia/Damascus')
        ->withoutOverlapping();

    Schedule::call(function () {
        app(FlexNotificationGenerator::class)->generate();
    })
        ->name('notifications:flex')
        // ->dailyAt('08:00')
        ->everyMinute()
        ->timezone('Asia/Damascus')
        ->withoutOverlapping();


    Schedule::call(function () {
        app(ElectronicNotificationGenerator::class)->generate();
    })
        ->name('notifications:electronic')
        // ->dailyAt('08:00')
        ->everyMinute()
        ->timezone('Asia/Damascus')
        ->withoutOverlapping();

    Schedule::call(function () {
        app(OutdoorNotificationGenerator::class)->generate();
    })
        ->name('notifications:outdoor')
        // ->dailyAt('08:00')
        ->everyMinute()
        ->timezone('Asia/Damascus')
        ->withoutOverlapping();

