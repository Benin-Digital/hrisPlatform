<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    title: {
        type: String,
        default: 'Suivi des Tâches'
    },
    taskData: {
        type: Object,
        required: true
    }
});

const user = computed(() => usePage().props.auth.user);
const canCreateTask = computed(() => {
    const roles = user.value.roles.map(r => r.nom);
    return roles.some(r => ['super_admin', 'admin_entite', 'responsable_rh', 'manager', 'formateur'].includes(r));
});

const getStatusClass = (statut) => {
    switch (statut) {
        case 'terminee': return 'bg-green-100 text-green-700';
        case 'en_cours': return 'bg-blue-100 text-blue-700';
        case 'en_attente': return 'bg-orange-100 text-orange-700';
        case 'annulee': return 'bg-red-100 text-red-700';
        default: return 'bg-gray-100 text-gray-700';
    }
};

const formatStatut = (statut) => {
    if (!statut) return 'Inconnu';
    return statut.replace('_', ' ').charAt(0).toUpperCase() + statut.replace('_', ' ').slice(1);
};
</script>

<template>
    <div class="mt-10">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">
                {{ title }}
            </h2>
            <Link 
                v-if="canCreateTask"
                :href="route('taches.create')" 
                class="px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-lg hover:bg-indigo-700 transition shadow-md"
            >
                + Nouvelle Tâche
            </Link>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Total</p>
                <div class="flex items-baseline space-x-2">
                    <p class="text-3xl font-black text-gray-900">{{ taskData.stats.total }}</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition border-l-4 border-l-blue-500">
                <p class="text-xs font-bold text-blue-400 uppercase tracking-widest mb-1">En cours</p>
                <p class="text-3xl font-black text-blue-600">{{ taskData.stats.enCours }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition border-l-4 border-l-green-500">
                <p class="text-xs font-bold text-green-400 uppercase tracking-widest mb-1">Terminées</p>
                <p class="text-3xl font-black text-green-600">{{ taskData.stats.terminees }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition border-l-4 border-l-red-500">
                <p class="text-xs font-bold text-red-400 uppercase tracking-widest mb-1">En retard</p>
                <p class="text-3xl font-black text-red-600">{{ taskData.stats.enRetard }}</p>
            </div>
        </div>

        <!-- Recent Tasks List -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                <h3 class="font-bold text-gray-700 text-sm uppercase tracking-wider">Dernières activités</h3>
                <Link :href="route('taches.index')" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">Voir tout l'espace tâches →</Link>
            </div>
            <div class="divide-y divide-gray-50">
                <div v-for="tache in taskData.recents" :key="tache.id" class="p-4 hover:bg-gray-50 transition flex items-center justify-between group">
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold group-hover:scale-110 transition duration-300">
                            {{ tache.titre.charAt(0).toUpperCase() }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <Link :href="route('taches.show', tache.id)" class="font-bold text-gray-900 group-hover:text-indigo-600 transition truncate block">{{ tache.titre }}</Link>
                            <div class="flex items-center space-x-2 mt-1">
                                <div class="flex-1 bg-gray-100 rounded-full h-1.5 min-w-[60px] max-w-[100px] overflow-hidden">
                                    <div 
                                        class="h-full bg-indigo-500 transition-all duration-500"
                                        :style="{ width: (tache.progression_pourcentage ?? 0) + '%' }"
                                    ></div>
                                </div>
                                <span class="text-[10px] font-black text-gray-400">{{ tache.progression_pourcentage ?? 0 }}%</span>
                            </div>
                            <p class="text-[10px] text-gray-400 mt-1">
                                Par {{ tache.createur_prenom_nom }} 
                                <span class="mx-1 text-gray-300">•</span>
                                <span class="text-gray-600">→ {{ tache.assigne_prenom_nom }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest shadow-sm" :class="getStatusClass(tache.statut)">
                            {{ formatStatut(tache.statut) }}
                        </span>
                        <Link :href="route('taches.show', tache.id)" class="p-2 text-gray-300 hover:text-indigo-600 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </Link>
                    </div>
                </div>
                <div v-if="taskData.recents.length === 0" class="p-12 text-center text-gray-400 italic">
                    Aucune tâche à afficher.
                </div>
            </div>
        </div>
    </div>
</template>
