<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    annonce: Object,
});

const formatDate = (dateStr) => {
    if (!dateStr) return '—';
    const date = new Date(dateStr);
    return date.toLocaleDateString('fr-FR', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
};

// Vérifie si l'utilisateur peut modifier (à adapter avec tes rôles)
const canEdit = computed(() => {
    const user = props.annonce.auth?.user; // suppose que auth est partagé via middleware
    return user && (
        user.id === props.annonce.auteur_id ||
        user.roles?.some(r => r.nom === 'super_admin')
    );
});
</script>

<template>
    <Head :title="annonce.titre" />

    <AuthenticatedLayout>
        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Carte principale -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                    <!-- Bannière image ou placeholder -->
                    <div class="relative">
                        <img
                            v-if="annonce.image"
                            :src="`/storage/${annonce.image}`"
                            class="w-full h-64 md:h-96 object-cover"
                            alt="Image de l'annonce"
                        />
                        <div v-else class="h-64 md:h-96 bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center">
                            <div class="text-center text-gray-500">
                                <svg class="w-16 h-16 mx-auto mb-4 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="text-lg font-medium">Aucune image pour cette annonce</p>
                            </div>
                        </div>

                        <!-- Badge type + statut -->
                        <div class="absolute top-6 left-6 flex gap-3">
                            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold text-white bg-indigo-600 shadow-md">
                                {{ annonce.type_annonce?.toUpperCase() || 'INFORMATION' }}
                            </span>
                            <span v-if="annonce.est_epingle" class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold bg-yellow-500 text-white shadow-md">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 5a2 2 0 012 2v12a2 2 0 01-2 2H3V7a4 4 0 014-4z" />
                                </svg>
                                Épinglée
                            </span>
                            <span v-if="annonce.date_expiration && new Date(annonce.date_expiration) < new Date()" class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold bg-red-500 text-white shadow-md">
                                Expirée
                            </span>
                        </div>
                    </div>

                    <!-- Contenu -->
                    <div class="p-8 lg:p-12">
                        <!-- Métadonnées -->
                        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 mb-6">
                            <span class="font-medium text-indigo-600">{{ annonce.type_annonce?.toUpperCase() || 'INFO' }}</span>
                            <span>•</span>
                            <span>{{ formatDate(annonce.date_publication) }}</span>
                            <span>•</span>
                            <span>Par {{ annonce.createur?.prenom_nom || 'Administration' }}</span>
                            <span v-if="annonce.visibilite === 'global'" class="ml-2 px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                                Visible à tous
                            </span>
                        </div>

                        <!-- Titre -->
                        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-8 leading-tight">
                            {{ annonce.titre }}
                        </h1>

                        <!-- Contenu riche -->
                        <div class="prose prose-lg max-w-none text-gray-800 prose-headings:text-gray-900 prose-a:text-indigo-600 hover:prose-a:text-indigo-800">
                            <div v-html="annonce.contenu"></div>
                        </div>

                        <!-- Actions -->
                        <div class="mt-12 flex flex-wrap gap-4 border-t pt-8">
                            <Link
                                :href="route('actualites.index')"
                                class="inline-flex items-center px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium rounded-xl transition"
                            >
                                ← Retour aux actualités
                            </Link>

                            <Link
                                v-if="canEdit"
                                :href="route('actualites.edit', annonce.id)"
                                class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl transition shadow-md"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Modifier l'annonce
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>