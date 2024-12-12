<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyMetric;
use App\Models\PlatformSnapshot;
use App\Models\SearchAnalytic;
use App\Models\User;
use App\Models\UserActivity;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(protected AnalyticsService $analytics)
    {
    }

    public function index(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = $this->getStartDate($period);
        $snapshot = PlatformSnapshot::latest()->first();

        return Inertia::render('Admin/Dashboard', [
            'overview' => [
                'total_users' => $snapshot->total_users,
                'total_products' => $snapshot->total_products,
                'total_orders' => $snapshot->total_orders,
                'total_rfqs' => $snapshot->total_rfqs,
                'total_order_value' => $snapshot->total_order_value,
                'average_order_value' => $snapshot->average_order_value,
                'growth_rates' => $snapshot->getGrowthRates(),
            ],
            'trends' => [
                'users' => $this->getUserTrends($startDate),
                'orders' => $this->getOrderTrends($startDate),
                'rfqs' => $this->getRfqTrends($startDate),
                'revenue' => $this->getRevenueTrends($startDate),
            ],
            'activity' => [
                'active_users' => $snapshot->getActiveUsersGrowth(),
                'recent_activities' => $this->getRecentActivities(),
                'top_users' => $this->getTopUsers(),
            ],
            'insights' => [
                'top_categories' => $snapshot->getTopCategories(),
                'top_locations' => $snapshot->getTopLocations(),
                'top_searches' => $this->getTopSearches(),
                'conversion_rates' => $this->getConversionRates($startDate),
            ],
        ]);
    }

    public function export(Request $request)
    {
        $request->validate([
            'type' => ['required', 'string', 'in:platform,metrics,user'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'format' => ['required', 'string', 'in:csv,json'],
        ]);

        $command = \Artisan::call('analytics:export', [
            'type' => $request->type,
            '--start' => $request->start_date,
            '--end' => $request->end_date,
            '--format' => $request->format,
            '--user' => $request->user_id,
        ]);

        if ($command === 0) {
            return response()->download(
                storage_path("app/exports/analytics_{$request->type}_{$request->start_date}_{$request->end_date}.{$request->format}")
            );
        }

        return back()->with('error', 'Failed to generate export');
    }

    protected function getStartDate(string $period): string
    {
        return match($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'quarter' => now()->subQuarter(),
            'year' => now()->subYear(),
            default => now()->subMonth(),
        };
    }

    protected function getUserTrends($startDate): array
    {
        return DailyMetric::ofType('new_users')
            ->forPeriod($startDate, now())
            ->get()
            ->map(fn($metric) => [
                'date' => $metric->date->format('Y-m-d'),
                'value' => $metric->value,
            ])
            ->values()
            ->toArray();
    }

    protected function getOrderTrends($startDate): array
    {
        return DailyMetric::ofType('new_orders')
            ->forPeriod($startDate, now())
            ->get()
            ->map(fn($metric) => [
                'date' => $metric->date->format('Y-m-d'),
                'value' => $metric->value,
            ])
            ->values()
            ->toArray();
    }

    protected function getRfqTrends($startDate): array
    {
        return DailyMetric::ofType('new_rfqs')
            ->forPeriod($startDate, now())
            ->get()
            ->map(fn($metric) => [
                'date' => $metric->date->format('Y-m-d'),
                'value' => $metric->value,
            ])
            ->values()
            ->toArray();
    }

    protected function getRevenueTrends($startDate): array
    {
        return DailyMetric::ofType('total_order_value')
            ->forPeriod($startDate, now())
            ->get()
            ->map(fn($metric) => [
                'date' => $metric->date->format('Y-m-d'),
                'value' => $metric->value,
            ])
            ->values()
            ->toArray();
    }

    protected function getRecentActivities(int $limit = 10): array
    {
        return UserActivity::with('user')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn($activity) => [
                'user' => $activity->user->name,
                'action' => $activity->activity_description,
                'time' => $activity->created_at->diffForHumans(),
            ])
            ->toArray();
    }

    protected function getTopUsers(int $limit = 5): array
    {
        return User::withCount(['orders', 'rfqs', 'products'])
            ->withSum('orders', 'amount')
            ->orderByDesc('orders_count')
            ->limit($limit)
            ->get()
            ->map(fn($user) => [
                'name' => $user->name,
                'orders' => $user->orders_count,
                'rfqs' => $user->rfqs_count,
                'products' => $user->products_count,
                'total_value' => $user->orders_sum_amount,
            ])
            ->toArray();
    }

    protected function getTopSearches(int $limit = 10): array
    {
        return SearchAnalytic::select('query', \DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('query')
            ->orderByDesc('count')
            ->limit($limit)
            ->get()
            ->map(fn($search) => [
                'term' => $search->query,
                'count' => $search->count,
            ])
            ->toArray();
    }

    protected function getConversionRates($startDate): array
    {
        $metrics = DailyMetric::whereIn('metric_type', ['new_rfqs', 'new_orders'])
            ->forPeriod($startDate, now())
            ->get()
            ->groupBy('date');

        return $metrics->map(function($dayMetrics) {
            $rfqs = $dayMetrics->firstWhere('metric_type', 'new_rfqs')?->value ?? 0;
            $orders = $dayMetrics->firstWhere('metric_type', 'new_orders')?->value ?? 0;

            return [
                'date' => $dayMetrics->first()->date->format('Y-m-d'),
                'value' => $rfqs > 0 ? ($orders / $rfqs) * 100 : 0,
            ];
        })
        ->values()
        ->toArray();
    }
} 