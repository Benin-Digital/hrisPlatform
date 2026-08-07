<script setup>
import { Link } from '@inertiajs/vue3';
import { format } from 'date-fns';
import { fr } from 'date-fns/locale';

const props = defineProps({
    annonces: Array,
    events: Array,
});

const formatDate = (date) => {
    if (!date) return '';
    return format(new Date(date), 'dd MMM yyyy', { locale: fr });
};

const formatTime = (date) => {
    if (!date) return '';
    return format(new Date(date), 'HH:mm', { locale: fr });
};

// Helper route
const route = (name, params = {}) => {
    return window.route ? window.route(name, params) : `/${name}`;
};
</script>

<template>
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mt-8">
        <!-- Actualités / Annonces -->
        <div class="card overflow-hidden hover-lift border-0">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-gray-900 flex items-center">
                    <span class="w-1 h-6 bg-primary-600 rounded-full mr-3"></span>
                    Actualités Récentes
                </h3>
                <Link :href="route('actualites.index')" class="text-sm text-primary-600 hover:text-primary-800 font-semibold transition-colors">
                    Voir tout →
                </Link>
            </div>

            <div v-if="annonces && annonces.length > 0" class="space-y-4">
                <div v-for="annonce in annonces" :key="annonce.id" class="group">
                    <Link :href="route('actualites.show', annonce.id)" class="block hover:bg-primary-50 -mx-3 p-3 rounded-xl transition-all duration-300">
                        <div class="flex items-start">
                            <div v-if="annonce.image" class="flex-shrink-0 mr-4">
                                <img :src="`/storage/${annonce.image}`" alt="" class="h-16 w-16 md:h-20 md:w-20 object-cover rounded-xl shadow-sm group-hover:shadow-md transition-shadow" />
                            </div>
                            <div v-else class="flex-shrink-0 mr-4 h-16 w-16 md:h-20 md:w-20 bg-gray-100 rounded-xl flex items-center justify-center text-2xl text-gray-400">
                                📰
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span v-if="annonce.est_epingle" class="bg-red-100 text-red-600 text-[10px] font-bold px-1.5 py-0.5 rounded flex items-center uppercase">
                                       <span class="mr-1">📌</span> Épinglé
                                    </span>
                                    <p class="text-[10px] text-gray-400 font-medium uppercase tracking-wider">
                                        {{ formatDate(annonce.created_at) }}
                                    </p>
                                </div>
                                <h4 class="text-sm md:text-base font-bold text-gray-800 group-hover:text-primary-700 transition-colors line-clamp-1">
                                    {{ annonce.titre }}
                                </h4>
                                <p class="text-xs text-gray-500 mt-1 flex items-center">
                                    <span class="mr-1 opacity-60">👤</span> {{ annonce.createur?.prenom }} {{ annonce.createur?.nom }}
                                </p>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
            <div v-else class="text-sm text-gray-400 italic text-center py-12 flex flex-col items-center">
                <span class="text-4xl mb-3 opacity-20">📰</span>
                Aucune actualité récente.
            </div>
        </div>

        <!-- Événements / Agenda -->
        <div class="card overflow-hidden hover-lift border-0">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-gray-900 flex items-center">
                    <span class="w-1 h-6 bg-success-600 rounded-full mr-3"></span>
                    Événements à Venir
                </h3>
                <Link :href="route('agenda.index')" class="text-sm text-success-600 hover:text-success-800 font-semibold transition-colors">
                    Calendrier →
                </Link>
            </div>

            <div v-if="events && events.length > 0" class="space-y-4">
                <div v-for="event in events" :key="event.id" class="group">
                    <div class="flex items-start bg-white group-hover:bg-success-50 -mx-3 p-3 rounded-xl transition-all duration-300 border border-transparent hover:border-success-100">
                        <div class="flex-shrink-0 w-14 text-center bg-gray-50 group-hover:bg-white rounded-xl p-2 mr-4 border border-gray-100 shadow-sm transition-colors">
                            <div class="text-[10px] text-red-500 font-extrabold uppercase leading-none mb-1">
                                {{ format(new Date(event.date_debut), 'MMM', { locale: fr }) }}
                            </div>
                            <div class="text-xl font-bold text-gray-800 leading-none">
                                {{ format(new Date(event.date_debut), 'dd', { locale: fr }) }}
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <Link :href="route('agenda.show', event.id)" class="block">
                                <h4 class="text-sm md:text-base font-bold text-gray-900 group-hover:text-success-700 transition-colors truncate">
                                    {{ event.titre }}
                                </h4>
                            </Link>
                            <div class="flex flex-wrap items-center gap-y-1 gap-x-3 mt-1.5 text-xs text-gray-500">
                                <span class="flex items-center">
                                    <span class="mr-1 opacity-60">⏰</span> {{ formatTime(event.date_debut) }} - {{ formatTime(event.date_fin) }}
                                </span>
                                <span v-if="event.lieu" class="flex items-center font-medium text-success-600">
                                    <span class="mr-1 opacity-60 text-gray-400">📍</span> {{ event.lieu }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="text-sm text-gray-400 italic text-center py-12 flex flex-col items-center">
                <span class="text-4xl mb-3 opacity-20">📅</span>
                Aucun événement à venir.
            </div>
        </div>
    </div>
</template>

