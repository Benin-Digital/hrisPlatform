<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import {
    UsersIcon,
    AcademicCapIcon,
    DocumentTextIcon,
    CalendarDaysIcon,
} from '@heroicons/vue/24/outline';

const page = usePage();

// Mapping des noms d'icônes vers les composants
const iconMap = {
    'users': UsersIcon,
    'academic-cap': AcademicCapIcon,
    'document-text': DocumentTextIcon,
    'calendar-days': CalendarDaysIcon,
};

// Stats avec icônes SVG
const stats = ref([
    { name: 'Collaborateurs', value: '48', icon: 'users' },
    { name: 'Formations en cours', value: '12', icon: 'academic-cap' },
    { name: 'Documents actifs', value: '324', icon: 'document-text' },
    { name: 'Événements ce mois', value: '8', icon: 'calendar-days' },
]);

// Computed pour gérer les rôles de manière sécurisée
const userRoles = computed(() => {
    const roles = page.props.auth?.user?.roles;
    if (roles && Array.isArray(roles) && roles.length > 0) {
        return roles.map(r => r.nom_affichage || r.name).join(', ');
    }
    return 'Collaborateur';
});

// Date formatée
const formattedDate = computed(() => {
    return new Date().toLocaleDateString('fr-FR', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
});
</script>

<template>
    <Head title="Tableau de bord" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Message de bienvenue personnalisé -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                    <div class="p-8 text-gray-900">
                        <h1 class="text-3xl font-bold text-indigo-700">
                            Bienvenue, {{ $page.props.auth?.user?.prenom }} {{ $page.props.auth?.user?.nom }} !
                        </h1>
                        <p class="mt-4 text-lg text-gray-600">
                            Vous êtes connecté en tant que 
                            <span class="font-semibold text-indigo-600">
                                {{ userRoles }}
                            </span>
                        </p>
                        <p class="mt-2 text-gray-500">
                            Aujourd'hui : {{ formattedDate }}
                        </p>
                    </div>
                </div>

                <!-- Cartes de statistiques -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="stat in stats" :key="stat.name"
                         class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center hover:shadow-lg transition">
                        <component 
                            :is="iconMap[stat.icon]" 
                            class="w-10 h-10 mx-auto mb-4 text-indigo-600" 
                        />
                        <div class="text-3xl font-bold text-indigo-600">{{ stat.value }}</div>
                        <div class="text-gray-600 mt-2">{{ stat.name }}</div>
                    </div>
                </div>

                <!-- Section raccourcis (optionnel) -->
                <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white p-8 rounded-lg shadow-lg hover:from-indigo-600 hover:to-purple-700 transition cursor-pointer">
                        <h3 class="text-xl font-bold">Gestion RH</h3>
                        <p class="mt-2">Ajouter, modifier ou consulter les collaborateurs</p>
                    </div>
                    <div class="bg-gradient-to-r from-green-500 to-teal-600 text-white p-8 rounded-lg shadow-lg hover:from-green-600 hover:to-teal-700 transition cursor-pointer">
                        <h3 class="text-xl font-bold">Formations</h3>
                        <p class="mt-2">Accéder au catalogue et suivre vos progrès</p>
                    </div>
                    <div class="bg-gradient-to-r from-orange-500 to-red-600 text-white p-8 rounded-lg shadow-lg hover:from-orange-600 hover:to-red-700 transition cursor-pointer">
                        <h3 class="text-xl font-bold">Documents</h3>
                        <p class="mt-2">Consulter et gérer vos documents professionnels</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>