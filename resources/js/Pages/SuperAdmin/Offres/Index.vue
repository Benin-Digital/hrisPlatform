<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    offres: Array
});

const form = useForm({});

const deleteOffre = (id) => {
    if (confirm('Voulez-vous vraiment supprimer cette offre ?')) {
        form.delete(route('super-admin.offres.destroy', id), {
            preserveScroll: true,
        });
    }
};

const togglePublish = (id) => {
    form.post(route('super-admin.offres.toggle-publish', id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Gestion des Offres d'Emploi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Recrutement / Offres</h2>
                <Link :href="route('super-admin.offres.create')" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition">
                    + Nouvelle Offre
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 border-b border-gray-200">
                        
                        <div v-if="offres.length === 0" class="text-center py-10 text-gray-500">
                            Aucune offre d'emploi pour le moment.
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Titre</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contrat / Lieu</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiration</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="offre in offres" :key="offre.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ offre.titre }}</div>
                                            <div class="text-sm text-gray-500">{{ offre.departement || 'Aucun département' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ offre.type_contrat }}
                                            </span>
                                            <div class="text-sm text-gray-500 mt-1">{{ offre.lieu }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ offre.date_expiration ? new Date(offre.date_expiration).toLocaleDateString() : 'Illimité' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button @click="togglePublish(offre.id)" 
                                                class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                :class="[offre.is_published ? 'bg-green-600' : 'bg-gray-200']">
                                                <span class="sr-only">Statut</span>
                                                <span aria-hidden="true" 
                                                    class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"
                                                    :class="[offre.is_published ? 'translate-x-5' : 'translate-x-0']">
                                                </span>
                                            </button>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('super-admin.offres.edit', offre.id)" class="text-indigo-600 hover:text-indigo-900 mr-4">Modifier</Link>
                                            <button @click="deleteOffre(offre.id)" class="text-red-600 hover:text-red-900">Supprimer</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
