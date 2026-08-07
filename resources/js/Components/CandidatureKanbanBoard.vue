<template>
    <div v-if="hasOffres" class="space-y-8">
        <div v-for="offre in offres" :key="offre.id" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <h3 class="font-bold text-lg text-gray-900 dark:text-gray-100 mb-4">
                📌 {{ offre.titre }}
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div
                    v-for="(column, statut) in colonnes"
                    :key="statut"
                    class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 min-h-[200px]"
                >
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="font-bold text-xs text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                            {{ column.label }}
                        </h4>
                        <span class="bg-white dark:bg-gray-600 px-2 py-0.5 rounded-full text-xs font-bold text-gray-500 dark:text-gray-300">
                            {{ column.candidatures.length }}
                        </span>
                    </div>

                    <div class="space-y-2">
                        <div
                            v-for="cand in column.candidatures"
                            :key="cand.id"
                            class="bg-white dark:bg-gray-600 p-3 rounded-lg shadow-sm border border-gray-100 dark:border-gray-500 hover:shadow-md transition"
                        >
                            <p class="font-bold text-sm text-gray-800 dark:text-gray-100">
                                {{ cand.prenom }} {{ cand.nom }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-300">{{ cand.email }}</p>
                            <div class="mt-2 flex flex-wrap gap-1">
                                <button
                                    v-for="(label, key) in statutList"
                                    :key="key"
                                    @click="moveCandidature(cand.id, key, offre.id)"
                                    class="px-2 py-0.5 text-[9px] font-bold rounded-lg transition"
                                    :class="{
                                        'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-200 dark:hover:bg-indigo-800': key !== cand.statut,
                                        'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 cursor-default': key === cand.statut,
                                    }"
                                    :disabled="key === cand.statut"
                                >
                                    {{ label }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div v-else class="text-center py-12 text-gray-500 dark:text-gray-400">
        Aucune offre avec candidatures.
    </div>
</template>

<script setup>
import axios from 'axios';
import { ref, watch, onMounted } from 'vue';

const props = defineProps({
    offres: {
        type: Array,
        required: true,
        default: () => [],
    },
});

const hasOffres = ref(false);

const statutList = {
    reçue: '📨 Reçue',
    examen: '🔍 Examen',
    entretien: '🎤 Entretien',
    offre: '📄 Offre',
    accepté: '✅ Accepté',
    refusé: '❌ Refusé',
};

const colonnes = {
    reçue: { label: '📨 Reçues', candidatures: [] },
    examen: { label: '🔍 Examen', candidatures: [] },
    entretien: { label: '🎤 Entretien', candidatures: [] },
    offre: { label: '📄 Offre', candidatures: [] },
    accepté: { label: '✅ Accepté', candidatures: [] },
    refusé: { label: '❌ Refusé', candidatures: [] },
};

const moveCandidature = async (candidatureId, newStatut, offreId) => {
    try {
        await axios.patch(`/candidatures/${candidatureId}/etape`, { statut: newStatut });
        initColonnes();
    } catch (error) {
        console.error('Erreur', error);
        alert('Erreur lors du changement de statut.');
    }
};

const initColonnes = () => {
    Object.keys(colonnes).forEach(key => {
        colonnes[key].candidatures = [];
    });

    if (props.offres && props.offres.length > 0) {
        props.offres.forEach(offre => {
            offre.candidatures.forEach(cand => {
                const statut = cand.statut || 'reçue';
                if (colonnes[statut]) {
                    colonnes[statut].candidatures.push(cand);
                } else {
                    colonnes.reçue.candidatures.push(cand);
                }
            });
        });
        hasOffres.value = true;
    } else {
        hasOffres.value = false;
    }
};

onMounted(() => {
    initColonnes();
});

watch(() => props.offres, () => {
    initColonnes();
}, { deep: true });
</script>