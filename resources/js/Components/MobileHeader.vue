<template>
    <div class="mobile-header bg-white border-b border-gray-200 lg:hidden safe-top">
        <div class="flex items-center justify-between h-16 px-4">
            <!-- Menu Button -->
            <button
                @click="$emit('toggle-menu')"
                class="p-2 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
                aria-label="Toggle menu"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <!-- Logo -->
            <Link :href="route('dashboard')" class="flex items-center active:scale-95 transition-transform">
                <span class="text-xl font-bold font-heading text-primary-600">HRIS Pro</span>
            </Link>

            <!-- User Avatar -->
            <div class="relative">
                <button
                    @click="showUserMenu = !showUserMenu"
                    class="flex items-center focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-full"
                >
                    <img
                        v-if="user?.photo_profil"
                        :src="`/storage/${user.photo_profil}`"
                        class="h-8 w-8 rounded-full object-cover border-2 border-primary-500"
                        :alt="`${user.prenom} ${user.nom}`"
                    />
                    <div
                        v-else
                        class="h-8 w-8 rounded-full bg-primary-500 flex items-center justify-center text-white font-semibold text-sm"
                    >
                        {{ user?.prenom?.charAt(0) }}{{ user?.nom?.charAt(0) }}
                    </div>
                </button>

                <!-- User Dropdown -->
                <Transition name="fade">
                    <div
                        v-if="showUserMenu"
                        class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 py-1 z-50"
                        @click.stop
                    >
                        <Link
                            :href="route('profile.edit')"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                            @click="showUserMenu = false"
                        >
                            Mon profil
                        </Link>
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors"
                            @click="showUserMenu = false"
                        >
                            Déconnexion
                        </Link>
                    </div>
                </Transition>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    user: Object,
});

defineEmits(['toggle-menu']);

const showUserMenu = ref(false);

// Helper function for route
const route = (name, params = {}) => {
    return window.route ? window.route(name, params) : `/${name}`;
};

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
    if (showUserMenu.value && !event.target.closest('.relative')) {
        showUserMenu.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
