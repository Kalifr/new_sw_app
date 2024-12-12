<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-cocomat text-xl text-wamucii leading-tight">
                Your Analytics Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Period Selector -->
                <div class="mb-6">
                    <select v-model="selectedPeriod" @change="updatePeriod" class="font-futura border-gray-300 focus:border-selina focus:ring-selina rounded-md shadow-sm">
                        <option value="7">Last 7 days</option>
                        <option value="30">Last 30 days</option>
                        <option value="90">Last 90 days</option>
                    </select>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="font-cocomat text-lg mb-2">Total Orders</h3>
                        <p class="text-3xl font-futura text-selina">{{ summary.total_orders }}</p>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="font-cocomat text-lg mb-2">Total Order Value</h3>
                        <p class="text-3xl font-futura text-selina">${{ formatNumber(summary.total_order_value) }}</p>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="font-cocomat text-lg mb-2">Total RFQs</h3>
                        <p class="text-3xl font-futura text-selina">{{ summary.total_rfqs }}</p>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="font-cocomat text-lg mb-2">Active Products</h3>
                        <p class="text-3xl font-futura text-selina">{{ summary.total_products }}</p>
                    </div>
                </div>

                <!-- Charts Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Orders Chart -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="font-cocomat text-lg mb-4">Orders Over Time</h3>
                        <LineChart :data="charts.orders.data" />
                    </div>

                    <!-- Order Value Chart -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="font-cocomat text-lg mb-4">Order Value Over Time</h3>
                        <LineChart 
                            :data="charts.orderValue.data" 
                            :format="value => `$${formatNumber(value)}`" 
                        />
                    </div>

                    <!-- RFQs Chart -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="font-cocomat text-lg mb-4">RFQs Over Time</h3>
                        <LineChart :data="charts.rfqs.data" />
                    </div>

                    <!-- Products Chart -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="font-cocomat text-lg mb-4">Products Over Time</h3>
                        <LineChart :data="charts.products.data" />
                    </div>
                </div>

                <!-- Activity and Search Analytics -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Recent Activities -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="font-cocomat text-lg mb-4">Recent Activities</h3>
                        <div class="space-y-4">
                            <div v-for="activity in recentActivities" :key="activity.date" class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-2 h-2 mt-2 rounded-full bg-selina"></div>
                                </div>
                                <div>
                                    <p class="text-sm font-futura">{{ activity.description }}</p>
                                    <p class="text-xs text-gray-500">{{ activity.date }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Search Analytics -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="font-cocomat text-lg mb-4">Top Searches</h3>
                        <div class="space-y-4">
                            <div v-for="search in searchAnalytics" :key="search.query" class="flex justify-between items-center">
                                <div>
                                    <p class="font-futura">{{ search.query }}</p>
                                    <p class="text-sm text-gray-500">{{ search.avgResults }} avg. results</p>
                                </div>
                                <div class="text-selina font-futura">
                                    {{ search.count }} times
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import LineChart from '@/Components/Charts/LineChart.vue';

const props = defineProps({
    summary: Object,
    charts: Object,
    recentActivities: Array,
    searchAnalytics: Array,
});

const selectedPeriod = ref(props.summary.period.toString());

function updatePeriod() {
    router.get(route('analytics.user'), {
        period: selectedPeriod.value
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function formatNumber(value) {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value);
}
</script> 