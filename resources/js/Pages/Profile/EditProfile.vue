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

const props = defineProps({
    user: Object,
});

const page = usePage();

const form = useForm({
    first_name: props.user.first_name,
    last_name: props.user.last_name,
    email: props.user.email,
});

const isSaving = ref(false);

const isActive = (path) => {
    return page.url === path;
};

const submitForm = () => {
    isSaving.value = true;
    form.patch(route('profile.update'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            router.visit(route('dashboard'));
        },
        onFinish: () => {
            isSaving.value = false;
        },
    });
};
</script>

<template>
    <Head title="Edit Profile" />

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
                        Edit Profile
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-8">
                        Update your account information. Your username cannot be changed.
                    </p>

                    <form @submit.prevent="submitForm" class="max-w-2xl space-y-6">
                        <!-- First Name -->
                        <div>
                            <InputLabel for="first_name" value="First Name" />
                            <TextInput
                                id="first_name"
                                v-model="form.first_name"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="Your first name"
                                required
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
                                required
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
                                required
                            />
                            <InputError :message="form.errors.email" class="mt-2" />
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
                            <PrimaryButton :disabled="isSaving || form.processing">
                                {{ isSaving ? 'Saving...' : 'Save Changes' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</template>
