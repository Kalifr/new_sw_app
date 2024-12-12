<?php

namespace App\Http\Controllers;

use App\Models\DailyMetric;
use App\Models\UserMetric;
use App\Models\UserActivity;
use App\Models\SearchAnalytic;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class UserAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $period = $request->get('period', '30'); // Default to 30 days
        $startDate = now()->subDays($period);

        // Get user's metrics
        $metrics = UserMetric::query()
            ->forUser($user)
            ->forPeriod($startDate, now())
            ->get()
            ->groupBy('metric_type');

        // Format metrics for charts
        $ordersData = $this->formatMetricsForChart(
            $metrics->get(UserMetric::TYPE_ORDERS_COUNT, collect()),
            'Orders'
        );

        $orderValueData = $this->formatMetricsForChart(
            $metrics->get(UserMetric::TYPE_ORDERS_VALUE, collect()),
            'Order Value',
            function($value) {
                return '$' . number_format($value, 2);
            }
        );

        $rfqsData = $this->formatMetricsForChart(
            $metrics->get(UserMetric::TYPE_RFQS_COUNT, collect()),
            'RFQs'
        );

        $productsData = $this->formatMetricsForChart(
            $metrics->get(UserMetric::TYPE_PRODUCTS_COUNT, collect()),
            'Products'
        );

        // Get recent activities
        $recentActivities = UserActivity::query()
            ->forUser($user)
            ->with(['entity'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($activity) {
                return [
                    'type' => $activity->activity_type,
                    'description' => $activity->activity_description,
                    'date' => $activity->created_at->diffForHumans(),
                    'metadata' => $activity->metadata,
                ];
            });

        // Get search analytics
        $searchAnalytics = SearchAnalytic::query()
            ->where('user_id', $user->id)
            ->selectRaw('query, COUNT(*) as count, AVG(results_count) as avg_results')
            ->groupBy('query')
            ->orderByDesc('count')
            ->take(5)
            ->get()
            ->map(function ($search) {
                return [
                    'query' => $search->query,
                    'count' => $search->count,
                    'avgResults' => round($search->avg_results),
                ];
            });

        // Calculate summary statistics
        $summary = [
            'total_orders' => $metrics->get(UserMetric::TYPE_ORDERS_COUNT, collect())->sum('value'),
            'total_order_value' => $metrics->get(UserMetric::TYPE_ORDERS_VALUE, collect())->sum('value'),
            'total_rfqs' => $metrics->get(UserMetric::TYPE_RFQS_COUNT, collect())->sum('value'),
            'total_products' => $metrics->get(UserMetric::TYPE_PRODUCTS_COUNT, collect())->last()?->value ?? 0,
            'period' => $period,
        ];

        return Inertia::render('Analytics/UserDashboard', [
            'summary' => $summary,
            'charts' => [
                'orders' => $ordersData,
                'orderValue' => $orderValueData,
                'rfqs' => $rfqsData,
                'products' => $productsData,
            ],
            'recentActivities' => $recentActivities,
            'searchAnalytics' => $searchAnalytics,
        ]);
    }

    private function formatMetricsForChart($metrics, $label, $formatter = null)
    {
        $formatter = $formatter ?? function($value) { return $value; };

        return [
            'label' => $label,
            'data' => $metrics->map(function ($metric) use ($formatter) {
                return [
                    'date' => $metric->date->format('Y-m-d'),
                    'value' => $metric->value,
                    'formatted' => $formatter($metric->value),
                ];
            })->values()->all(),
        ];
    }
} 