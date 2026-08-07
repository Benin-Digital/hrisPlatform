<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, watch, ref } from 'vue';

// Props reçues du contrôleur
const props = defineProps({
    soldeAnnuel: {
        type: Number,
        default: 0,
    },
});

const form = useForm({
    type_conge: 'annuel',
    date_debut: '',
    date_fin: '',
    motif: '',
});

// Calcul automatique du nombre de jours OUVRABLES demandés (hors
// week-ends), aligné sur le calcul fait côté serveur dans
// CongeController::store(). Purement indicatif : le calcul définitif
// reste fait côté serveur à la soumission.
const joursDemandes = computed(() => {
    if (form.date_debut && form.date_fin) {
        const debut = new Date(form.date_debut);
        const fin = new Date(form.date_fin);
        if (fin < debut) return 0;

        let jours = 0;
        const curseur = new Date(debut);
        while (curseur <= fin) {
            const jourSemaine = curseur.getDay(); // 0 = dimanche, 6 = samedi
            if (jourSemaine !== 0 && jourSemaine !== 6) jours++;
            curseur.setDate(curseur.getDate() + 1);
        }
        return jours;
    }
    return 0;
});

// Vérification du solde lors du changement de dates
const soldeSuffisant = computed(() => {
    if (form.type_conge === 'annuel' && joursDemandes.value > 0) {
        return joursDemandes.value <= props.soldeAnnuel;
    }
    return true; // pour les autres types, pas de vérification (sauf si vous voulez)
});

// Message d'alerte si solde insuffisant
const messageSolde = computed(() => {
    if (form.type_conge === 'annuel' && joursDemandes.value > 0 && !soldeSuffisant.value) {
        return `Solde insuffisant. Vous disposez de ${props.soldeAnnuel} jours, vous en demandez ${joursDemandes.value}.`;
    }
    return null;
});

// Adaptation du message d'information selon le type de congé
const infoMessage = computed(() => {
    if (form.type_conge === 'annuel') {
        return `Vous disposez de ${props.soldeAnnuel} jours de congés annuels.`;
    } else if (form.type_conge === 'sans_solde') {
        return '⚠️ Ce type de congé est sans solde. Votre salaire sera déduit du nombre de jours non travaillés.';
    } else if (form.type_conge === 'maladie') {
        return 'ℹ️ Les congés maladie sont soumis à des règles spécifiques (délai de carence, justificatif).';
    } else if (form.type_conge === 'formation') {
        return '📚 Les congés formation sont généralement payés par l\'employeur.';
    }
    return '';
});

const submit = () => {
    form.post(route('conges.store'), {
        onSuccess: () => {
            form.reset();
            // On pourrait afficher une notification de succès ici
        },
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Demander un congé" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center w-full">
                <h2 class="font-bold text-xl text-gray-800 dark:text-gray-100 leading-tight">Demander un congé</h2>
            </div>
        </template>

        <div class="py-6">
            <div class="page-container max-w-2xl">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <!-- Affichage du solde annuel -->
                    <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6 flex items-start gap-3">
                        <span class="text-2xl">📅</span>
                        <div>
                            <p class="text-sm text-blue-700 dark:text-blue-300 font-bold">Solde annuel disponible</p>
                            <p class="text-2xl font-black text-blue-600 dark:text-blue-400">{{ soldeAnnuel }} jours</p>
                            <p v-if="joursDemandes > 0 && form.type_conge === 'annuel'" class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                                (Demande : {{ joursDemandes }} jours - Restant : {{ soldeAnnuel - joursDemandes }} jours)
                            </p>
                        </div>
                    </div>

                    <!-- Message d'information selon le type -->
                    <div v-if="infoMessage" class="bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4 mb-6 text-sm text-gray-700 dark:text-gray-300">
                        {{ infoMessage }}
                    </div>

                    <!-- Alertes d'erreur -->
                    <div v-if="messageSolde" class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-4 text-sm text-red-700 dark:text-red-300">
                        ⚠️ {{ messageSolde }}
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Type de congé</label>
                            <select v-model="form.type_conge" class="w-full mt-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500">
                                <option value="annuel">Annuel</option>
                                <option value="maladie">Maladie</option>
                                <option value="sans_solde">Sans solde</option>
                                <option value="formation">Formation</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Date de début</label>
                            <input v-model="form.date_debut" type="date" required 
                                   class="w-full mt-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500">
                            <div v-if="form.errors.date_debut" class="text-red-500 text-sm mt-1">{{ form.errors.date_debut }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Date de fin</label>
                            <input v-model="form.date_fin" type="date" required 
                                   class="w-full mt-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500">
                            <div v-if="form.errors.date_fin" class="text-red-500 text-sm mt-1">{{ form.errors.date_fin }}</div>
                            <div v-if="joursDemandes > 0 && form.type_conge === 'annuel'" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Nombre de jours demandés : <span class="font-bold">{{ joursDemandes }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Motif (optionnel)</label>
                            <textarea v-model="form.motif" rows="3" 
                                      class="w-full mt-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500" 
                                      placeholder="Précisez la raison si nécessaire..."></textarea>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="submit" :disabled="form.processing || (!soldeSuffisant && form.type_conge === 'annuel')" 
                                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                {{ form.processing ? 'Envoi en cours...' : 'Envoyer la demande' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>