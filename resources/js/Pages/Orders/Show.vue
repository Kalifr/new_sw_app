<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-cocomat text-xl text-wamucii leading-tight">
                    Order #{{ order.order_number }}
                </h2>
                <div class="flex items-center space-x-4">
                    <span :class="[
                        'px-3 py-1 rounded-full text-sm font-medium',
                        statusClasses[order.status] || 'bg-gray-100 text-gray-800'
                    ]">
                        {{ statuses[order.status] }}
                    </span>
                    <button v-if="canCancel" 
                        @click="confirmCancel"
                        class="text-red-600 hover:text-red-800">
                        Cancel Order
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Order Summary -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="font-cocomat text-lg text-wamucii mb-4">Order Details</h3>
                            <dl class="grid grid-cols-2 gap-4">
                                <dt class="text-gray-600">Product</dt>
                                <dd>{{ order.rfq.product.name }}</dd>
                                <dt class="text-gray-600">Quantity</dt>
                                <dd>{{ order.quote.quantity }} {{ order.quote.quantity_unit }}</dd>
                                <dt class="text-gray-600">Price</dt>
                                <dd>{{ formatAmount(order.amount) }}</dd>
                                <dt class="text-gray-600">Payment Status</dt>
                                <dd>
                                    <span :class="[
                                        'px-2 py-1 rounded-full text-xs font-medium',
                                        paymentStatusClasses[order.payment_status]
                                    ]">
                                        {{ formatPaymentStatus(order.payment_status) }}
                                    </span>
                                </dd>
                        </dl>
                    </div>
                        <div>
                            <h3 class="font-cocomat text-lg text-wamucii mb-4">Shipping Details</h3>
                            <dl class="grid grid-cols-2 gap-4">
                                <dt class="text-gray-600">Method</dt>
                                <dd>{{ order.shipping_details.method }}</dd>
                                <dt class="text-gray-600">Origin</dt>
                                <dd>{{ order.shipping_details.origin }}</dd>
                                <dt class="text-gray-600">Destination</dt>
                                <dd>{{ order.shipping_details.destination }}</dd>
                                <dt class="text-gray-600">Estimated Delivery</dt>
                                <dd>{{ formatDate(order.estimated_delivery_date) }}</dd>
                            </dl>
                        </div>
                    </div>
                                        </div>

                <!-- Payment Section -->
                <div v-if="showPaymentSection" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <PaymentForm 
                            :order="order"
                            :bank-details="bankDetails"
                        />
                    </div>
                </div>

                <!-- Payment Records -->
                <div v-if="order.payment_records.length > 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="font-cocomat text-lg text-wamucii mb-4">Payment Records</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-cocomat text-wamucii uppercase">Date</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-cocomat text-wamucii uppercase">Amount</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-cocomat text-wamucii uppercase">Transaction ID</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-cocomat text-wamucii uppercase">Status</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-cocomat text-wamucii uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="payment in order.payment_records" :key="payment.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ formatDate(payment.created_at) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ formatAmount(payment.amount) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ payment.transaction_id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="[
                                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                                paymentStatusClasses[payment.status]
                                            ]">
                                                {{ formatPaymentStatus(payment.status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <div class="flex space-x-2">
                                                <Link :href="route('payments.receipt.download', payment.id)"
                                                    class="text-selina hover:text-wamucii">
                                                    Download Receipt
                                                </Link>
                                                <template v-if="canVerifyPayment && payment.status === 'pending_verification'">
                                                    <button @click="verifyPayment(payment)"
                                                        class="text-green-600 hover:text-green-800">
                                                        Verify
                                                    </button>
                                                    <button @click="rejectPayment(payment)"
                                                        class="text-red-600 hover:text-red-800">
                                                        Reject
                            </button>
                                                </template>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                                    </div>

                <!-- Documents Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <DocumentUpload 
                            :order="order"
                            :documents="order.documents"
                            :can-sign="canSign"
                        />
                    </div>
                </div>

                <!-- Inspection Section -->
                <div v-if="showInspectionSection" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <InspectionChecklist 
                            :order="order"
                        />
                    </div>
                </div>

                <!-- Inspection Records -->
                <div v-if="order.inspection_records.length > 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="font-cocomat text-lg text-wamucii mb-4">Inspection History</h3>
                        <div class="space-y-4">
                            <div v-for="record in order.inspection_records" :key="record.id"
                                class="border rounded-lg p-4">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <p class="font-medium">
                                            Inspection by {{ record.inspector.name }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            {{ formatDate(record.inspection_date) }} at {{ record.location }}
                                        </p>
                                    </div>
                                    <span :class="[
                                        'px-2 py-1 rounded-full text-xs font-medium',
                                        inspectionStatusClasses[record.status]
                                    ]">
                                        {{ formatInspectionStatus(record.status) }}
                                    </span>
                                </div>
                                <div class="prose prose-sm max-w-none">
                                    <p class="text-gray-700">{{ record.findings }}</p>
                                </div>
                                <div v-if="record.photos.length > 0" class="mt-4">
                                    <p class="text-sm font-medium mb-2">Photos:</p>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        <a v-for="photo in record.photos" :key="photo.id"
                                            :href="route('inspections.photo.download', [record.id, photo.id])"
                                            class="block aspect-square">
                                            <img :src="photo.url" class="w-full h-full object-cover rounded-lg" />
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status History -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="font-cocomat text-lg text-wamucii mb-4">Status History</h3>
                        <div class="flow-root">
                            <ul class="-mb-8">
                                <li v-for="(status, index) in order.status_history" :key="status.id">
                                    <div class="relative pb-8">
                                        <span v-if="index !== order.status_history.length - 1"
                                            class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"
                                            aria-hidden="true">
                                        </span>
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span :class="[
                                                    'h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white',
                                                    statusClasses[status.status]
                                                ]">
                                                    <CheckIcon v-if="status.status === 'completed'"
                                                        class="h-5 w-5 text-white"
                                                        aria-hidden="true" />
                                                    <XMarkIcon v-else-if="status.status === 'cancelled'"
                                                        class="h-5 w-5 text-white"
                                                        aria-hidden="true" />
                                                    <ClockIcon v-else
                                                        class="h-5 w-5 text-white"
                                                        aria-hidden="true" />
                                                </span>
                    </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                        <p class="text-sm text-gray-500">
                                                        {{ statuses[status.status] }}
                                                        <span class="font-medium text-gray-900">
                                                            by {{ status.user.name }}
                                                        </span>
                                                    </p>
                                                    <p v-if="status.notes" class="mt-1 text-sm text-gray-500">
                                                        {{ status.notes }}
                                                    </p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                    <time :datetime="status.created_at">
                                                        {{ formatDate(status.created_at) }}
                                                    </time>
                                                </div>
                                            </div>
                                        </div>
                                </div>
                            </li>
                        </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cancel Order Modal -->
        <Modal :show="showCancelModal" @close="showCancelModal = false">
            <div class="p-6">
                <h3 class="font-cocomat text-lg text-wamucii mb-4">
                    Cancel Order
                </h3>
                <p class="text-sm text-gray-600 mb-4">
                    Are you sure you want to cancel this order? This action cannot be undone.
                </p>
                <form @submit.prevent="submitCancel">
                    <div class="mb-4">
                        <InputLabel for="cancel_reason" value="Reason for Cancellation" />
                        <TextArea
                            id="cancel_reason"
                            v-model="cancelForm.reason"
                            class="mt-1 block w-full"
                            rows="3"
                            required
                        />
                        <InputError :message="cancelForm.errors.reason" class="mt-2" />
                    </div>
                    <div class="flex justify-end space-x-3">
                        <SecondaryButton @click="showCancelModal = false">
                            Cancel
                        </SecondaryButton>
                        <DangerButton :disabled="cancelForm.processing">
                            Confirm Cancellation
                        </DangerButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Payment Verification Modal -->
        <Modal :show="showVerificationModal" @close="closeVerificationModal">
            <div class="p-6">
                <h3 class="font-cocomat text-lg text-wamucii mb-4">
                    {{ verificationAction === 'verify' ? 'Verify Payment' : 'Reject Payment' }}
                </h3>
                <form @submit.prevent="submitVerification">
                    <div class="mb-4">
                        <InputLabel for="verification_notes" :value="verificationAction === 'verify' ? 'Notes (Optional)' : 'Reason for Rejection'" />
                        <TextArea
                            id="verification_notes"
                            v-model="verificationForm.notes"
                            class="mt-1 block w-full"
                            rows="3"
                            :required="verificationAction === 'reject'"
                        />
                        <InputError :message="verificationForm.errors.notes" class="mt-2" />
                    </div>
                    <div class="flex justify-end space-x-3">
                        <SecondaryButton @click="closeVerificationModal">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton v-if="verificationAction === 'verify'"
                            :disabled="verificationForm.processing">
                            Verify Payment
                        </PrimaryButton>
                        <DangerButton v-else
                            :disabled="verificationForm.processing">
                            Reject Payment
                        </DangerButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PaymentForm from '@/Components/Orders/PaymentForm.vue';
import DocumentUpload from '@/Components/Orders/DocumentUpload.vue';
import InspectionChecklist from '@/Components/Orders/InspectionChecklist.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextArea from '@/Components/TextArea.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { CheckIcon, XMarkIcon, ClockIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    order: Object,
    canEdit: Boolean,
    canCancel: Boolean,
    canInspect: Boolean,
    canVerifyPayment: Boolean,
    bankDetails: Object,
});

const showCancelModal = ref(false);
const showVerificationModal = ref(false);
const verificationAction = ref('verify');
const selectedPayment = ref(null);

const cancelForm = useForm({
    reason: '',
});

const verificationForm = useForm({
    notes: '',
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
    pending_verification: 'bg-blue-100 text-blue-800',
    verified: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
};

const inspectionStatusClasses = {
    pending: 'bg-yellow-100 text-yellow-800',
    in_progress: 'bg-blue-100 text-blue-800',
    passed: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
    needs_review: 'bg-orange-100 text-orange-800',
};

const showPaymentSection = computed(() => {
    return ['draft', 'proforma_issued', 'payment_pending'].includes(props.order.status);
});

const showInspectionSection = computed(() => {
    return props.canInspect && ['payment_verified', 'in_preparation', 'inspection_pending'].includes(props.order.status);
});

const canSign = computed(() => {
    return props.order.status !== 'cancelled' && props.order.status !== 'completed';
});

const formatAmount = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: props.order.currency,
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

const formatInspectionStatus = (status) => {
    return status.split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};

const confirmCancel = () => {
    showCancelModal.value = true;
};

const submitCancel = () => {
    cancelForm.post(route('orders.cancel', props.order.id), {
        preserveScroll: true,
        onSuccess: () => {
            showCancelModal.value = false;
            cancelForm.reset();
        },
    });
};

const verifyPayment = (payment) => {
    verificationAction.value = 'verify';
    selectedPayment.value = payment;
    showVerificationModal.value = true;
};

const rejectPayment = (payment) => {
    verificationAction.value = 'reject';
    selectedPayment.value = payment;
    showVerificationModal.value = true;
};

const closeVerificationModal = () => {
    showVerificationModal.value = false;
    verificationForm.reset();
    selectedPayment.value = null;
};

const submitVerification = () => {
    if (!selectedPayment.value) return;

    const route = verificationAction.value === 'verify'
        ? 'payments.verify'
        : 'payments.reject';

    verificationForm.post(route(route, selectedPayment.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeVerificationModal();
        },
    });
};
</script>