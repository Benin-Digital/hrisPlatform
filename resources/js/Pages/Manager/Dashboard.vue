<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import DashboardWidgets from '@/Components/Widgets/DashboardWidgets.vue';
import {
    UserIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    ClipboardDocumentListIcon,
    PlusIcon,
    UsersIcon,
    ChartBarIcon,
    ClipboardIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    stats: {
        type: Object,
        required: true,
        default: () => ({}),
    },
    recentAnnonces: {
        type: Array,
        default: () => [],
    },
    upcomingEvents: {
        type: Array,
        default: () => [],
    },
    tachesEntite: {
        type: Object,
        default: () => ({}),
    },
});

const user = window.Laravel?.user || {};
</script>

<template>
    <Head title="Dashboard Manager" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                Dashboard Manager
            </h2>
        </template>

        <div class="py-6">
            <div class="page-container">
                <!-- Message de bienvenue -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8 flex items-center">
                    <UserIcon class="w-10 h-10 text-indigo-600 dark:text-indigo-400 mr-4 flex-shrink-0" />
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-indigo-700 dark:text-indigo-400 mb-2">
                            Bienvenue, {{ user?.prenom || 'Manager' }} !
                        </h1>
                        <p class="text-gray-600 dark:text-gray-300">Supervisez les tâches et la productivité de votre équipe</p>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Tâches en cours -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                                <ClipboardIcon class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Actives</span>
                        </div>
                        <h3 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-gray-100 mb-1">
                            {{ stats.tachesEnCours || 0 }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Tâches en cours</p>
                    </div>

                    <!-- Tâches terminées -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-green-50 dark:bg-green-900/30 flex items-center justify-center">
                                <CheckCircleIcon class="w-6 h-6 text-green-600 dark:text-green-400" />
                            </div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Terminées</span>
                        </div>
                        <h3 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-gray-100 mb-1">
                            {{ stats.tachesTerminees || 0 }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Tâches terminées</p>
                    </div>

                    <!-- Collaborateurs -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center">
                                <UsersIcon class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                            </div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Équipe</span>
                        </div>
                        <h3 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-gray-100 mb-1">
                            {{ stats.collaborateurs || 0 }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Collaborateurs</p>
                    </div>

                    <!-- Tâches en retard -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-red-50 dark:bg-red-900/30 flex items-center justify-center">
                                <ExclamationTriangleIcon class="w-6 h-6 text-red-600 dark:text-red-400" />
                            </div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Retard</span>
                        </div>
                        <h3 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-gray-100 mb-1">
                            {{ stats.tachesEnRetard || 0 }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Tâches en retard</p>
                    </div>
                </div>

                <!-- Actions rapides Manager -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center">
                        <ClipboardDocumentListIcon class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-400" />
                        Actions rapides
                    </h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                        <Link href="/taches" class="bg-blue-50 dark:bg-blue-900/30 p-4 rounded-xl text-center hover:bg-blue-100 dark:hover:bg-blue-900/50 transition text-blue-700 dark:text-blue-300 flex flex-col items-center">
                            <ClipboardIcon class="w-8 h-8 mb-2" />
                            Voir les tâches
                        </Link>
                        <Link href="/taches/create" class="bg-green-50 dark:bg-green-900/30 p-4 rounded-xl text-center hover:bg-green-100 dark:hover:bg-green-900/50 transition text-green-700 dark:text-green-300 flex flex-col items-center">
                            <PlusIcon class="w-8 h-8 mb-2" />
                            Nouvelle tâche
                        </Link>
                        <Link href="/collaborateurs" class="bg-purple-50 dark:bg-purple-900/30 p-4 rounded-xl text-center hover:bg-purple-100 dark:hover:bg-purple-900/50 transition text-purple-700 dark:text-purple-300 flex flex-col items-center">
                            <UsersIcon class="w-8 h-8 mb-2" />
                            Mon équipe
                        </Link>
                        <Link href="/productivite" class="bg-yellow-50 dark:bg-yellow-900/30 p-4 rounded-xl text-center hover:bg-yellow-100 dark:hover:bg-yellow-900/50 transition text-yellow-700 dark:text-yellow-300 flex flex-col items-center">
                            <ChartBarIcon class="w-8 h-8 mb-2" />
                            Productivité
                        </Link>
                    </div>
                </div>

                <!-- Widgets d'actualités et événements -->
                <DashboardWidgets 
                    :annonces="recentAnnonces" 
                    :events="upcomingEvents" 
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>