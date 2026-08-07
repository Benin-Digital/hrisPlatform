<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    NewspaperIcon,
    StarIcon,
    BookmarkIcon,
    UserCircleIcon,
    PencilIcon,
    TrashIcon,
    DocumentTextIcon,
    ArrowRightIcon,
    PlusIcon,
    PhotoIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    annonces: Object,
    epinglees: Array,
});

const page = usePage();

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

const stripAndTruncate = (html) => {
    const text = html?.replace(/<[^>]*>/g, '').trim() || '';
    return text.length > 150 ? text.substring(0, 150) + '...' : text;
};

const canCreateAnnonce = computed(() => {
    const roles = page.props.auth.user.roles.map(r => r.nom);
    return !roles.includes('collaborateur') && !roles.includes('invite');
});

const canModify = (annonce) => {
    const user = page.props.auth.user;
    const userRoles = user.roles.map(r => r.nom);
    const isAuthor = user.id === annonce.auteur_id;
    const isSuperAdmin = userRoles.includes('super_admin');
    const isRH = userRoles.includes('responsable_rh');
    return isAuthor || isSuperAdmin || isRH;
};

const deleteAnnonce = (id) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette annonce ? Cette action est irréversible.')) {
        router.delete(route('actualites.destroy', id), {
            onSuccess: () => {},
            onError: (errors) => {
                alert('Erreur lors de la suppression.');
                console.error(errors);
            }
        });
    }
};

const route = (name, params = {}) => {
    return window.route ? window.route(name, params) : `/${name}`;
};
</script>

<template>
    <Head title="Actualités & Communications" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl text-gray-800 leading-tight">
                Actualités & Communications
            </h2>
        </template>

        <div class="py-6 md:py-10">
            <div class="page-container">
                <!-- Header -->
                <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center">
                        <NewspaperIcon class="w-8 h-8 text-indigo-600 mr-3" />
                        <div>
                            <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900">Actualités</h1>
                            <p class="text-sm text-gray-500 mt-1">Restez informé des dernières nouvelles de l'organisation.</p>
                        </div>
                    </div>
                    
                    <Link
                        v-if="canCreateAnnonce"
                        :href="route('actualites.create')"
                        class="btn btn-primary self-start md:self-auto inline-flex items-center"
                    >
                        <PlusIcon class="w-5 h-5 mr-2" />
                        Nouvelle annonce
                    </Link>
                </div>

                <!-- Featured / Pinned News -->
                <div v-if="epinglees.length > 0" class="mb-12">
                    <h3 class="text-xs font-black text-primary-600 uppercase tracking-widest mb-6 flex items-center">
                        <StarIcon class="w-4 h-4 text-yellow-500 mr-2" />
                        À la une
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <div
                            v-for="annonce in epinglees"
                            :key="annonce.id"
                            class="card group border-0 overflow-hidden hover-lift p-0 relative"
                        >
                            <Link :href="route('actualites.show', annonce.id)" class="block">
                                <div class="relative h-48 md:h-56 bg-gray-100 overflow-hidden">
                                    <img
                                        v-if="annonce.image"
                                        :src="`/storage/${annonce.image}`"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                                        alt="Featured news"
                                    />
                                    <div v-else class="flex flex-col items-center justify-center h-full bg-gradient-to-br from-indigo-500 to-purple-600 opacity-80">
                                        <NewspaperIcon class="w-16 h-16 text-white opacity-20" />
                                    </div>
                                    <div class="absolute top-4 left-4">
                                        <span class="badge badge-primary shadow-lg border border-white/20">
                                            {{ annonce.type_annonce?.toUpperCase() || 'INFO' }}
                                        </span>
                                    </div>
                                    <div class="absolute bottom-4 right-4">
                                        <span class="bg-white/90 backdrop-blur-sm text-[10px] font-black px-2 py-1 rounded-lg text-primary-700 shadow-sm border border-white/20 inline-flex items-center">
                                            <BookmarkIcon class="w-3 h-3 mr-1" />
                                            ÉPINGLÉ
                                        </span>
                                    </div>
                                </div>

                                <div class="p-6 md:p-8">
                                    <h3 class="text-lg md:text-xl font-extrabold text-gray-900 mb-3 group-hover:text-primary-600 transition-colors leading-tight">
                                        {{ annonce.titre }}
                                    </h3>
                                    <p class="text-sm text-gray-500 mb-6 line-clamp-2 md:line-clamp-3 font-medium leading-relaxed">
                                        {{ stripAndTruncate(annonce.contenu) }}
                                    </p>
                                    <div class="flex items-center justify-between pt-4 border-t border-gray-50">
                                        <div class="flex items-center">
                                            <UserCircleIcon class="w-7 h-7 text-primary-600 mr-2" />
                                            <span class="text-[10px] font-bold text-gray-600 truncate max-w-[100px]">{{ annonce.createur?.prenom_nom || 'Admin' }}</span>
                                        </div>
                                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-tighter">{{ formatDate(annonce.date_publication) }}</span>
                                    </div>
                                </div>
                            </Link>

                            <!-- Actions pour les utilisateurs autorisés -->
                            <div v-if="canModify(annonce)" class="absolute top-4 right-4 flex gap-2">
                                <Link
                                    :href="route('actualites.edit', annonce.id)"
                                    class="bg-white/90 backdrop-blur-sm p-2 rounded-lg shadow-md hover:bg-white transition text-indigo-600"
                                    title="Modifier"
                                >
                                    <PencilIcon class="w-4 h-4" />
                                </Link>
                                <button
                                    @click="deleteAnnonce(annonce.id)"
                                    class="bg-white/90 backdrop-blur-sm p-2 rounded-lg shadow-md hover:bg-white transition text-red-600"
                                    title="Supprimer"
                                >
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main News List -->
                <div>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6 flex items-center">
                        <DocumentTextIcon class="w-4 h-4 mr-2" />
                        Toutes les actualités
                    </h3>

                    <div v-if="annonces.data.length === 0" class="card border-2 border-dashed border-gray-200 py-16 text-center">
                        <NewspaperIcon class="w-16 h-16 mx-auto text-gray-300 mb-4" />
                        <p class="text-xl font-extrabold text-gray-400">Aucune annonce pour le moment</p>
                        <p class="text-gray-400 mt-2 font-medium">Les news apparaîtront ici dès qu'elles seront publiées.</p>
                    </div>

                    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                        <div
                            v-for="annonce in annonces.data"
                            :key="annonce.id"
                            class="card border-0 hover:border-primary-100 transition-all group p-4 md:p-6 relative"
                        >
                            <Link :href="route('actualites.show', annonce.id)" class="block">
                                <div class="relative h-40 rounded-2xl bg-gray-50 mb-6 overflow-hidden">
                                    <img
                                        v-if="annonce.image"
                                        :src="`/storage/${annonce.image}`"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                                        alt="News image"
                                    />
                                    <div v-else class="flex items-center justify-center h-full text-3xl opacity-20 bg-gray-100">
                                        <PhotoIcon class="w-12 h-12 text-gray-400" />
                                    </div>
                                    <div class="absolute top-3 left-3">
                                        <span class="bg-white/90 backdrop-blur-sm text-[9px] font-black px-2 py-1 rounded-lg text-primary-700 shadow-sm border border-gray-100">
                                            {{ annonce.type_annonce?.toUpperCase() || 'INFO' }}
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <h3 class="text-base font-extrabold text-gray-900 mb-2 truncate group-hover:text-primary-600 transition-colors">
                                        {{ annonce.titre }}
                                    </h3>
                                    <p class="text-xs text-gray-500 mb-6 line-clamp-2 md:line-clamp-3 font-medium leading-relaxed">
                                        {{ stripAndTruncate(annonce.contenu) }}
                                    </p>
                                    <div class="flex items-center justify-between text-[10px] font-black text-gray-400 uppercase tracking-tighter">
                                        <span>{{ formatDate(annonce.date_publication) }}</span>
                                        <span class="group-hover:text-primary-600 transition-colors flex items-center">
                                            Lire la suite
                                            <ArrowRightIcon class="w-3 h-3 ml-1" />
                                        </span>
                                    </div>
                                </div>
                            </Link>

                            <!-- Actions pour les utilisateurs autorisés -->
                            <div v-if="canModify(annonce)" class="absolute top-4 right-4 flex gap-2">
                                <Link
                                    :href="route('actualites.edit', annonce.id)"
                                    class="bg-white/90 backdrop-blur-sm p-1.5 rounded-lg shadow-md hover:bg-white transition text-indigo-600 text-sm"
                                    title="Modifier"
                                >
                                    <PencilIcon class="w-4 h-4" />
                                </Link>
                                <button
                                    @click="deleteAnnonce(annonce.id)"
                                    class="bg-white/90 backdrop-blur-sm p-1.5 rounded-lg shadow-md hover:bg-white transition text-red-600 text-sm"
                                    title="Supprimer"
                                >
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="annonces.links.length > 3" class="mt-12 flex justify-center">
                        <nav class="flex items-center gap-1">
                            <template v-for="(link, index) in annonces.links" :key="index">
                                <div
                                    v-if="!link.url"
                                    class="px-3 py-2 text-gray-300 pointer-events-none text-sm font-bold opacity-50"
                                    v-html="link.label"
                                />
                                <Link
                                    v-else
                                    :href="link.url"
                                    :class="[
                                        'px-4 py-2 rounded-xl text-sm font-extrabold transition-all',
                                        link.active ? 'bg-primary-600 text-white shadow-lg shadow-primary-200' : 'bg-white border border-gray-100 text-gray-600 hover:bg-gray-50 hover:border-gray-200 shadow-sm'
                                    ]"
                                    v-html="link.label"
                                />
                            </template>
                        </nav>
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
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>