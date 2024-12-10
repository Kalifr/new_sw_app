<template>
    <Head title="Complete Profile" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-cocomat text-xl text-wamucii leading-tight tracking-brand">
                Complete Your Profile
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <section class="max-w-xl">
                        <header>
                            <h2 class="text-lg font-cocomat text-wamucii">
                                Profile Information
                            </h2>

                            <p class="mt-1 text-sm text-gray-600">
                                Please give us more information about yourself and your organisation.
                            </p>
                        </header>

                        <form @submit.prevent="form.post(route('profile.complete.store'))" class="mt-6 space-y-6">
                            <div>
                                <InputLabel for="organization_name" value="Organisation name" />
                                <TextInput
                                    id="organization_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.organization_name"
                                    required
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.organization_name" />
                            </div>

                            <div>
                                <InputLabel for="organization_type" value="Organisation type" />
                                <select
                                    id="organization_type"
                                    class="mt-1 block w-full border-gray-300 focus:border-selina focus:ring-selina rounded-md shadow-sm"
                                    v-model="form.organization_type"
                                    required
                                >
                                    <option value="">Select type</option>
                                    <option value="Producer">Producer</option>
                                    <option value="Cooperative">Cooperative</option>
                                    <option value="Processor">Processor</option>
                                    <option value="Farmer">Farmer</option>
                                    <option value="Other">Other</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.organization_type" />
                            </div>

                            <div>
                                <InputLabel for="country" value="Country" />
                                <select
                                    id="country"
                                    class="mt-1 block w-full border-gray-300 focus:border-selina focus:ring-selina rounded-md shadow-sm"
                                    v-model="form.country"
                                    required
                                >
                                    <option value="">Select country</option>
                                    <option value="Kenya">Kenya</option>
                                    <option value="Uganda">Uganda</option>
                                    <option value="Tanzania">Tanzania</option>
                                    <option value="Rwanda">Rwanda</option>
                                    <option value="Ethiopia">Ethiopia</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.country" />
                            </div>

                            <div>
                                <InputLabel for="phone" value="Phone" />
                                <TextInput
                                    id="phone"
                                    type="tel"
                                    class="mt-1 block w-full"
                                    v-model="form.phone"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.phone" />
                            </div>

                            <div>
                                <InputLabel for="looking_for" value="What are you looking for?" />
                                <select
                                    id="looking_for"
                                    class="mt-1 block w-full border-gray-300 focus:border-selina focus:ring-selina rounded-md shadow-sm"
                                    v-model="form.looking_for"
                                >
                                    <option value="">Select what you're looking for</option>
                                    <option value="Buying">Buying</option>
                                    <option value="Selling">Selling</option>
                                    <option value="Both">Both</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.looking_for" />
                            </div>

                            <div>
                                <InputLabel value="Choose categories" />
                                <div class="mt-2 space-y-2">
                                    <div v-for="category in categories" :key="category" class="flex items-start">
                                        <div class="flex h-6 items-center">
                                            <input
                                                :id="category"
                                                name="categories"
                                                type="checkbox"
                                                :value="category"
                                                v-model="form.categories"
                                                class="h-4 w-4 rounded border-gray-300 text-selina focus:ring-selina"
                                            />
                                        </div>
                                        <div class="ml-3 text-sm leading-6">
                                            <label :for="category" class="font-medium text-gray-900">{{ category }}</label>
                                        </div>
                                    </div>
                                </div>
                                <InputError class="mt-2" :message="form.errors.categories" />
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing" class="bg-selina hover:bg-wamucii">
                                    Save
                                </PrimaryButton>

                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
                                </Transition>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    categories: {
        type: Array,
        required: true
    }
});

const form = useForm({
    organization_name: '',
    organization_type: '',
    country: '',
    phone: '',
    looking_for: '',
    categories: []
});
</script> 