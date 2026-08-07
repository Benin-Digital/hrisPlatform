<template>
    <!-- Sidebar Container -->
    <aside :class="[
        'sidebar bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transition-all duration-300 z-40',
        'flex flex-col shadow-xl h-full',
        isMobile ? 'fixed top-0 left-0' : 'relative',
        isMobile && !isOpen ? '-translate-x-full' : 'translate-x-0',
        isCollapsed && !isMobile ? 'w-20' : 'w-72'
    ]">
        <!-- Sidebar Header -->
        <div
            class="sidebar-header flex items-center justify-between h-16 px-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-primary dark:bg-gradient-primary-dark">
            <Link :href="route('dashboard')" class="flex items-center overflow-hidden group">
                <div v-if="!isCollapsed || isMobile" class="flex items-center space-x-3 transition-all duration-300">
                    <div
                        class="w-10 h-10 rounded-lg bg-white/20 dark:bg-white/10 backdrop-blur flex items-center justify-center group-hover:bg-white/30 dark:group-hover:bg-white/20 transition-all">
                        <BuildingOfficeIcon class="w-6 h-6 text-white" />
                    </div>
                    <div class="text-white">
                        <h1 class="font-heading font-bold text-lg leading-tight">HRIS Pro</h1>
                        <p class="text-xs text-white/80">Plateforme RH</p>
                    </div>
                </div>
                <div v-else class="flex items-center justify-center w-full">
                    <BuildingOfficeIcon class="w-8 h-8 text-white group-hover:scale-110 transition-transform duration-300" />
                </div>
            </Link>

            <!-- Toggle Button -->
            <button v-if="!isMobile" @click="toggleCollapse"
                class="p-1.5 rounded-lg text-white/80 hover:bg-white/20 dark:hover:bg-white/10 transition-colors hidden lg:block ml-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        :d="isCollapsed ? 'M13 5l7 7-7 7M5 5l7 7-7 7' : 'M11 19l-7-7 7-7m8 14l-7-7 7-7'" />
                </svg>
            </button>
        </div>

        <!-- User Info -->
        <div v-if="user" :class="[
            'user-info border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 transition-all',
            isCollapsed && !isMobile ? 'p-2' : 'p-4'
        ]">
            <div :class="['flex items-center', isCollapsed && !isMobile ? 'justify-center' : 'space-x-3']">
                <img v-if="user.photo_profil" :src="`/storage/${user.photo_profil}`"
                    :class="['rounded-full object-cover border-2 border-primary-500', isCollapsed && !isMobile ? 'w-10 h-10' : 'w-12 h-12']"
                    :alt="`${user.prenom} ${user.nom}`" />
                <div v-else :class="[
                    'rounded-full bg-primary-500 dark:bg-primary-600 flex items-center justify-center text-white font-bold border-2 border-primary-600 dark:border-primary-500',
                    isCollapsed && !isMobile ? 'w-10 h-10 text-sm' : 'w-12 h-12 text-lg'
                ]">
                    {{ user.prenom?.charAt(0) }}{{ user.nom?.charAt(0) }}
                </div>

                <div v-if="!isCollapsed || isMobile" class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                        {{ user.prenom }} {{ user.nom }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                        {{ user.mainRole?.nom_affichage || 'Collaborateur' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto custom-scrollbar py-4">
            <ul class="space-y-1 px-2">
                <li v-for="item in navigationItems" :key="item.name">
                    <Link v-if="hasPermission(item.roles)" :href="item.href" :class="[
                        'nav-item flex items-center rounded-lg transition-all duration-200',
                        isCollapsed && !isMobile ? 'justify-center p-3' : 'px-4 py-3 space-x-3',
                        isActive(item.href)
                            ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 font-semibold shadow-sm'
                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100'
                    ]" :title="isCollapsed && !isMobile ? item.name : ''">
                        <component :is="iconComponents[item.icon]" class="w-6 h-6 flex-shrink-0" />
                        <span v-if="!isCollapsed || isMobile" class="text-sm font-medium">{{ item.name }}</span>
                        <span v-if="item.badge && (!isCollapsed || isMobile)"
                            class="ml-auto px-2 py-0.5 text-xs font-semibold rounded-full bg-red-500 text-white">
                            {{ item.badge }}
                        </span>
                    </Link>
                </li>
            </ul>
        </nav>

        <!-- Footer Actions -->
        <div class="border-t border-gray-200 dark:border-gray-700 p-2">
            <Link :href="route('profile.edit')" :class="[
                'flex items-center rounded-lg transition-colors',
                isCollapsed && !isMobile ? 'justify-center p-3' : 'px-4 py-3 space-x-3',
                'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
            ]" :title="isCollapsed && !isMobile ? 'Paramètres' : ''">
                <CogIcon class="w-6 h-6 flex-shrink-0" />
                <span v-if="!isCollapsed || isMobile" class="text-sm font-medium">Paramètres</span>
            </Link>

            <Link :href="route('logout')" method="post" as="button" :class="[
                'w-full flex items-center rounded-lg transition-colors',
                isCollapsed && !isMobile ? 'justify-center p-3' : 'px-4 py-3 space-x-3',
                'text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30'
            ]" :title="isCollapsed && !isMobile ? 'Déconnexion' : ''">
                <ArrowRightOnRectangleIcon class="w-6 h-6 flex-shrink-0" />
                <span v-if="!isCollapsed || isMobile" class="text-sm font-medium">Déconnexion</span>
            </Link>
        </div>
    </aside>

    <!-- Backdrop for mobile -->
    <Transition name="fade">
        <div v-if="isOpen && isMobile" class="fixed inset-0 bg-black/50 z-30 lg:hidden" @click="$emit('close')"></div>
    </Transition>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    ChartBarIcon,
    UsersIcon,
    BriefcaseIcon,
    RocketLaunchIcon,
    ClipboardDocumentListIcon,
    ChartPieIcon,
    DocumentTextIcon,
    AcademicCapIcon,
    CalendarDaysIcon,
    NewspaperIcon,
    SunIcon,
    HandRaisedIcon,
    FolderIcon,
    ArrowTrendingUpIcon,
    ClockIcon,
    PhotoIcon,
    EnvelopeIcon,
    CogIcon,
    ArrowRightOnRectangleIcon,
    BuildingOfficeIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false,
    },
    isCollapsed: {
        type: Boolean,
        default: false,
    },
    isMobile: {
        type: Boolean,
        default: false,
    },
    user: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['close', 'toggle-collapse']);

const page = usePage();

// Helper function for route
const route = (name, params = {}) => {
    return window.route ? window.route(name, params) : `/${name}`;
};

// Mapping des noms d'icônes vers les composants Heroicons
const iconComponents = {
    'chart-bar': ChartBarIcon,
    'users': UsersIcon,
    'briefcase': BriefcaseIcon,
    'target': RocketLaunchIcon,
    'clipboard': ClipboardDocumentListIcon,
    'chart-pie': ChartPieIcon,
    'document-text': DocumentTextIcon,
    'academic-cap': AcademicCapIcon,
    'calendar-days': CalendarDaysIcon,
    'newspaper': NewspaperIcon,
    'sun': SunIcon,
    'handshake': HandRaisedIcon,
    'folder': FolderIcon,
    'trending-up': ArrowTrendingUpIcon,
    'clock': ClockIcon,
    'photo': PhotoIcon,
    'envelope': EnvelopeIcon,
};

// Navigation items configuration (icônes remplacées par des clés)
const navigationItems = computed(() => [
    {
        name: 'Tableau de bord',
        href: route('dashboard'),
        icon: 'chart-bar',
        roles: ['all'],
    },
    {
        name: 'Collaborateurs',
        href: route('collaborateurs.index'),
        icon: 'users',
        roles: ['super_admin', 'admin_entite', 'responsable_rh'],
    },
    {
        name: 'Offres',
        href: route('super-admin.offres.index'),
        icon: 'briefcase',
        roles: ['super_admin', 'responsable_rh', 'admin_entite'],
    },
    {
        name: 'Recrutement',
        href: route('recrutement.pipeline'),
        icon: 'target',
        roles: ['super_admin', 'responsable_rh', 'manager'],
    },
    //{
        //name: 'Tâches',
       // href: route('taches.index'),
       // icon: 'clipboard',
       // roles: ['all'],
    //},
    {
        name: 'Analyses RH',
        href: route('rh.analyses'),
        icon: 'chart-pie',
        roles: ['responsable_rh', 'admin_entite', 'super_admin'],
    },
    {
        name: 'Documents',
        href: route('documents.index'),
        icon: 'document-text',
        roles: ['all'],
    },
    {
        name: 'Formations',
        href: route('formations.index'),
        icon: 'academic-cap',
        roles: ['all'],
    },
    {
        name: 'Agenda',
        href: route('agenda.index'),
        icon: 'calendar-days',
        roles: ['all'],
    },
    {
        name: 'Actualités',
        href: route('actualites.index'),
        icon: 'newspaper',
        roles: ['all'],
    },
    {
        name: 'Congés',
        href: route('conges.index'),
        icon: 'sun',
        roles: ['all'],
    },
    {
        name: 'Collaboration',
        href: route('collaboration.index'),
        icon: 'handshake',
        roles: ['all'],
    },
    {
        name: 'Rubriques',
        href: route('rubriques.index'),
        icon: 'folder',
        roles: ['super_admin', 'admin_entite', 'responsable_rh', 'manager'],
    },
    {
        name: 'Productivité',
        href: route('productivite.index'),
        icon: 'trending-up',
        roles: ['all'],
    },
    {
        name: 'Pointage',
        href: route('pointages.index'),
        icon: 'clock',
        roles: ['all'],
    },
    {
        name: 'Galerie',
        href: route('super-admin.gallery.index'),
        icon: 'photo',
        roles: ['super_admin'],
    },
    {
        name: 'Newsletter',
        href: route('super-admin.newsletters.index'),
        icon: 'envelope',
        roles: ['super_admin'],
    },
]);

// Check if user has permission to see a navigation item
const hasPermission = (allowedRoles) => {
    if (allowedRoles.includes('all')) return true;

    const userRoles = props.user?.roles || [];
    const mainRole = props.user?.mainRole?.nom;

    if (mainRole && allowedRoles.includes(mainRole)) return true;
    return userRoles.some(role => allowedRoles.includes(role.nom));
};

// Check if current route is active
const isActive = (href) => {
    try {
        const currentUrl = page.url;
        const targetPath = href.startsWith('http')
            ? new URL(href).pathname
            : href;

        if (targetPath === '/dashboard' || targetPath === '/') {
            return currentUrl === '/dashboard' || currentUrl === '/' || page.component === 'Dashboard';
        }
        return currentUrl.startsWith(targetPath);
    } catch (e) {
        return false;
    }
};

// Toggle collapse
const toggleCollapse = () => {
    emit('toggle-collapse');
};
</script>

<style scoped>
.sidebar {
    will-change: transform;
}

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
    background: var(--color-primary-600);
    border-radius: 0 3px 3px 0;
    transition: height 0.2s ease;
}

.nav-item.bg-primary-50::before {
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