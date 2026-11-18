<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('store:low-stock-digest')->dailyAt('08:00');
        $schedule->command('store:cart-reminders')->hourly();

        // Hosting without Supervisor: send waiting emails from the every-minute scheduler instead.
        if (config('queue.via_scheduler')) {
            $schedule->command('queue:work --queue=emails,default --stop-when-empty --max-time=50 --tries=3')
                ->everyMinute()
                ->withoutOverlapping(5);
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
