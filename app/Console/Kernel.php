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
        $schedule->command('summit:json-generator')->everyTenMinutes()->withoutOverlapping();
        //Austin
        $schedule->command('summit:json-generator',[6])->everyTenMinutes()->withoutOverlapping();
        //BCN
        $schedule->command('summit:json-generator', [7])->everyTenMinutes()->withoutOverlapping();
        //Boston
        $schedule->command('summit:json-generator', [22])->everyTenMinutes()->withoutOverlapping();

        // Calendar Sync Jobs

        // Admin Actions
        $schedule->command('summit:admin-schedule-action-process')->everyMinute()->withoutOverlapping();
        // Member Actions
        // Google Calendar
        $schedule->command('summit:member-schedule-action-process', [CalendarSyncInfo::ProviderGoogle, 1000])->everyMinute()->withoutOverlapping();
        // Outlook
        $schedule->command('summit:member-schedule-action-process', [CalendarSyncInfo::ProviderOutlook, 1000])->everyMinute()->withoutOverlapping();
        // iCloud
        $schedule->command('summit:member-schedule-action-process', [CalendarSyncInfo::ProvideriCloud, 1000])->everyMinute()->withoutOverlapping();

    }
}
