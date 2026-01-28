<script setup>
import { ref, inject } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
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

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const isSaving = ref(false);
const passwordsMatch = ref(true);

// Password visibility states
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const closeModal = () => {
    form.reset();
    passwordsMatch.value = true;
    showCurrentPassword.value = false;
    showNewPassword.value = false;
    showConfirmPassword.value = false;
    emit('close');
};

const checkPasswordsMatch = () => {
    passwordsMatch.value = form.password === form.password_confirmation;
};

const submitForm = async () => {
    if (!passwordsMatch.value) {
        showFlash?.('Passwords do not match. Please check your confirmation password.', 'error');
        return;
    }

    isSaving.value = true;
    form.put(route('password.update'), {
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
                Change Password
            </h2>

            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Ensure your account is using a strong, unique password to stay secure.
            </p>

            <form @submit.prevent="submitForm" class="mt-6 space-y-6">
                <!-- Current Password -->
                <div>
                    <InputLabel for="current_password" value="Current Password" />
                    <div class="relative">
                        <TextInput
                            id="current_password"
                            v-model="form.current_password"
                            :type="showCurrentPassword ? 'text' : 'password'"
                            class="mt-1 block w-full pr-10"
                            placeholder="Enter your current password"
                            autocomplete="current-password"
                        />
                        <button
                            type="button"
                            @click="showCurrentPassword = !showCurrentPassword"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition"
                            title="Toggle password visibility"
                        >
                            <svg
                                v-if="!showCurrentPassword"
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                />
                            </svg>
                            <svg
                                v-else
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-4.803m5.596-3.856a3.375 3.375 0 11-4.753 4.753m4.753-4.753L3.596 3.596"
                                />
                            </svg>
                        </button>
                    </div>
                    <InputError :message="form.errors.current_password" class="mt-2" />
                </div>

                <!-- New Password -->
                <div>
                    <InputLabel for="password" value="New Password" />
                    <div class="relative">
                        <TextInput
                            id="password"
                            v-model="form.password"
                            @input="checkPasswordsMatch"
                            :type="showNewPassword ? 'text' : 'password'"
                            class="mt-1 block w-full pr-10"
                            placeholder="Enter your new password"
                            autocomplete="new-password"
                        />
                        <button
                            type="button"
                            @click="showNewPassword = !showNewPassword"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition"
                            title="Toggle password visibility"
                        >
                            <svg
                                v-if="!showNewPassword"
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                />
                            </svg>
                            <svg
                                v-else
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-4.803m5.596-3.856a3.375 3.375 0 11-4.753 4.753m4.753-4.753L3.596 3.596"
                                />
                            </svg>
                        </button>
                    </div>
                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <!-- Confirm Password -->
                <div>
                    <InputLabel for="password_confirmation" value="Confirm Password" />
                    <div class="relative">
                        <TextInput
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            @input="checkPasswordsMatch"
                            :type="showConfirmPassword ? 'text' : 'password'"
                            class="mt-1 block w-full pr-10"
                            :class="{ 'border-red-500': !passwordsMatch && form.password_confirmation }"
                            placeholder="Re-enter your new password"
                            autocomplete="new-password"
                        />
                        <button
                            type="button"
                            @click="showConfirmPassword = !showConfirmPassword"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition"
                            title="Toggle password visibility"
                        >
                            <svg
                                v-if="!showConfirmPassword"
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                />
                            </svg>
                            <svg
                                v-else
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-4.803m5.596-3.856a3.375 3.375 0 11-4.753 4.753m4.753-4.753L3.596 3.596"
                                />
                            </svg>
                        </button>
                    </div>
                    <InputError :message="form.errors.password_confirmation" class="mt-2" />
                    <p
                        v-if="!passwordsMatch && form.password_confirmation"
                        class="mt-2 text-sm text-red-600"
                    >
                        Passwords do not match.
                    </p>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end gap-4">
                    <SecondaryButton @click="closeModal" :disabled="isSaving">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton
                        :disabled="isSaving || form.processing || !passwordsMatch || !form.current_password || !form.password"
                    >
                        {{ isSaving ? 'Updating...' : 'Update Password' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
