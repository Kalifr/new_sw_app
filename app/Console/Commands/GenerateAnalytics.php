<?php

namespace App\Console\Commands;

use App\Services\AnalyticsService;
use Illuminate\Console\Command;

class GenerateAnalytics extends Command
{
    protected $signature = 'analytics:generate {--date= : The date to generate analytics for (YYYY-MM-DD)}';
    protected $description = 'Generate platform analytics for a specific date';

    public function handle(AnalyticsService $analytics)
    {
        $date = $this->option('date') ? now()->parse($this->option('date')) : now()->subDay();

        $this->info("Generating analytics for {$date->format('Y-m-d')}...");

        try {
            $analytics->generateDailySnapshot();
            $this->info('Analytics generated successfully.');
        } catch (\Exception $e) {
            $this->error("Failed to generate analytics: {$e->getMessage()}");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
} 