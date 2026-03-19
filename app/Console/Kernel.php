<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $middleware=[
        \Fruitcake\Cors\CorsMiddleware::class,
    ];
    protected $commands = [
        Commands\SendEventReminders::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('events:send-reminders')->everyMinute();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}