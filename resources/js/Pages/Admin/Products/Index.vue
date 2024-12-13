<template>
    <Head>
        <title>Manage Products</title>
        <meta name="description" content="Manage your products">
    </Head>

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Manage Products
                </h2>
                <Link
                    :href="route('admin.products.create')"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200"
                >
                    Add New Product
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="products.data.length > 0">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Product
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Price
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Created
                                            </th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="product in products.data" :key="product.id">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="h-10 w-10 flex-shrink-0">
                                                        <img v-if="product.mainImage" 
                                                            :src="'/storage/' + product.mainImage.path" 
                                                            class="h-10 w-10 rounded-full object-cover"
                                                            :alt="product.name">
                                                        <div v-else class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                            <span class="text-xs text-gray-500">No img</span>
                                                        </div>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ product.name }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span :class="{
                                                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                                                    'bg-green-100 text-green-800': product.status === 'published',
                                                    'bg-yellow-100 text-yellow-800': product.status === 'draft',
                                                    'bg-red-100 text-red-800': product.status === 'expired',
                                                    'bg-blue-100 text-blue-800': product.status === 'sold'
                                                }">
                                                    {{ product.status.charAt(0).toUpperCase() + product.status.slice(1) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                ${{ product.price.toLocaleString() }} / {{ product.price_unit }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ new Date(product.created_at).toLocaleDateString() }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <Link
                                                    :href="route('admin.products.edit', product.id)"
                                                    class="text-blue-600 hover:text-blue-900 mr-4"
                                                >
                                                    Edit
                                                </Link>
                                                <button
                                                    v-if="product.status === 'draft'"
                                                    @click="publish(product)"
                                                    class="text-green-600 hover:text-green-900 mr-4"
                                                >
                                                    Publish
                                                </button>
                                                <Link
                                                    :href="route('admin.products.destroy', product.id)"
                                                    method="delete"
                                                    as="button"
                                                    class="text-red-600 hover:text-red-900"
                                                    @click="confirmDelete"
                                                >
                                                    Delete
                                                </Link>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <Pagination :links="products.links" class="mt-6" />
                        </div>
                        <div v-else class="text-center py-8 text-gray-500">
                            No products available yet.
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
import Pagination from '@/Components/Pagination.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    products: {
        type: Object,
        required: true
    }
});

const publish = (product) => {
    router.patch(route('admin.products.publish', product.id));
};

const confirmDelete = (e) => {
    if (!confirm('Are you sure you want to delete this product?')) {
        e.preventDefault();
    }
};
</script> 