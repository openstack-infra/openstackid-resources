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
        \App\Console\Commands\SummitJsonGenerator::class,
        \App\Console\Commands\ChatTeamMessagesSender::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //Current
        $schedule->command('summit:json-generator')->everyTenMinutes()->withoutOverlapping();
        //Austin
        $schedule->command('summit:json-generator 6')->everyTenMinutes()->withoutOverlapping();
        //BCN
        $schedule->command('summit:json-generator 7')->everyTenMinutes()->withoutOverlapping();
        //Boston
        $schedule->command('summit:json-generator 22')->everyTenMinutes()->withoutOverlapping();
        // teams messages
        $schedule->command('teams:message-sender 100')->everyMinute()->withoutOverlapping();
    }
}
