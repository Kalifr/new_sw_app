<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Ticket #{{ ticket.id }}: {{ ticket.title }}
                </h2>
                <div class="flex items-center space-x-4">
                    <button
                        v-if="ticket.status === 'closed'"
                        @click="reopenTicket"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                    >
                        Reopen Ticket
                    </button>
                    <button
                        v-else
                        @click="closeTicket"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                    >
                        Close Ticket
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Ticket Details -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Status</h3>
                                <span :class="getStatusClass(ticket.status)" class="mt-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                    {{ ticket.status }}
                                </span>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Priority</h3>
                                <span :class="getPriorityClass(ticket.priority)" class="mt-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                    {{ ticket.priority }}
                                </span>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Category</h3>
                                <p class="mt-2 text-sm text-gray-600">{{ ticket.category.name }}</p>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Created By</h3>
                                <p class="mt-2 text-sm text-gray-600">{{ ticket.user.name }}</p>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Created At</h3>
                                <p class="mt-2 text-sm text-gray-600">{{ ticket.created_at }}</p>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Assigned To</h3>
                                <p class="mt-2 text-sm text-gray-600">{{ ticket.assigned_to?.name || 'Unassigned' }}</p>
                            </div>
                        </div>

                        <!-- Original Description -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Description</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-gray-600 whitespace-pre-wrap">{{ ticket.description }}</p>
                            </div>
                            <!-- Original Attachments -->
                            <div v-if="ticket.attachments.length" class="mt-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Attachments:</h4>
                                <ul class="space-y-2">
                                    <li v-for="attachment in ticket.attachments" :key="attachment.id" class="flex items-center">
                                        <a :href="attachment.download_url" class="text-indigo-600 hover:text-indigo-900 text-sm">
                                            {{ attachment.file_name }}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Responses -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Responses</h3>
                            <div class="space-y-6">
                                <div v-for="response in ticket.responses" :key="response.id" class="bg-gray-50 p-4 rounded-lg">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <span class="font-medium text-gray-900">{{ response.user.name }}</span>
                                            <span class="text-gray-500 text-sm ml-2">{{ response.created_at }}</span>
                                        </div>
                                        <span v-if="response.is_internal" class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">
                                            Internal Note
                                        </span>
                                    </div>
                                    <p class="text-gray-600 whitespace-pre-wrap">{{ response.content }}</p>
                                    <!-- Response Attachments -->
                                    <div v-if="response.attachments.length" class="mt-4">
                                        <h4 class="text-sm font-medium text-gray-700 mb-2">Attachments:</h4>
                                        <ul class="space-y-2">
                                            <li v-for="attachment in response.attachments" :key="attachment.id" class="flex items-center">
                                                <a :href="attachment.download_url" class="text-indigo-600 hover:text-indigo-900 text-sm">
                                                    {{ attachment.file_name }}
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Add Response Form -->
                        <form v-if="ticket.status !== 'closed'" @submit.prevent="submit" class="space-y-6">
                            <div>
                                <InputLabel for="content" value="Add Response" />
                                <TextArea
                                    id="content"
                                    v-model="form.content"
                                    class="mt-1 block w-full"
                                    rows="4"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.content" />
                            </div>

                            <div v-if="canAddInternalNotes" class="flex items-center">
                                <input
                                    type="checkbox"
                                    id="is_internal"
                                    v-model="form.is_internal"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50"
                                />
                                <label for="is_internal" class="ml-2 text-sm text-gray-600">
                                    Internal Note (only visible to support staff)
                                </label>
                            </div>

                            <div>
                                <InputLabel for="response_attachments" value="Attachments" />
                                <input
                                    type="file"
                                    id="response_attachments"
                                    @change="handleFileUpload"
                                    multiple
                                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                />
                                <p class="mt-1 text-sm text-gray-500">
                                    You can upload multiple files (max 10MB each)
                                </p>
                                <InputError class="mt-2" :message="form.errors.attachments" />
                            </div>

                            <div class="flex justify-end">
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Add Response
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
import TextArea from '@/Components/TextArea.vue'
import { useForm } from '@inertiajs/vue3'

export default defineComponent({
    components: {
        AuthenticatedLayout,
        InputError,
        InputLabel,
        PrimaryButton,
        TextArea,
    },

    props: {
        ticket: Object,
        canAddInternalNotes: Boolean,
    },

    setup() {
        const form = useForm({
            content: '',
            is_internal: false,
            attachments: [],
        })

        return { form }
    },

    methods: {
        handleFileUpload(e) {
            this.form.attachments = Array.from(e.target.files)
        },

        submit() {
            this.form.post(route('support.responses.store', this.ticket.id), {
                preserveScroll: true,
                onSuccess: () => {
                    this.form.reset()
                },
            })
        },

        closeTicket() {
            if (confirm('Are you sure you want to close this ticket?')) {
                this.$inertia.post(route('support.close', this.ticket.id))
            }
        },

        reopenTicket() {
            this.$inertia.post(route('support.reopen', this.ticket.id))
        },

        getStatusClass(status) {
            return {
                'bg-yellow-100 text-yellow-800': status === 'open',
                'bg-blue-100 text-blue-800': status === 'in_progress',
                'bg-green-100 text-green-800': status === 'resolved',
                'bg-gray-100 text-gray-800': status === 'closed',
            }
        },

        getPriorityClass(priority) {
            return {
                'bg-gray-100 text-gray-800': priority === 'low',
                'bg-yellow-100 text-yellow-800': priority === 'medium',
                'bg-red-100 text-red-800': priority === 'high',
            }
        },
    },
})
</script> 