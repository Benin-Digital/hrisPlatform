<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const page = usePage();
const isInvite = computed(() => page.props.auth.user.roles.some(r => r.nom === 'invite'));

const props = defineProps({
    espaces: {
        type: Array,
        required: true
    }
});

const getInitials = (name) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
};
</script>

<template>
    <Head title="Collaboration & Espaces" />

    <div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Collaboration</h1>
                    <p class="mt-2 text-gray-600">Gérez vos espaces de travail et collaborez avec vos collègues.</p>
                </div>
                <div class="flex items-center gap-4">
                    <Link
                        href="/dashboard"
                        class="px-5 py-3 bg-gray-100 text-gray-700 rounded-2xl font-bold hover:bg-gray-200 transition"
                    >
                        ← Dashboard
                    </Link>
                    <Link
                        v-if="!isInvite"
                        href="/collaboration/creer"
                        class="flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-2xl font-bold shadow-lg shadow-indigo-600/30 hover:bg-indigo-700 transition-all active:scale-95 group"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Nouvel Espace
                    </Link>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="espaces.length === 0" class="text-center py-20 bg-white rounded-3xl shadow-sm border border-gray-100">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-indigo-50 text-indigo-600 rounded-full mb-6">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Aucun espace de travail</h3>
                <p class="text-gray-500 max-w-sm mx-auto mb-8">Vous ne faites partie d'aucun espace collaboratif pour le moment. Créez-en un pour commencer à travailler avec votre équipe.</p>
                <Link
                    href="/collaboration/creer"
                    class="text-indigo-600 font-semibold hover:text-indigo-500 underline decoration-2 underline-offset-4"
                >
                    Créer mon premier espace
                </Link>
            </div>

            <!-- Grid -->
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <div 
                    v-for="espace in espaces" 
                    :key="espace.id"
                    class="group bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 flex flex-col"
                >
                    <!-- Espace Image/Cover -->
                    <div class="h-32 bg-gradient-to-br from-indigo-500 to-purple-600 relative">
                        <div class="absolute inset-0 bg-black opacity-0 group-hover:opacity-10 transition-opacity"></div>
                        <div class="absolute -bottom-6 left-6">
                            <div class="w-16 h-16 bg-white rounded-2xl shadow-lg flex items-center justify-center text-indigo-600 font-bold text-xl border-4 border-white">
                                {{ getInitials(espace.nom) }}
                            </div>
                        </div>
                        <div class="absolute top-4 right-4">
                            <span v-if="espace.est_prive" class="px-2 py-1 bg-black bg-opacity-20 backdrop-blur-md text-white text-xs font-semibold rounded-lg flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                </svg>
                                Privé
                            </span>
                            <span v-else class="px-2 py-1 bg-green-500 bg-opacity-20 backdrop-blur-md text-white text-xs font-semibold rounded-lg flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                </svg>
                                Public
                            </span>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="pt-10 pb-6 px-6 flex-grow">
                        <h2 class="text-xl font-bold text-gray-900 group-hover:text-indigo-600 transition-colors line-clamp-1 mb-2">
                             <Link :href="`/collaboration/${espace.uuid}`">{{ espace.nom }}</Link>
                        </h2>
                        <p class="text-gray-500 text-sm line-clamp-2 mb-4 h-10">
                            {{ espace.description || 'Aucune description fournie.' }}
                        </p>

                        <div class="flex items-center justify-between text-xs text-gray-400 border-t border-gray-50 pt-4">
                            <div class="flex -space-x-2">
                                <div 
                                    v-for="(membre, idx) in espace.membres.slice(0, 3)" 
                                    :key="idx"
                                    class="w-6 h-6 rounded-full border-2 border-white bg-gray-200 flex items-center justify-center text-[8px] font-bold text-gray-600"
                                    :title="membre.prenom + ' ' + membre.nom"
                                >
                                    {{ getInitials(membre.prenom + ' ' + membre.nom) }}
                                </div>
                                <div v-if="espace.membres_count > 3" class="w-6 h-6 rounded-full border-2 border-white bg-indigo-100 flex items-center justify-center text-[8px] font-bold text-indigo-600">
                                    +{{ espace.membres_count - 3 }}
                                </div>
                            </div>
                            <span>Mis à jour {{ new Date(espace.updated_at).toLocaleDateString() }}</span>
                        </div>
                    </div>

                    <!-- Footer Action -->
                    <div class="px-6 py-4 bg-gray-50 hover:bg-indigo-50 transition-colors border-t border-gray-100 group">
                        <Link 
                            :href="`/collaboration/${espace.uuid}`"
                            class="flex items-center justify-center text-sm font-bold text-indigo-600"
                        >
                            Ouvrir l'Espace
                            <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
