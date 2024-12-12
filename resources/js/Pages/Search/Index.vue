<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-cocomat text-xl text-wamucii leading-tight">
                Search Products
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Search Bar with Suggestions -->
                        <div class="relative search-container">
                            <input
                                v-model="searchQuery"
                                type="text"
                                class="w-full px-4 py-2 text-lg border-gray-300 rounded-lg focus:border-selina focus:ring-selina"
                                placeholder="Search for products..."
                                @input="handleInput"
                                @keydown.enter="search"
                            >
                            <!-- Search Suggestions -->
                            <div v-if="showSuggestions && suggestions.length > 0" 
                                class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg">
                                <div v-for="suggestion in suggestions" 
                                    :key="suggestion.id" 
                                    class="flex items-center p-3 hover:bg-gray-50 cursor-pointer"
                                    @click="selectSuggestion(suggestion)">
                                    <img v-if="suggestion.image" 
                                        :src="suggestion.image" 
                                        class="w-12 h-12 object-cover rounded"
                                        :alt="suggestion.name">
                                    <div class="ml-3">
                                        <div class="font-medium">{{ suggestion.name }}</div>
                                        <div class="text-sm text-gray-500">{{ suggestion.category }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Categories -->
                            <div>
                                <label class="block font-cocomat text-sm text-wamucii mb-2">
                                    Categories
                                </label>
                                <select v-model="selectedCategories" 
                                    class="w-full rounded-md" 
                                    multiple>
                                    <option v-for="category in categories" 
                                        :key="category" 
                                        :value="category">
                                        {{ category }}
                                    </option>
                                </select>
                            </div>

                            <!-- Locations -->
                            <div>
                                <label class="block font-cocomat text-sm text-wamucii mb-2">
                                    Locations
                                </label>
                                <select v-model="selectedLocations" 
                                    class="w-full rounded-md" 
                                    multiple>
                                    <option v-for="location in locations" 
                                        :key="location" 
                                        :value="location">
                                        {{ location }}
                                    </option>
                                </select>
                            </div>

                            <!-- Price Range -->
                            <div>
                                <label class="block font-cocomat text-sm text-wamucii mb-2">
                                    Price Range
                                </label>
                                <div class="flex space-x-2">
                                    <input v-model.number="priceMin" 
                                        type="number" 
                                        class="w-1/2 rounded-md" 
                                        placeholder="Min">
                                    <input v-model.number="priceMax" 
                                        type="number" 
                                        class="w-1/2 rounded-md" 
                                        placeholder="Max">
                                </div>
                            </div>
                        </div>

                        <!-- Search Button -->
                        <div class="mt-6 flex justify-center">
                            <PrimaryButton @click="search" class="px-8">
                                Search
                            </PrimaryButton>
                        </div>

                        <!-- No Results RFQ Form -->
                        <div v-if="showNoResultsForm" class="mt-8 bg-selina-light p-6 rounded-lg">
                            <h3 class="font-cocomat text-lg text-wamucii mb-4">
                                Can't find what you're looking for?
                            </h3>
                            <p class="mb-4">
                                Create an RFQ and let suppliers come to you with their best offers.
                            </p>
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

                                <div class="flex justify-end">
                                    <PrimaryButton type="submit">
                                        Create RFQ
                                    </PrimaryButton>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    categories: Array,
    locations: Array,
});

const searchQuery = ref('');
const selectedCategories = ref([]);
const selectedLocations = ref([]);
const priceMin = ref(null);
const priceMax = ref(null);
const suggestions = ref([]);
const showSuggestions = ref(false);
const showNoResultsForm = ref(false);

const rfqForm = ref({
    product_name: '',
    quantity: null,
    quantity_unit: 'kg',
    specifications: '',
    delivery_location: '',
    target_delivery_date: '',
    target_price_range: '',
});

const handleInput = debounce(async () => {
    if (searchQuery.value.length < 2) {
        suggestions.value = [];
        showSuggestions.value = false;
        return;
    }

    try {
        const response = await fetch(`/api/search/suggestions?query=${encodeURIComponent(searchQuery.value)}`);
        suggestions.value = await response.json();
        showSuggestions.value = true;
    } catch (error) {
        console.error('Error fetching suggestions:', error);
    }
}, 300);

const selectSuggestion = (suggestion) => {
    searchQuery.value = suggestion.name;
    showSuggestions.value = false;
    router.visit(suggestion.url);
};

const search = async () => {
    const params = new URLSearchParams({
        query: searchQuery.value,
        ...(selectedCategories.value.length && { categories: JSON.stringify(selectedCategories.value) }),
        ...(selectedLocations.value.length && { locations: JSON.stringify(selectedLocations.value) }),
        ...(priceMin.value && { price_min: priceMin.value }),
        ...(priceMax.value && { price_max: priceMax.value }),
    });

    try {
        const response = await fetch(`/api/search?${params.toString()}`);
        const data = await response.json();
        
        if (data.meta.total === 0) {
            showNoResultsForm.value = true;
            rfqForm.value.product_name = searchQuery.value;
        } else {
            router.visit(`/search/results?${params.toString()}`);
        }
    } catch (error) {
        console.error('Error performing search:', error);
    }
};

const submitRfq = () => {
    router.post('/search/create-rfq', rfqForm.value);
};

// Close suggestions when clicking outside
const handleClickOutside = (event) => {
    if (!event.target.closest('.search-container')) {
        showSuggestions.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script> 