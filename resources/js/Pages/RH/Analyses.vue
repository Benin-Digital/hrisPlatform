<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import {
    ChartBarIcon,
    DocumentTextIcon,
    TableCellsIcon,
    ArrowLeftIcon,
    UsersIcon,
    UserGroupIcon,
    UserPlusIcon,
    ShieldCheckIcon,
    ClockIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    stats: {
        type: Object,
        required: true,
        default: () => ({
            total: 0,
            actifs: 0,
            taux_activite: 0,
            nouveaux_mois: 0,
        })
    },
    repartitionStatut: {
        type: Object,
        default: () => ({ actif: 0, inactif: 0, suspendu: 0, conges: 0 })
    },
    repartitionType: {
        type: Object,
        default: () => ({ interne: 0, externe: 0 })
    },
    evolutionRecrutement: {
        type: Array,
        default: () => []
    },
    repartitionEntite: {
        type: Array,
        default: () => []
    },
    dernieresRecrues: {
        type: Array,
        default: () => []
    },
});

// Chart - Statut (Donut)
const statutChartOptions = ref({
    chart: { type: 'donut', fontFamily: 'Inter, sans-serif' },
    labels: ['Actif', 'Inactif', 'Suspendu', 'Congés'],
    colors: ['#10b981', '#ef4444', '#f59e0b', '#3b82f6'],
    legend: { position: 'bottom' },
    plotOptions: {
        pie: {
            donut: {
                size: '75%',
                labels: {
                    show: true,
                    total: {
                        show: true,
                        label: 'Utilisateurs',
                        formatter: () => props.stats.total || 0
                    }
                }
            }
        }
    }
});

const statutSeries = computed(() => [
    props.repartitionStatut.actif || 0,
    props.repartitionStatut.inactif || 0,
    props.repartitionStatut.suspendu || 0,
    props.repartitionStatut.conges || 0,
]);

// Chart - Evolution Recrutement (Area)
const evolutionChartOptions = ref({
    chart: { type: 'area', toolbar: { show: false }, zoom: { enabled: false } },
    colors: ['#6366f1'],
    fill: {
        type: 'gradient',
        gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.3 }
    },
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 3 },
    xaxis: { categories: props.evolutionRecrutement.map(e => e.mois) },
    grid: { borderColor: '#f1f5f9' }
});

const evolutionSeries = computed(() => [{
    name: 'Nouveaux collaborateurs',
    data: props.evolutionRecrutement.map(e => e.total)
}]);

// Chart - Type (Bar)
const typeChartOptions = ref({
    chart: { type: 'bar', toolbar: { show: false } },
    plotOptions: { bar: { borderRadius: 8, horizontal: true } },
    colors: ['#4f46e5'],
    xaxis: { categories: ['Interne', 'Externe'] },
});

const typeSeries = computed(() => [{
    name: 'Total',
    data: [props.repartitionType.interne || 0, props.repartitionType.externe || 0]
}]);

// Exports
const exportCsv = () => {
    window.open(route('rh.analyses.export-csv'), '_blank');
};

const exportPdf = () => {
    window.open(route('rh.analyses.export-pdf'), '_blank');
};

const exportExcel = () => {
    window.open(route('rh.analyses.export-excel'), '_blank');
};
</script>

<template>
    <Head title="Rapports RH" />

    <AuthenticatedLayout>
        <div class="py-12 bg-gray-50 min-h-screen dark:bg-gray-900">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-wrap justify-between items-center mb-10 gap-4">
                    <div class="flex items-center">
                        <ChartBarIcon class="w-10 h-10 text-indigo-600 dark:text-indigo-400 mr-4" />
                        <div>
                            <h1 class="text-4xl font-black text-gray-900 dark:text-gray-100 tracking-tight mb-1">Rapports RH</h1>
                            <p class="text-gray-500 dark:text-gray-400 font-medium">Analyse globale des effectifs et de la structure</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button @click="exportCsv" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-bold inline-flex items-center gap-2">
                            <DocumentTextIcon class="w-5 h-5" />
                            CSV
                        </button>
                        <button @click="exportPdf" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-bold inline-flex items-center gap-2">
                            <DocumentTextIcon class="w-5 h-5" />
                            PDF
                        </button>
                        <button @click="exportExcel" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-bold inline-flex items-center gap-2">
                            <TableCellsIcon class="w-5 h-5" />
                            Excel
                        </button>
                        <Link :href="route('rh.dashboard')" class="px-6 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-bold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition shadow-sm inline-flex items-center gap-2">
                            <ArrowLeftIcon class="w-5 h-5" />
                            Dashboard
                        </Link>
                    </div>
                </div>

                <!-- KPI Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center flex-shrink-0">
                            <UsersIcon class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                        </div>
                        <div>
                            <div class="text-gray-400 dark:text-gray-500 text-xs font-black uppercase tracking-widest">Total Effectif</div>
                            <div class="text-3xl font-black text-indigo-600 dark:text-indigo-400">{{ stats.total ?? 0 }}</div>
                            <div class="mt-1 text-sm text-gray-500 dark:text-gray-400 font-bold">Collaborateurs</div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-green-50 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0">
                            <UserGroupIcon class="w-6 h-6 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <div class="text-gray-400 dark:text-gray-500 text-xs font-black uppercase tracking-widest">Actifs</div>
                            <div class="text-3xl font-black text-green-600 dark:text-green-400">{{ stats.actifs ?? 0 }}</div>
                            <div class="mt-1 text-sm text-green-600/70 dark:text-green-400/70 font-bold">{{ stats.taux_activite ?? 0 }}% de l'effectif</div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                            <UserPlusIcon class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <div class="text-gray-400 dark:text-gray-500 text-xs font-black uppercase tracking-widest">Nouveaux (Mois)</div>
                            <div class="text-3xl font-black text-blue-600 dark:text-blue-400">{{ stats.nouveaux_mois ?? 0 }}</div>
                            <div class="mt-1 text-sm text-gray-500 dark:text-gray-400 font-bold">Arrivées récentes</div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center flex-shrink-0">
                            <ShieldCheckIcon class="w-6 h-6 text-amber-500 dark:text-amber-400" />
                        </div>
                        <div>
                            <div class="text-gray-400 dark:text-gray-500 text-xs font-black uppercase tracking-widest">Stabilité</div>
                            <div class="text-3xl font-black text-amber-500 dark:text-amber-400">98%</div>
                            <div class="mt-1 text-sm text-gray-500 dark:text-gray-400 font-bold">Indice de rétention</div>
                        </div>
                    </div>
                </div>

                <!-- Charts row 1 -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                    <div class="bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 lg:col-span-1">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-6">Répartition par Statut</h3>
                        <VueApexCharts type="donut" height="300" :options="statutChartOptions" :series="statutSeries" />
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 lg:col-span-2">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-6">Évolution des Recrutements (12 mois)</h3>
                        <VueApexCharts type="area" height="300" :options="evolutionChartOptions" :series="evolutionSeries" />
                    </div>
                </div>

                <!-- Charts row 2 -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
                    <div class="bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-6">Type de Collaborateurs</h3>
                        <VueApexCharts type="bar" height="250" :options="typeChartOptions" :series="typeSeries" />
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                            <ClockIcon class="w-6 h-6 text-gray-500" />
                            Dernières Recrues
                        </h3>
                        <div class="space-y-4">
                            <div v-for="recrue in dernieresRecrues" :key="recrue.id" class="flex items-center gap-4 p-3 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-2xl transition">
                                <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-xl flex items-center justify-center font-black text-xs">
                                    {{ recrue.prenom?.[0] }}{{ recrue.nom?.[0] }}
                                </div>
                                <div class="flex-grow">
                                    <div class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ recrue.prenom }} {{ recrue.nom }}</div>
                                    <div class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider">{{ recrue.entite?.nom || 'Externe' }} • {{ recrue.poste || 'Poste non défini' }}</div>
                                </div>
                                <div class="text-[10px] font-bold text-gray-400 dark:text-gray-500">
                                    {{ new Date(recrue.created_at).toLocaleDateString() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Entités -->
                <div v-if="repartitionEntite && repartitionEntite.length > 0" class="bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-6">Répartition par Entité</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left border-b border-gray-100 dark:border-gray-700">
                                    <th class="pb-4 text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Entité</th>
                                    <th class="pb-4 text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest text-center">Effectif</th>
                                    <th class="pb-4 text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="entite in repartitionEntite" :key="entite.id" class="border-b border-gray-50 dark:border-gray-700 last:border-0 hover:bg-gray-50/50 dark:hover:bg-gray-700/50 transition">
                                    <td class="py-4 font-bold text-gray-900 dark:text-gray-100">{{ entite.nom }}</td>
                                    <td class="py-4 text-center font-black text-indigo-600 dark:text-indigo-400">{{ entite.total }}</td>
                                    <td class="py-4 text-right">
                                        <Link :href="route('collaborateurs.index', { entite_id: entite.id })" class="text-xs font-bold text-indigo-500 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 inline-flex items-center gap-1">
                                            Voir tout
                                            <ArrowLeftIcon class="w-3 h-3 rotate-180" />
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>