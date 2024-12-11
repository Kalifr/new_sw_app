<template>
    <Head title="Create RFQ" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create RFQ for {{ product.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <!-- Product Info -->
                        <div class="mb-8 bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900">Product Information</h3>
                            <div class="mt-2 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Seller</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ product.user.name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Product</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ product.name }}</dd>
                                </div>
                            </div>
                        </div>

                        <!-- Quantity and Units -->
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Quantity and Units</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Specify the quantity you want to purchase.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-3">
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

                                <div class="sm:col-span-3">
                                    <InputLabel for="quantity_unit" value="Unit" />
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

                        <!-- Requirements -->
                        <div class="mt-8 space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Requirements</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Specify your requirements for this order.
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
                                    <InputLabel for="packaging_requirements" value="Packaging Requirements" />
                                    <textarea
                                        id="packaging_requirements"
                                        v-model="form.packaging_requirements"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-selina focus:ring-selina sm:text-sm"
                                    />
                                    <InputError :message="form.errors.packaging_requirements" class="mt-2" />
                                </div>

                                <div class="sm:col-span-6">
                                    <InputLabel for="shipping_requirements" value="Shipping Requirements" />
                                    <textarea
                                        id="shipping_requirements"
                                        v-model="form.shipping_requirements"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-selina focus:ring-selina sm:text-sm"
                                    />
                                    <InputError :message="form.errors.shipping_requirements" class="mt-2" />
                                </div>

                                <div class="sm:col-span-6">
                                    <InputLabel for="quality_requirements" value="Quality Requirements" />
                                    <textarea
                                        id="quality_requirements"
                                        v-model="form.quality_requirements"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-selina focus:ring-selina sm:text-sm"
                                    />
                                    <InputError :message="form.errors.quality_requirements" class="mt-2" />
                                </div>

                                <div class="sm:col-span-6">
                                    <InputLabel for="certification_requirements" value="Certification Requirements" />
                                    <textarea
                                        id="certification_requirements"
                                        v-model="form.certification_requirements"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-selina focus:ring-selina sm:text-sm"
                                    />
                                    <InputError :message="form.errors.certification_requirements" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Delivery and Payment -->
                        <div class="mt-8 space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Delivery and Payment</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Specify delivery and payment details.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-3">
                                    <InputLabel for="target_delivery_date" value="Target Delivery Date" />
                                    <TextInput
                                        id="target_delivery_date"
                                        v-model="form.target_delivery_date"
                                        type="date"
                                        class="mt-1 block w-full"
                                    />
                                    <InputError :message="form.errors.target_delivery_date" class="mt-2" />
                                </div>

                                <div class="sm:col-span-3">
                                    <InputLabel for="target_price_range" value="Target Price Range" />
                                    <TextInput
                                        id="target_price_range"
                                        v-model="form.target_price_range"
                                        type="text"
                                        class="mt-1 block w-full"
                                        placeholder="e.g. $100-150 per tonne"
                                    />
                                    <InputError :message="form.errors.target_price_range" class="mt-2" />
                                </div>

                                <div class="sm:col-span-3">
                                    <InputLabel for="payment_terms" value="Payment Terms" />
                                    <select
                                        id="payment_terms"
                                        v-model="form.payment_terms"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-selina focus:ring-selina sm:text-sm"
                                    >
                                        <option value="">Select payment terms</option>
                                        <option v-for="term in payment_terms" :key="term" :value="term">
                                            {{ term }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.payment_terms" class="mt-2" />
                                </div>

                                <div class="sm:col-span-3">
                                    <InputLabel for="delivery_location" value="Delivery Location" />
                                    <TextInput
                                        id="delivery_location"
                                        v-model="form.delivery_location"
                                        type="text"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError :message="form.errors.delivery_location" class="mt-2" />
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

                        <div class="mt-8 flex justify-end space-x-3">
                            <Link
                                :href="route('products.show', product.id)"
                                class="inline-flex justify-center rounded-md border border-gray-300 py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-selina focus:ring-offset-2"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                class="inline-flex justify-center rounded-md border border-transparent bg-selina py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-wamucii focus:outline-none focus:ring-2 focus:ring-selina focus:ring-offset-2"
                                :disabled="form.processing"
                            >
                                Send RFQ
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
    product: Object,
    units: Object,
    payment_terms: Array,
});

const form = useForm({
    quantity: '',
    quantity_unit: '',
    specifications: '',
    packaging_requirements: '',
    shipping_requirements: '',
    quality_requirements: '',
    certification_requirements: '',
    target_delivery_date: '',
    target_price_range: '',
    payment_terms: '',
    delivery_location: '',
    valid_until: '',
});

const submit = () => {
    form.post(route('products.rfq.store', props.product.id), {
        onSuccess: () => {
            // Success notification handled by global handler
        },
    });
};
</script> 