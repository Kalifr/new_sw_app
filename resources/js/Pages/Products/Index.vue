<template>
    <Head>
        <title>{{ meta?.title || 'Browse Products' }}</title>
        <meta name="description" :content="meta?.description || 'Browse our products'">
        <meta name="keywords" :content="meta?.keywords || 'products, marketplace'">
    </Head>

    <PublicLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Browse Products
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Categories Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                    <div class="p-6">
                        <h2 class="text-2xl font-bold mb-6">Product Categories</h2>
                        <div v-if="categories && categories.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <Link 
                                v-for="category in categories" 
                                :key="category.id"
                                :href="route('products.listing.category', category.slug)"
                                class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200"
                            >
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ category.name }}</h3>
                                    <p class="text-sm text-gray-600">{{ category.products_count || 0 }} products</p>
                                </div>
                            </Link>
                        </div>
                        <div v-else class="text-center py-8 text-gray-500">
                            No categories available yet.
                        </div>
                    </div>
                </div>

                <!-- Featured Products Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-2xl font-bold mb-6">Featured Products</h2>
                        <div v-if="featuredProducts && featuredProducts.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            <div v-for="product in featuredProducts" :key="product.id" 
                                class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                                <Link :href="route('products.listing.detail', [product.category?.slug || 'uncategorized', product.country?.slug || 'unknown', product.slug])"
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
                                        <p class="text-gray-600 text-sm mb-2">{{ product.country?.name || 'Unknown location' }}</p>
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
                        <div v-else class="text-center py-8 text-gray-500">
                            No featured products available yet.
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

defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
    featuredProducts: {
        type: Array,
        default: () => [],
    },
    meta: {
        type: Object,
        default: () => ({
            title: 'Browse Products',
            description: 'Browse our products',
            keywords: 'products, marketplace',
        }),
    },
});
</script> 