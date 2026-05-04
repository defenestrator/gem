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
        $schedule->command('app:heartbeat')
            ->everyMinute()
            ->thenPing('https://heartbeats.laravel.com/01kqtm6dwrfgb3f9kmgd8chkzt/ping');

        $schedule->command('animals:sync')->hourly()->withoutOverlapping()->runInBackground();

        $schedule->command('species:fetch-images --model=all --queue')
            ->weekly()->sundays()->at('03:00')
            ->timezone('America/Boise')
            ->withoutOverlapping()
            ->runInBackground();

        $schedule->command('logs:upload')
            ->monthlyOn(15, '00:00')
            ->withoutOverlapping()
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
