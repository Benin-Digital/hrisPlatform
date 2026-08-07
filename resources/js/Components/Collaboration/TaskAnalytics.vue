<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  CategoryScale,
  LinearScale,
  BarElement
} from 'chart.js';
import { Pie, Bar } from 'vue-chartjs';

ChartJS.register(Title, Tooltip, Legend, ArcElement, CategoryScale, LinearScale, BarElement);

const props = defineProps({
    espaceId: {
        type: Number,
        required: true
    }
});

const stats = ref(null);
const loading = ref(true);

const fetchStats = async () => {
    loading.value = true;
    try {
        const res = await axios.get(route('taches.stats.espace', props.espaceId));
        stats.value = res.data;
    } catch (e) {
        console.error('Erreur stats', e);
    } finally {
        loading.value = false;
    }
};

onMounted(fetchStats);

// --- Données Graphique Statut ---
const chartDataStatut = computed(() => {
    if (!stats.value) return null;
    return {
        labels: stats.value.stats_statut.map(s => s.statut),
        datasets: [{
            backgroundColor: ['#4F46E5', '#10B981', '#F59E0B', '#EF4444'],
            data: stats.value.stats_statut.map(s => s.total)
        }]
    };
});

// --- Données Graphique Priorité ---
const chartDataPriorite = computed(() => {
    if (!stats.value) return null;
    return {
        labels: stats.value.stats_priorite.map(p => p.priorite),
        datasets: [{
            label: 'Tâches',
            backgroundColor: '#6366F1',
            data: stats.value.stats_priorite.map(p => p.total)
        }]
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false
};
</script>

<template>
    <div v-if="loading" class="flex justify-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
    </div>

    <div v-else-if="stats" class="space-y-8 animate-in fade-in duration-500">
        <!-- KPI Row -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="card p-6 bg-gradient-to-br from-indigo-500 to-indigo-600 text-white border-0">
                <p class="text-indigo-100 text-sm font-medium uppercase tracking-wider">Taux de Complétion</p>
                <div class="mt-2 flex items-baseline">
                    <span class="text-4xl font-black">{{ stats.taux_completion }}%</span>
                </div>
                <div class="mt-4 bg-white/20 h-2 rounded-full overflow-hidden">
                    <div class="bg-white h-full transition-all duration-1000" :style="{ width: stats.taux_completion + '%' }"></div>
                </div>
            </div>

            <div class="card p-6 border-0 shadow-lg bg-white">
                <p class="text-gray-500 text-sm font-medium uppercase tracking-wider">Respect des Délais</p>
                <div class="mt-2 flex items-baseline">
                    <span class="text-4xl font-black text-gray-900">
                        {{ stats.respect_delais.total > 0 ? Math.round((stats.respect_delais.a_temps / stats.respect_delais.total) * 100) : 0 }}%
                    </span>
                    <span class="ml-2 text-sm text-gray-400">livrées à temps</span>
                </div>
            </div>

            <div class="card p-6 border-0 shadow-lg bg-white">
                <p class="text-gray-500 text-sm font-medium uppercase tracking-wider">Précision d'Estimation</p>
                <div class="mt-2 flex items-baseline">
                    <span class="text-4xl font-black text-gray-900">
                        {{ stats.charge_travail.total_reel > 0 ? (stats.charge_travail.total_estime / stats.charge_travail.total_reel).toFixed(1) : '1.0' }}
                    </span>
                    <span class="ml-2 text-sm text-gray-400">Ratio Estime/Réel</span>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="card p-6 border-0 shadow-lg bg-white">
                <h4 class="font-bold text-gray-900 mb-6 flex items-center">
                    <span class="w-1 h-5 bg-indigo-500 rounded-full mr-3"></span>
                    Répartition par Statut
                </h4>
                <div class="h-64 relative">
                    <Pie v-if="chartDataStatut" :data="chartDataStatut" :options="chartOptions" />
                </div>
            </div>

            <div class="card p-6 border-0 shadow-lg bg-white">
                <h4 class="font-bold text-gray-900 mb-6 flex items-center">
                    <span class="w-1 h-5 bg-indigo-500 rounded-full mr-3"></span>
                    Charge par Priorité
                </h4>
                <div class="h-64 relative">
                    <Bar v-if="chartDataPriorite" :data="chartDataPriorite" :options="chartOptions" />
                </div>
            </div>
        </div>

        <!-- Note -->
        <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 text-xs text-center text-gray-400">
            📊 Les statistiques sont calculées en temps réel sur la base des tâches enregistrées dans cet espace.
        </div>
    </div>
</template>
