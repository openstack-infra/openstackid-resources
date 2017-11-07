<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use models\summit\CalendarSync\CalendarSyncInfo;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\SummitJsonGenerator::class,
        \App\Console\Commands\MemberActionsCalendarSyncProcessingCommand::class,
        \App\Console\Commands\AdminActionsCalendarSyncProcessingCommand::class,
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
        $schedule->command('summit:json-generator')->everyFiveMinutes()->withoutOverlapping();
        //Austin
        $schedule->command('summit:json-generator',[6])->everyFiveMinutes()->withoutOverlapping();
        //BCN
        $schedule->command('summit:json-generator', [7])->everyFiveMinutes()->withoutOverlapping();
        //Boston
        $schedule->command('summit:json-generator', [22])->everyFiveMinutes()->withoutOverlapping();
        //Sydney
        $schedule->command('summit:json-generator', [23])->everyFiveMinutes()->withoutOverlapping();

        // Calendar Sync Jobs

        // Admin Actions
        $schedule->command('summit:admin-schedule-action-process')->withoutOverlapping();
        // Member Actions
        // Google Calendar
        $schedule->command('summit:member-schedule-action-process', [CalendarSyncInfo::ProviderGoogle, 1000])->withoutOverlapping();
        // Outlook
        $schedule->command('summit:member-schedule-action-process', [CalendarSyncInfo::ProviderOutlook, 1000])->withoutOverlapping();
        // iCloud
        $schedule->command('summit:member-schedule-action-process', [CalendarSyncInfo::ProvideriCloud, 1000])->withoutOverlapping();

    }
}
