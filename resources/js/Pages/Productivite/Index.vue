<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import {
    ChartBarIcon,
    ClipboardDocumentListIcon,
    CheckCircleIcon,
    BoltIcon,
    ExclamationTriangleIcon,
    UserCircleIcon,
    BuildingOfficeIcon,
    ArrowRightIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    stats: {
        type: Object,
        required: true
    },
    repartitionPriorite: {
        type: Object,
        default: () => ({})
    },
    repartitionStatut: {
        type: Object,
        required: true
    },
    topUtilisateurs: {
        type: Array,
        default: () => []
    },
    evolutionSemaine: {
        type: Array,
        default: () => []
    },
    tachesParEntite: {
        type: Array,
        default: () => []
    },
    isManager: {
        type: Boolean,
        default: false
    }
});

// Configuration graphique - Répartition par statut (Donut)
const statutChartOptions = ref({
    chart: {
        type: 'donut',
        fontFamily: 'Inter, sans-serif',
    },
    labels: ['En Attente', 'En Cours', 'Terminées', 'Annulées'],
    colors: ['#fbbf24', '#3b82f6', '#10b981', '#6b7280'],
    legend: {
        position: 'bottom',
        fontSize: '14px',
        fontWeight: 600,
    },
    plotOptions: {
        pie: {
            donut: {
                size: '70%',
                labels: {
                    show: true,
                    name: {
                        show: true,
                        fontSize: '16px',
                        fontWeight: 700,
                    },
                    value: {
                        show: true,
                        fontSize: '24px',
                        fontWeight: 900,
                    },
                    total: {
                        show: true,
                        label: 'Total',
                        fontSize: '14px',
                        fontWeight: 600,
                        formatter: () => props.stats.total
                    }
                }
            }
        }
    },
    dataLabels: {
        enabled: true,
        style: {
            fontSize: '12px',
            fontWeight: 'bold',
        }
    },
    responsive: [{
        breakpoint: 480,
        options: {
            chart: {
                width: 300
            },
            legend: {
                position: 'bottom'
            }
        }
    }]
});

const statutChartSeries = computed(() => [
    props.repartitionStatut.en_attente || 0,
    props.repartitionStatut.en_cours || 0,
    props.repartitionStatut.terminee || 0,
    props.repartitionStatut.annulee || 0,
]);

// Configuration graphique - Évolution hebdomadaire (Line)
const evolutionChartOptions = ref({
    chart: {
        type: 'line',
        height: 350,
        fontFamily: 'Inter, sans-serif',
        toolbar: {
            show: false
        },
        zoom: {
            enabled: false
        }
    },
    colors: ['#10b981', '#3b82f6'],
    stroke: {
        width: [3, 3],
        curve: 'smooth'
    },
    xaxis: {
        categories: props.evolutionSemaine.map(e => e.date),
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
    legend: {
        position: 'top',
        fontSize: '14px',
        fontWeight: 600,
    },
    dataLabels: {
        enabled: false
    },
    grid: {
        borderColor: '#e5e7eb',
    }
});

const evolutionChartSeries = computed(() => [
    {
        name: 'Terminées',
        data: props.evolutionSemaine.map(e => e.terminees)
    },
    {
        name: 'Créées',
        data: props.evolutionSemaine.map(e => e.creees)
    }
]);

// Configuration graphique - Top utilisateurs (Bar)
const topUsersChartOptions = ref({
    chart: {
        type: 'bar',
        height: 350,
        fontFamily: 'Inter, sans-serif',
        toolbar: {
            show: false
        }
    },
    plotOptions: {
        bar: {
            horizontal: true,
            borderRadius: 8,
            dataLabels: {
                position: 'top',
            },
        }
    },
    colors: ['#4f46e5'],
    dataLabels: {
        enabled: true,
        offsetX: 30,
        style: {
            fontSize: '12px',
            fontWeight: 'bold',
            colors: ['#4f46e5']
        }
    },
    xaxis: {
        categories: props.topUtilisateurs.map(u => `${u.prenom} ${u.nom}`),
        labels: {
            style: {
                fontSize: '12px',
                fontWeight: 600,
            }
        }
    },
    yaxis: {
        labels: {
            style: {
                fontSize: '12px',
                fontWeight: 600,
            }
        }
    },
    grid: {
        borderColor: '#e5e7eb',
    }
});

const topUsersChartSeries = computed(() => [{
    name: 'Tâches terminées',
    data: props.topUtilisateurs.map(u => u.total_terminees)
}]);
</script>

<template>
    <Head title="Analyse de Productivité" />

    <AuthenticatedLayout>
        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8 flex items-start">
                    <ChartBarIcon class="w-10 h-10 text-indigo-600 mr-4 flex-shrink-0 mt-1" />
                    <div>
                        <h1 class="text-4xl font-black text-gray-900 mb-2">Analyse de Productivité</h1>
                        <p class="text-gray-600 font-medium">Vue d'ensemble des performances et statistiques des tâches</p>
                    </div>
                </div>

                <!-- KPI Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                    <!-- Total Tâches -->
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center">
                                <ClipboardDocumentListIcon class="w-7 h-7 text-indigo-600" />
                            </div>
                            <span class="text-xs font-black text-gray-400 uppercase tracking-widest">Total</span>
                        </div>
                        <h3 class="text-3xl font-black text-gray-900 mb-1">{{ stats.total }}</h3>
                        <p class="text-sm text-gray-500 font-medium">Tâches au total</p>
                    </div>

                    <!-- Taux de Complétion -->
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-2xl bg-green-50 flex items-center justify-center">
                                <CheckCircleIcon class="w-7 h-7 text-green-600" />
                            </div>
                            <span class="text-xs font-black text-gray-400 uppercase tracking-widest">Complétion</span>
                        </div>
                        <h3 class="text-3xl font-black text-green-600 mb-1">{{ stats.taux_completion }}%</h3>
                        <p class="text-sm text-gray-500 font-medium">{{ stats.terminees }} terminées</p>
                    </div>

                    <!-- En Cours -->
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center">
                                <BoltIcon class="w-7 h-7 text-blue-600" />
                            </div>
                            <span class="text-xs font-black text-gray-400 uppercase tracking-widest">En Cours</span>
                        </div>
                        <h3 class="text-3xl font-black text-blue-600 mb-1">{{ stats.en_cours }}</h3>
                        <p class="text-sm text-gray-500 font-medium">Tâches actives</p>
                    </div>

                    <!-- En Retard -->
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-2xl bg-red-50 flex items-center justify-center">
                                <ExclamationTriangleIcon class="w-7 h-7 text-red-600" />
                            </div>
                            <span class="text-xs font-black text-gray-400 uppercase tracking-widest">En Retard</span>
                        </div>
                        <h3 class="text-3xl font-black text-red-600 mb-1">{{ stats.en_retard }}</h3>
                        <p class="text-sm text-gray-500 font-medium">Nécessitent attention</p>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Répartition par Statut -->
                    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                            <span class="w-2 h-8 bg-indigo-600 rounded-full mr-4"></span>
                            Répartition par Statut
                        </h2>
                        <VueApexCharts 
                            type="donut" 
                            :options="statutChartOptions" 
                            :series="statutChartSeries"
                            height="350"
                        />
                    </div>

                    <!-- Évolution Hebdomadaire -->
                    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                            <span class="w-2 h-8 bg-indigo-600 rounded-full mr-4"></span>
                            Évolution (7 derniers jours)
                        </h2>
                        <VueApexCharts 
                            type="line" 
                            :options="evolutionChartOptions" 
                            :series="evolutionChartSeries"
                            height="350"
                        />
                    </div>
                </div>

                <!-- Top Utilisateurs -->
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 mb-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                        <span class="w-2 h-8 bg-indigo-600 rounded-full mr-4"></span>
                        Top 5 Utilisateurs les Plus Productifs
                    </h2>
                    <VueApexCharts 
                        v-if="topUtilisateurs.length > 0"
                        type="bar" 
                        :options="topUsersChartOptions" 
                        :series="topUsersChartSeries"
                        height="350"
                    />
                    <div v-else class="text-center py-10 text-gray-400 italic">
                        Aucune donnée disponible
                    </div>
                </div>

                <!-- Tâches par Entité (si super admin) -->
                <div v-if="tachesParEntite.length > 0" class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                        <span class="w-2 h-8 bg-indigo-600 rounded-full mr-4"></span>
                        Performance par Entité
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-3 px-4 text-sm font-black text-gray-700 uppercase tracking-widest">Entité</th>
                                    <th class="text-center py-3 px-4 text-sm font-black text-gray-700 uppercase tracking-widest">Total</th>
                                    <th class="text-center py-3 px-4 text-sm font-black text-gray-700 uppercase tracking-widest">Terminées</th>
                                    <th class="text-center py-3 px-4 text-sm font-black text-gray-700 uppercase tracking-widest">Taux</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="entite in tachesParEntite" :key="entite.nom" class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-4 px-4 font-bold text-gray-900">{{ entite.nom }}</td>
                                    <td class="py-4 px-4 text-center font-semibold text-gray-700">{{ entite.total }}</td>
                                    <td class="py-4 px-4 text-center font-semibold text-green-600">{{ entite.terminees }}</td>
                                    <td class="py-4 px-4 text-center">
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-black"
                                              :class="entite.total > 0 && (entite.terminees / entite.total * 100) >= 70 ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'">
                                            {{ entite.total > 0 ? Math.round((entite.terminees / entite.total) * 100) : 0 }}%
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Quick Links -->
                <div v-if="isManager" class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                    <Link :href="route('productivite.utilisateur')" 
                          class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-3xl p-8 text-white hover:shadow-xl transition transform hover:-translate-y-1 flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-black mb-2 flex items-center">
                                <UserCircleIcon class="w-8 h-8 mr-3" />
                                Analyse par Utilisateur
                            </h3>
                            <p class="text-indigo-100 font-medium">Consultez les performances individuelles</p>
                        </div>
                        <ArrowRightIcon class="w-8 h-8 text-white/80" />
                    </Link>

                    <Link :href="route('productivite.entite')" 
                          class="bg-gradient-to-r from-blue-500 to-cyan-600 rounded-3xl p-8 text-white hover:shadow-xl transition transform hover:-translate-y-1 flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-black mb-2 flex items-center">
                                <BuildingOfficeIcon class="w-8 h-8 mr-3" />
                                Analyse par Entité
                            </h3>
                            <p class="text-blue-100 font-medium">Comparez les départements</p>
                        </div>
                        <ArrowRightIcon class="w-8 h-8 text-white/80" />
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>