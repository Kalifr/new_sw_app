<?php

namespace App\Services;

use App\Models\DailyMetric;
use App\Models\Order;
use App\Models\PlatformSnapshot;
use App\Models\Product;
use App\Models\Rfq;
use App\Models\SearchAnalytic;
use App\Models\User;
use App\Models\UserActivity;
use App\Models\UserMetric;
use App\Models\UserSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function generateDailySnapshot(): void
    {
        $date = now()->startOfDay();

        // Calculate platform-wide metrics
        $snapshot = new PlatformSnapshot([
            'date' => $date,
            'total_users' => User::count(),
            'total_products' => Product::count(),
            'total_orders' => Order::count(),
            'total_rfqs' => Rfq::count(),
            'total_order_value' => Order::sum('amount'),
            'average_order_value' => Order::avg('amount') ?? 0,
            'category_distribution' => $this->calculateCategoryDistribution(),
            'location_distribution' => $this->calculateLocationDistribution(),
            'additional_metrics' => $this->calculateAdditionalMetrics(),
        ]);

        $snapshot->save();

        // Calculate and store daily metrics
        $this->storeDailyMetrics($date);

        // Calculate and store user-specific metrics
        $this->storeUserMetrics($date);

        // Clean up old data (keep 2 years of history)
        $this->cleanupOldData();
    }

    protected function calculateCategoryDistribution(): array
    {
        return Product::select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();
    }

    protected function calculateLocationDistribution(): array
    {
        return User::select('country', DB::raw('count(*) as count'))
            ->groupBy('country')
            ->pluck('count', 'country')
            ->toArray();
    }

    protected function calculateAdditionalMetrics(): array
    {
        return [
            'active_users_24h' => $this->getActiveUsers(1),
            'active_users_7d' => $this->getActiveUsers(7),
            'active_users_30d' => $this->getActiveUsers(30),
            'conversion_rate' => $this->calculateConversionRate(),
            'average_session_duration' => $this->calculateAverageSessionDuration(),
            'top_search_terms' => $this->getTopSearchTerms(),
        ];
    }

    protected function getActiveUsers(int $days): int
    {
        return UserActivity::where('created_at', '>=', now()->subDays($days))
            ->distinct('user_id')
            ->count('user_id');
    }

    protected function calculateConversionRate(): float
    {
        $totalRfqs = Rfq::count();
        $convertedRfqs = Order::count();

        return $totalRfqs > 0 ? ($convertedRfqs / $totalRfqs) * 100 : 0;
    }

    protected function calculateAverageSessionDuration(): float
    {
        return UserSession::whereNotNull('ended_at')
            ->where('created_at', '>=', now()->subDays(30))
            ->avg(DB::raw('TIMESTAMPDIFF(SECOND, started_at, ended_at)')) ?? 0;
    }

    protected function getTopSearchTerms(int $limit = 10): array
    {
        return SearchAnalytic::select('query', DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('query')
            ->orderByDesc('count')
            ->limit($limit)
            ->pluck('count', 'query')
            ->toArray();
    }

    protected function storeDailyMetrics(Carbon $date): void
    {
        $metrics = [
            'new_users' => User::whereDate('created_at', $date)->count(),
            'new_products' => Product::whereDate('created_at', $date)->count(),
            'new_orders' => Order::whereDate('created_at', $date)->count(),
            'new_rfqs' => Rfq::whereDate('created_at', $date)->count(),
            'total_order_value' => Order::whereDate('created_at', $date)->sum('amount'),
            'average_order_value' => Order::whereDate('created_at', $date)->avg('amount') ?? 0,
            'active_users' => UserActivity::whereDate('created_at', $date)->distinct('user_id')->count(),
            'search_queries' => SearchAnalytic::whereDate('created_at', $date)->count(),
        ];

        foreach ($metrics as $type => $value) {
            DailyMetric::create([
                'date' => $date,
                'metric_type' => $type,
                'value' => $value,
            ]);
        }
    }

    protected function storeUserMetrics(Carbon $date): void
    {
        User::chunk(100, function ($users) use ($date) {
            foreach ($users as $user) {
                $metrics = [
                    'orders_count' => $user->orders()->whereDate('created_at', $date)->count(),
                    'orders_value' => $user->orders()->whereDate('created_at', $date)->sum('amount'),
                    'rfqs_count' => $user->rfqs()->whereDate('created_at', $date)->count(),
                    'products_count' => $user->products()->whereDate('created_at', $date)->count(),
                    'search_queries' => $user->searchAnalytics()->whereDate('created_at', $date)->count(),
                    'session_duration' => $this->calculateUserSessionDuration($user, $date),
                ];

                foreach ($metrics as $type => $value) {
                    UserMetric::create([
                        'user_id' => $user->id,
                        'date' => $date,
                        'metric_type' => $type,
                        'value' => $value,
                    ]);
                }
            }
        });
    }

    protected function calculateUserSessionDuration(User $user, Carbon $date): float
    {
        return $user->sessions()
            ->whereDate('started_at', $date)
            ->whereNotNull('ended_at')
            ->avg(DB::raw('TIMESTAMPDIFF(SECOND, started_at, ended_at)')) ?? 0;
    }

    protected function cleanupOldData(): void
    {
        $cutoff = now()->subYears(2);

        DailyMetric::where('date', '<', $cutoff)->delete();
        PlatformSnapshot::where('date', '<', $cutoff)->delete();
        UserMetric::where('date', '<', $cutoff)->delete();
        UserActivity::where('created_at', '<', $cutoff)->delete();
        SearchAnalytic::where('created_at', '<', $cutoff)->delete();
        UserSession::where('created_at', '<', $cutoff)->delete();
    }

    public function getMetricsForPeriod(string $period = 'day', ?string $metricType = null, ?string $dimension = null)
    {
        $query = DailyMetric::query();

        if ($metricType) {
            $query->where('metric_type', $metricType);
        }

        if ($dimension) {
            $query->where('dimension', $dimension);
        }

        $grouping = match ($period) {
            'week' => DB::raw('YEARWEEK(date)'),
            'month' => DB::raw('DATE_FORMAT(date, "%Y-%m")'),
            'year' => DB::raw('YEAR(date)'),
            default => 'date',
        };

        return $query->select(
                $grouping . ' as period',
                'metric_type',
                'dimension',
                DB::raw('SUM(value) as total_value'),
                DB::raw('AVG(value) as average_value'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('period', 'metric_type', 'dimension')
            ->orderBy('period')
            ->get();
    }

    public function getUserMetrics(User $user, string $period = 'day'): array
    {
        $metrics = UserMetric::where('user_id', $user->id)
            ->where('date', '>=', now()->subDays(match ($period) {
                'week' => 7,
                'month' => 30,
                'year' => 365,
                default => 1,
            }))
            ->get()
            ->groupBy('metric_type');

        $activities = UserActivity::where('user_id', $user->id)
            ->latest()
            ->limit(50)
            ->get();

        $searchHistory = SearchAnalytic::where('user_id', $user->id)
            ->latest()
            ->limit(20)
            ->get();

        return [
            'metrics' => $metrics,
            'activities' => $activities,
            'search_history' => $searchHistory,
            'summary' => [
                'total_orders' => $user->orders()->count(),
                'total_order_value' => $user->orders()->sum('amount'),
                'total_rfqs' => $user->rfqs()->count(),
                'total_products' => $user->products()->count(),
                'average_order_value' => $user->orders()->avg('amount') ?? 0,
                'last_active' => $user->activities()->latest()->first()?->created_at,
            ],
        ];
    }
} 