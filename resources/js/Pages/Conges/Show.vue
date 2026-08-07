<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    conge: Object,
    canValider: Boolean,
});

const valider = (statut) => {
    if (confirm(`Confirmer la ${statut === 'valide' ? 'validation' : 'validation'} ?`)) {
        router.patch(route('conges.valider', props.conge.id), { statut });
    }
};

const annuler = () => {
    if (confirm('Annuler cette demande ?')) {
        router.patch(route('conges.annuler', props.conge.id));
    }
};
</script>

<template>
    <Head title="Détail congé" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center w-full">
                <h2 class="font-bold text-xl text-gray-800 dark:text-gray-100 leading-tight">Détail de la demande</h2>
                <Link href="/conges" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">← Retour</Link>
            </div>
        </template>

        <div class="py-6">
            <div class="page-container max-w-3xl">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                    <div><span class="font-bold">Employé :</span> {{ conge.utilisateur?.prenom }} {{ conge.utilisateur?.nom }}</div>
                    <div><span class="font-bold">Type :</span> {{ conge.type_conge }}</div>
                    <div><span class="font-bold">Période :</span> {{ new Date(conge.date_debut).toLocaleDateString('fr-FR') }} → {{ new Date(conge.date_fin).toLocaleDateString('fr-FR') }}</div>
                    <div><span class="font-bold">Durée :</span> {{ conge.duree_ouvrable }} jours</div>
                    <div><span class="font-bold">Statut :</span> {{ conge.statut }}</div>
                    <div v-if="conge.motif"><span class="font-bold">Motif :</span> {{ conge.motif }}</div>
                    <div v-if="conge.valide_par"><span class="font-bold">Validé par :</span> {{ conge.validateur?.prenom }} {{ conge.validateur?.nom }}</div>
                    <div v-if="conge.date_validation"><span class="font-bold">Date de validation :</span> {{ new Date(conge.date_validation).toLocaleDateString('fr-FR') }}</div>
                    <div v-if="conge.commentaire_validation"><span class="font-bold">Commentaire :</span> {{ conge.commentaire_validation }}</div>

                    <div v-if="canValider && conge.statut === 'en_attente'" class="pt-4 flex gap-4">
                        <button @click="valider('valide')" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Valider</button>
                        <button @click="valider('rejete')" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Rejeter</button>
                    </div>
                    <div v-if="conge.statut === 'en_attente' && conge.utilisateur_id === $page.props.auth.user.id">
                        <button @click="annuler" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">Annuler ma demande</button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>