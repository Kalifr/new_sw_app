<template>
    <AdminLayout title="Dashboard">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Period Selector -->
                <div class="mb-6">
                    <select v-model="period" @change="updatePeriod" class="rounded-md border-gray-300 shadow-sm focus:border-selina focus:ring-selina">
                        <option value="week">Last Week</option>
                        <option value="month">Last Month</option>
                        <option value="quarter">Last Quarter</option>
                        <option value="year">Last Year</option>
                    </select>
                </div>

                <!-- Overview Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <div v-for="(metric, key) in overviewMetrics" :key="key" class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-cocomat text-wamucii mb-2">{{ metric.label }}</h3>
                            <div class="flex items-end justify-between">
                                <div>
                                    <p class="text-3xl font-futura">{{ formatValue(metric.value) }}</p>
                                    <p class="text-sm text-gray-500">{{ metric.subtext }}</p>
                                </div>
                                <div :class="getGrowthClass(metric.growth)">
                                    {{ formatGrowth(metric.growth) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Users Trend -->
                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <h3 class="text-lg font-cocomat text-wamucii mb-4">User Growth</h3>
                        <LineChart :data="trends.users" />
                    </div>

                    <!-- Revenue Trend -->
                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <h3 class="text-lg font-cocomat text-wamucii mb-4">Revenue</h3>
                        <LineChart :data="trends.revenue" :format="formatCurrency" />
                    </div>

                    <!-- RFQ to Order Conversion -->
                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <h3 class="text-lg font-cocomat text-wamucii mb-4">RFQ to Order Conversion</h3>
                        <LineChart :data="insights.conversion_rates" :format="formatPercentage" />
                    </div>

                    <!-- Category Distribution -->
                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <h3 class="text-lg font-cocomat text-wamucii mb-4">Product Categories</h3>
                        <PieChart :data="insights.top_categories" />
                    </div>
                </div>

                <!-- Activity and Insights -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Recent Activity -->
                    <div class="bg-white p-6 rounded-lg shadow-sm lg:col-span-2">
                        <h3 class="text-lg font-cocomat text-wamucii mb-4">Recent Activity</h3>
                        <div class="space-y-4">
                            <div v-for="(activity, index) in activity.recent_activities" :key="index" class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-2 h-2 mt-2 rounded-full bg-selina"></div>
                                </div>
                                <div>
                                    <p class="text-sm">
                                        <span class="font-medium">{{ activity.user }}</span>
                                        {{ activity.action }}
                                    </p>
                                    <p class="text-xs text-gray-500">{{ activity.time }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Users -->
                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <h3 class="text-lg font-cocomat text-wamucii mb-4">Top Users</h3>
                        <div class="space-y-4">
                            <div v-for="(user, index) in activity.top_users" :key="index" class="flex justify-between items-center">
                                <div>
                                    <p class="font-medium">{{ user.name }}</p>
                                    <p class="text-sm text-gray-500">{{ user.orders }} orders · {{ formatCurrency(user.total_value) }}</p>
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ user.products }} products
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Export Section -->
                <div class="mt-8 bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-cocomat text-wamucii mb-4">Export Data</h3>
                    <form @submit.prevent="exportData" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Type</label>
                                <select v-model="exportForm.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-selina focus:ring-selina">
                                    <option value="platform">Platform Metrics</option>
                                    <option value="metrics">Daily Metrics</option>
                                    <option value="user">User Metrics</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Start Date</label>
                                <input type="date" v-model="exportForm.start_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-selina focus:ring-selina">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">End Date</label>
                                <input type="date" v-model="exportForm.end_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-selina focus:ring-selina">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Format</label>
                                <select v-model="exportForm.format" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-selina focus:ring-selina">
                                    <option value="csv">CSV</option>
                                    <option value="json">JSON</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <PrimaryButton type="submit">Export Data</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import LineChart from '@/Components/Charts/LineChart.vue';
import PieChart from '@/Components/Charts/PieChart.vue';

const props = defineProps({
    overview: Object,
    trends: Object,
    activity: Object,
    insights: Object,
});

const period = ref('month');
const exportForm = ref({
    type: 'platform',
    start_date: '',
    end_date: '',
    format: 'csv',
});

const overviewMetrics = computed(() => [
    {
        label: 'Total Users',
        value: props.overview.total_users,
        growth: props.overview.growth_rates.users,
        subtext: `${props.activity.active_users.current} active in last 30 days`,
    },
    {
        label: 'Total Orders',
        value: props.overview.total_orders,
        growth: props.overview.growth_rates.orders,
        subtext: `$${formatNumber(props.overview.total_order_value)} total value`,
    },
    {
        label: 'Average Order Value',
        value: props.overview.average_order_value,
        growth: 0,
        subtext: 'Per order',
        format: 'currency',
    },
    {
        label: 'Total RFQs',
        value: props.overview.total_rfqs,
        growth: props.overview.growth_rates.rfqs,
        subtext: `${formatPercentage(props.insights.conversion_rates[0]?.value || 0)} conversion rate`,
    },
    {
        label: 'Total Products',
        value: props.overview.total_products,
        growth: props.overview.growth_rates.products,
        subtext: `Across ${Object.keys(props.insights.top_categories).length} categories`,
    },
]);

function updatePeriod() {
    router.get(route('admin.dashboard'), { period: period.value }, { preserveState: true });
}

function exportData() {
    router.post(route('admin.dashboard.export'), exportForm.value, {
        preserveState: true,
    });
}

function formatValue(value, format = null) {
    if (format === 'currency') {
        return formatCurrency(value);
    }
    return formatNumber(value);
}

function formatNumber(value) {
    return new Intl.NumberFormat().format(value);
}

function formatCurrency(value) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
}

function formatPercentage(value) {
    return new Intl.NumberFormat('en-US', {
        style: 'percent',
        minimumFractionDigits: 1,
        maximumFractionDigits: 1,
    }).format(value / 100);
}

function formatGrowth(value) {
    const formatted = formatPercentage(value);
    return value >= 0 ? `+${formatted}` : formatted;
}

function getGrowthClass(value) {
    return {
        'text-green-600': value > 0,
        'text-red-600': value < 0,
        'text-gray-500': value === 0,
    };
}
</script> 