<template>
    <Head title="Offres d'emploi" />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h1 class="text-4xl font-extrabold text-gray-900 dark:text-gray-100 flex items-center justify-center gap-3">
                    <!-- Icône Briefcase -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 text-indigo-600 dark:text-indigo-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.114 48.114 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z" />
                    </svg>
                    Offres d'emploi et de stage
                </h1>
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                    Rejoignez notre équipe et construisons ensemble l'avenir
                </p>
            </div>

            <!-- Filtres -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 mb-8 border border-gray-200 dark:border-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Rechercher un poste..."
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                            @input="applyFilters"
                        />
                    </div>
                    <div>
                        <select v-model="filters.type" @change="applyFilters" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                            <option value="">Tous les contrats</option>
                            <option value="CDI">CDI</option>
                            <option value="CDD">CDD</option>
                            <option value="Stage">Stage</option>
                            <option value="Alternance">Alternance</option>
                            <option value="Freelance">Freelance</option>
                        </select>
                    </div>
                    <div>
                        <input
                            v-model="filters.lieu"
                            type="text"
                            placeholder="Lieu..."
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                            @input="applyFilters"
                        />
                    </div>
                    <div class="flex items-end">
                        <button @click="resetFilters" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            Réinitialiser
                        </button>
                    </div>
                </div>
            </div>

            <!-- Liste des offres -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="offre in offres.data"
                    :key="offre.id"
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-lg transition group"
                >
                    <div class="flex justify-between items-start">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition">
                            {{ offre.titre }}
                        </h3>
                        <span class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-full text-xs font-bold">
                            {{ offre.type_contrat }}
                        </span>
                    </div>

                    <div class="mt-3 space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <p class="flex items-center gap-2">
                            <!-- Icône MapPin -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-indigo-500 dark:text-indigo-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                            </svg>
                            <span class="font-medium">Lieu :</span> {{ offre.lieu || 'Non spécifié' }}
                        </p>
                        <p class="flex items-center gap-2">
                            <!-- Icône Calendar -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-indigo-500 dark:text-indigo-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                            <span class="font-medium">Publiée le :</span> {{ new Date(offre.created_at).toLocaleDateString() }}
                        </p>
                        <p v-if="offre.date_expiration" class="flex items-center gap-2 text-red-500 dark:text-red-400 font-medium">
                            <!-- Icône Clock -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-red-500 dark:text-red-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Expire le {{ new Date(offre.date_expiration).toLocaleDateString() }}
                        </p>
                    </div>

                    <p class="mt-4 text-sm text-gray-600 dark:text-gray-400 line-clamp-3">
                        {{ offre.description }}
                    </p>

                    <div class="mt-6 flex justify-between items-center">
                        <Link
                            :href="route('offres.public.show', offre.id)"
                            class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline text-sm flex items-center gap-1"
                        >
                            Voir l'offre
                            <!-- Icône ArrowRight -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </Link>
                        <span v-if="offre.is_published" class="flex items-center gap-1 text-xs text-green-600 dark:text-green-400 font-bold">
                            <!-- Icône CheckCircle -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-green-600 dark:text-green-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Publiée
                        </span>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="offres.links" class="mt-8 flex justify-center">
                <div class="flex space-x-2">
                    <template v-for="link in offres.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            :class="{
                                'px-4 py-2 rounded bg-indigo-600 text-white': link.active,
                                'px-4 py-2 rounded bg-gray-200 dark:bg-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500': !link.active,
                            }"
                        />
                        <span
                            v-else
                            v-html="link.label"
                            class="px-4 py-2 rounded bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed"
                        ></span>
                    </template>
                </div>
            </div>

            <div v-if="offres.data.length === 0" class="text-center py-12 text-gray-500">
                Aucune offre disponible pour le moment.
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps({
    offres: Object,
    filters: Object,
});

const filters = reactive({
    search: props.filters?.search || '',
    type: props.filters?.type || '',
    lieu: props.filters?.lieu || '',
});

const applyFilters = () => {
    router.get(
        route('offres.public.index'),
        filters,
        { preserveState: true }
    );
};

const resetFilters = () => {
    filters.search = '';
    filters.type = '';
    filters.lieu = '';
    applyFilters();
};
</script>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>