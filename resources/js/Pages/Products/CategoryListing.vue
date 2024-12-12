<template>
    <Head>
        <title>{{ meta.title }}</title>
        <meta name="description" :content="meta.description">
        <meta name="keywords" :content="meta.keywords">
    </Head>

    <PublicLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ category.name }} Products
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="products.data.length === 0" class="text-center py-12">
                            <h3 class="text-lg font-medium text-gray-900">No products found</h3>
                            <p class="mt-1 text-sm text-gray-500">There are currently no products in this category.</p>
                        </div>

                        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            <div v-for="product in products.data" :key="product.id" 
                                class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                                <Link :href="route('products.listing.detail', [category.slug, product.country.slug, product.slug])"
                                    class="block">
                                    <div class="aspect-w-16 aspect-h-9">
                                        <img v-if="product.images && product.images.length" 
                                            :src="'/storage/' + product.images[0].path"
                                            :alt="product.name"
                                            class="object-cover w-full h-48">
                                        <div v-else class="bg-gray-200 w-full h-48 flex items-center justify-center">
                                            <span class="text-gray-400">No image available</span>
                                        </div>
                                    </div>
                                    <div class="p-4">
                                        <h3 class="text-lg font-semibold mb-2 text-gray-900">{{ product.name }}</h3>
                                        <p class="text-gray-600 text-sm mb-2">{{ product.country.name }}</p>
                                        <p v-if="product.price" class="text-gray-800 font-bold">
                                            ${{ product.price.toLocaleString() }}
                                        </p>
                                        <p v-else class="text-gray-600 italic">Price on request</p>
                                    </div>
                                </Link>
                                <div class="px-4 pb-4">
                                    <Link v-if="$page.props.auth.user" 
                                        :href="route('rfqs.create', { product_id: product.id })"
                                        class="block w-full text-center bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition-colors duration-300">
                                        Request Quote
                                    </Link>
                                    <Link v-else 
                                        :href="route('login')"
                                        class="block w-full text-center bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition-colors duration-300">
                                        Login to Request Quote
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="products.data.length > 0" class="mt-8">
                            <Pagination :links="products.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import Pagination from '@/Components/Pagination.vue';

defineProps({
    products: Object,
    category: Object,
    meta: Object,
});
</script> 