<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import CalendrierConges from '@/Components/CalendrierConges.vue';

const props = defineProps({
    conges: Array,
    stats: Object,
    canValider: Boolean,
    canCreer: Boolean,
});

const getStatusColor = (statut) => ({
    en_attente: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200',
    valide: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200',
    rejete: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200',
    annule: 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
}[statut] || 'bg-gray-100 text-gray-600');

const getStatusLabel = (statut) => ({
    en_attente: 'En attente',
    valide: 'Validé',
    rejete: 'Rejeté',
    annule: 'Annulé',
}[statut] || statut);

const valider = (id, statut) => {
    if (confirm(`Confirmer la ${statut === 'valide' ? 'validation' : 'validation'} ?`)) {
        router.patch(route('conges.valider', id), { statut });
    }
};

const annuler = (id) => {
    if (confirm('Annuler cette demande ?')) {
        router.patch(route('conges.annuler', id));
    }
};
</script>

<template>
    <Head title="Gestion des congés" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl text-gray-800 dark:text-gray-100 leading-tight">Gestion des congés</h2>
        </template>

        <div class="py-6">
            <div class="page-container">
                <!-- Stats -->
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
                        <p class="text-xs text-gray-400">Total</p>
                        <p class="text-2xl font-bold">{{ stats.total }}</p>
                    </div>
                    <div class="bg-yellow-50 dark:bg-yellow-900/30 rounded-xl p-4 border border-yellow-200 dark:border-yellow-800">
                        <p class="text-xs text-yellow-600 dark:text-yellow-300">En attente</p>
                        <p class="text-2xl font-bold text-yellow-700 dark:text-yellow-200">{{ stats.en_attente }}</p>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/30 rounded-xl p-4 border border-green-200 dark:border-green-800">
                        <p class="text-xs text-green-600 dark:text-green-300">Validés</p>
                        <p class="text-2xl font-bold text-green-700 dark:text-green-200">{{ stats.valides }}</p>
                    </div>
                    <div class="bg-red-50 dark:bg-red-900/30 rounded-xl p-4 border border-red-200 dark:border-red-800">
                        <p class="text-xs text-red-600 dark:text-red-300">Rejetés</p>
                        <p class="text-2xl font-bold text-red-700 dark:text-red-200">{{ stats.rejetes }}</p>
                    </div>
                </div>

                <!-- ✅ Calendrier des congés -->
                <div class="mb-8">
                    <CalendrierConges />
                </div>

                <!-- Bouton créer -->
                <div v-if="canCreer" class="mb-6 flex justify-between items-center">
                    <Link href="/conges/create" class="px-6 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition font-bold shadow-lg shadow-indigo-200 dark:shadow-indigo-900/30">
                        + Demander un congé
                    </Link>
                    <span v-if="$page.props.auth.user.mainRole?.nom === 'responsable_rh' || $page.props.auth.user.mainRole?.nom === 'super_admin'" class="text-sm text-gray-500 dark:text-gray-400">
                        Solde annuel : {{ stats.soldeMoyen ?? 'N/A' }} jours en moyenne
                    </span>
                </div>

                <!-- Tableau -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">Employé</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">Période</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">Durée</th>
                                <!-- ✅ Colonne Solde (visible pour RH, Manager, Super Admin) -->
                                <th v-if="canValider" class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">Solde restant</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">Statut</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-400 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="conge in conges" :key="conge.id" class="border-t border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-4 py-3 text-sm font-medium">{{ conge.utilisateur?.prenom }} {{ conge.utilisateur?.nom }}</td>
                                <td class="px-4 py-3 text-sm capitalize">{{ conge.type_conge }}</td>
                                <td class="px-4 py-3 text-sm">{{ new Date(conge.date_debut).toLocaleDateString('fr-FR') }} → {{ new Date(conge.date_fin).toLocaleDateString('fr-FR') }}</td>
                                <td class="px-4 py-3 text-sm font-bold">{{ conge.duree_ouvrable }}j</td>
                                <!-- ✅ Affichage du solde (si disponible) -->
                                <td v-if="canValider" class="px-4 py-3 text-sm font-medium text-indigo-600 dark:text-indigo-400">
                                    {{ conge.utilisateur?.solde_annuel ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span :class="['px-3 py-1 rounded-full text-xs font-bold', getStatusColor(conge.statut)]">
                                        {{ getStatusLabel(conge.statut) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    <Link :href="`/conges/${conge.id}`" class="text-indigo-600 hover:underline text-sm">Voir</Link>
                                    <span v-if="canValider && conge.statut === 'en_attente'">
                                        <button @click="valider(conge.id, 'valide')" class="text-green-600 hover:underline text-sm">Valider</button>
                                        <button @click="valider(conge.id, 'rejete')" class="text-red-600 hover:underline text-sm">Rejeter</button>
                                    </span>
                                    <button v-if="conge.statut === 'en_attente' && canCreer" @click="annuler(conge.id)" class="text-gray-500 hover:underline text-sm">Annuler</button>
                                </td>
                            </tr>
                            <tr v-if="conges.length === 0">
                                <td colspan="7" class="text-center py-6 text-gray-500">Aucune demande de congé.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>