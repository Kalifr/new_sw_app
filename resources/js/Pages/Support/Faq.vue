<?php
<template>
    <PublicLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Frequently Asked Questions
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Categories -->
                        <div class="mb-8">
                            <div class="flex space-x-4">
                                <button
                                    v-for="category in categories"
                                    :key="category.id"
                                    class="px-4 py-2 text-sm font-medium rounded-md"
                                    :class="{
                                        'bg-selina text-white': selectedCategory === category.id,
                                        'text-gray-700 hover:bg-gray-100': selectedCategory !== category.id
                                    }"
                                    @click="selectedCategory = category.id"
                                >
                                    {{ category.name }}
                                </button>
                            </div>
                        </div>

                        <!-- FAQs -->
                        <div class="space-y-8">
                            <template v-for="(categoryFaqs, categoryName) in faqs" :key="categoryName">
                                <div v-if="!selectedCategory || categories.find(c => c.id === selectedCategory)?.name === categoryName">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ categoryName }}</h3>
                                    <div class="space-y-4">
                                        <div v-for="faq in categoryFaqs" :key="faq.id" class="border rounded-lg p-4">
                                            <button
                                                class="flex justify-between items-start w-full text-left"
                                                @click="toggleFaq(faq.id)"
                                            >
                                                <span class="text-gray-900 font-medium">{{ faq.question }}</span>
                                                <span class="ml-6 flex-shrink-0">
                                                    <svg
                                                        class="h-6 w-6 transform transition-transform duration-200"
                                                        :class="{ 'rotate-180': openFaqs.includes(faq.id) }"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                    >
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </span>
                                            </button>
                                            <div v-show="openFaqs.includes(faq.id)" class="mt-4">
                                                <p class="text-gray-700">{{ faq.answer }}</p>
                                                <div class="mt-4 flex items-center space-x-4 text-sm">
                                                    <button
                                                        class="text-gray-600 hover:text-selina flex items-center space-x-1"
                                                        @click="markHelpful(faq.id)"
                                                    >
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                            <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z" />
                                                        </svg>
                                                        <span>Helpful ({{ faq.helpful_count }})</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Contact Support -->
                        <div class="mt-12 text-center">
                            <p class="text-gray-600 mb-4">Can't find what you're looking for?</p>
                            <Link
                                :href="route('support.tickets.create')"
                                class="inline-flex items-center px-4 py-2 bg-selina border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-wamucii"
                            >
                                Contact Support
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineProps({
    faqs: {
        type: Object,
        required: true
    },
    categories: {
        type: Array,
        required: true
    }
});

const selectedCategory = ref(null);
const openFaqs = ref([]);

const toggleFaq = (id) => {
    const index = openFaqs.value.indexOf(id);
    if (index === -1) {
        openFaqs.value.push(id);
    } else {
        openFaqs.value.splice(index, 1);
    }
};

const markHelpful = async (id) => {
    try {
        await axios.post(route('support.faq.helpful', id));
    } catch (error) {
        console.error('Error marking FAQ as helpful:', error);
    }
};
</script> 