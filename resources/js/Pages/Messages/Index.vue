<template>
    <Head title="Messages" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Messages
                </h2>
                <button
                    @click="showNewThreadModal = true"
                    class="inline-flex items-center px-4 py-2 bg-selina border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-wamucii focus:bg-wamucii active:bg-wamucii focus:outline-none focus:ring-2 focus:ring-selina focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    New Message
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="threads.data.length === 0" class="text-center py-12">
                            <p class="text-gray-500">No messages yet.</p>
                        </div>
                        <div v-else class="divide-y divide-gray-200">
                            <div
                                v-for="thread in threads.data"
                                :key="thread.id"
                                class="py-4 hover:bg-gray-50 cursor-pointer"
                                @click="visitThread(thread)"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">
                                            {{ thread.subject }}
                                        </h3>
                                        <div class="mt-1 flex items-center space-x-2 text-sm text-gray-500">
                                            <span>{{ participantsExceptUser(thread.participants).join(', ') }}</span>
                                            <span>&middot;</span>
                                            <span>{{ formatDate(thread.latest_message?.created_at) }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-4">
                                        <span class="text-sm text-gray-600">
                                            {{ thread.messages_count }} {{ thread.messages_count === 1 ? 'message' : 'messages' }}
                                        </span>
                                        <div
                                            v-if="hasUnreadMessages(thread)"
                                            class="w-3 h-3 bg-selina rounded-full"
                                        ></div>
                                    </div>
                                </div>
                                <p v-if="thread.latest_message" class="mt-1 text-sm text-gray-600 line-clamp-1">
                                    {{ thread.latest_message.body }}
                                </p>
                            </div>
                        </div>

                        <Pagination
                            v-if="threads.data.length > 0"
                            :links="threads.links"
                            class="mt-6"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- New Thread Modal -->
        <Modal :show="showNewThreadModal" @close="showNewThreadModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    New Message
                </h2>

                <form @submit.prevent="createThread" class="mt-6">
                    <div>
                        <InputLabel for="subject" value="Subject" />
                        <TextInput
                            id="subject"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.subject"
                            required
                        />
                        <InputError :message="form.errors.subject" class="mt-2" />
                    </div>

                    <div class="mt-6">
                        <InputLabel for="participants" value="Recipients" />
                        <Multiselect
                            v-model="form.participants"
                            :options="users"
                            :multiple="true"
                            track-by="id"
                            label="name"
                            placeholder="Select recipients"
                            class="mt-1"
                        />
                        <InputError :message="form.errors.participants" class="mt-2" />
                    </div>

                    <div class="mt-6">
                        <InputLabel for="message" value="Message" />
                        <textarea
                            id="message"
                            v-model="form.message"
                            rows="4"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-selina focus:ring-selina sm:text-sm"
                            required
                        ></textarea>
                        <InputError :message="form.errors.message" class="mt-2" />
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            class="inline-flex justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-selina focus:ring-offset-2"
                            @click="showNewThreadModal = false"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="inline-flex justify-center rounded-md border border-transparent bg-selina px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-wamucii focus:outline-none focus:ring-2 focus:ring-selina focus:ring-offset-2"
                            :disabled="form.processing"
                        >
                            Send Message
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import Pagination from '@/Components/Pagination.vue';
import Multiselect from '@vueform/multiselect';
import '@vueform/multiselect/themes/default.css';

const props = defineProps({
    threads: Object,
    users: Array,
});

const showNewThreadModal = ref(false);

const form = useForm({
    subject: '',
    participants: [],
    message: '',
});

const visitThread = (thread) => {
    window.location = route('messages.show', thread.id);
};

const createThread = () => {
    form.post(route('messages.threads.store'), {
        onSuccess: () => {
            showNewThreadModal.value = false;
            form.reset();
        },
    });
};

const participantsExceptUser = (participants) => {
    return participants
        .filter(p => p.id !== usePage().props.auth.user.id)
        .map(p => p.name);
};

const hasUnreadMessages = (thread) => {
    const lastRead = thread.participants.find(
        p => p.id === usePage().props.auth.user.id
    )?.pivot.last_read_at;

    if (!lastRead) return true;

    return thread.latest_message &&
        new Date(thread.latest_message.created_at) > new Date(lastRead);
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
</script> 