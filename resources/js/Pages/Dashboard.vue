<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DashboardWidgets from '@/Components/Widgets/DashboardWidgets.vue';
import {
    HandRaisedIcon,
    BuildingOfficeIcon,
    UsersIcon,
    AcademicCapIcon,
    DocumentTextIcon,
    CalendarDaysIcon,
    UserGroupIcon,
    BookOpenIcon,
} from '@heroicons/vue/24/outline';

const page = usePage();
// Mapping des icônes pour les statistiques
const iconMap = {
    'collaborateurs': UsersIcon,
    'formations': AcademicCapIcon,
    'documents': DocumentTextIcon,
    'evenements': CalendarDaysIcon,
};

// Stats avec des clés pour les icônes
const stats = ref([
    { name: 'Collaborateurs', value: '48', iconKey: 'collaborateurs', color: 'bg-primary-50 text-primary-600' },
    { name: 'Formations', value: '12', iconKey: 'formations', color: 'bg-indigo-50 text-indigo-600' },
    { name: 'Documents', value: '324', iconKey: 'documents', color: 'bg-success-50 text-success-600' },
    { name: 'Événements', value: '8', iconKey: 'evenements', color: 'bg-warning-50 text-warning-600' },
]);

// Rôles
const userRoles = computed(() => {
    const roles = page.props.auth?.user?.roles;
    if (roles && Array.isArray(roles) && roles.length > 0) {
        return roles.map(r => r.nom_affichage || r.name).join(', ');
    }
    return 'Collaborateur';
});

const route = (name, params = {}) => {
    return window.route ? window.route(name, params) : `/${name}`;
};
</script>

<template>
    <Head title="Tableau de bord" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl text-gray-800 leading-tight flex items-center gap-2">
                Bienvenue, {{ $page.props.auth?.user?.prenom }}
                <HandRaisedIcon class="w-6 h-6 text-indigo-500" />
            </h2>
        </template>

        <div class="py-6 md:py-10">
            <div class="page-container">
                <!-- Welcome Banner -->
                <div class="bg-gradient-primary rounded-3xl p-6 md:p-10 mb-8 text-white shadow-xl relative overflow-hidden">
                    <div class="relative z-10">
                        <h1 class="text-2xl md:text-4xl font-extrabold mb-2 md:mb-4">
                            Bonjour, {{ $page.props.auth?.user?.prenom }} {{ $page.props.auth?.user?.nom }} !
                        </h1>
                        <p class="text-white/80 text-sm md:text-lg max-w-2xl mb-6">
                            Vous êtes connecté en tant que <span class="font-bold text-white">{{ userRoles }}</span>. 
                            Voici un aperçu de vos activités pour aujourd'hui.
                        </p>
                        <div class="flex flex-wrap gap-3">
                            <Link :href="route('profile.edit')" class="btn bg-white/10 hover:bg-white/20 border-white/20 text-white backdrop-blur-sm">
                                Mon Profil
                            </Link>
                            <Link :href="route('documents.index')" class="btn bg-white text-primary-700 hover:bg-white/90">
                                Mes Documents
                            </Link>
                        </div>
                    </div>
                    <!-- Decorative element -->
                    <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                    <div class="absolute right-10 top-0 opacity-10 hidden lg:block">
                        <BuildingOfficeIcon class="w-32 h-32 text-white/20" />
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
                    <div 
                        v-for="stat in stats" 
                        :key="stat.name"
                        class="card group hover-lift border-0 p-4 md:p-6"
                    >
                        <div class="flex items-center justify-between mb-4">
                            <div :class="['w-10 h-10 md:w-12 md:h-12 rounded-xl flex items-center justify-center shadow-sm', stat.color]">
                                <component :is="iconMap[stat.iconKey]" class="w-6 h-6" />
                            </div>
                            <span class="text-xs font-bold text-gray-400 group-hover:text-primary-500 transition-colors uppercase tracking-widest">Aperçu</span>
                        </div>
                        <h3 class="text-2xl md:text-3xl font-extrabold text-gray-900 mb-1">{{ stat.value }}</h3>
                        <p class="text-xs md:text-sm text-gray-500 font-medium">{{ stat.name }}</p>
                    </div>
                </div>

                <!-- Widgets Section (Annonces & Événements) -->
                <DashboardWidgets 
                    :annonces="$page.props.annonces || []" 
                    :events="$page.props.events || []" 
                />

                <!-- Quick Access Gradient Cards -->
                <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Link :href="route('collaborateurs.index')" class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white p-6 md:p-8 rounded-2xl shadow-lg hover:shadow-indigo-200/50 hover:-translate-y-1 transition-all duration-300">
                        <div class="text-3xl mb-4">
                            <UsersIcon class="w-8 h-8" />
                        </div>
                        <h3 class="text-xl font-bold">Gestion RH</h3>
                        <p class="mt-2 text-white/80 text-sm">Consulter l'annuaire et la liste des collaborateurs</p>
                    </Link>
                    <Link :href="route('formations.index')" class="bg-gradient-to-r from-green-500 to-teal-600 text-white p-6 md:p-8 rounded-2xl shadow-lg hover:shadow-green-200/50 hover:-translate-y-1 transition-all duration-300">
                        <div class="text-3xl mb-4">
                            <AcademicCapIcon class="w-8 h-8" />
                        </div>
                        <h3 class="text-xl font-bold">Formations</h3>
                        <p class="mt-2 text-white/80 text-sm">Accéder au catalogue et suivre vos progrès</p>
                    </Link>
                    <Link :href="route('documents.index')" class="bg-gradient-to-r from-orange-500 to-red-600 text-white p-6 md:p-8 rounded-2xl shadow-lg hover:shadow-orange-200/50 hover:-translate-y-1 transition-all duration-300">
                        <div class="text-3xl mb-4">
                            <DocumentTextIcon class="w-8 h-8" />
                        </div>
                        <h3 class="text-xl font-bold">Documents</h3>
                        <p class="mt-2 text-white/80 text-sm">Gérer vos documents et dossiers professionnels</p>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>