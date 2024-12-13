<template>
    <Head title="Edit Product" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Product
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <!-- Basic Information -->
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Basic Information</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Update the basic details about your product.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-4">
                                    <InputLabel for="name" value="Product Name" />
                                    <TextInput
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError :message="form.errors.name" class="mt-2" />
                                </div>

                                <div class="sm:col-span-6">
                                    <InputLabel for="description" value="Description" />
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        required
                                    />
                                    <InputError :message="form.errors.description" class="mt-2" />
                                </div>

                                <div class="sm:col-span-2">
                                    <InputLabel for="variety" value="Variety" />
                                    <TextInput
                                        id="variety"
                                        v-model="form.variety"
                                        type="text"
                                        class="mt-1 block w-full"
                                    />
                                    <InputError :message="form.errors.variety" class="mt-2" />
                                </div>

                                <div class="sm:col-span-2">
                                    <InputLabel for="grade" value="Grade" />
                                    <TextInput
                                        id="grade"
                                        v-model="form.grade"
                                        type="text"
                                        class="mt-1 block w-full"
                                    />
                                    <InputError :message="form.errors.grade" class="mt-2" />
                                </div>

                                <div class="sm:col-span-2">
                                    <InputLabel for="growing_method" value="Growing Method" />
                                    <TextInput
                                        id="growing_method"
                                        v-model="form.growing_method"
                                        type="text"
                                        class="mt-1 block w-full"
                                    />
                                    <InputError :message="form.errors.growing_method" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Pricing and Quantity -->
                        <div class="mt-8 space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Pricing and Quantity</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Update your product's pricing and availability details.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-2">
                                    <InputLabel for="price" value="Price" />
                                    <div class="mt-1 flex rounded-md shadow-sm">
                                        <TextInput
                                            id="price"
                                            v-model="form.price"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="block w-full"
                                            required
                                        />
                                    </div>
                                    <InputError :message="form.errors.price" class="mt-2" />
                                </div>

                                <div class="sm:col-span-2">
                                    <InputLabel for="price_unit" value="Price Unit" />
                                    <select
                                        id="price_unit"
                                        v-model="form.price_unit"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        required
                                    >
                                        <optgroup v-for="(units, category) in props.units" :key="category" :label="category">
                                            <option v-for="unit in units" :key="unit" :value="unit">
                                                per {{ unit }}
                                            </option>
                                        </optgroup>
                                    </select>
                                    <InputError :message="form.errors.price_unit" class="mt-2" />
                                </div>

                                <div class="sm:col-span-2">
                                    <InputLabel for="minimum_order" value="Minimum Order" />
                                    <div class="mt-1 flex rounded-md shadow-sm">
                                        <TextInput
                                            id="minimum_order"
                                            v-model="form.minimum_order"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="block w-full"
                                            required
                                        />
                                    </div>
                                    <InputError :message="form.errors.minimum_order" class="mt-2" />
                                </div>

                                <div class="sm:col-span-2">
                                    <InputLabel for="minimum_order_unit" value="Minimum Order Unit" />
                                    <select
                                        id="minimum_order_unit"
                                        v-model="form.minimum_order_unit"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        required
                                    >
                                        <optgroup v-for="(units, category) in props.units" :key="category" :label="category">
                                            <option v-for="unit in units" :key="unit" :value="unit">{{ unit }}</option>
                                        </optgroup>
                                    </select>
                                    <InputError :message="form.errors.minimum_order_unit" class="mt-2" />
                                </div>

                                <div class="sm:col-span-2">
                                    <InputLabel for="quantity_available" value="Quantity Available" />
                                    <div class="mt-1 flex rounded-md shadow-sm">
                                        <TextInput
                                            id="quantity_available"
                                            v-model="form.quantity_available"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="block w-full"
                                            required
                                        />
                                    </div>
                                    <InputError :message="form.errors.quantity_available" class="mt-2" />
                                </div>

                                <div class="sm:col-span-2">
                                    <InputLabel for="quantity_unit" value="Quantity Unit" />
                                    <select
                                        id="quantity_unit"
                                        v-model="form.quantity_unit"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
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

                        <!-- Origin and Dates -->
                        <div class="mt-8 space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Origin and Dates</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Update where your product comes from and important dates.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-3">
                                    <InputLabel for="country_of_origin" value="Country of Origin" />
                                    <TextInput
                                        id="country_of_origin"
                                        v-model="form.country_of_origin"
                                        type="text"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError :message="form.errors.country_of_origin" class="mt-2" />
                                </div>

                                <div class="sm:col-span-3">
                                    <InputLabel for="region" value="Region" />
                                    <TextInput
                                        id="region"
                                        v-model="form.region"
                                        type="text"
                                        class="mt-1 block w-full"
                                    />
                                    <InputError :message="form.errors.region" class="mt-2" />
                                </div>

                                <div class="sm:col-span-3">
                                    <InputLabel for="harvest_date" value="Harvest Date" />
                                    <TextInput
                                        id="harvest_date"
                                        v-model="form.harvest_date"
                                        type="date"
                                        class="mt-1 block w-full"
                                    />
                                    <InputError :message="form.errors.harvest_date" class="mt-2" />
                                </div>

                                <div class="sm:col-span-3">
                                    <InputLabel for="expiry_date" value="Expiry Date" />
                                    <TextInput
                                        id="expiry_date"
                                        v-model="form.expiry_date"
                                        type="date"
                                        class="mt-1 block w-full"
                                    />
                                    <InputError :message="form.errors.expiry_date" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Additional Details -->
                        <div class="mt-8 space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Additional Details</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Update details about storage, packaging, and certifications.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-3">
                                    <InputLabel for="storage_conditions" value="Storage Conditions" />
                                    <TextInput
                                        id="storage_conditions"
                                        v-model="form.storage_conditions"
                                        type="text"
                                        class="mt-1 block w-full"
                                    />
                                    <InputError :message="form.errors.storage_conditions" class="mt-2" />
                                </div>

                                <div class="sm:col-span-3">
                                    <InputLabel for="packaging_details" value="Packaging Details" />
                                    <TextInput
                                        id="packaging_details"
                                        v-model="form.packaging_details"
                                        type="text"
                                        class="mt-1 block w-full"
                                    />
                                    <InputError :message="form.errors.packaging_details" class="mt-2" />
                                </div>

                                <div class="sm:col-span-6">
                                    <InputLabel value="Certifications" />
                                    <div class="mt-2 space-y-2">
                                        <div v-for="cert in props.certifications" :key="cert" class="flex items-center">
                                            <input
                                                :id="'cert-' + cert"
                                                v-model="form.certifications"
                                                :value="cert"
                                                type="checkbox"
                                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            />
                                            <label :for="'cert-' + cert" class="ml-2 text-sm text-gray-600">
                                                {{ cert }}
                                            </label>
                                        </div>
                                    </div>
                                    <InputError :message="form.errors.certifications" class="mt-2" />
                                </div>

                                <div class="sm:col-span-6">
                                    <InputLabel value="Processing Level" />
                                    <div class="mt-2 space-y-2">
                                        <div v-for="level in props.processing_levels" :key="level" class="flex items-center">
                                            <input
                                                :id="'level-' + level"
                                                v-model="form.processing_level"
                                                :value="level"
                                                type="checkbox"
                                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            />
                                            <label :for="'level-' + level" class="ml-2 text-sm text-gray-600">
                                                {{ level }}
                                            </label>
                                        </div>
                                    </div>
                                    <InputError :message="form.errors.processing_level" class="mt-2" />
                                </div>

                                <div class="sm:col-span-6">
                                    <InputLabel value="Payment Terms" />
                                    <div class="mt-2 space-y-2">
                                        <div v-for="term in props.payment_terms" :key="term" class="flex items-center">
                                            <input
                                                :id="'payment-' + term"
                                                v-model="form.payment_terms"
                                                :value="term"
                                                type="checkbox"
                                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            />
                                            <label :for="'payment-' + term" class="ml-2 text-sm text-gray-600">
                                                {{ term }}
                                            </label>
                                        </div>
                                    </div>
                                    <InputError :message="form.errors.payment_terms" class="mt-2" />
                                </div>

                                <div class="sm:col-span-6">
                                    <InputLabel value="Delivery Terms" />
                                    <div class="mt-2 space-y-2">
                                        <div v-for="term in props.delivery_terms" :key="term" class="flex items-center">
                                            <input
                                                :id="'delivery-' + term"
                                                v-model="form.delivery_terms"
                                                :value="term"
                                                type="checkbox"
                                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            />
                                            <label :for="'delivery-' + term" class="ml-2 text-sm text-gray-600">
                                                {{ term }}
                                            </label>
                                        </div>
                                    </div>
                                    <InputError :message="form.errors.delivery_terms" class="mt-2" />
                                </div>

                                <div class="sm:col-span-6">
                                    <div class="flex items-center">
                                        <input
                                            id="sample_available"
                                            v-model="form.sample_available"
                                            type="checkbox"
                                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        />
                                        <label for="sample_available" class="ml-2 text-sm text-gray-600">
                                            Sample Available
                                        </label>
                                    </div>
                                    <InputError :message="form.errors.sample_available" class="mt-2" />
                                </div>

                                <div class="sm:col-span-6">
                                    <InputLabel value="Available Months" />
                                    <div class="mt-2 grid grid-cols-3 gap-4 sm:grid-cols-6">
                                        <div v-for="month in months" :key="month" class="flex items-center">
                                            <input
                                                :id="'month-' + month"
                                                v-model="form.available_months"
                                                :value="month"
                                                type="checkbox"
                                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            />
                                            <label :for="'month-' + month" class="ml-2 text-sm text-gray-600">
                                                {{ month }}
                                            </label>
                                        </div>
                                    </div>
                                    <InputError :message="form.errors.available_months" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Images -->
                        <div class="mt-8 space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Product Images</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Update images of your product. The first image will be used as the main image.
                                </p>
                            </div>

                            <div class="space-y-4">
                                <div
                                    class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md"
                                    @dragover.prevent
                                    @drop.prevent="handleDrop"
                                >
                                    <div class="space-y-1 text-center">
                                        <svg
                                            class="mx-auto h-12 w-12 text-gray-400"
                                            stroke="currentColor"
                                            fill="none"
                                            viewBox="0 0 48 48"
                                            aria-hidden="true"
                                        >
                                            <path
                                                d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                        <div class="flex text-sm text-gray-600">
                                            <label
                                                for="images"
                                                class="relative cursor-pointer rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500"
                                            >
                                                <span>Upload files</span>
                                                <input
                                                    id="images"
                                                    type="file"
                                                    multiple
                                                    accept="image/*"
                                                    class="sr-only"
                                                    @change="handleFileSelect"
                                                />
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, GIF up to 5MB</p>
                                    </div>
                                </div>

                                <!-- Existing Images -->
                                <div v-if="product.images.length > 0" class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                                    <div
                                        v-for="image in product.images"
                                        :key="image.id"
                                        class="relative group"
                                    >
                                        <div class="aspect-w-1 aspect-h-1 rounded-lg bg-gray-100 overflow-hidden">
                                            <img
                                                :src="'/storage/' + image.path"
                                                class="object-cover"
                                                :class="{ 'border-2 border-indigo-500': image.type === 'main' }"
                                            />
                                            <button
                                                type="button"
                                                class="absolute top-2 right-2 p-1 rounded-full bg-red-100 text-red-600 opacity-0 group-hover:opacity-100 transition-opacity"
                                                @click="deleteImage(image.id)"
                                            >
                                                <XMarkIcon class="h-4 w-4" />
                                            </button>
                                        </div>
                                        <div class="mt-2">
                                            <select
                                                v-model="image.type"
                                                class="block w-full text-sm rounded-md border-gray-300"
                                                @change="updateImageType(image)"
                                            >
                                                <option value="main">Main Image</option>
                                                <option value="gallery">Gallery Image</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- New Images Preview -->
                                <div v-if="previewImages.length > 0" class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                                    <div
                                        v-for="(preview, index) in previewImages"
                                        :key="index"
                                        class="relative group"
                                    >
                                        <div class="aspect-w-1 aspect-h-1 rounded-lg bg-gray-100 overflow-hidden">
                                            <img
                                                :src="preview.url"
                                                class="object-cover"
                                            />
                                            <button
                                                type="button"
                                                class="absolute top-2 right-2 p-1 rounded-full bg-red-100 text-red-600 opacity-0 group-hover:opacity-100 transition-opacity"
                                                @click="removeImage(index)"
                                            >
                                                <XMarkIcon class="h-4 w-4" />
                                            </button>
                                        </div>
                                        <div class="mt-2">
                                            <select
                                                v-model="preview.type"
                                                class="block w-full text-sm rounded-md border-gray-300"
                                            >
                                                <option value="main">Main Image</option>
                                                <option value="gallery">Gallery Image</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-between">
                            <div>
                                <select
                                    v-model="form.status"
                                    class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="expired">Expired</option>
                                </select>
                            </div>
                            <div class="flex space-x-3">
                                <Link
                                    :href="route('products.listing.index')"
                                    class="inline-flex justify-center rounded-md border border-gray-300 py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                    :disabled="form.processing"
                                >
                                    Update Product
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { XMarkIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    product: Object,
    units: Object,
    certifications: Array,
    processing_levels: Array,
    payment_terms: Array,
    delivery_terms: Array,
});

const months = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

const form = useForm({
    ...props.product,
    deleted_images: [],
    new_images: [],
});

const previewImages = ref([]);

const handleFileSelect = (event) => {
    const files = Array.from(event.target.files);
    handleFiles(files);
};

const handleDrop = (event) => {
    const files = Array.from(event.dataTransfer.files);
    handleFiles(files);
};

const handleFiles = (files) => {
    files.forEach((file) => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                previewImages.value.push({
                    file,
                    url: e.target.result,
                    type: 'gallery',
                    order: previewImages.value.length
                });
            };
            reader.readAsDataURL(file);
        }
    });
};

const removeImage = (index) => {
    previewImages.value.splice(index, 1);
};

const deleteImage = (imageId) => {
    form.deleted_images.push(imageId);
};

const updateImageType = (image) => {
    if (image.type === 'main') {
        // Ensure only one main image
        props.product.images.forEach(img => {
            if (img.id !== image.id && img.type === 'main') {
                img.type = 'gallery';
            }
        });
    }
};

const submit = () => {
    const formData = new FormData();
    
    // Append all form fields
    Object.keys(form).forEach(key => {
        if (key !== 'new_images' && form[key] !== null && form[key] !== undefined) {
            if (Array.isArray(form[key])) {
                formData.append(key, JSON.stringify(form[key]));
            } else {
                formData.append(key, form[key]);
            }
        }
    });

    // Append new images
    previewImages.value.forEach((preview, index) => {
        formData.append(`new_images[${index}][file]`, preview.file);
        formData.append(`new_images[${index}][type]`, preview.type);
        formData.append(`new_images[${index}][order]`, preview.order);
    });

    form.post(route('products.update', props.product.id), {
        forceFormData: true,
        data: formData,
        preserveScroll: true,
        onSuccess: () => {
            previewImages.value = [];
        },
    });
};
</script>
