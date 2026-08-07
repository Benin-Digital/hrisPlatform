<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    entite: Object, // L'entité avec utilisateurs et directions chargés
});
</script>

<template>
    <Head title="Détails de l'entité" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-8">
                        <!-- En-tête -->
                        <div class="flex justify-between items-center mb-8">
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900">
                                    {{ entite?.nom || 'Nom non défini' }}
                                </h1>
                                <p class="text-gray-600 mt-1">
                                    Code : <span class="font-medium">{{ entite?.code_entite || 'Non défini' }}</span>
                                </p>
                            </div>

                            <div class="flex space-x-4">
                                <!-- CORRIGÉ : nom de route avec double préfixe temporaire -->
                                <Link
                                    :href="route('super-admin.entites.edit', entite.id)"
                                    class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition">
                                    Éditer
                                </Link>

                                <!-- Retour à la liste -->
                                <Link
                                    :href="route('super-admin.entites.index')"
                                    class="bg-gray-600 text-white px-5 py-2 rounded-lg hover:bg-gray-700 transition">
                                    ← Retour à la liste
                                </Link>
                            </div>
                        </div>

                        <!-- Informations principales -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                            <div>
                                <h2 class="text-xl font-semibold mb-4">Informations générales</h2>
                                <dl class="space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Description</dt>
                                        <dd class="mt-1 text-gray-900">{{ entite?.description || 'Aucune description' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Adresse</dt>
                                        <dd class="mt-1 text-gray-900">{{ entite?.adresse || 'Non renseignée' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Téléphone</dt>
                                        <dd class="mt-1 text-gray-900">{{ entite?.telephone || 'Non renseigné' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                                        <dd class="mt-1 text-gray-900">{{ entite?.email || 'Non renseigné' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Statut</dt>
                                        <dd class="mt-1">
                                            <span
                                                :class="entite?.est_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                class="px-3 py-1 rounded-full text-sm font-medium">
                                                {{ entite?.est_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Couleur du thème</dt>
                                        <dd class="mt-1 flex items-center space-x-3">
                                            <div
                                                class="w-12 h-12 rounded-lg border-2 border-gray-300"
                                                :style="{ backgroundColor: entite?.couleur_theme || '#cccccc' }">
                                            </div>
                                            <span class="text-gray-900">{{ entite?.couleur_theme || 'Non définie' }}</span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Logo -->
                            <div class="flex flex-col items-center">
                                <h2 class="text-xl font-semibold mb-4 w-full">Logo de l'entité</h2>
                                <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-xl w-64 h-64 flex items-center justify-center">
                                    <img
                                        v-if="entite?.logo"
                                        :src="`/storage/${entite.logo}`"
                                        alt="Logo de l'entité"
                                        class="max-w-full max-h-full object-contain rounded-lg"
                                    />
                                    <p v-else class="text-gray-500">Aucun logo</p>
                                </div>
                            </div>
                        </div>

                        <!-- Utilisateurs -->
                        <div class="mt-12">
                            <h2 class="text-xl font-semibold mb-4">
                                Utilisateurs rattachés ({{ entite?.utilisateurs?.length || 0 }})
                            </h2>
                            <div v-if="entite?.utilisateurs && entite.utilisateurs.length > 0" class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prénom</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rôles</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="user in entite.utilisateurs" :key="user.id">
                                            <td class="px-6 py-4 whitespace-nowrap">{{ user.nom }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ user.prenom }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ user.email }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    v-for="role in user.roles"
                                                    :key="role.id"
                                                    class="inline-block bg-indigo-100 text-indigo-800 text-xs px-2 py-1 rounded mr-1">
                                                    {{ role.nom }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <p v-else class="text-gray-500 italic">Aucun utilisateur rattaché à cette entité.</p>
                        </div>

                        <!-- Directions -->
                        <div class="mt-12">
                            <h2 class="text-xl font-semibold mb-4">
                                Directions rattachées ({{ entite?.directions?.length || 0 }})
                            </h2>
                            <div v-if="entite?.directions && entite.directions.length > 0" class="space-y-3">
                                <div
                                    v-for="direction in entite.directions"
                                    :key="direction.id"
                                    class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                    <div class="flex justify-between">
                                        <div>
                                            <p class="font-medium text-lg">{{ direction.nom }}</p>
                                            <p class="text-gray-600">{{ direction.description || 'Pas de description' }}</p>
                                        </div>
                                        <span class="text-sm text-gray-500">
                                            {{ direction.utilisateurs_count || 0 }} collaborateur{{ direction.utilisateurs_count > 1 ? 's' : '' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <p v-else class="text-gray-500 italic">Aucune direction rattachée à cette entité.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>