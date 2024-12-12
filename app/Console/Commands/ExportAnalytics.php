<?php

namespace App\Console\Commands;

use App\Models\DailyMetric;
use App\Models\PlatformSnapshot;
use App\Models\UserMetric;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExportAnalytics extends Command
{
    protected $signature = 'analytics:export 
        {type : Type of data to export (platform/metrics/user)}
        {--start= : Start date (YYYY-MM-DD)}
        {--end= : End date (YYYY-MM-DD)}
        {--user= : User ID for user-specific exports}
        {--format=csv : Export format (csv/json)}';

    protected $description = 'Export analytics data to CSV or JSON format';

    public function handle()
    {
        $type = $this->argument('type');
        $start = $this->option('start') ? now()->parse($this->option('start')) : now()->subMonth();
        $end = $this->option('end') ? now()->parse($this->option('end')) : now();
        $format = $this->option('format');

        $this->info("Exporting {$type} analytics from {$start->format('Y-m-d')} to {$end->format('Y-m-d')}...");

        try {
            $data = match($type) {
                'platform' => $this->exportPlatformData($start, $end),
                'metrics' => $this->exportMetricsData($start, $end),
                'user' => $this->exportUserData($start, $end),
                default => throw new \InvalidArgumentException("Invalid export type: {$type}"),
            };

            $filename = "analytics_{$type}_{$start->format('Ymd')}_{$end->format('Ymd')}.{$format}";
            
            if ($format === 'json') {
                Storage::put("exports/{$filename}", json_encode($data, JSON_PRETTY_PRINT));
            } else {
                $this->exportToCsv($data, $filename);
            }

            $this->info("Data exported successfully to exports/{$filename}");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Export failed: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    protected function exportPlatformData($start, $end): array
    {
        return PlatformSnapshot::forPeriod($start, $end)
            ->get()
            ->map(fn($snapshot) => [
                'date' => $snapshot->date->format('Y-m-d'),
                'total_users' => $snapshot->total_users,
                'total_products' => $snapshot->total_products,
                'total_orders' => $snapshot->total_orders,
                'total_rfqs' => $snapshot->total_rfqs,
                'total_order_value' => $snapshot->total_order_value,
                'average_order_value' => $snapshot->average_order_value,
                'active_users_24h' => $snapshot->additional_metrics['active_users_24h'] ?? 0,
                'active_users_7d' => $snapshot->additional_metrics['active_users_7d'] ?? 0,
                'active_users_30d' => $snapshot->additional_metrics['active_users_30d'] ?? 0,
                'conversion_rate' => $snapshot->additional_metrics['conversion_rate'] ?? 0,
            ])
            ->toArray();
    }

    protected function exportMetricsData($start, $end): array
    {
        return DailyMetric::forPeriod($start, $end)
            ->get()
            ->map(fn($metric) => [
                'date' => $metric->date->format('Y-m-d'),
                'metric_type' => $metric->metric_type,
                'dimension' => $metric->dimension,
                'value' => $metric->value,
            ])
            ->toArray();
    }

    protected function exportUserData($start, $end): array
    {
        $query = UserMetric::forPeriod($start, $end);
        
        if ($userId = $this->option('user')) {
            $query->where('user_id', $userId);
        }

        return $query->get()
            ->map(fn($metric) => [
                'date' => $metric->date->format('Y-m-d'),
                'user_id' => $metric->user_id,
                'metric_type' => $metric->metric_type,
                'value' => $metric->value,
            ])
            ->toArray();
    }

    protected function exportToCsv(array $data, string $filename): void
    {
        if (empty($data)) {
            throw new \RuntimeException('No data to export');
        }

        $handle = fopen(storage_path("app/exports/{$filename}"), 'w');
        
        // Write headers
        fputcsv($handle, array_keys($data[0]));
        
        // Write data
        foreach ($data as $row) {
            fputcsv($handle, $row);
        }
        
        fclose($handle);
    }
} 