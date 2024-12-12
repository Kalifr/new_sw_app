<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-cocomat text-xl text-wamucii leading-tight">
                Orders
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="mb-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 flex items-center space-x-4">
                        <div class="flex-1">
                            <select v-model="filters.status" 
                                class="w-full rounded-md"
                                @change="filterOrders">
                                <option value="">All Statuses</option>
                                <option v-for="(label, value) in statuses" 
                                    :key="value" 
                                    :value="value">
                                    {{ label }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Orders List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="orders.data.length > 0">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-cocomat text-wamucii uppercase tracking-wider">
                                                Order Number
                                            </th>
                                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-cocomat text-wamucii uppercase tracking-wider">
                                                Product
                                            </th>
                                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-cocomat text-wamucii uppercase tracking-wider">
                                                Amount
                                            </th>
                                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-cocomat text-wamucii uppercase tracking-wider">
                                                Status
                                            </th>
                                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-cocomat text-wamucii uppercase tracking-wider">
                                                Payment Status
                                            </th>
                                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-cocomat text-wamucii uppercase tracking-wider">
                                                Created
                                            </th>
                                            <th class="px-6 py-3 bg-gray-50"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="order in orders.data" :key="order.id">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ order.order_number }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ order.rfq.product.name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ formatAmount(order.amount, order.currency) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span :class="[
                                                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                                    statusClasses[order.status] || 'bg-gray-100 text-gray-800'
                                                ]">
                                                    {{ statuses[order.status] }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span :class="[
                                                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                                    paymentStatusClasses[order.payment_status] || 'bg-gray-100 text-gray-800'
                                                ]">
                                                    {{ formatPaymentStatus(order.payment_status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ formatDate(order.created_at) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <Link :href="route('orders.show', order.id)" 
                                                    class="text-selina hover:text-wamucii">
                                                    View Details
                                                </Link>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="mt-6">
                                <Pagination :links="orders.meta.links" />
                            </div>
                        </div>
                        <div v-else class="text-center py-12">
                            <h3 class="font-cocomat text-lg text-wamucii mb-2">
                                No Orders Found
                            </h3>
                            <p class="text-gray-500">
                                There are no orders matching your criteria.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    orders: Object,
    filters: Object,
    statuses: Object,
});

const statusClasses = {
    draft: 'bg-gray-100 text-gray-800',
    proforma_issued: 'bg-blue-100 text-blue-800',
    payment_pending: 'bg-yellow-100 text-yellow-800',
    payment_verified: 'bg-green-100 text-green-800',
    in_preparation: 'bg-purple-100 text-purple-800',
    inspection_pending: 'bg-orange-100 text-orange-800',
    inspection_passed: 'bg-green-100 text-green-800',
    ready_for_shipping: 'bg-indigo-100 text-indigo-800',
    shipped: 'bg-blue-100 text-blue-800',
    delivered: 'bg-green-100 text-green-800',
    completed: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
};

const paymentStatusClasses = {
    pending: 'bg-yellow-100 text-yellow-800',
    partial: 'bg-blue-100 text-blue-800',
    paid: 'bg-green-100 text-green-800',
    overdue: 'bg-red-100 text-red-800',
    refunded: 'bg-gray-100 text-gray-800',
};

const formatAmount = (amount, currency) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
    }).format(amount);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const formatPaymentStatus = (status) => {
    return status.split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};

const filterOrders = () => {
    router.get(route('orders.index'), props.filters, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script> 