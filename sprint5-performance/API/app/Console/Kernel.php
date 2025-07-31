<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        if (!app()->environment('local')) {
            $schedule->command('migrate:fresh --seed --force')->hourly()->environments(['production']);
            $schedule->command('invoice:remove')->hourly()->environments(['production']);
            $schedule->command('cache:clear')->hourly()->environments(['production']);
        }
        $schedule->command('invoice:generate')
            ->everyMinute();
        $schedule->command('queue:work --stop-when-empty')
            ->everyMinute();
        $schedule->command('order:update')
            ->everyTenMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
