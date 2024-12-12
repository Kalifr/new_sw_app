<template>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="font-cocomat text-lg text-wamucii mb-4">Inspection Checklist</h3>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Location and Date -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <InputLabel for="location" value="Inspection Location" />
                    <TextInput
                        id="location"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.location"
                        :disabled="form.processing"
                        required
                    />
                    <InputError :message="form.errors.location" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="inspection_date" value="Inspection Date" />
                    <TextInput
                        id="inspection_date"
                        type="date"
                        class="mt-1 block w-full"
                        v-model="form.inspection_date"
                        :disabled="form.processing"
                        :min="today"
                        required
                    />
                    <InputError :message="form.errors.inspection_date" class="mt-2" />
                </div>
            </div>

            <!-- Checklist Items -->
            <div class="space-y-4">
                <h4 class="font-cocomat text-wamucii">Inspection Points</h4>
                
                <div v-for="(item, index) in form.checklist_results" :key="index" 
                    class="border rounded-lg p-4 space-y-3">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <InputLabel :for="'item_' + index" value="Inspection Item" />
                            <TextInput
                                :id="'item_' + index"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="item.item"
                                :disabled="form.processing"
                                required
                            />
                        </div>
                        <button 
                            type="button"
                            @click="removeChecklistItem(index)"
                            class="ml-2 text-red-600 hover:text-red-800"
                            :disabled="form.processing"
                        >
                            Remove
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel :for="'status_' + index" value="Status" />
                            <select
                                :id="'status_' + index"
                                class="mt-1 block w-full rounded-md"
                                v-model="item.status"
                                :disabled="form.processing"
                                required
                            >
                                <option value="passed">Passed</option>
                                <option value="failed">Failed</option>
                                <option value="skipped">Skipped</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel :for="'notes_' + index" value="Notes" />
                            <TextArea
                                :id="'notes_' + index"
                                class="mt-1 block w-full"
                                v-model="item.notes"
                                :disabled="form.processing"
                                rows="2"
                            />
                        </div>
                    </div>
                </div>

                <button 
                    type="button"
                    @click="addChecklistItem"
                    class="text-selina hover:text-wamucii text-sm"
                    :disabled="form.processing"
                >
                    + Add Inspection Point
                </button>
            </div>

            <!-- Photos -->
            <div>
                <InputLabel value="Inspection Photos" />
                <input
                    type="file"
                    class="mt-1 block w-full"
                    @input="handlePhotos"
                    :disabled="form.processing"
                    accept="image/*"
                    multiple
                />
                <p class="mt-1 text-sm text-gray-500">
                    You can upload multiple photos (JPG, PNG). Max 10MB per photo.
                </p>
                <InputError :message="form.errors.photos" class="mt-2" />

                <!-- Photo Preview -->
                <div v-if="photoPreview.length > 0" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div v-for="(photo, index) in photoPreview" :key="index" 
                        class="relative aspect-square">
                        <img :src="photo" class="w-full h-full object-cover rounded-lg" />
                        <button 
                            type="button"
                            @click="removePhoto(index)"
                            class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center"
                            :disabled="form.processing"
                        >
                            ×
                        </button>
                    </div>
                </div>
            </div>

            <!-- Findings -->
            <div>
                <InputLabel for="findings" value="Overall Findings" />
                <TextArea
                    id="findings"
                    class="mt-1 block w-full"
                    v-model="form.findings"
                    :disabled="form.processing"
                    rows="4"
                    required
                />
                <InputError :message="form.errors.findings" class="mt-2" />
            </div>

            <div class="flex items-center justify-between pt-4 border-t">
                <div class="text-sm">
                    <span class="font-medium">Status: </span>
                    <span :class="{
                        'text-green-600': allPassed,
                        'text-red-600': anyFailed,
                        'text-gray-600': !allPassed && !anyFailed
                    }">
                        {{ inspectionStatus }}
                    </span>
                </div>
                <PrimaryButton :disabled="form.processing">
                    Submit Inspection
                </PrimaryButton>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    location: '',
    inspection_date: new Date().toISOString().split('T')[0],
    checklist_results: [
        { item: '', status: 'passed', notes: '' }
    ],
    findings: '',
    photos: [],
});

const photoPreview = ref([]);
const today = new Date().toISOString().split('T')[0];

const addChecklistItem = () => {
    form.checklist_results.push({ item: '', status: 'passed', notes: '' });
};

const removeChecklistItem = (index) => {
    form.checklist_results.splice(index, 1);
};

const handlePhotos = (e) => {
    const files = Array.from(e.target.files);
    form.photos = files;

    // Generate previews
    photoPreview.value = [];
    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = (e) => {
            photoPreview.value.push(e.target.result);
        };
        reader.readAsDataURL(file);
    });
};

const removePhoto = (index) => {
    const newPhotos = Array.from(form.photos);
    newPhotos.splice(index, 1);
    form.photos = newPhotos;
    photoPreview.value.splice(index, 1);
};

const allPassed = computed(() => {
    return form.checklist_results.every(item => 
        item.status === 'passed' || item.status === 'skipped'
    );
});

const anyFailed = computed(() => {
    return form.checklist_results.some(item => item.status === 'failed');
});

const inspectionStatus = computed(() => {
    if (form.checklist_results.length === 0) return 'No Items';
    if (allPassed.value) return 'All Passed';
    if (anyFailed.value) return 'Issues Found';
    return 'In Progress';
});

const submit = () => {
    form.post(route('orders.inspections.store', props.order.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            photoPreview.value = [];
        },
    });
};
</script> 