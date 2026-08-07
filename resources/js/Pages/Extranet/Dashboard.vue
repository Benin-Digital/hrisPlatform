<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import DashboardWidgets from '@/Components/Widgets/DashboardWidgets.vue';
import {
    FolderIcon,
    CalendarDaysIcon,
    AcademicCapIcon,
    PhotoIcon,
} from '@heroicons/vue/24/outline';

defineProps({
    recentAnnonces: Array,
    upcomingEvents: Array,
});
</script>

<template>
    <Head title="Extranet" />
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8 p-8 border-l-8 border-yellow-500">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">
                        Espace Invité (Extranet)
                    </h1>
                    <p class="text-lg text-gray-600">Bienvenue sur notre portail partenaire.</p>
                </div>

                <div class="bg-white rounded-lg shadow p-6 mb-8">
                    <h2 class="text-xl font-bold mb-4">Accès Rapides</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <a href="/documents" class="flex items-center gap-3 p-4 bg-gray-50 hover:bg-gray-100 rounded border">
                            <FolderIcon class="w-6 h-6 text-gray-700" />
                            <span>Documents Partagés</span>
                        </a>
                        <a href="/agenda" class="flex items-center gap-3 p-4 bg-gray-50 hover:bg-gray-100 rounded border">
                            <CalendarDaysIcon class="w-6 h-6 text-gray-700" />
                            <span>Calendrier Externe</span>
                        </a>
                        <Link :href="route('formations.index')" class="flex items-center gap-3 p-4 bg-gray-50 hover:bg-gray-100 rounded border">
                            <AcademicCapIcon class="w-6 h-6 text-gray-700" />
                            <span>Formations & Modules</span>
                        </Link>
                        <a href="/gallery" class="flex items-center gap-3 p-4 bg-gray-50 hover:bg-gray-100 rounded border">
                            <PhotoIcon class="w-6 h-6 text-gray-700" />
                            <span>Galerie Photos</span>
                        </a>
                    </div>
                </div>

                <!-- Widgets Collaboratifs -->
                <DashboardWidgets 
                    :annonces="recentAnnonces" 
                    :events="upcomingEvents" 
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>