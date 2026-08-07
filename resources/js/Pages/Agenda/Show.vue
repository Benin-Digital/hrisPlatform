<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    evenement: Object,
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const canEdit = computed(() => {
    return user.value.id === props.evenement.organisateur_id || 
           user.value.roles?.some(r => r.nom === 'super_admin');
});

const formatDate = (dateStr) => {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleString('fr-FR', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
};

const duration = computed(() => {
    if (!props.evenement.duree_minutes) return 'Durée non précisée';
    const hours = Math.floor(props.evenement.duree_minutes / 60);
    const minutes = props.evenement.duree_minutes % 60;
    return `${hours ? hours + 'h ' : ''}${minutes ? minutes + 'min' : ''}`.trim() || '—';
});
</script>

<template>
    <Head :title="evenement.titre" />

    <AuthenticatedLayout>
        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                    <!-- Bannière / Image -->
                    <div class="relative h-64 md:h-80 bg-gradient-to-br from-indigo-50 to-purple-50 flex items-center justify-center">
                        <div v-if="evenement.lieu" class="text-center px-8">
                            <svg class="w-16 h-16 mx-auto text-indigo-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <p class="text-xl font-medium text-gray-700">Lieu : {{ evenement.lieu }}</p>
                        </div>
                        <div v-else class="text-center text-gray-500">
                            <p class="text-xl">Aucune image ou lieu spécifié</p>
                        </div>

                        <!-- Badge priorité -->
                        <div v-if="evenement.est_epingle" class="absolute top-6 left-6">
                            <span class="inline-flex items-center px-5 py-2 rounded-full text-sm font-bold bg-yellow-500 text-white shadow-lg">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 5a2 2 0 012 2v12a2 2 0 01-2 2H3V7a4 4 0 014-4z" />
                                </svg>
                                ÉPINGLÉ
                            </span>
                        </div>
                    </div>

                    <!-- Contenu principal -->
                    <div class="p-8 lg:p-12">
                        <!-- Métadonnées -->
                        <div class="flex flex-wrap gap-4 text-sm text-gray-600 mb-6">
                            <span class="font-medium px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full">
                                {{ evenement.type_evenement?.toUpperCase() || 'ÉVÉNEMENT' }}
                            </span>
                            <span>•</span>
                            <span>{{ formatDate(evenement.date_debut) }}</span>
                            <span v-if="evenement.date_fin && evenement.date_fin !== evenement.date_debut">→ {{ formatDate(evenement.date_fin) }}</span>
                            <span>•</span>
                            <span>Durée : {{ duration }}</span>
                        </div>

                        <!-- Titre -->
                        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-8">
                            {{ evenement.titre }}
                        </h1>

                        <!-- Description -->
                        <div class="prose prose-lg max-w-none text-gray-800 mb-12">
                            <p v-if="evenement.description" v-html="evenement.description"></p>
                            <p v-else class="text-gray-500 italic">Aucune description fournie.</p>
                        </div>

                        <!-- Infos supplémentaires -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                            <div class="bg-gray-50 p-6 rounded-xl">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informations pratiques</h3>
                                <dl class="space-y-3 text-sm">
                                    <div>
                                        <dt class="font-medium text-gray-700">Lieu</dt>
                                        <dd class="mt-1 text-gray-600">{{ evenement.lieu || 'Non spécifié' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-700">Lien virtuel</dt>
                                        <dd class="mt-1">
                                            <a v-if="evenement.lien_virtuel" :href="evenement.lien_virtuel" target="_blank" class="text-indigo-600 hover:underline">
                                                Rejoindre la réunion
                                            </a>
                                            <span v-else class="text-gray-500">Aucun lien</span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-700">Organisateur</dt>
                                        <dd class="mt-1 text-gray-600">{{ evenement.organisateur?.prenom_nom || 'Inconnu' }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <div class="bg-gray-50 p-6 rounded-xl">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">Visibilité & Inscription</h3>
                                <dl class="space-y-3 text-sm">
                                    <div>
                                        <dt class="font-medium text-gray-700">Visibilité</dt>
                                        <dd class="mt-1 text-gray-600">{{ evenement.visibilite || 'Entité actuelle' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-700">Inscription requise</dt>
                                        <dd class="mt-1">
                                            <span :class="evenement.inscription_requise ? 'text-green-600' : 'text-gray-500'">
                                                {{ evenement.inscription_requise ? 'Oui' : 'Non' }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div v-if="evenement.capacite_max">
                                        <dt class="font-medium text-gray-700">Capacité</dt>
                                        <dd class="mt-1 text-gray-600">{{ evenement.capacite_max }} places</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Boutons -->
                        <div class="flex flex-wrap gap-4 pt-8 border-t">
                            <Link
                                :href="route('agenda.index')"
                                class="px-8 py-4 bg-gray-200 text-gray-800 rounded-xl hover:bg-gray-300 transition font-medium"
                            >
                                ← Retour à l'agenda
                            </Link>

                            <Link
                                v-if="canEdit"
                                :href="route('agenda.edit', evenement.id)"
                                class="px-8 py-4 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition font-medium shadow-md"
                            >
                                Modifier l'événement
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
