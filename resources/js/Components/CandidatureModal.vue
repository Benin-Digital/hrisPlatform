<template>
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="$emit('close')">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-start mb-4">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ candidature.prenom }} {{ candidature.nom }}
                </h2>
                <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600" :disabled="loading">✕</button>
            </div>

            <!-- Informations générales -->
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-xs text-gray-400">Email</p>
                    <p class="font-bold text-sm">{{ candidature.email }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Téléphone</p>
                    <p class="font-bold text-sm">{{ candidature.telephone || 'Non renseigné' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Offre</p>
                    <p class="font-bold text-sm">{{ candidature.offre?.titre || 'Sans offre' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Type</p>
                    <p class="font-bold text-sm capitalize">{{ candidature.type }}</p>
                </div>
                <div class="col-span-2">
                    <p class="text-xs text-gray-400">Statut</p>
                    <span class="inline-block px-3 py-1 rounded-full text-sm font-bold" :class="{
                        'bg-blue-100 text-blue-700': candidature.statut === 'nouveau',
                        'bg-yellow-100 text-yellow-700': candidature.statut === 'en_cours',
                        'bg-purple-100 text-purple-700': candidature.statut === 'entretien_planifie',
                        'bg-indigo-100 text-indigo-700': candidature.statut === 'entretien_realise',
                        'bg-orange-100 text-orange-700': candidature.statut === 'offre',
                        'bg-green-100 text-green-700': candidature.statut === 'accepte',
                        'bg-red-100 text-red-700': candidature.statut === 'refuse',
                    }">
                        {{ statuts[candidature.statut]?.label || candidature.statut }}
                    </span>
                </div>
            </div>

            <!-- Détails de l'entretien (si existant) -->
            <div v-if="candidature.date_entretien" class="border-t pt-4">
                <h3 class="font-bold text-gray-700 mb-2">📅 Entretien</h3>
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div><span class="text-gray-400">Date :</span> {{ new Date(candidature.date_entretien).toLocaleDateString() }}</div>
                    <div><span class="text-gray-400">Heure :</span> {{ candidature.heure_entretien }}</div>
                    <div class="col-span-2"><span class="text-gray-400">Lieu :</span> {{ candidature.lieu_entretien || 'Non défini' }}</div>
                </div>
                <div v-if="candidature.score_total" class="mt-2">
                    <p class="text-gray-400 text-xs">Score total :</p>
                    <div class="flex items-center gap-2">
                        <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-500 transition-all" :style="{ width: candidature.score_total + '%' }"></div>
                        </div>
                        <span class="font-bold text-indigo-600">{{ candidature.score_total }}%</span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-4 border-t pt-4 flex flex-wrap gap-2">
                <button @click="$emit('close')" class="px-4 py-2 bg-gray-200 rounded-lg text-sm font-bold" :disabled="loading">Fermer</button>

                <!-- Télécharger le CV -->
                <button
                    @click="downloadCv"
                    :disabled="loading"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 disabled:opacity-50 flex items-center gap-2"
                >
                    <span v-if="loading" class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
                    📄 Voir CV
                </button>

                <!-- Planifier un entretien -->
                <button
                    v-if="candidature.statut === 'nouveau' || candidature.statut === 'en_cours'"
                    @click="planifierEntretien"
                    :disabled="loading"
                    class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-bold hover:bg-purple-700 disabled:opacity-50 flex items-center gap-2"
                >
                    <span v-if="loading" class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
                    📅 Planifier
                </button>

                <!-- ✅ NOUVEAU : Marquer l'entretien comme réalisé -->
                <button
                    v-if="candidature.statut === 'entretien_planifie'"
                    @click="marquerRealise"
                    :disabled="loading"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 disabled:opacity-50 flex items-center gap-2"
                >
                    <span v-if="loading" class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
                    ✅ Réaliser l'entretien
                </button>

                <!-- Noter l'entretien -->
               <!--  <button
                    v-if="candidature.statut === 'entretien_realise'"
                    @click="noterEntretien"
                    :disabled="loading"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 disabled:opacity-50"
                >
                    ⭐ Noter
                </button> -->

                <!-- Valider (embauche) -->
                <button
                    v-if="candidature.statut === 'entretien_realise' || candidature.statut === 'offre'"
                    @click="validerCandidat"
                    :disabled="loading"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-bold hover:bg-green-700 disabled:opacity-50 flex items-center gap-2"
                >
                    <span v-if="loading" class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
                    ✅ Valider
                </button>

                <!-- Rejeter -->
                <button
                    v-if="candidature.statut !== 'accepte' && candidature.statut !== 'refuse'"
                    @click="rejeterCandidat"
                    :disabled="loading"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-bold hover:bg-red-700 disabled:opacity-50"
                >
                    ❌ Rejeter
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    candidature: Object,
    statuts: Object,
});

const emit = defineEmits(['close', 'updated']);
const loading = ref(false);

// Téléchargement du CV
const downloadCv = async () => {
    loading.value = true;
    try {
        const response = await axios.get(
            `/recrutement/candidatures/${props.candidature.id}/download-cv`,
            { responseType: 'blob' }
        );
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `CV_${props.candidature.nom}_${props.candidature.prenom}.pdf`);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error('Erreur CV :', error);
        alert(error.response?.status === 404 ? 'CV introuvable.' : 'Erreur de téléchargement.');
    } finally {
        loading.value = false;
    }
};

// Planifier un entretien
const planifierEntretien = () => {
    loading.value = true;
    router.visit(route('recrutement.planifier-form', props.candidature.id), {
        onFinish: () => { loading.value = false; },
        onError: () => { alert('Erreur de planification.'); loading.value = false; }
    });
};

// ✅ Marquer l'entretien comme réalisé (version corrigée)
const marquerRealise = async () => {
    if (!confirm('Marquer cet entretien comme réalisé ?')) return;
    loading.value = true;
    try {
        await axios.patch(
            route('recrutement.change-statut', props.candidature.id),
            { statut: 'entretien_realise' }
        );
        emit('updated');
    } catch (error) {
        console.error('Erreur :', error);
        alert('Erreur lors du passage en réalisé.');
    } finally {
        loading.value = false;
    }
};

// Noter l'entretien
//const noterEntretien = () => {
   // loading.value = true;
   // router.get(route('recrutement.noter-entretien', props.candidature.id), {}, {
      //  onFinish: () => { loading.value = false; },
       // onError: () => { alert('Erreur de notation.'); loading.value = false; }
   // });
//};

// Valider (embauche)
const validerCandidat = () => {
    if (!confirm('Valider cette candidature (embauche) ?')) return;
    loading.value = true;
    router.post(route('recrutement.valider', props.candidature.id), {}, {
        onSuccess: () => { emit('updated'); },
        onError: () => { alert('Erreur de validation.'); },
        onFinish: () => { loading.value = false; }
    });
};

// Rejeter
const rejeterCandidat = () => {
    const motif = prompt('Motif du rejet (optionnel) :');
    if (motif === null) return;
    loading.value = true;
    router.post(route('recrutement.rejeter', props.candidature.id), { motif }, {
        onSuccess: () => { emit('updated'); },
        onError: () => { alert('Erreur de rejet.'); },
        onFinish: () => { loading.value = false; }
    });
};
</script>

<style scoped>
.animate-spin {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>