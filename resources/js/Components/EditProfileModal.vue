<script setup>
import { ref, inject } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const showFlash = inject('showFlash');

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const page = usePage();
const user = page.props.auth.user;

const form = useForm({
    first_name: user.first_name || '',
    last_name: user.last_name || '',
    email: user.email || '',
});

const isSaving = ref(false);

const closeModal = () => {
    form.reset();
    emit('close');
};

const submitForm = async () => {
    isSaving.value = true;
    form.patch(route('profile.update'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            closeModal();
            // Flash message and redirect are handled by the server
        },
        onError: (errors) => {
            // Modal stays open on error so user can correct their input
            // Errors are displayed via InputError components in the form
        },
        onFinish: () => {
            isSaving.value = false;
        },
    });
};
</script>

<template>
    <Modal :show="props.show" @close="closeModal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Edit Profile
            </h2>

            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Update your account information. Your username cannot be changed.
            </p>

            <form @submit.prevent="submitForm" class="mt-6 space-y-6">
                <!-- First Name -->
                <div>
                    <InputLabel for="first_name" value="First Name" />
                    <TextInput
                        id="first_name"
                        v-model="form.first_name"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Your first name"
                    />
                    <InputError :message="form.errors.first_name" class="mt-2" />
                </div>

                <!-- Last Name -->
                <div>
                    <InputLabel for="last_name" value="Last Name" />
                    <TextInput
                        id="last_name"
                        v-model="form.last_name"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Your last name"
                    />
                    <InputError :message="form.errors.last_name" class="mt-2" />
                </div>

                <!-- Username (Disabled) -->
                <div>
                    <InputLabel for="username" value="Username" />
                    <TextInput
                        id="username"
                        :model-value="user.username"
                        type="text"
                        class="mt-1 block w-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 cursor-not-allowed"
                        disabled
                    />
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        Your username cannot be changed.
                    </p>
                </div>

                <!-- Email -->
                <div>
                    <InputLabel for="email" value="Email" />
                    <TextInput
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="mt-1 block w-full"
                        placeholder="your@email.com"
                    />
                    <InputError :message="form.errors.email" class="mt-2" />
                </div>

                <!-- Buttons -->
                <div class="flex justify-end gap-4">
                    <SecondaryButton @click="closeModal" :disabled="isSaving">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton :disabled="isSaving || form.processing">
                        {{ isSaving ? 'Saving...' : 'Save Changes' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
