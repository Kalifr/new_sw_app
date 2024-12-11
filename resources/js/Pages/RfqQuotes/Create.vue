<template>
    <Head title="Submit Quote" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Submit Quote for RFQ
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <!-- RFQ Summary -->
                    <div class="p-6 bg-gray-50 border-b">
                        <h3 class="text-lg font-medium text-gray-900">RFQ Details</h3>
                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Product</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ rfq.product.name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Buyer</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ rfq.buyer.name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Quantity Required</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ rfq.quantity }} {{ rfq.quantity_unit }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Target Price Range</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ rfq.target_price_range || 'Not specified' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Target Delivery Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ rfq.target_delivery_date ? new Date(rfq.target_delivery_date).toLocaleDateString() : 'Not specified' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Valid Until</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ new Date(rfq.valid_until).toLocaleDateString() }}</dd>
                            </div>
                        </div>
                    </div>

                    <!-- Quote Form -->
                    <form @submit.prevent="submit" class="p-6">
                        <!-- Price and Quantity -->
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Price and Quantity</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Specify your offer details.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-2">
                                    <InputLabel for="price" value="Price" />
                                    <TextInput
                                        id="price"
                                        v-model="form.price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError :message="form.errors.price" class="mt-2" />
                                </div>

                                <div class="sm:col-span-2">
                                    <InputLabel for="price_unit" value="Price Unit" />
                                    <select
                                        id="price_unit"
                                        v-model="form.price_unit"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-selina focus:ring-selina sm:text-sm"
                                        required
                                    >
                                        <optgroup v-for="(units, category) in props.units" :key="category" :label="category">
                                            <option v-for="unit in units" :key="unit" :value="unit">per {{ unit }}</option>
                                        </optgroup>
                                    </select>
                                    <InputError :message="form.errors.price_unit" class="mt-2" />
                                </div>

                                <div class="sm:col-span-2">
                                    <InputLabel for="quantity" value="Quantity" />
                                    <TextInput
                                        id="quantity"
                                        v-model="form.quantity"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError :message="form.errors.quantity" class="mt-2" />
                                </div>

                                <div class="sm:col-span-2">
                                    <InputLabel for="quantity_unit" value="Quantity Unit" />
                                    <select
                                        id="quantity_unit"
                                        v-model="form.quantity_unit"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-selina focus:ring-selina sm:text-sm"
                                        required
                                    >
                                        <optgroup v-for="(units, category) in props.units" :key="category" :label="category">
                                            <option v-for="unit in units" :key="unit" :value="unit">{{ unit }}</option>
                                        </optgroup>
                                    </select>
                                    <InputError :message="form.errors.quantity_unit" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Specifications -->
                        <div class="mt-8 space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Specifications</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Provide detailed specifications for your offer.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-6">
                                    <InputLabel for="specifications" value="Product Specifications" />
                                    <textarea
                                        id="specifications"
                                        v-model="form.specifications"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-selina focus:ring-selina sm:text-sm"
                                    />
                                    <InputError :message="form.errors.specifications" class="mt-2" />
                                </div>

                                <div class="sm:col-span-6">
                                    <InputLabel for="packaging_details" value="Packaging Details" />
                                    <textarea
                                        id="packaging_details"
                                        v-model="form.packaging_details"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-selina focus:ring-selina sm:text-sm"
                                    />
                                    <InputError :message="form.errors.packaging_details" class="mt-2" />
                                </div>

                                <div class="sm:col-span-6">
                                    <InputLabel for="shipping_details" value="Shipping Details" />
                                    <textarea
                                        id="shipping_details"
                                        v-model="form.shipping_details"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-selina focus:ring-selina sm:text-sm"
                                    />
                                    <InputError :message="form.errors.shipping_details" class="mt-2" />
                                </div>

                                <div class="sm:col-span-6">
                                    <InputLabel for="quality_certifications" value="Quality Certifications" />
                                    <textarea
                                        id="quality_certifications"
                                        v-model="form.quality_certifications"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-selina focus:ring-selina sm:text-sm"
                                    />
                                    <InputError :message="form.errors.quality_certifications" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Terms -->
                        <div class="mt-8 space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Terms</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Specify delivery and payment terms.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-3">
                                    <InputLabel for="delivery_date" value="Delivery Date" />
                                    <TextInput
                                        id="delivery_date"
                                        v-model="form.delivery_date"
                                        type="date"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError :message="form.errors.delivery_date" class="mt-2" />
                                </div>

                                <div class="sm:col-span-3">
                                    <InputLabel for="payment_terms" value="Payment Terms" />
                                    <select
                                        id="payment_terms"
                                        v-model="form.payment_terms"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-selina focus:ring-selina sm:text-sm"
                                        required
                                    >
                                        <option value="">Select payment terms</option>
                                        <option v-for="term in payment_terms" :key="term" :value="term">
                                            {{ term }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.payment_terms" class="mt-2" />
                                </div>

                                <div class="sm:col-span-3">
                                    <InputLabel for="valid_until" value="Valid Until" />
                                    <TextInput
                                        id="valid_until"
                                        v-model="form.valid_until"
                                        type="date"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError :message="form.errors.valid_until" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Additional Notes -->
                        <div class="mt-8 space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Additional Notes</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Any additional information you want to provide.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-6">
                                    <InputLabel for="additional_notes" value="Additional Notes" />
                                    <textarea
                                        id="additional_notes"
                                        v-model="form.additional_notes"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-selina focus:ring-selina sm:text-sm"
                                    />
                                    <InputError :message="form.errors.additional_notes" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end space-x-3">
                            <Link
                                :href="route('rfqs.show', rfq.id)"
                                class="inline-flex justify-center rounded-md border border-gray-300 py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-selina focus:ring-offset-2"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                class="inline-flex justify-center rounded-md border border-transparent bg-selina py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-wamucii focus:outline-none focus:ring-2 focus:ring-selina focus:ring-offset-2"
                                :disabled="form.processing"
                            >
                                Submit Quote
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    rfq: Object,
    units: Object,
    payment_terms: Array,
});

const form = useForm({
    price: '',
    price_unit: '',
    quantity: rfq.quantity,
    quantity_unit: rfq.quantity_unit,
    specifications: '',
    packaging_details: '',
    shipping_details: '',
    quality_certifications: '',
    delivery_date: '',
    payment_terms: '',
    additional_notes: '',
    valid_until: '',
});

const submit = () => {
    form.post(route('rfqs.quotes.store', props.rfq.id), {
        onSuccess: () => {
            // Success notification handled by global handler
        },
    });
};
</script> 