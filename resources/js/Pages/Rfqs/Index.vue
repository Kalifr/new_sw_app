<template>
    <Head title="RFQs" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                RFQs
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div v-if="rfqs.data.length === 0" class="text-center py-12">
                            <h3 class="text-lg font-medium text-gray-900">No RFQs yet</h3>
                            <p class="mt-1 text-sm text-gray-500">Browse products to send RFQs to sellers.</p>
                            <div class="mt-6">
                                <Link
                                    :href="route('products.listing.index')"
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-selina hover:bg-wamucii focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-selina"
                                >
                                    Browse Products
                                </Link>
                            </div>
                        </div>

                        <div v-else class="space-y-6">
                            <!-- RFQ List -->
                            <div v-for="rfq in rfqs.data" :key="rfq.id" class="bg-white border rounded-lg shadow-sm">
                                <div class="p-6">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="text-lg font-medium text-gray-900">
                                                <Link :href="route('rfqs.show', rfq.id)" class="hover:underline">
                                                    {{ rfq.product.name }}
                                                </Link>
                                            </h3>
                                            <p class="mt-1 text-sm text-gray-500">
                                                {{ rfq.quantity }} {{ rfq.quantity_unit }}
                                            </p>
                                        </div>
                                        <div class="flex items-center space-x-4">
                                            <span
                                                :class="{
                                                    'bg-green-100 text-green-800': rfq.status === 'open',
                                                    'bg-blue-100 text-blue-800': rfq.status === 'closed',
                                                    'bg-red-100 text-red-800': rfq.status === 'expired'
                                                }"
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                                            >
                                                {{ rfq.status }}
                                            </span>
                                            <span class="text-sm text-gray-500">
                                                {{ rfq.quotes.length }} {{ rfq.quotes.length === 1 ? 'quote' : 'quotes' }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Delivery Location</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ rfq.delivery_location }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Target Delivery</dt>
                                            <dd class="mt-1 text-sm text-gray-900">
                                                {{ rfq.target_delivery_date ? new Date(rfq.target_delivery_date).toLocaleDateString() : 'Not specified' }}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Valid Until</dt>
                                            <dd class="mt-1 text-sm text-gray-900">
                                                {{ new Date(rfq.valid_until).toLocaleDateString() }}
                                            </dd>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex justify-between items-center">
                                        <div class="flex items-center space-x-2">
                                            <template v-if="$page.props.auth.user.id === rfq.product.user_id">
                                                <Link
                                                    v-if="canQuote(rfq)"
                                                    :href="route('rfqs.quotes.create', rfq.id)"
                                                    class="text-sm font-medium text-selina hover:text-wamucii"
                                                >
                                                    Submit Quote
                                                </Link>
                                            </template>
                                            <template v-else>
                                                <Link
                                                    v-if="rfq.status === 'open'"
                                                    :href="route('rfqs.edit', rfq.id)"
                                                    class="text-sm font-medium text-selina hover:text-wamucii"
                                                >
                                                    Edit
                                                </Link>
                                                <button
                                                    v-if="rfq.status === 'open'"
                                                    @click="closeRfq(rfq)"
                                                    class="text-sm font-medium text-gray-600 hover:text-gray-500"
                                                >
                                                    Close
                                                </button>
                                            </template>
                                        </div>
                                        <Link
                                            :href="route('rfqs.show', rfq.id)"
                                            class="text-sm font-medium text-selina hover:text-wamucii"
                                        >
                                            View Details
                                        </Link>
                                    </div>
                                </div>
                            </div>

                            <!-- Pagination -->
                            <div v-if="rfqs.data.length > 0" class="mt-6">
                                <Pagination :links="rfqs.links" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    rfqs: Object,
});

const canQuote = (rfq) => {
    // Check if RFQ is open
    if (rfq.status !== 'open') {
        return false;
    }

    // Check if seller has already submitted a quote
    return !rfq.quotes.some(quote => quote.seller_id === props.auth.user.id);
};

const closeRfq = (rfq) => {
    if (confirm('Are you sure you want to close this RFQ?')) {
        router.patch(route('rfqs.close', rfq.id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                // Success notification handled by global handler
            },
        });
    }
};
</script> 