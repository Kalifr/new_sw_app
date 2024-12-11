<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanNotifications extends Command
{
    protected $signature = 'notifications:clean';
    protected $description = 'Clean up old notifications from the database';

    public function handle()
    {
        $this->info('Cleaning up old notifications...');

        // Delete notifications older than 6 months
        $deleted = DB::table('notifications')
            ->where('created_at', '<', now()->subMonths(6))
            ->delete();

        // Delete database notifications older than 3 months
        $databaseDeleted = DB::table('notifications')
            ->where('type', 'LIKE', '%DatabaseNotification')
            ->where('created_at', '<', now()->subMonths(3))
            ->delete();

        $this->info("Deleted {$deleted} old notifications.");
        $this->info("Deleted {$databaseDeleted} old database notifications.");

        return Command::SUCCESS;
    }
} 