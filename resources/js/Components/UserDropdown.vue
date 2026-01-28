<script setup>
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const isDropdownOpen = ref(false);

const openEditProfile = () => {
    router.visit(route('profile.edit-profile'));
    isDropdownOpen.value = false;
};

const openChangePassword = () => {
    router.visit(route('profile.change-password'));
    isDropdownOpen.value = false;
};

const closeDropdown = () => {
    isDropdownOpen.value = false;
};
</script>

<template>
    <div class="relative inline-block">
        <!-- User Name Display with Dropdown Trigger -->
        <button
            @click="isDropdownOpen = !isDropdownOpen"
            class="inline-flex items-center gap-2 rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
        >
            <span class="text-sm text-gray-700">
                {{ user?.first_name || user?.name || 'User' }}
            </span>

            <!-- Dropdown Arrow Icon -->
            <svg
                :class="{ 'rotate-180': isDropdownOpen }"
                class="h-4 w-4 transition-transform duration-200"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
            >
                <path
                    fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd"
                />
            </svg>
        </button>

        <!-- Dropdown Menu -->
        <transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-if="isDropdownOpen"
                class="absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5"
            >
                <div class="px-4 py-3 border-b border-gray-100">
                    <div class="text-sm font-semibold text-gray-900">
                        {{ ((user?.first_name || '') + ' ' + (user?.last_name || '')).trim() || user?.name }}
                    </div>
                </div>

                <div class="py-1">
                    <button
                        @click="openEditProfile"
                        class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 transition"
                    >
                        Edit Profile
                    </button>

                    <button
                        @click="openChangePassword"
                        class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 transition"
                    >
                        Change Password
                    </button>

                    <a
                        href="#"
                        @click.prevent="$inertia.post(route('logout'))"
                        class="block px-4 py-2 text-left text-sm text-red-600 hover:bg-gray-100 transition"
                    >
                        Logout
                    </a>
                </div>
            </div>
        </transition>

        <!-- Close dropdown when clicking outside -->
        <div
            v-if="isDropdownOpen"
            @click="closeDropdown"
            class="fixed inset-0 z-40"
        ></div>
    </div>
</template>

<style scoped>
/* Ensure button doesn't get cut off */
.relative {
    position: relative;
}
</style>
