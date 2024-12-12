<?php

namespace App\Console;

use App\Jobs\SendEngagementReminders;
use App\Jobs\SendUnreadMessageNotifications;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * These commands will be run in a single, sequential run.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        // Send notifications for unread messages every minute
        $schedule->job(new SendUnreadMessageNotifications)->everyMinute();

        // Send engagement reminders daily at 10 AM
        $schedule->job(new SendEngagementReminders)->dailyAt('10:00');

        // Clean up old notifications weekly
        $schedule->command('notifications:clean')->weekly();

        // Generate analytics daily at midnight
        $schedule->command('analytics:generate')->dailyAt('00:00');

        // Clean up old exports weekly
        $schedule->exec('find storage/app/exports -type f -mtime +7 -delete')->weekly();
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