<template>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="font-cocomat text-lg text-wamucii mb-4">Record Payment</h3>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="amount" value="Payment Amount" />
                <TextInput
                    id="amount"
                    type="number"
                    step="0.01"
                    class="mt-1 block w-full"
                    v-model="form.amount"
                    :disabled="form.processing"
                    required
                />
                <InputError :message="form.errors.amount" class="mt-2" />
            </div>

            <div>
                <InputLabel for="transaction_id" value="Transaction ID" />
                <TextInput
                    id="transaction_id"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.transaction_id"
                    :disabled="form.processing"
                    required
                />
                <InputError :message="form.errors.transaction_id" class="mt-2" />
            </div>

            <div>
                <InputLabel for="receipt" value="Payment Receipt" />
                <input
                    id="receipt"
                    type="file"
                    class="mt-1 block w-full"
                    @input="form.receipt = $event.target.files[0]"
                    :disabled="form.processing"
                    accept=".pdf,.jpg,.jpeg,.png"
                    required
                />
                <p class="mt-1 text-sm text-gray-500">
                    Accepted formats: PDF, JPG, PNG (max 10MB)
                </p>
                <InputError :message="form.errors.receipt" class="mt-2" />
            </div>

            <div>
                <InputLabel for="notes" value="Notes (Optional)" />
                <TextArea
                    id="notes"
                    class="mt-1 block w-full"
                    v-model="form.notes"
                    :disabled="form.processing"
                    rows="3"
                />
                <InputError :message="form.errors.notes" class="mt-2" />
            </div>

            <div class="flex items-center justify-between mt-6">
                <p class="text-sm text-gray-500">
                    Remaining Balance: {{ formatAmount(remainingBalance) }}
                </p>
                <PrimaryButton :disabled="form.processing">
                    Record Payment
                </PrimaryButton>
            </div>
        </form>

        <!-- Payment Instructions -->
        <div class="mt-8 border-t pt-6">
            <h4 class="font-cocomat text-wamucii mb-3">Payment Instructions</h4>
            <div class="bg-gray-50 rounded p-4 text-sm">
                <p class="font-medium mb-2">Bank Transfer Details:</p>
                <ul class="space-y-2">
                    <li><span class="text-gray-600">Bank Name:</span> {{ bankDetails.name }}</li>
                    <li><span class="text-gray-600">Account Name:</span> {{ bankDetails.account_name }}</li>
                    <li><span class="text-gray-600">Account Number:</span> {{ bankDetails.account_number }}</li>
                    <li><span class="text-gray-600">Swift Code:</span> {{ bankDetails.swift_code }}</li>
                    <li><span class="text-gray-600">Reference:</span> {{ order.order_number }}</li>
                </ul>
                <p class="mt-4 text-sm text-gray-500">
                    Please include your order number as payment reference.
                    Payments typically take 1-2 business days to verify.
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
    bankDetails: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    amount: '',
    transaction_id: '',
    receipt: null,
    notes: '',
});

const remainingBalance = computed(() => {
    const totalPaid = props.order.payment_records.reduce((sum, record) => {
        return sum + (record.status === 'verified' ? record.amount : 0);
    }, 0);
    return props.order.amount - totalPaid;
});

const formatAmount = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: props.order.currency,
    }).format(amount);
};

const submit = () => {
    form.post(route('orders.payments.store', props.order.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};
</script> 