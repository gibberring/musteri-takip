<?php

namespace App\Console;

use App\Console\Commands\ProcessYarinGidilecekReminders;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        ProcessYarinGidilecekReminders::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('servis:yarin-gidilecek')->dailyAt('00:00');
    }
}
