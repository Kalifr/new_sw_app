<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-cocomat text-xl text-wamucii leading-tight">
                Search Results
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Active Filters -->
                <div v-if="hasActiveFilters" class="mb-6">
                    <h3 class="font-cocomat text-sm text-wamucii mb-2">Active Filters:</h3>
                    <div class="flex flex-wrap gap-2">
                        <div v-if="filters.query" 
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-selina-light text-wamucii">
                            Search: {{ filters.query }}
                            <button @click="removeFilter('query')" class="ml-2 text-wamucii hover:text-red-500">
                                &times;
                            </button>
                        </div>
                        <div v-for="category in filters.categories" :key="category"
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-selina-light text-wamucii">
                            Category: {{ category }}
                            <button @click="removeCategory(category)" class="ml-2 text-wamucii hover:text-red-500">
                                &times;
                            </button>
                        </div>
                        <div v-for="location in filters.locations" :key="location"
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-selina-light text-wamucii">
                            Location: {{ location }}
                            <button @click="removeLocation(location)" class="ml-2 text-wamucii hover:text-red-500">
                                &times;
                            </button>
                        </div>
                        <div v-if="filters.price_min || filters.price_max" 
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-selina-light text-wamucii">
                            Price: {{ formatPriceRange }}
                            <button @click="removePriceRange" class="ml-2 text-wamucii hover:text-red-500">
                                &times;
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Results Grid -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="results.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-for="product in results.data" :key="product.id" 
                                class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                                <img v-if="product.mainImage" 
                                    :src="product.mainImage.path" 
                                    :alt="product.name"
                                    class="w-full h-48 object-cover">
                                <div class="p-4">
                                    <h3 class="font-cocomat text-lg text-wamucii mb-2">
                                        {{ product.name }}
                                    </h3>
                                    <p class="text-gray-600 text-sm mb-2">
                                        {{ truncate(product.description, 100) }}
                                    </p>
                                    <div class="flex justify-between items-center">
                                        <span class="text-selina font-medium">
                                            {{ formatPrice(product.price) }} / {{ product.price_unit }}
                                        </span>
                                        <Link :href="route('products.show', product.id)" 
                                            class="text-selina hover:text-wamucii">
                                            View Details →
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-12">
                            <h3 class="font-cocomat text-xl text-wamucii mb-4">
                                No products found
                            </h3>
                            <p class="text-gray-600 mb-6">
                                We couldn't find any products matching your criteria.
                            </p>
                            <div class="flex justify-center gap-4">
                                <PrimaryButton @click="router.visit(route('search.index'))">
                                    Modify Search
                                </PrimaryButton>
                                <PrimaryButton @click="showRfqForm = true">
                                    Create RFQ Instead
                                </PrimaryButton>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="results.data.length > 0" class="mt-6">
                            <Pagination :links="results.meta.links" />
                        </div>
                    </div>
                </div>

                <!-- RFQ Form Modal -->
                <Modal :show="showRfqForm" @close="showRfqForm = false">
                    <div class="p-6">
                        <h3 class="font-cocomat text-lg text-wamucii mb-4">
                            Create a Request for Quote
                        </h3>
                        <form @submit.prevent="submitRfq" class="space-y-4">
                            <div>
                                <InputLabel for="product_name" value="Product Name" />
                                <TextInput
                                    id="product_name"
                                    v-model="rfqForm.product_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="quantity" value="Quantity" />
                                    <TextInput
                                        id="quantity"
                                        v-model.number="rfqForm.quantity"
                                        type="number"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                </div>
                                <div>
                                    <InputLabel for="quantity_unit" value="Unit" />
                                    <select
                                        id="quantity_unit"
                                        v-model="rfqForm.quantity_unit"
                                        class="mt-1 block w-full rounded-md"
                                        required
                                    >
                                        <option value="kg">Kilograms (kg)</option>
                                        <option value="ton">Metric Tons</option>
                                        <option value="pieces">Pieces</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <InputLabel for="specifications" value="Specifications" />
                                <textarea
                                    id="specifications"
                                    v-model="rfqForm.specifications"
                                    class="mt-1 block w-full rounded-md"
                                    rows="3"
                                ></textarea>
                            </div>

                            <div>
                                <InputLabel for="delivery_location" value="Delivery Location" />
                                <TextInput
                                    id="delivery_location"
                                    v-model="rfqForm.delivery_location"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="target_delivery_date" value="Target Delivery Date" />
                                    <TextInput
                                        id="target_delivery_date"
                                        v-model="rfqForm.target_delivery_date"
                                        type="date"
                                        class="mt-1 block w-full"
                                    />
                                </div>
                                <div>
                                    <InputLabel for="target_price_range" value="Target Price Range" />
                                    <TextInput
                                        id="target_price_range"
                                        v-model="rfqForm.target_price_range"
                                        type="text"
                                        class="mt-1 block w-full"
                                        placeholder="e.g. $100-150 per ton"
                                    />
                                </div>
                            </div>

                            <div class="flex justify-end gap-4">
                                <SecondaryButton @click="showRfqForm = false">
                                    Cancel
                                </SecondaryButton>
                                <PrimaryButton type="submit">
                                    Create RFQ
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </Modal>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    results: Object,
    filters: Object,
});

const showRfqForm = ref(false);
const rfqForm = ref({
    product_name: props.filters.query || '',
    quantity: null,
    quantity_unit: 'kg',
    specifications: '',
    delivery_location: '',
    target_delivery_date: '',
    target_price_range: '',
});

const hasActiveFilters = computed(() => {
    return props.filters.query || 
           (props.filters.categories && props.filters.categories.length) ||
           (props.filters.locations && props.filters.locations.length) ||
           props.filters.price_min ||
           props.filters.price_max;
});

const formatPriceRange = computed(() => {
    if (props.filters.price_min && props.filters.price_max) {
        return `$${props.filters.price_min} - $${props.filters.price_max}`;
    } else if (props.filters.price_min) {
        return `Min: $${props.filters.price_min}`;
    } else if (props.filters.price_max) {
        return `Max: $${props.filters.price_max}`;
    }
    return '';
});

const removeFilter = (filter) => {
    const newFilters = { ...props.filters };
    delete newFilters[filter];
    router.visit(route('search.results', newFilters));
};

const removeCategory = (category) => {
    const newCategories = props.filters.categories.filter(c => c !== category);
    router.visit(route('search.results', {
        ...props.filters,
        categories: newCategories,
    }));
};

const removeLocation = (location) => {
    const newLocations = props.filters.locations.filter(l => l !== location);
    router.visit(route('search.results', {
        ...props.filters,
        locations: newLocations,
    }));
};

const removePriceRange = () => {
    const newFilters = { ...props.filters };
    delete newFilters.price_min;
    delete newFilters.price_max;
    router.visit(route('search.results', newFilters));
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);
};

const truncate = (text, length) => {
    if (text.length <= length) return text;
    return text.substring(0, length) + '...';
};

const submitRfq = () => {
    router.post('/search/create-rfq', rfqForm.value, {
        onSuccess: () => {
            showRfqForm.value = false;
        },
    });
};
</script> 