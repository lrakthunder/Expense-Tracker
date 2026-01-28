<script setup>
import '../../../css/expense.css';
import { ref } from 'vue';
import { Head, useForm, router, Link, usePage } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import SiteLogo from '@/Components/SiteLogo.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import UserDropdown from '@/Components/UserDropdown.vue';
import FlashToast from '@/Components/FlashToast.vue';

const page = usePage();

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

const isActive = (path) => {
    return page.url === path;
};

const checkPasswordsMatch = () => {
    passwordsMatch.value = form.password === form.password_confirmation;
};

const submitForm = () => {
    if (!passwordsMatch.value) {
        return;
    }

    isSaving.value = true;
    form.put(route('password.update'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            form.reset();
            router.visit(route('dashboard'));
        },
        onFinish: () => {
            isSaving.value = false;
        },
    });
};
</script>

<template>
    <Head title="Change Password" />

    <div class="expense-bg">
        <header class="expense-header">
            <SiteLogo />
            <div style="display: flex; align-items: center; gap: 12px;">
                <span class="client-name">{{ page.props.fullname }}</span>
                <ThemeToggle />
                <UserDropdown />
            </div>
        </header>
        <div class="expense-container">
            <aside class="sidebar">
                <nav class="sidebar-nav">
                    <Link href="/dashboard" class="sidebar-link" :class="{ active: isActive('/dashboard') }">
                        <span class="sidebar-icon">📊</span>
                        <span class="sidebar-label">Dashboard</span>
                    </Link>
                    <Link href="/expense" class="sidebar-link" :class="{ active: isActive('/expense') }">
                        <span class="sidebar-icon">💸</span>
                        <span class="sidebar-label">Expenses</span>
                    </Link>
                    <Link href="/income" class="sidebar-link" :class="{ active: isActive('/income') }">
                        <span class="sidebar-icon">💰</span>
                        <span class="sidebar-label">Income</span>
                    </Link>
                    <Link href="/report" class="sidebar-link" :class="{ active: isActive('/report') }">
                        <span class="sidebar-icon">📈</span>
                        <span class="sidebar-label">Reports</span>
                    </Link>
                    <Link href="/calendar" class="sidebar-link" :class="{ active: isActive('/calendar') }">
                        <span class="sidebar-icon">📅</span>
                        <span class="sidebar-label">Calendar</span>
                    </Link>
                    <Link href="/settings" class="sidebar-link" :class="{ active: isActive('/settings') }">
                        <span class="sidebar-icon">⚙️</span>
                        <span class="sidebar-label">Settings</span>
                    </Link>
                </nav>
            </aside>
            <main class="expense-main">
                <FlashToast />
                <div class="p-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Change Password
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-8">
                        Ensure your account is using a strong, unique password to stay secure.
                    </p>

                    <form @submit.prevent="submitForm" class="max-w-2xl space-y-6">
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
                                        required
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
                                        required
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
                                        required
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
                                <p v-if="!passwordsMatch && form.password_confirmation" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                    Passwords do not match.
                                </p>
                                <InputError :message="form.errors.password_confirmation" class="mt-2" />
                            </div>

                            <!-- Buttons -->
                            <div class="flex justify-end gap-4 pt-4">
                                <button
                                    type="button"
                                    @click="router.visit(route('dashboard'))"
                                    class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150"
                                    :disabled="isSaving"
                                >
                                    Cancel
                                </button>
                                <PrimaryButton :disabled="isSaving || form.processing || !passwordsMatch">
                                    {{ isSaving ? 'Updating...' : 'Update Password' }}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </main>
    </div>
</div>
</template>
