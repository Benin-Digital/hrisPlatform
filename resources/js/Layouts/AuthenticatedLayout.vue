<template>
    <div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-gray-900">
        <!-- Sidebar -->
        <Sidebar
            :is-open="sidebarOpen"
            :is-collapsed="sidebarCollapsed"
            :is-mobile="isMobile"
            :user="$page.props.auth?.user"
            @close="sidebarOpen = false"
            @toggle-collapse="sidebarCollapsed = !sidebarCollapsed"
        />

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Mobile Header -->
            <MobileHeader
                :user="$page.props.auth?.user"
                @toggle-menu="sidebarOpen = !sidebarOpen"
            />

            <!-- Header toujours visible (desktop) -->
            <header class="hidden lg:block bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="page-container py-4 flex items-center justify-between">
                    <!-- Titre de la page (slot optionnel) -->
                    <div class="flex-1">
                        <slot name="header">
                            <h1 class="text-xl font-bold text-gray-800 dark:text-gray-100">Tableau de bord</h1>
                        </slot>
                    </div>

                    <!-- Actions : notifications + dark mode toggle + profil -->
                    <div class="flex items-center gap-4 flex-shrink-0">
                        <NotificationBell />

                        <!-- Toggle Dark Mode -->
                        <button
                            @click="toggleDarkMode"
                            class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                            :title="isDarkMode ? 'Mode clair' : 'Mode sombre'"
                        >
                            <span class="text-lg">{{ isDarkMode ? '☀️' : '🌙' }}</span>
                        </button>

                        <Link :href="route('profile.edit')" class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                            {{ $page.props.auth.user.name }}
                        </Link>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto custom-scrollbar safe-bottom p-4 lg:p-8 bg-gray-50 dark:bg-gray-900">
                <Breadcrumbs />
                <slot />
            </main>
        </div>

        <!-- Universal Chat Component -->
        <UniversalChat />
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from "vue";
import { usePage, Link } from "@inertiajs/vue3";
import Sidebar from "@/Components/Sidebar.vue";
import MobileHeader from "@/Components/MobileHeader.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import UniversalChat from "@/Components/UniversalChat.vue";
import { useResponsive } from "@/Composables/useResponsive.js";
import Swal from "sweetalert2";
import NotificationBell from "@/Components/NotificationBell.vue";
import axios from "axios";

const page = usePage();
const { isMobile } = useResponsive();

// Dark Mode state
const isDarkMode = ref(false);

// Sidebar state
const sidebarOpen = ref(false);
const sidebarCollapsed = ref(false);

// Initialize sidebar state based on screen size
onMounted(() => {
    if (!isMobile.value) {
        sidebarOpen.value = true;
        const savedCollapsed = localStorage.getItem("sidebar-collapsed");
        if (savedCollapsed !== null) {
            sidebarCollapsed.value = savedCollapsed === "true";
        }
    }

    // Dark Mode init
    const userTheme = page.props.auth?.user?.theme;
    if (userTheme === 'sombre' || userTheme === 'dark') {
        isDarkMode.value = true;
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
});

// Save collapsed state to localStorage
watch(sidebarCollapsed, (newValue) => {
    localStorage.setItem("sidebar-collapsed", newValue.toString());
});

// Close sidebar on mobile when route changes
watch(
    () => page.url,
    () => {
        if (isMobile.value) {
            sidebarOpen.value = false;
        }
    },
);

// Handle escape key to close sidebar on mobile
const handleEscape = (event) => {
    if (event.key === "Escape" && isMobile.value && sidebarOpen.value) {
        sidebarOpen.value = false;
    }
};

// Toggle Dark Mode
const toggleDarkMode = async () => {
    isDarkMode.value = !isDarkMode.value;
    const theme = isDarkMode.value ? 'sombre' : 'clair';

    // Appliquer immédiatement
    document.documentElement.classList.toggle('dark', isDarkMode.value);

    // Sauvegarder en base via PATCH /profile
    try {
        await axios.patch('/profile', { theme });
    } catch (error) {
        console.error('Erreur lors de la sauvegarde du thème', error);
        // Revenir en arrière
        isDarkMode.value = !isDarkMode.value;
        document.documentElement.classList.toggle('dark', isDarkMode.value);
    }
};

onMounted(() => {
    document.addEventListener("keydown", handleEscape);

    // Écouteur de notifications temps réel (toast)
    if (page.props.auth?.user) {
        window.Echo.private(`user.${page.props.auth.user.id}`).notification(
            (notification) => {
                Swal.fire({
                    title: notification.titre || "Notification",
                    text: notification.message,
                    icon: "info",
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener("mouseenter", Swal.stopTimer);
                        toast.addEventListener("mouseleave", Swal.resumeTimer);
                    },
                });
            },
        );
    }
});

onUnmounted(() => {
    document.removeEventListener("keydown", handleEscape);
});

// Flash Messages with SweetAlert
watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) {
            Swal.fire({
                title: "Succès !",
                text: flash.success,
                icon: "success",
                confirmButtonText: "OK",
                confirmButtonColor: "#4F46E5",
                timer: 3000,
                timerProgressBar: true,
            });
        }
        if (flash?.error) {
            Swal.fire({
                title: "Erreur",
                text: flash.error,
                icon: "error",
                confirmButtonText: "OK",
                confirmButtonColor: "#EF4444",
            });
        }
    },
    { deep: true, immediate: true },
);
</script>

<style scoped>
.flex {
    transition: margin-left 0.3s ease;
}
</style>