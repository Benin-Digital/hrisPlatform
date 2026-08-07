<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import {
    BuildingOfficeIcon,
    ClipboardDocumentListIcon,
    CheckCircleIcon,
    BoltIcon,
    ChartBarIcon,
    ArrowLeftIcon,
    ArrowRightIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    entiteCible: {
        type: Object,
        required: true
    },
    stats: {
        type: Object,
        required: true
    },
    utilisateursStats: {
        type: Array,
        default: () => []
    },
    entites: {
        type: Array,
        default: () => []
    }
});

const selectedEntiteId = ref(props.entiteCible.id);

const changerEntite = () => {
    if (selectedEntiteId.value) {
        router.get(route('productivite.entite', selectedEntiteId.value));
    }
};

// Configuration graphique - Performance des utilisateurs
const performanceChartOptions = ref({
    chart: {
        type: 'bar',
        height: 400,
        fontFamily: 'Inter, sans-serif',
        toolbar: {
            show: false
        }
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '55%',
            borderRadius: 8,
        },
    },
    dataLabels: {
        enabled: false
    },
    stroke: {
        show: true,
        width: 2,
        colors: ['transparent']
    },
    xaxis: {
        categories: props.utilisateursStats.map(u => u.nom_complet.split(' ')[0]),
        labels: {
            style: {
                fontSize: '12px',
                fontWeight: 600,
            }
        }
    },
    yaxis: {
        title: {
            text: 'Nombre de tâches',
            style: {
                fontSize: '14px',
                fontWeight: 700,
            }
        },
        labels: {
            style: {
                fontSize: '12px',
                fontWeight: 600,
            }
        }
    },
    fill: {
        opacity: 1
    },
    colors: ['#3b82f6', '#10b981'],
    legend: {
        position: 'top',
        fontSize: '14px',
        fontWeight: 600,
    },
    grid: {
        borderColor: '#e5e7eb',
    }
});

const performanceChartSeries = ref([
    {
        name: 'Assignées',
        data: props.utilisateursStats.map(u => u.taches_assignees)
    },
    {
        name: 'Terminées',
        data: props.utilisateursStats.map(u => u.taches_terminees)
    }
]);
</script>

<template>
    <Head :title="`Productivité - ${entiteCible.nom}`" />

    <AuthenticatedLayout>
        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header with Entity Selector -->
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <Link :href="route('productivite.index')" class="text-indigo-600 hover:text-indigo-700 font-bold mb-2 inline-flex items-center">
                            <ArrowLeftIcon class="w-4 h-4 mr-2" />
                            Retour au tableau de bord
                        </Link>
                        <h1 class="text-4xl font-black text-gray-900 mb-2 flex items-center">
                            <BuildingOfficeIcon class="w-10 h-10 text-indigo-600 mr-3" />
                            {{ entiteCible.nom }}
                        </h1>
                        <p class="text-gray-600 font-medium">Analyse de productivité par entité</p>
                    </div>

                    <!-- Entity Selector (if super admin) -->
                    <div v-if="entites.length > 0" class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Changer d'entité</label>
                        <select 
                            v-model="selectedEntiteId" 
                            @change="changerEntite"
                            class="rounded-xl border-gray-200 font-semibold text-gray-700 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option v-for="entite in entites" :key="entite.id" :value="entite.id">
                                {{ entite.nom }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- KPI Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
                    <!-- Total Tâches -->
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center mb-4">
                            <ClipboardDocumentListIcon class="w-7 h-7 text-indigo-600" />
                        </div>
                        <h3 class="text-3xl font-black text-gray-900 mb-1">{{ stats.total }}</h3>
                        <p class="text-sm text-gray-500 font-medium">Total Tâches</p>
                    </div>

                    <!-- Terminées -->
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition">
                        <div class="w-12 h-12 rounded-2xl bg-green-50 flex items-center justify-center mb-4">
                            <CheckCircleIcon class="w-7 h-7 text-green-600" />
                        </div>
                        <h3 class="text-3xl font-black text-green-600 mb-1">{{ stats.terminees }}</h3>
                        <p class="text-sm text-gray-500 font-medium">Terminées</p>
                    </div>

                    <!-- En Cours -->
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center mb-4">
                            <BoltIcon class="w-7 h-7 text-blue-600" />
                        </div>
                        <h3 class="text-3xl font-black text-blue-600 mb-1">{{ stats.en_cours }}</h3>
                        <p class="text-sm text-gray-500 font-medium">En Cours</p>
                    </div>

                    <!-- Taux de Complétion -->
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center mb-4">
                            <ChartBarIcon class="w-7 h-7 text-emerald-600" />
                        </div>
                        <h3 class="text-3xl font-black text-emerald-600 mb-1">{{ stats.taux_completion }}%</h3>
                        <p class="text-sm text-gray-500 font-medium">Taux de Complétion</p>
                    </div>
                </div>

                <!-- Performance Chart -->
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 mb-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                        <span class="w-2 h-8 bg-indigo-600 rounded-full mr-4"></span>
                        Performance des Utilisateurs
                    </h2>
                    <VueApexCharts 
                        v-if="utilisateursStats.length > 0"
                        type="bar" 
                        :options="performanceChartOptions" 
                        :series="performanceChartSeries"
                        height="400"
                    />
                    <div v-else class="text-center py-10 text-gray-400 italic">
                        Aucune donnée disponible
                    </div>
                </div>

                <!-- Users Table -->
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                        <span class="w-2 h-8 bg-indigo-600 rounded-full mr-4"></span>
                        Détails par Utilisateur
                    </h2>

                    <div v-if="utilisateursStats.length > 0" class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b-2 border-gray-200">
                                    <th class="text-left py-4 px-4 text-sm font-black text-gray-700 uppercase tracking-widest">Utilisateur</th>
                                    <th class="text-center py-4 px-4 text-sm font-black text-gray-700 uppercase tracking-widest">Assignées</th>
                                    <th class="text-center py-4 px-4 text-sm font-black text-gray-700 uppercase tracking-widest">Terminées</th>
                                    <th class="text-center py-4 px-4 text-sm font-black text-gray-700 uppercase tracking-widest">Taux</th>
                                    <th class="text-center py-4 px-4 text-sm font-black text-gray-700 uppercase tracking-widest">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in utilisateursStats" :key="user.id" 
                                    class="border-b border-gray-100 hover:bg-gray-50 transition">
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                                <span class="text-indigo-600 font-black text-sm">
                                                    {{ user.nom_complet.split(' ').map(n => n[0]).join('') }}
                                                </span>
                                            </div>
                                            <span class="font-bold text-gray-900">{{ user.nom_complet }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span class="inline-flex px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-sm font-black">
                                            {{ user.taches_assignees }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span class="inline-flex px-3 py-1 rounded-full bg-green-50 text-green-700 text-sm font-black">
                                            {{ user.taches_terminees }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <div class="w-24 bg-gray-200 rounded-full h-2">
                                                <div class="bg-gradient-to-r from-indigo-500 to-emerald-500 h-2 rounded-full transition-all" 
                                                     :style="{ width: `${user.taux_completion}%` }">
                                                </div>
                                            </div>
                                            <span class="text-sm font-black text-gray-700">{{ user.taux_completion }}%</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <Link :href="route('productivite.utilisateur', user.id)" 
                                              class="inline-flex items-center px-4 py-2 bg-indigo-50 text-indigo-600 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-indigo-100 transition">
                                            Voir détails
                                            <ArrowRightIcon class="w-4 h-4 ml-1.5" />
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="text-center py-10 text-gray-400 italic">
                        Aucun utilisateur dans cette entité
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>