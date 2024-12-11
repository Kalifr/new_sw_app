<template>
    <Head :title="thread.subject" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        {{ thread.subject }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        With {{ participantsExceptUser(thread.participants).join(', ') }}
                    </p>
                </div>
                <Link
                    :href="route('messages.index')"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-medium text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-selina focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                >
                    Back to Messages
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Messages -->
                        <div class="space-y-6">
                            <div
                                v-for="message in messages.data"
                                :key="message.id"
                                :class="[
                                    'flex',
                                    message.user.id === $page.props.auth.user.id ? 'justify-end' : 'justify-start'
                                ]"
                            >
                                <div
                                    :class="[
                                        'max-w-[70%] rounded-lg px-4 py-2',
                                        message.user.id === $page.props.auth.user.id
                                            ? 'bg-selina text-white'
                                            : 'bg-gray-100 text-gray-900'
                                    ]"
                                >
                                    <div class="flex items-center space-x-2">
                                        <span class="font-medium">{{ message.user.name }}</span>
                                        <span class="text-xs opacity-75">
                                            {{ formatDate(message.created_at) }}
                                        </span>
                                    </div>
                                    
                                    <!-- Text Message -->
                                    <div v-if="message.type === 'text'" class="mt-1">
                                        {{ message.body }}
                                    </div>

                                    <!-- Image Message -->
                                    <div v-else-if="message.type === 'image'" class="mt-2">
                                        <img
                                            :src="storage(message.metadata.path)"
                                            :alt="message.metadata.filename"
                                            class="rounded-lg max-w-full"
                                        />
                                        <p class="mt-1 text-sm">{{ message.body }}</p>
                                    </div>

                                    <!-- File Message -->
                                    <div v-else-if="message.type === 'file'" class="mt-2">
                                        <a
                                            :href="storage(message.metadata.path)"
                                            target="_blank"
                                            class="flex items-center space-x-2 text-current hover:underline"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <span>{{ message.metadata.filename }}</span>
                                        </a>
                                        <p class="mt-1 text-sm">{{ message.body }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <Pagination
                            v-if="messages.data.length > 0"
                            :links="messages.links"
                            class="mt-6"
                        />

                        <!-- Message Input -->
                        <div class="mt-6 border-t pt-4">
                            <form @submit.prevent="sendMessage" class="space-y-4">
                                <div>
                                    <textarea
                                        v-model="form.body"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-selina focus:ring-selina sm:text-sm"
                                        placeholder="Type your message..."
                                        required
                                    ></textarea>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <input
                                            ref="fileInput"
                                            type="file"
                                            class="hidden"
                                            @change="handleFileUpload"
                                            accept="image/*,.pdf,.doc,.docx,.xls,.xlsx"
                                        />
                                        <button
                                            type="button"
                                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-selina"
                                            @click="$refs.fileInput.click()"
                                        >
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                            </svg>
                                            Attach File
                                        </button>
                                        <div v-if="form.file" class="text-sm text-gray-600">
                                            {{ form.file.name }}
                                            <button
                                                type="button"
                                                class="ml-2 text-red-600 hover:text-red-500"
                                                @click="form.file = null"
                                            >
                                                &times;
                                            </button>
                                        </div>
                                    </div>

                                    <button
                                        type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-selina border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-wamucii focus:bg-wamucii active:bg-wamucii focus:outline-none focus:ring-2 focus:ring-selina focus:ring-offset-2 transition ease-in-out duration-150"
                                        :disabled="form.processing"
                                    >
                                        Send Message
                                    </button>
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
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    thread: Object,
    messages: Object,
});

const fileInput = ref(null);

const form = useForm({
    body: '',
    type: 'text',
    file: null,
});

const participantsExceptUser = (participants) => {
    return participants
        .filter(p => p.id !== usePage().props.auth.user.id)
        .map(p => p.name);
};

const formatDate = (date) => {
    if (!date) return '';
    
    const messageDate = new Date(date);
    const now = new Date();
    const diffInHours = (now - messageDate) / (1000 * 60 * 60);

    if (diffInHours < 24) {
        return messageDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    } else if (diffInHours < 48) {
        return 'Yesterday';
    } else {
        return messageDate.toLocaleDateString();
    }
};

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    form.file = file;
    form.type = file.type.startsWith('image/') ? 'image' : 'file';
};

const sendMessage = () => {
    form.post(route('messages.store', props.thread.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            if (fileInput.value) {
                fileInput.value.value = '';
            }
        },
    });
};

const storage = (path) => {
    return `/storage/${path}`;
};

// Listen for new messages
onMounted(() => {
    window.Echo.private(`thread.${props.thread.id}`)
        .listen('NewMessage', (e) => {
            messages.data.unshift(e.message);
        });
});

onUnmounted(() => {
    window.Echo.leave(`thread.${props.thread.id}`);
});
</script> 