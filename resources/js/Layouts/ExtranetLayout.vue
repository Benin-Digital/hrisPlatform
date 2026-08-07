<template>
    <div class="flex h-screen overflow-hidden bg-gray-50">
        <!-- Simplified Sidebar for Extranet -->
        <aside
            :class="[
                'sidebar fixed top-0 left-0 h-full bg-white border-r border-gray-200 transition-all duration-300 z-40',
                'flex flex-col shadow-xl',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
                'w-72'
            ]"
        >
            <!-- Sidebar Header -->
            <Link :href="route('extranet.dashboard')" class="sidebar-header flex items-center justify-between h-16 px-4 border-b border-gray-200 bg-gradient-to-r from-indigo-600 to-purple-600 transition-opacity hover:opacity-90">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg bg-white/20 backdrop-blur flex items-center justify-center">
                        <span class="text-2xl">🌐</span>
                    </div>
                    <div class="text-white">
                        <h1 class="font-heading font-bold text-lg leading-tight">Extranet</h1>
                        <p class="text-xs text-white/80">Espace Partenaires</p>
                    </div>
                </div>
            </Link>

            <!-- User Info -->
            <div class="user-info border-b border-gray-200 bg-gray-50 p-4">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 rounded-full bg-purple-500 flex items-center justify-center text-white font-bold text-lg border-2 border-purple-600">
                        {{ $page.props.auth.user.prenom?.charAt(0) }}{{ $page.props.auth.user.nom?.charAt(0) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">
                            {{ $page.props.auth.user.prenom }} {{ $page.props.auth.user.nom }}
                        </p>
                        <p class="text-xs text-gray-500 truncate">
                            {{ $page.props.auth.user.mainRole?.nom_affichage || 'Invité' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto custom-scrollbar py-4">
                <ul class="space-y-1 px-2">
                    <li>
                        <Link
                            href="/extranet"
                            :class="[
                                'nav-item flex items-center px-4 py-3 space-x-3 rounded-lg transition-all duration-200',
                                $page.url === '/extranet' || $page.url === '/extranet/dashboard'
                                    ? 'bg-purple-50 text-purple-700 font-semibold shadow-sm'
                                    : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
                            ]"
                        >
                            <span class="text-xl">📊</span>
                            <span class="text-sm font-medium">Tableau de bord</span>
                        </Link>
                    </li>
                    <li>
                        <Link
                            :href="route('formations.index')"
                            :class="[
                                'nav-item flex items-center px-4 py-3 space-x-3 rounded-lg transition-all duration-200',
                                $page.url.startsWith('/formations')
                                    ? 'bg-purple-50 text-purple-700 font-semibold shadow-sm'
                                    : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
                            ]"
                        >
                            <span class="text-xl">📚</span>
                            <span class="text-sm font-medium">Formations</span>
                        </Link>
                    </li>
                    <li>
                        <Link
                            :href="route('gallery.index')"
                            :class="[
                                'nav-item flex items-center px-4 py-3 space-x-3 rounded-lg transition-all duration-200',
                                $page.url.startsWith('/gallery')
                                    ? 'bg-purple-50 text-purple-700 font-semibold shadow-sm'
                                    : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
                            ]"
                        >
                            <span class="text-xl">🖼️</span>
                            <span class="text-sm font-medium">Galerie</span>
                        </Link>
                    </li>
                </ul>
            </nav>

            <!-- Footer Actions -->
            <div class="border-t border-gray-200 p-2">
                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    class="w-full flex items-center px-4 py-3 space-x-3 rounded-lg transition-colors text-red-600 hover:bg-red-50"
                >
                    <span class="text-xl">🚪</span>
                    <span class="text-sm font-medium">Déconnexion</span>
                </Link>
            </div>
        </aside>

        <!-- Backdrop for mobile -->
        <Transition name="fade">
            <div
                v-if="sidebarOpen && isMobile"
                class="fixed inset-0 bg-black/50 z-30 lg:hidden"
                @click="sidebarOpen = false"
            ></div>
        </Transition>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Mobile Header -->
            <div class="mobile-header bg-white border-b border-gray-200 lg:hidden safe-top">
                <div class="flex items-center justify-between h-16 px-4">
                    <!-- Menu Button -->
                    <button
                        @click="sidebarOpen = !sidebarOpen"
                        class="p-2 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Logo -->
                    <Link :href="route('extranet.dashboard')" class="flex items-center active:scale-95 transition-transform">
                        <span class="text-xl font-bold font-heading text-purple-600">Extranet</span>
                    </Link>

                    <!-- User Avatar -->
                    <div class="w-8 h-8 rounded-full bg-purple-500 flex items-center justify-center text-white font-semibold text-sm">
                        {{ $page.props.auth.user.prenom?.charAt(0) }}{{ $page.props.auth.user.nom?.charAt(0) }}
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto custom-scrollbar safe-bottom p-4 lg:p-8">
                <Breadcrumbs />
                <div class="page-container py-4">
                    <slot />
                </div>
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 mt-auto">
                <div class="page-container py-6 text-center text-sm text-gray-500">
                    © {{ new Date().getFullYear() }} HRIS Pro - Espace Extranet
                </div>
            </footer>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import { useResponsive } from '@/Composables/useResponsive.js';

const page = usePage();
const { isMobile } = useResponsive();

const sidebarOpen = ref(false);

// Helper function for route
const route = (name, params = {}) => {
    return window.route ? window.route(name, params) : `/${name}`;
};

// Initialize sidebar state
onMounted(() => {
    if (!isMobile.value) {
        sidebarOpen.value = true;
    }
});

// Close sidebar on mobile when route changes
watch(() => page.url, () => {
    if (isMobile.value) {
        sidebarOpen.value = false;
    }
});
</script>

<style scoped>
.nav-item {
    position: relative;
}

.nav-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 0;
    background: var(--color-secondary-600);
    border-radius: 0 3px 3px 0;
    transition: height 0.2s ease;
}

.nav-item.bg-purple-50::before {
    height: 70%;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
