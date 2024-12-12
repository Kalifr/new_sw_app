<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create Support Ticket
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Suggested FAQs -->
                        <div v-if="suggested_faqs.length" class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                Before creating a ticket, check if these FAQs help:
                            </h3>
                            <div class="space-y-4">
                                <div v-for="faq in suggested_faqs" :key="faq.id" class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="font-medium text-gray-900">{{ faq.question }}</h4>
                                    <p class="mt-2 text-gray-600">{{ faq.answer }}</p>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <InputLabel for="title" value="Title" />
                                <TextInput
                                    id="title"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.title"
                                    required
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.title" />
                            </div>

                            <div>
                                <InputLabel for="category" value="Category" />
                                <select
                                    id="category"
                                    v-model="form.category_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                >
                                    <option value="">Select Category</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.category_id" />
                            </div>

                            <div>
                                <InputLabel for="priority" value="Priority" />
                                <select
                                    id="priority"
                                    v-model="form.priority"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                >
                                    <option value="">Select Priority</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.priority" />
                            </div>

                            <div>
                                <InputLabel for="description" value="Description" />
                                <TextArea
                                    id="description"
                                    v-model="form.description"
                                    class="mt-1 block w-full"
                                    rows="6"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.description" />
                            </div>

                            <div>
                                <InputLabel for="attachments" value="Attachments" />
                                <input
                                    type="file"
                                    id="attachments"
                                    @change="handleFileUpload"
                                    multiple
                                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                />
                                <p class="mt-1 text-sm text-gray-500">
                                    You can upload multiple files (max 10MB each)
                                </p>
                                <InputError class="mt-2" :message="form.errors.attachments" />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <Link
                                    :href="route('support.index')"
                                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 mr-4"
                                >
                                    Cancel
                                </Link>
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Create Ticket
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import { defineComponent } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import TextArea from '@/Components/TextArea.vue'
import { Link, useForm } from '@inertiajs/vue3'

export default defineComponent({
    components: {
        AuthenticatedLayout,
        InputError,
        InputLabel,
        PrimaryButton,
        TextInput,
        TextArea,
        Link,
    },

    props: {
        categories: Array,
        suggested_faqs: Array,
    },

    setup() {
        const form = useForm({
            title: '',
            category_id: '',
            priority: '',
            description: '',
            attachments: [],
        })

        return { form }
    },

    methods: {
        handleFileUpload(e) {
            this.form.attachments = Array.from(e.target.files)
        },

        submit() {
            this.form.post(route('support.store'), {
                preserveScroll: true,
                onSuccess: () => {
                    this.form.reset()
                },
            })
        },
    },
})
</script> 