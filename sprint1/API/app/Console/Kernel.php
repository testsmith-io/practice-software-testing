<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\OrderUpdate::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        if (!app()->environment('local')) {
            $schedule->command('migrate:fresh --seed --force')->withoutOverlapping()->everyTwoHours();
        }
        $schedule->command('order:update')
            ->withoutOverlapping()->everyTenMinutes();
    }
}
