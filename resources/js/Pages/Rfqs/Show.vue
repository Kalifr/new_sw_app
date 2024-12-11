<template>
    <Head title="RFQ Details" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    RFQ Details
                </h2>
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
                    <template v-if="$page.props.auth.user.id === rfq.product.user_id">
                        <Link
                            v-if="canQuote"
                            :href="route('rfqs.quotes.create', rfq.id)"
                            class="inline-flex items-center px-4 py-2 bg-selina border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-wamucii focus:bg-wamucii active:bg-wamucii focus:outline-none focus:ring-2 focus:ring-selina focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            Submit Quote
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            v-if="rfq.status === 'open'"
                            :href="route('rfqs.edit', rfq.id)"
                            class="inline-flex items-center px-4 py-2 bg-selina border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-wamucii focus:bg-wamucii active:bg-wamucii focus:outline-none focus:ring-2 focus:ring-selina focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            Edit RFQ
                        </Link>
                    </template>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- RFQ Details -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Product Information</h3>
                                <dl class="mt-4 space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Product</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ rfq.product.name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Seller</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ rfq.product.user.name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Buyer</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ rfq.buyer.name }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Order Details</h3>
                                <dl class="mt-4 space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Quantity</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ rfq.quantity }} {{ rfq.quantity_unit }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Target Price Range</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ rfq.target_price_range || 'Not specified' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Payment Terms</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ rfq.payment_terms || 'Not specified' }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Delivery Information</h3>
                                <dl class="mt-4 space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Delivery Location</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ rfq.delivery_location }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Target Delivery Date</dt>
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
                                </dl>
                            </div>

                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Requirements</h3>
                                <dl class="mt-4 space-y-4">
                                    <div v-if="rfq.specifications">
                                        <dt class="text-sm font-medium text-gray-500">Product Specifications</dt>
                                        <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ rfq.specifications }}</dd>
                                    </div>
                                    <div v-if="rfq.packaging_requirements">
                                        <dt class="text-sm font-medium text-gray-500">Packaging Requirements</dt>
                                        <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ rfq.packaging_requirements }}</dd>
                                    </div>
                                    <div v-if="rfq.shipping_requirements">
                                        <dt class="text-sm font-medium text-gray-500">Shipping Requirements</dt>
                                        <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ rfq.shipping_requirements }}</dd>
                                    </div>
                                    <div v-if="rfq.quality_requirements">
                                        <dt class="text-sm font-medium text-gray-500">Quality Requirements</dt>
                                        <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ rfq.quality_requirements }}</dd>
                                    </div>
                                    <div v-if="rfq.certification_requirements">
                                        <dt class="text-sm font-medium text-gray-500">Certification Requirements</dt>
                                        <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ rfq.certification_requirements }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quotes -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900">Quotes</h3>
                        <div v-if="rfq.quotes.length === 0" class="mt-4 text-center py-12">
                            <p class="text-sm text-gray-500">No quotes yet.</p>
                        </div>
                        <div v-else class="mt-6 space-y-6">
                            <div v-for="quote in rfq.quotes" :key="quote.id" class="bg-white border rounded-lg shadow-sm">
                                <div class="p-6">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="text-lg font-medium text-gray-900">
                                                Quote from {{ quote.seller.name }}
                                            </h4>
                                            <p class="mt-1 text-sm text-gray-500">
                                                {{ quote.price }} {{ quote.price_unit }} for {{ quote.quantity }} {{ quote.quantity_unit }}
                                            </p>
                                        </div>
                                        <div class="flex items-center space-x-4">
                                            <span
                                                :class="{
                                                    'bg-yellow-100 text-yellow-800': quote.status === 'pending',
                                                    'bg-green-100 text-green-800': quote.status === 'accepted',
                                                    'bg-red-100 text-red-800': quote.status === 'rejected',
                                                    'bg-gray-100 text-gray-800': quote.status === 'expired'
                                                }"
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                                            >
                                                {{ quote.status }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Delivery Date</dt>
                                            <dd class="mt-1 text-sm text-gray-900">
                                                {{ new Date(quote.delivery_date).toLocaleDateString() }}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Payment Terms</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ quote.payment_terms }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Valid Until</dt>
                                            <dd class="mt-1 text-sm text-gray-900">
                                                {{ new Date(quote.valid_until).toLocaleDateString() }}
                                            </dd>
                                        </div>
                                    </div>

                                    <div v-if="quote.specifications || quote.packaging_details || quote.shipping_details || quote.quality_certifications" class="mt-4 space-y-4">
                                        <div v-if="quote.specifications">
                                            <dt class="text-sm font-medium text-gray-500">Specifications</dt>
                                            <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ quote.specifications }}</dd>
                                        </div>
                                        <div v-if="quote.packaging_details">
                                            <dt class="text-sm font-medium text-gray-500">Packaging Details</dt>
                                            <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ quote.packaging_details }}</dd>
                                        </div>
                                        <div v-if="quote.shipping_details">
                                            <dt class="text-sm font-medium text-gray-500">Shipping Details</dt>
                                            <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ quote.shipping_details }}</dd>
                                        </div>
                                        <div v-if="quote.quality_certifications">
                                            <dt class="text-sm font-medium text-gray-500">Quality Certifications</dt>
                                            <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ quote.quality_certifications }}</dd>
                                        </div>
                                    </div>

                                    <div v-if="quote.additional_notes" class="mt-4">
                                        <dt class="text-sm font-medium text-gray-500">Additional Notes</dt>
                                        <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ quote.additional_notes }}</dd>
                                    </div>

                                    <div class="mt-6 flex justify-between items-center">
                                        <div class="flex items-center space-x-4">
                                            <template v-if="$page.props.auth.user.id === quote.seller_id">
                                                <Link
                                                    v-if="quote.status === 'pending'"
                                                    :href="route('quotes.edit', quote.id)"
                                                    class="text-sm font-medium text-selina hover:text-wamucii"
                                                >
                                                    Edit Quote
                                                </Link>
                                                <button
                                                    v-if="quote.status === 'pending'"
                                                    @click="deleteQuote(quote)"
                                                    class="text-sm font-medium text-red-600 hover:text-red-500"
                                                >
                                                    Delete
                                                </button>
                                            </template>
                                            <template v-else-if="$page.props.auth.user.id === rfq.buyer_id && quote.status === 'pending'">
                                                <button
                                                    @click="updateQuoteStatus(quote, 'accepted')"
                                                    class="text-sm font-medium text-green-600 hover:text-green-500"
                                                >
                                                    Accept
                                                </button>
                                                <button
                                                    @click="updateQuoteStatus(quote, 'rejected')"
                                                    class="text-sm font-medium text-red-600 hover:text-red-500"
                                                >
                                                    Reject
                                                </button>
                                            </template>
                                        </div>
                                    </div>
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
import { Head, Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    rfq: Object,
});

const canQuote = computed(() => {
    // Check if RFQ is open
    if (props.rfq.status !== 'open') {
        return false;
    }

    // Check if seller has already submitted a quote
    return !props.rfq.quotes.some(quote => quote.seller_id === props.$page.props.auth.user.id);
});

const deleteQuote = (quote) => {
    if (confirm('Are you sure you want to delete this quote?')) {
        router.delete(route('quotes.destroy', quote.id), {
            preserveScroll: true,
            onSuccess: () => {
                // Success notification handled by global handler
            },
        });
    }
};

const updateQuoteStatus = (quote, status) => {
    const action = status === 'accepted' ? 'accept' : 'reject';
    if (confirm(`Are you sure you want to ${action} this quote?`)) {
        router.patch(route('quotes.status.update', quote.id), {
            status: status
        }, {
            preserveScroll: true,
            onSuccess: () => {
                // Success notification handled by global handler
            },
        });
    }
};
</script> 