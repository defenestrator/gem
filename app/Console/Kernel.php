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
            ->environments('production')
            ->everyMinute()
            ->thenPing('https://heartbeats.laravel.com/01kqtm6dwrfgb3f9kmgd8chkzt/ping');
        
        $schedule->command('animals:sync')->dailyAt("9:00")->withoutOverlapping()->runInBackground();
        
        $schedule->command('media:process-animals')
            ->dailyAt("11:00")
            ->environments('production')
            ->withoutOverlapping()
            ->runInBackground();
        
        $schedule->command('species:fetch-images --model=all --queue')
            ->weeklyOn(0, '0:00')
            ->timezone('America/Boise')
            ->environments('production')
            ->withoutOverlapping()
            ->runInBackground();

        $schedule->command('media:process-species')
            ->weeklyOn(0, '6:00')
            ->timezone('America/Boise')
            ->environments('production')
            ->withoutOverlapping()
            ->runInBackground();

        $schedule->command('logs:upload')
            ->monthlyOn(15, '00:00')
            ->environments('production')
            ->withoutOverlapping()
            ->runInBackground();

        // Backups — production only
        $schedule->command('backup:run --only-db')
            ->everySixHours()
            ->environments('production')
            ->withoutOverlapping()
            ->runInBackground();

        $schedule->command('backup:run')
            ->dailyAt('02:00')
            ->environments('production')
            ->withoutOverlapping()
            ->runInBackground();

        $schedule->command('backup:clean')
            ->dailyAt('05:00')
            ->environments('production')
            ->withoutOverlapping();
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
