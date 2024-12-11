<template>
    <Head title="My Products" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    My Products
                </h2>
                <Link
                    :href="route('products.create')"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    Add New Product
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div v-if="products.data.length === 0" class="text-center py-12">
                            <h3 class="text-lg font-medium text-gray-900">No products yet</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by creating a new product.</p>
                            <div class="mt-6">
                                <Link
                                    :href="route('products.create')"
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    <PlusIcon class="-ml-1 mr-2 h-5 w-5" aria-hidden="true" />
                                    Add New Product
                                </Link>
                            </div>
                        </div>

                        <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div v-for="product in products.data" :key="product.id" class="relative bg-white border rounded-lg shadow-sm">
                                <div class="h-48 overflow-hidden rounded-t-lg">
                                    <img
                                        v-if="product.main_image"
                                        :src="'/storage/' + product.main_image.path"
                                        :alt="product.name"
                                        class="w-full h-full object-cover"
                                    />
                                    <div v-else class="w-full h-full bg-gray-100 flex items-center justify-center">
                                        <PhotoIcon class="w-12 h-12 text-gray-400" />
                                    </div>
                                </div>
                                <div class="p-4">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-lg font-medium text-gray-900">
                                            <Link :href="route('products.edit', product.id)" class="hover:underline">
                                                {{ product.name }}
                                            </Link>
                                        </h3>
                                        <span
                                            :class="{
                                                'bg-yellow-100 text-yellow-800': product.status === 'draft',
                                                'bg-green-100 text-green-800': product.status === 'published',
                                                'bg-blue-100 text-blue-800': product.status === 'sold',
                                                'bg-red-100 text-red-800': product.status === 'expired'
                                            }"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                                        >
                                            {{ product.status }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ product.price }} {{ product.price_unit }}
                                    </p>
                                    <div class="mt-4 flex justify-between items-center">
                                        <Link
                                            :href="route('products.edit', product.id)"
                                            class="text-sm font-medium text-indigo-600 hover:text-indigo-500"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            v-if="product.status === 'draft'"
                                            @click="publish(product)"
                                            class="text-sm font-medium text-green-600 hover:text-green-500"
                                        >
                                            Publish
                                        </button>
                                        <button
                                            @click="confirmDelete(product)"
                                            class="text-sm font-medium text-red-600 hover:text-red-500"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="products.data.length > 0" class="mt-6">
                            <Pagination :links="products.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { PlusIcon, PhotoIcon } from '@heroicons/vue/24/outline';
import Pagination from '@/Components/Pagination.vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    products: Object,
});

const publish = (product) => {
    router.patch(route('products.publish', product.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Success notification handled by global handler
        },
    });
};

const confirmDelete = (product) => {
    if (confirm('Are you sure you want to delete this product?')) {
        router.delete(route('products.destroy', product.id), {
            onSuccess: () => {
                // Success notification handled by global handler
            },
        });
    }
};
</script> 