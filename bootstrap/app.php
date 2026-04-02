<?php

use App\Models\Visit;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Privateer\Basecms\Http\Middleware\TrackWebsiteVisits;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('model:prune', [
            '--model' => [Visit::class],
        ])->daily();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('web', [
            TrackWebsiteVisits::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
