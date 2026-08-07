<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import CandidatureKanbanBoard from '@/Components/CandidatureKanbanBoard.vue';
import {router} from '@inertiajs/vue3';

const props = defineProps({
    candidatures: {
        type: Array,
        default: () => [],
    },
    offres: {
        type: Array,
        default: () => [],
    },
});

const viewMode = ref('list');

const toggleView = () => {
    viewMode.value = viewMode.value === 'list' ? 'kanban' : 'list';
};
const deleteCandidature = (id) => {
    if (confirm('Supprimer cette candidature ?')) {
        router.delete(`/super-admin/candidatures/${id}`);
    }
};
</script>

<template>
    <Head title="Gestion des candidatures" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center w-full">
                <h2 class="font-bold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    Candidatures
                </h2>
                <button
                    @click="toggleView"
                    class="px-3 py-2 text-sm font-bold rounded-lg transition bg-indigo-600 text-white hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600"
                >
                    {{ viewMode === 'list' ? '📊 Vue Pipeline' : '📋 Vue Liste' }}
                </button>
            </div>
        </template>

        <div class="py-6">
            <div class="page-container">
                <!-- Vue Liste -->
                <div v-if="viewMode === 'list'" class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Candidat</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Offre</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="cand in candidatures" :key="cand.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ cand.prenom }} {{ cand.nom }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ cand.email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ cand.offre?.titre }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                        :class="{
                                            'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200': cand.statut === 'reçue',
                                            'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200': cand.statut === 'examen',
                                            'bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200': cand.statut === 'entretien',
                                            'bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200': cand.statut === 'offre',
                                            'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200': cand.statut === 'accepté',
                                            'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200': cand.statut === 'refusé',
                                        }"
                                    >
                                        {{ cand.statut }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ new Date(cand.created_at).toLocaleDateString('fr-FR') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a :href="`/super-admin/candidatures/${cand.id}/download-cv`" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 mr-3">
                                        📄 CV
                                    </a>
                                    <Link :href="`/super-admin/candidatures/${cand.id}/edit`" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 mr-3">
                                        ✏️
                                    </Link>
                                    <button @click="deleteCandidature(cand.id)" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">
                                        🗑️
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-if="candidatures.length === 0" class="text-center py-12 text-gray-500 dark:text-gray-400">
                        Aucune candidature pour le moment.
                    </div>
                </div>

                <!-- Vue Kanban -->
                <CandidatureKanbanBoard v-else :offres="offres" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>