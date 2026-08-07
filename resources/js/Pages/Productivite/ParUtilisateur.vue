<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    UserCircleIcon,
    ClipboardDocumentListIcon,
    PencilSquareIcon,
    CheckCircleIcon,
    BoltIcon,
    ChartBarIcon,
    ClockIcon,
    ArrowLeftIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    utilisateurCible: {
        type: Object,
        required: true
    },
    stats: {
        type: Object,
        required: true
    },
    tachesRecentes: {
        type: Array,
        default: () => []
    },
    utilisateurs: {
        type: Array,
        default: () => []
    }
});

const selectedUserId = ref(props.utilisateurCible.id);

const changerUtilisateur = () => {
    if (selectedUserId.value) {
        router.get(route('productivite.utilisateur', selectedUserId.value));
    }
};

const getStatutBadgeClass = (statut) => {
    const classes = {
        'en_attente': 'bg-yellow-100 text-yellow-700',
        'en_cours': 'bg-blue-100 text-blue-700',
        'terminee': 'bg-green-100 text-green-700',
        'annulee': 'bg-gray-100 text-gray-700',
    };
    return classes[statut] || 'bg-gray-100 text-gray-700';
};

const getPrioriteBadgeClass = (priorite) => {
    const classes = {
        'basse': 'bg-gray-100 text-gray-600',
        'moyenne': 'bg-orange-100 text-orange-600',
        'haute': 'bg-red-100 text-red-600',
    };
    return classes[priorite] || 'bg-gray-100 text-gray-600';
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
};
</script>

<template>
    <Head :title="`Productivité - ${utilisateurCible.prenom} ${utilisateurCible.nom}`" />

    <AuthenticatedLayout>
        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header with User Selector -->
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <Link :href="route('productivite.index')" class="text-indigo-600 hover:text-indigo-700 font-bold mb-2 inline-flex items-center">
                            <ArrowLeftIcon class="w-4 h-4 mr-2" />
                            Retour au tableau de bord
                        </Link>
                        <h1 class="text-4xl font-black text-gray-900 mb-2 flex items-center">
                            <UserCircleIcon class="w-10 h-10 text-indigo-600 mr-3" />
                            {{ utilisateurCible.prenom }} {{ utilisateurCible.nom }}
                        </h1>
                        <p class="text-gray-600 font-medium">Analyse de productivité individuelle</p>
                    </div>

                    <!-- User Selector -->
                    <div v-if="utilisateurs.length > 0" class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Changer d'utilisateur</label>
                        <select 
                            v-model="selectedUserId" 
                            @change="changerUtilisateur"
                            class="rounded-xl border-gray-200 font-semibold text-gray-700 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option v-for="user in utilisateurs" :key="user.id" :value="user.id">
                                {{ user.prenom }} {{ user.nom }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- KPI Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-6 mb-12">
                    <!-- Tâches Assignées -->
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center mb-3">
                            <ClipboardDocumentListIcon class="w-6 h-6 text-indigo-600" />
                        </div>
                        <h3 class="text-2xl font-black text-gray-900 mb-1">{{ stats.assignees }}</h3>
                        <p class="text-xs text-gray-500 font-bold uppercase tracking-widest">Assignées</p>
                    </div>

                    <!-- Tâches Créées -->
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center mb-3">
                            <PencilSquareIcon class="w-6 h-6 text-purple-600" />
                        </div>
                        <h3 class="text-2xl font-black text-gray-900 mb-1">{{ stats.creees }}</h3>
                        <p class="text-xs text-gray-500 font-bold uppercase tracking-widest">Créées</p>
                    </div>

                    <!-- Terminées -->
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition">
                        <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center mb-3">
                            <CheckCircleIcon class="w-6 h-6 text-green-600" />
                        </div>
                        <h3 class="text-2xl font-black text-green-600 mb-1">{{ stats.terminees }}</h3>
                        <p class="text-xs text-gray-500 font-bold uppercase tracking-widest">Terminées</p>
                    </div>

                    <!-- En Cours -->
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center mb-3">
                            <BoltIcon class="w-6 h-6 text-blue-600" />
                        </div>
                        <h3 class="text-2xl font-black text-blue-600 mb-1">{{ stats.en_cours }}</h3>
                        <p class="text-xs text-gray-500 font-bold uppercase tracking-widest">En Cours</p>
                    </div>

                    <!-- Taux de Complétion -->
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center mb-3">
                            <ChartBarIcon class="w-6 h-6 text-emerald-600" />
                        </div>
                        <h3 class="text-2xl font-black text-emerald-600 mb-1">{{ stats.taux_completion }}%</h3>
                        <p class="text-xs text-gray-500 font-bold uppercase tracking-widest">Taux</p>
                    </div>

                    <!-- Temps Moyen -->
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition">
                        <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center mb-3">
                            <ClockIcon class="w-6 h-6 text-orange-600" />
                        </div>
                        <h3 class="text-2xl font-black text-orange-600 mb-1">{{ stats.temps_moyen_jours }}</h3>
                        <p class="text-xs text-gray-500 font-bold uppercase tracking-widest">Jours/Tâche</p>
                    </div>
                </div>

                <!-- Tâches Récentes -->
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                        <span class="w-2 h-8 bg-indigo-600 rounded-full mr-4"></span>
                        Tâches Récentes
                    </h2>

                    <div v-if="tachesRecentes.length > 0" class="space-y-4">
                        <div v-for="tache in tachesRecentes" :key="tache.id" 
                             class="border border-gray-100 rounded-2xl p-6 hover:shadow-md transition">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <Link :href="route('taches.show', tache.id)" 
                                          class="text-lg font-bold text-gray-900 hover:text-indigo-600 transition">
                                        {{ tache.titre }}
                                    </Link>
                                    <p v-if="tache.description" class="text-sm text-gray-600 mt-1 line-clamp-2">
                                        {{ tache.description }}
                                    </p>
                                </div>
                                <div class="flex flex-col items-end gap-2 ml-4">
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-black uppercase tracking-widest"
                                          :class="getStatutBadgeClass(tache.statut)">
                                        {{ tache.statut.replace('_', ' ') }}
                                    </span>
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-black uppercase tracking-widest"
                                          :class="getPrioriteBadgeClass(tache.priorite)">
                                        {{ tache.priorite }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-6 text-sm text-gray-500">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold">Créée par:</span>
                                    <span>{{ tache.createur?.prenom }} {{ tache.createur?.nom }}</span>
                                </div>
                                <div v-if="tache.date_echeance" class="flex items-center gap-2">
                                    <span class="font-bold">Échéance:</span>
                                    <span>{{ formatDate(tache.date_echeance) }}</span>
                                </div>
                                <div v-if="tache.entite" class="flex items-center gap-2">
                                    <span class="font-bold">Entité:</span>
                                    <span>{{ tache.entite.nom }}</span>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div class="mt-4">
                                <div class="flex items-center justify-between text-xs font-bold text-gray-600 mb-1">
                                    <span>Progression</span>
                                    <span>{{ tache.progression_pourcentage || 0 }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-indigo-600 h-2 rounded-full transition-all" 
                                         :style="{ width: `${tache.progression_pourcentage || 0}%` }">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-10 text-gray-400 italic">
                        Aucune tâche récente
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>