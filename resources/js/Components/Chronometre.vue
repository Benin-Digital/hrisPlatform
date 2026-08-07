<template>
    <div class="flex flex-col gap-4">
        <div class="flex items-center gap-3">
            <div class="text-lg font-mono font-bold text-gray-700 min-w-[80px]">
                {{ formattedTime }}
            </div>

            <button
                v-if="!isRunning"
                @click="startChrono"
                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-bold transition"
            >
                ▶ Démarrer
            </button>

            <button
                v-else
                @click="stopChrono"
                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-bold transition"
            >
                ⏹ Arrêter
            </button>
        </div>

        <!-- Historique des sessions -->
        <div v-if="historiqueSessions.length > 0 || sessionEnCours" class="mt-6 border-t border-gray-100 pt-4">
            <h4 class="text-sm font-bold text-gray-700 mb-3">📋 Historique des sessions</h4>
            <div class="max-h-48 overflow-y-auto space-y-2 pr-2">
                <!-- Session en cours -->
                <div v-if="sessionEnCours" class="bg-yellow-50 border border-yellow-200 rounded-lg px-3 py-2 flex justify-between items-center">
                    <div>
                        <span class="text-xs font-medium text-yellow-800">▶ En cours</span>
                        <span class="text-xs text-yellow-600 ml-2">depuis {{ sessionEnCours.depuis }}</span>
                    </div>
                </div>

                <!-- Sessions terminées -->
                <div
                    v-for="session in historiqueSessions"
                    :key="session.id"
                    class="bg-gray-50 rounded-lg px-3 py-2 flex justify-between items-center"
                >
                    <div>
                        <span class="text-xs font-medium text-gray-700">{{ session.debut }}</span>
                        <span class="text-xs text-gray-400 mx-1">→</span>
                        <span class="text-xs text-gray-700">{{ session.fin }}</span>
                    </div>
                    <span class="text-sm font-bold text-gray-600">{{ session.duree }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    tacheId: {
        type: Number,
        required: true,
    },
    tempsInitial: {
        type: Number,
        default: 0,
    },
});

const emit = defineEmits(['time-updated']);

const isRunning = ref(false);
const elapsedSeconds = ref(0);
const totalSeconds = ref(props.tempsInitial * 60);
const historiqueSessions = ref([]);
const sessionEnCours = ref(null);
let interval = null;

const formattedTime = computed(() => {
    const total = totalSeconds.value + elapsedSeconds.value;
    const h = Math.floor(total / 3600);
    const m = Math.floor((total % 3600) / 60);
    const s = Math.floor(total % 60);
    return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
});

const startChrono = async () => {
    if (isRunning.value) return;
    try {
        await axios.post(`/taches/${props.tacheId}/timer/start`);
        isRunning.value = true;
        interval = setInterval(() => {
            elapsedSeconds.value++;
        }, 1000);
        // Recharger l'historique
        await fetchHistorique();
    } catch (error) {
        console.error('Erreur au démarrage du chrono', error);
        alert(error.response?.data?.message || 'Erreur au démarrage du chronomètre.');
    }
};

const stopChrono = async () => {
    if (!isRunning.value) return;
    isRunning.value = false;

    if (interval) {
        clearInterval(interval);
        interval = null;
    }

    try {
        const response = await axios.post(`/taches/${props.tacheId}/timer/stop`);
        if (response.data.success) {
            totalSeconds.value = response.data.new_total * 60;
            elapsedSeconds.value = 0;
            emit('time-updated', response.data.new_total);
            await fetchHistorique();
        }
    } catch (error) {
        console.error('Erreur à l\'arrêt du chrono', error);
        // En cas d'erreur, on réinitialise localement
        totalSeconds.value += elapsedSeconds.value;
        elapsedSeconds.value = 0;
    }
};

const fetchHistorique = async () => {
    try {
        const response = await axios.get(`/taches/${props.tacheId}/historique-temps`);
        historiqueSessions.value = response.data.sessions || [];
        sessionEnCours.value = response.data.session_en_cours;

        // Si une session est en cours, on laisse le chrono tourner
        if (response.data.session_en_cours) {
            // Ne pas relancer le chrono automatiquement, on laisse l'utilisateur gérer
        }
    } catch (error) {
        console.error('Erreur chargement historique', error);
    }
};

onMounted(() => {
    fetchHistorique();
});

onUnmounted(() => {
    if (interval) {
        clearInterval(interval);
    }
});
</script>