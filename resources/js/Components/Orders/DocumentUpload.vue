<template>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="font-cocomat text-lg text-wamucii mb-4">Upload Document</h3>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="document_type" value="Document Type" />
                <select
                    id="document_type"
                    class="mt-1 block w-full rounded-md"
                    v-model="form.type"
                    :disabled="form.processing"
                    required
                >
                    <option value="">Select Type</option>
                    <option value="proforma_invoice">Proforma Invoice</option>
                    <option value="purchase_order">Purchase Order</option>
                    <option value="shipping_document">Shipping Document</option>
                    <option value="other">Other Document</option>
                </select>
                <InputError :message="form.errors.type" class="mt-2" />
            </div>

            <div>
                <InputLabel for="document" value="Document File" />
                <input
                    id="document"
                    type="file"
                    class="mt-1 block w-full"
                    @input="form.document = $event.target.files[0]"
                    :disabled="form.processing"
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                    required
                />
                <p class="mt-1 text-sm text-gray-500">
                    Accepted formats: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (max 10MB)
                </p>
                <InputError :message="form.errors.document" class="mt-2" />
            </div>

            <div>
                <InputLabel for="notes" value="Notes (Optional)" />
                <TextArea
                    id="notes"
                    class="mt-1 block w-full"
                    v-model="form.notes"
                    :disabled="form.processing"
                    rows="3"
                />
                <InputError :message="form.errors.notes" class="mt-2" />
            </div>

            <div class="flex justify-end mt-6">
                <PrimaryButton :disabled="form.processing">
                    Upload Document
                </PrimaryButton>
            </div>
        </form>

        <!-- Document List -->
        <div v-if="documents.length > 0" class="mt-8 border-t pt-6">
            <h4 class="font-cocomat text-wamucii mb-3">Uploaded Documents</h4>
            <ul class="space-y-4">
                <li v-for="doc in documents" :key="doc.id" class="flex items-start justify-between">
                    <div>
                        <p class="font-medium text-gray-900">{{ formatDocumentType(doc.type) }}</p>
                        <p class="text-sm text-gray-500">{{ doc.file_name }}</p>
                        <p class="text-xs text-gray-400">
                            Uploaded {{ formatDate(doc.created_at) }} • {{ doc.formatted_file_size }}
                        </p>
                    </div>
                    <div class="flex space-x-2">
                        <button
                            v-if="canSign && !doc.isSignedByUser"
                            @click="signDocument(doc)"
                            class="text-sm text-selina hover:text-wamucii"
                            :disabled="form.processing"
                        >
                            Sign
                        </button>
                        <Link
                            v-if="doc.preview_url"
                            :href="doc.preview_url"
                            class="text-sm text-selina hover:text-wamucii"
                            target="_blank"
                        >
                            Preview
                        </Link>
                        <Link
                            :href="doc.download_url"
                            class="text-sm text-selina hover:text-wamucii"
                        >
                            Download
                        </Link>
                    </div>
                </li>
            </ul>
        </div>
        <div v-else class="mt-8 border-t pt-6 text-center text-gray-500">
            No documents uploaded yet.
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextArea from '@/Components/TextArea.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
    documents: {
        type: Array,
        required: true,
    },
    canSign: {
        type: Boolean,
        default: false,
    },
});

const form = useForm({
    type: '',
    document: null,
    notes: '',
});

const formatDocumentType = (type) => {
    return type.split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const submit = () => {
    form.post(route('orders.documents.store', props.order.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};

const signDocument = (document) => {
    // Open signature modal or navigate to signature page
    router.get(route('documents.sign', document.id));
};
</script> 