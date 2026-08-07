<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    formations: {
        type: Object, // Paginator object with data, links, etc.
        default: () => ({ data: [], links: [] })
    },
    categories: {
        type: Array,
        default: () => []
    },
    canCreate: {
        type: Boolean,
        default: false
    },
    canManage: {
        type: Boolean,
        default: false
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    error: {
        type: String,
        default: null
    }
});

// État des filtres
const search = ref(props.filters.search || '');
const categorie = ref(props.filters.categorie || '');
const niveau = ref(props.filters.niveau || '');
const duree_min = ref(props.filters.duree_min || '');
const duree_max = ref(props.filters.duree_max || '');
const mode_acces = ref(props.filters.mode_acces || '');

let timeout = null;

const applyFilters = () => {
    router.get(route('formations.index'), {
        search: search.value,
        categorie: categorie.value,
        niveau: niveau.value,
        duree_min: duree_min.value,
        duree_max: duree_max.value,
        mode_acces: mode_acces.value,
    }, { preserveState: true });
};

const resetFilters = () => {
    search.value = '';
    categorie.value = '';
    niveau.value = '';
    duree_min.value = '';
    duree_max.value = '';
    mode_acces.value = '';
    applyFilters();
};

const onSearch = () => {
    clearTimeout(timeout);
    timeout = setTimeout(applyFilters, 300);
};

const deleteFormation = (id) => {
    if (confirm('Voulez-vous vraiment supprimer cette formation ?')) {
        router.delete(route('formations.destroy', id));
    }
};
</script>

<template>
    <Head title="Catalogue des Formations" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center w-full">
                <h2 class="font-bold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    Formations & E-Learning
                </h2>
                <Link 
                    v-if="canCreate"
                    :href="route('formations.create')" 
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 transition"
                >
                    + Nouvelle formation
                </Link>
            </div>
        </template>

        <div class="py-6 md:py-10">
            <div class="page-container">
                <!-- Filtres -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 mb-8 border border-gray-200 dark:border-gray-700">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                        <div>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Rechercher..."
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                @input="onSearch"
                            />
                        </div>
                        <div>
                            <select v-model="categorie" @change="applyFilters" class="w-full px-4 py-2 border rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="">Toutes catégories</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.nom }}</option>
                            </select>
                        </div>
                        <div>
                            <select v-model="niveau" @change="applyFilters" class="w-full px-4 py-2 border rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="">Tous niveaux</option>
                                <option value="debutant">Débutant</option>
                                <option value="intermediaire">Intermédiaire</option>
                                <option value="avance">Avancé</option>
                                <option value="expert">Expert</option>
                            </select>
                        </div>
                        <div>
                            <select v-model="mode_acces" @change="applyFilters" class="w-full px-4 py-2 border rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="">Tous modes</option>
                                <option value="interne">Interne</option>
                                <option value="externe">Externe</option>
                                <option value="mixte">Mixte</option>
                            </select>
                        </div>
                        <div>
                            <input
                                v-model="duree_min"
                                type="number"
                                placeholder="Durée min (min)"
                                class="w-full px-4 py-2 border rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                @input="applyFilters"
                            />
                        </div>
                        <div>
                            <input
                                v-model="duree_max"
                                type="number"
                                placeholder="Durée max (min)"
                                class="w-full px-4 py-2 border rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                @input="applyFilters"
                            />
                        </div>
                    </div>
                    <div class="mt-4 text-right">
                        <button @click="resetFilters" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            Réinitialiser
                        </button>
                    </div>
                </div>

                <!-- Error Message -->
                <div v-if="error" class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl flex items-center gap-3">
                    <span class="text-xl">⚠️</span>
                    <span class="font-medium">Erreur: {{ error }}</span>
                </div>

                <!-- Empty State -->
                <div v-if="!formations.data || formations.data.length === 0" class="bg-white dark:bg-gray-800 rounded-3xl border-2 border-dashed border-gray-200 dark:border-gray-700 p-12 md:p-20 text-center">
                    <div class="text-7xl mb-6 opacity-40 grayscale">📚</div>
                    <h3 class="text-xl font-bold text-gray-800 dark:text-gray-200 mb-2">Aucune formation disponible</h3>
                    <p class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto mb-8">Les modules de formation n'ont pas encore été publiés pour votre profil.</p>
                    <Link 
                        v-if="canCreate"
                        :href="route('formations.create')" 
                        class="px-6 py-3 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700 transition"
                    >
                        Créer la première formation
                    </Link>
                </div>

                <!-- Grid des formations -->
                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                    <div 
                        v-for="formation in formations.data" 
                        :key="formation.id" 
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition group"
                    >
                        <!-- Image / Cover -->
                        <div class="relative h-48 md:h-56 bg-gradient-to-br from-indigo-500 to-purple-700 overflow-hidden">
                            <img 
                                v-if="formation.image_couverture" 
                                :src="'/storage/' + formation.image_couverture" 
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <span class="text-white text-4xl font-black opacity-20 italic uppercase tracking-tighter">HRIS LEARN</span>
                            </div>

                            <!-- Badges -->
                            <div class="absolute top-4 left-4">
                                <span class="px-3 py-1 bg-white/20 backdrop-blur-md text-white text-[10px] font-black uppercase tracking-widest rounded-full border border-white/20">
                                    {{ formation.categorie?.nom || 'Général' }}
                                </span>
                            </div>
                            <div class="absolute top-4 right-4">
                                <span 
                                    :class="{
                                        'bg-green-500': formation.statut === 'publie',
                                        'bg-yellow-500': formation.statut === 'brouillon',
                                        'bg-gray-800': formation.statut === 'archive'
                                    }"
                                    class="px-3 py-1 text-white text-[10px] font-black uppercase tracking-tighter rounded-lg shadow-lg border border-white/20"
                                >
                                    {{ formation.statut === 'publie' ? 'Live' : formation.statut }}
                                </span>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-6 md:p-8 flex-1 flex flex-col">
                            <div class="flex items-center text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest mb-3">
                                <span class="bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 rounded-md mr-2">{{ formation.niveau }}</span>
                                <span>• {{ formation.duree_minutes }} MIN</span>
                            </div>
                            
                            <h3 class="text-lg md:text-xl font-extrabold text-gray-900 dark:text-gray-100 leading-tight mb-3 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                <Link :href="route('formations.show', formation.id)" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                    {{ formation.titre }}
                                </Link>
                            </h3>

                            <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 mb-6 font-medium leading-relaxed">
                                {{ formation.sous_titre || formation.description }}
                            </p>

                            <!-- Infos utilisateur -->
                            <div class="mt-auto pt-6 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-[10px] font-black text-gray-400 dark:text-gray-300 mr-2 border border-gray-100 dark:border-gray-600">
                                        {{ formation.formateur?.prenom?.charAt(0) }}{{ formation.formateur?.nom?.charAt(0) }}
                                    </div>
                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300 truncate max-w-[120px]">{{ formation.formateur?.prenom }} {{ formation.formateur?.nom }}</span>
                                </div>
                                
                                <span v-if="formation.mode_acces === 'externe'" class="px-2 py-1 bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 rounded-full text-[9px] font-bold uppercase">
                                    Externe
                                </span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="p-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-100 dark:border-gray-600 grid grid-cols-2 gap-2">
                            <Link 
                                :href="route('formations.show', formation.id)"
                                class="col-span-2 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 text-gray-900 dark:text-gray-100 rounded-xl font-bold text-xs uppercase tracking-widest text-center hover:bg-gray-900 hover:text-white dark:hover:bg-gray-600 transition shadow-sm"
                            >
                                Consulter
                            </Link>
                            
                            <template v-if="canManage">
                                <Link 
                                    :href="route('formations.edit', formation.id)"
                                    class="py-2.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 rounded-xl font-bold text-[10px] uppercase tracking-widest text-center hover:bg-indigo-600 hover:text-white transition"
                                >
                                    Éditer
                                </Link>
                                <button 
                                    @click="deleteFormation(formation.id)"
                                    class="py-2.5 bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-xl font-bold text-[10px] uppercase tracking-widest text-center hover:bg-red-600 hover:text-white transition"
                                >
                                    Supprim.
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="formations.links && formations.links.length > 3" class="mt-8">
                    <div class="flex justify-center space-x-2">
                        <Link
                            v-for="link in formations.links"
                            :key="link.label"
                            :href="link.url"
                            v-html="link.label"
                            :class="{
                                'px-4 py-2 rounded bg-indigo-600 text-white': link.active,
                                'px-4 py-2 rounded bg-gray-200 dark:bg-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500': !link.active && link.url,
                                'px-4 py-2 text-gray-500 dark:text-gray-400 cursor-not-allowed': !link.url,
                            }"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>