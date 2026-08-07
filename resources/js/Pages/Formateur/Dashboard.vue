<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import DashboardWidgets from '@/Components/Widgets/DashboardWidgets.vue';
import {
    AcademicCapIcon,
    UsersIcon,
    StarIcon,
    ChartBarIcon,
    LightningBoltIcon,
    PlusIcon,
    CalendarIcon,
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
});

const user = window.Laravel?.user || {};
</script>

<template>
    <Head title="Dashboard Formateur" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                Dashboard Formateur
            </h2>
        </template>

        <div class="py-6">
            <div class="page-container">
                <!-- Message de bienvenue -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8 flex items-center">
                    <AcademicCapIcon class="w-10 h-10 text-indigo-600 dark:text-indigo-400 mr-4 flex-shrink-0" />
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-indigo-700 dark:text-indigo-400 mb-2">
                            Bienvenue, {{ user?.prenom || 'Formateur' }} !
                        </h1>
                        <p class="text-gray-600 dark:text-gray-300">Gérez vos formations et suivez les apprenants</p>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                                <AcademicCapIcon class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Publiées</span>
                        </div>
                        <h3 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-gray-100 mb-1">
                            {{ stats.formationsPubliees || 0 }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Formations publiées</p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-green-50 dark:bg-green-900/30 flex items-center justify-center">
                                <UsersIcon class="w-6 h-6 text-green-600 dark:text-green-400" />
                            </div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Inscrits</span>
                        </div>
                        <h3 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-gray-100 mb-1">
                            {{ stats.inscritsTotal || 0 }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Inscrits totaux</p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-yellow-50 dark:bg-yellow-900/30 flex items-center justify-center">
                                <StarIcon class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                            </div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Évaluations</span>
                        </div>
                        <h3 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-gray-100 mb-1">
                            {{ stats.evaluationsRecues || 0 }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Évaluations reçues</p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center">
                                <ChartBarIcon class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                            </div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Moyenne</span>
                        </div>
                        <h3 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-gray-100 mb-1">
                            {{ (stats.noteMoyenne || 0).toFixed(1) }}/5
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Note moyenne</p>
                    </div>
                </div>

                <!-- Actions rapides Formateur -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center">
                        <LightningBoltIcon class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-400" />
                        Actions rapides
                    </h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                        <Link href="/formations" class="bg-blue-50 dark:bg-blue-900/30 p-4 rounded-xl text-center hover:bg-blue-100 dark:hover:bg-blue-900/50 transition text-blue-700 dark:text-blue-300 flex flex-col items-center">
                            <AcademicCapIcon class="w-8 h-8 mb-2" />
                            Mes formations
                        </Link>
                        <Link href="/formations/create" class="bg-green-50 dark:bg-green-900/30 p-4 rounded-xl text-center hover:bg-green-100 dark:hover:bg-green-900/50 transition text-green-700 dark:text-green-300 flex flex-col items-center">
                            <PlusIcon class="w-8 h-8 mb-2" />
                            Nouvelle formation
                        </Link>
                        <Link href="/collaborateurs" class="bg-purple-50 dark:bg-purple-900/30 p-4 rounded-xl text-center hover:bg-purple-100 dark:hover:bg-purple-900/50 transition text-purple-700 dark:text-purple-300 flex flex-col items-center">
                            <UsersIcon class="w-8 h-8 mb-2" />
                            Apprenants
                        </Link>
                        <Link href="/agenda" class="bg-yellow-50 dark:bg-yellow-900/30 p-4 rounded-xl text-center hover:bg-yellow-100 dark:hover:bg-yellow-900/50 transition text-yellow-700 dark:text-yellow-300 flex flex-col items-center">
                            <CalendarIcon class="w-8 h-8 mb-2" />
                            Agenda
                        </Link>
                    </div>
                </div>

                <!-- Flux d'informations -->
                <DashboardWidgets 
                    :annonces="recentAnnonces" 
                    :events="upcomingEvents" 
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>