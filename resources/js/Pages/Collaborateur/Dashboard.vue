<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DashboardWidgets from '@/Components/Widgets/DashboardWidgets.vue';
import { Head, usePage, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    HandRaisedIcon,
    SunIcon,
    ClipboardDocumentListIcon,
    FolderIcon,
    AcademicCapIcon,
    DocumentTextIcon,
    PhotoIcon,
    PaperClipIcon,
    ArrowRightIcon,
    CalendarIcon,
    XMarkIcon,
    ArrowDownTrayIcon,
    DocumentIcon,
} from '@heroicons/vue/24/outline';

const page = usePage();

const props = defineProps({
    recentAnnonces: Array,
    upcomingEvents: Array,
    tachesPersonnelles: Array,
    documentsRecents: Array,
    formationsDispo: Array,
    soldeAnnuel: {
        type: Number,
        default: 0,
    },
});

// État pour la prévisualisation
const isPreviewOpen = ref(false);
const selectedDoc = ref(null);

const openPreview = (doc) => {
    selectedDoc.value = doc;
    isPreviewOpen.value = true;
};

const closePreview = () => {
    isPreviewOpen.value = false;
    selectedDoc.value = null;
};

const isPDF = (doc) => doc.extension?.toLowerCase() === 'pdf';
const isImage = (doc) => ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(doc.extension?.toLowerCase());

// Date formatée
const formattedDate = computed(() => {
    return new Date().toLocaleDateString('fr-FR', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
});

// Mapping pour les icônes de la prévisualisation
const previewIconMap = {
    pdf: DocumentTextIcon,
    image: PhotoIcon,
    default: PaperClipIcon,
};
</script>

<template>
    <Head title="Tableau de bord - Collaborateur" />

    <AuthenticatedLayout>
        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Message de bienvenue -->
                <div class="bg-white overflow-hidden shadow-xl border-l-8 border-indigo-600 rounded-2xl mb-8">
                    <div class="p-8 text-gray-900">
                        <div class="flex items-center space-x-4">
                            <div class="h-16 w-16 bg-indigo-100 rounded-full flex items-center justify-center">
                                <HandRaisedIcon class="w-8 h-8 text-indigo-600" />
                            </div>
                            <div>
                                <h1 class="text-3xl font-extrabold text-gray-900">
                                    Bonjour, {{ $page.props.auth?.user?.prenom }} !
                                </h1>
                                <p class="text-lg text-gray-500 font-medium leading-relaxed">
                                    Ravis de vous revoir. Voici votre résumé personnalisé du jour.
                                </p>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center text-sm text-indigo-600 font-bold bg-indigo-50 px-4 py-2 rounded-full w-fit">
                            <CalendarIcon class="w-4 h-4 mr-2" />
                            {{ formattedDate }}
                        </div>
                    </div>
                </div>

                <!-- Widgets rapides -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 font-medium">Solde congés</p>
                                <p class="text-3xl font-bold text-indigo-600">{{ soldeAnnuel }}</p>
                                <p class="text-xs text-gray-400">jours restants</p>
                            </div>
                            <SunIcon class="w-10 h-10 text-amber-500" />
                        </div>
                        <Link :href="route('conges.create')" class="mt-3 inline-block text-sm text-indigo-600 hover:underline font-medium inline-flex items-center">
                            Demander un congé
                            <ArrowRightIcon class="w-4 h-4 ml-1" />
                        </Link>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 font-medium">Tâches en cours</p>
                                <p class="text-3xl font-bold text-orange-600">{{ tachesPersonnelles.length }}</p>
                                <p class="text-xs text-gray-400">assignées</p>
                            </div>
                            <ClipboardDocumentListIcon class="w-10 h-10 text-orange-500" />
                        </div>
                        <Link :href="route('taches.index')" class="mt-3 inline-block text-sm text-indigo-600 hover:underline font-medium inline-flex items-center">
                            Voir mes tâches
                            <ArrowRightIcon class="w-4 h-4 ml-1" />
                        </Link>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 font-medium">Documents</p>
                                <p class="text-3xl font-bold text-blue-600">{{ documentsRecents.length }}</p>
                                <p class="text-xs text-gray-400">récents</p>
                            </div>
                            <FolderIcon class="w-10 h-10 text-blue-500" />
                        </div>
                        <Link :href="route('documents.index')" class="mt-3 inline-block text-sm text-indigo-600 hover:underline font-medium inline-flex items-center">
                            Voir tous
                            <ArrowRightIcon class="w-4 h-4 ml-1" />
                        </Link>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 font-medium">Formations</p>
                                <p class="text-3xl font-bold text-green-600">{{ formationsDispo.length }}</p>
                                <p class="text-xs text-gray-400">disponibles</p>
                            </div>
                            <AcademicCapIcon class="w-10 h-10 text-green-500" />
                        </div>
                        <Link :href="route('formations.index')" class="mt-3 inline-block text-sm text-indigo-600 hover:underline font-medium inline-flex items-center">
                            Explorer
                            <ArrowRightIcon class="w-4 h-4 ml-1" />
                        </Link>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                    <!-- Mes Tâches (Perso) -->
                    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-bold text-gray-800 flex items-center">
                                <span class="bg-orange-100 text-orange-600 p-2 rounded-lg mr-3">
                                    <ClipboardDocumentListIcon class="w-6 h-6" />
                                </span>
                                Mes Tâches en cours
                            </h2>
                            <Link :href="route('taches.index')" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 inline-flex items-center">
                                Tout voir
                                <ArrowRightIcon class="w-4 h-4 ml-1" />
                            </Link>
                        </div>
                        
                        <div v-if="tachesPersonnelles.length === 0" class="text-center py-10">
                            <p class="text-gray-400 italic">Aucune tâche assignée pour le moment.</p>
                        </div>
                        <div v-else class="space-y-4">
                            <Link v-for="tache in tachesPersonnelles" :key="tache.id" :href="route('taches.show', tache.id)" class="flex items-center p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition group">
                                <div class="flex-1">
                                    <h4 class="font-bold text-gray-900 group-hover:text-indigo-600 transition">{{ tache.titre }}</h4>
                                    <p class="text-xs text-gray-500">Échéance : {{ new Date(tache.date_echeance).toLocaleDateString() }}</p>
                                </div>
                                <span class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-bold uppercase tracking-wider">
                                    {{ tache.statut }}
                                </span>
                            </Link>
                        </div>
                    </div>

                    <!-- Mes Documents Récents -->
                    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-bold text-gray-800 flex items-center">
                                <span class="bg-blue-100 text-blue-600 p-2 rounded-lg mr-3">
                                    <FolderIcon class="w-6 h-6" />
                                </span>
                                Documents
                            </h2>
                            <Link :href="route('documents.index')" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Voir tout</Link>
                        </div>
                        <div class="space-y-3">
                            <div v-for="doc in documentsRecents" :key="doc.id" class="p-3 border border-gray-50 rounded-xl hover:border-indigo-100 hover:shadow-sm transition group cursor-pointer" @click="openPreview(doc)">
                                <div class="flex items-center gap-3">
                                    <component 
                                        :is="isPDF(doc) ? DocumentTextIcon : isImage(doc) ? PhotoIcon : PaperClipIcon"
                                        class="w-5 h-5 text-gray-400 group-hover:text-indigo-600"
                                    />
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium text-gray-800 truncate text-sm group-hover:text-indigo-600">{{ doc.nom_original || doc.nom }}</div>
                                        <div class="text-[10px] text-gray-400 uppercase mt-1">{{ doc.extension }} • {{ (doc.taille_octets / 1024 / 1024).toFixed(2) }} MB</div>
                                    </div>
                                </div>
                            </div>
                            <div v-if="documentsRecents.length === 0" class="text-center py-4 text-gray-400 text-sm italic">
                                Aucun document récent.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formations Disponibles -->
                <div class="bg-white rounded-2xl shadow-sm p-8 border border-gray-100 mb-8">
                     <div class="flex justify-between items-center mb-8">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                                <span class="bg-green-100 text-green-600 p-2 rounded-lg mr-4">
                                    <AcademicCapIcon class="w-8 h-8" />
                                </span>
                                Formations suggérées
                            </h2>
                            <p class="text-gray-500 mt-1">Développez vos compétences avec nos nouveaux parcours.</p>
                        </div>
                        <Link :href="route('formations.index')" class="px-6 py-3 bg-indigo-50 text-indigo-700 font-bold rounded-xl hover:bg-indigo-100 transition inline-flex items-center">
                            Catalogue complet
                            <ArrowRightIcon class="w-5 h-5 ml-2" />
                        </Link>
                    </div>

                    <div v-if="formationsDispo.length === 0" class="text-center py-10 bg-gray-50 rounded-2xl">
                         <p class="text-gray-400 italic">Aucune formation disponible actuellement.</p>
                    </div>
                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="form in formationsDispo" :key="form.id" class="group bg-white border border-gray-100 rounded-2xl overflow-hidden hover:shadow-xl transition-all duration-300">
                            <div class="h-32 bg-gradient-to-br from-indigo-500 to-purple-600 p-6 flex items-end">
                                <h4 class="text-white font-bold text-lg leading-tight">{{ form.titre }}</h4>
                            </div>
                            <div class="p-6">
                                <div class="flex items-center text-xs text-gray-400 mb-4">
                                    <span class="mr-auto font-bold text-indigo-500 bg-indigo-50 px-2 py-1 rounded">DÉBUT : {{ new Date(form.date_debut).toLocaleDateString() }}</span>
                                </div>
                                <Link :href="route('formations.show', form.id)" class="block w-full py-3 bg-gray-900 text-white rounded-xl font-bold hover:bg-indigo-600 transition tracking-wide text-sm text-center">
                                    DÉTAILS DU PARCOURS
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Widgets Collaboratifs (News & Events) -->
                <div class="mt-12">
                    <h3 class="text-2xl font-black text-gray-900 mb-8 uppercase tracking-widest flex items-center">
                        <span class="w-12 h-1 bg-indigo-600 mr-4"></span>
                        ACTUALITÉS DE L'ENTREPRISE
                    </h3>
                    <DashboardWidgets 
                        :annonces="recentAnnonces" 
                        :events="upcomingEvents" 
                    />
                </div>

            </div>
        </div>

        <!-- Modal de Prévisualisation -->
        <div v-if="isPreviewOpen" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/75 backdrop-blur-sm p-4 sm:p-6" @click.self="closePreview">
            <div class="bg-white w-full max-w-5xl h-[90vh] rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-in fade-in zoom-in duration-300">
                <!-- Header -->
                <div class="px-6 py-4 flex justify-between items-center border-b bg-gray-50">
                    <div class="flex items-center space-x-3">
                        <component 
                            :is="isPDF(selectedDoc) ? DocumentTextIcon : isImage(selectedDoc) ? PhotoIcon : PaperClipIcon"
                            class="w-6 h-6 text-gray-600"
                        />
                        <div>
                            <h3 class="font-bold text-gray-900 truncate max-w-md">{{ selectedDoc.nom_original }}</h3>
                            <p class="text-xs text-gray-500 uppercase">{{ selectedDoc.extension }} • {{ (selectedDoc.taille_octets / 1024 / 1024).toFixed(2) }} MB</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <a :href="route('documents.download', selectedDoc.uuid)" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Télécharger">
                            <ArrowDownTrayIcon class="w-6 h-6" />
                        </a>
                        <button @click="closePreview" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                            <XMarkIcon class="w-6 h-6" />
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <div class="flex-1 bg-gray-800 relative overflow-hidden">
                    <div v-if="isPDF(selectedDoc)" class="w-full h-full">
                        <embed :src="route('documents.view', selectedDoc.uuid)" type="application/pdf" class="w-full h-full" />
                    </div>
                    <div v-else-if="isImage(selectedDoc)" class="w-full h-full flex items-center justify-center p-4">
                        <img :src="route('documents.view', selectedDoc.uuid)" :alt="selectedDoc.nom_original" class="max-w-full max-h-full object-contain rounded-sm" />
                    </div>
                    <div v-else class="w-full h-full flex flex-col items-center justify-center text-white space-y-4">
                        <DocumentIcon class="w-24 h-24 opacity-50" />
                        <p class="text-xl font-medium text-center px-6">La prévisualisation n'est pas disponible pour ce type de fichier.</p>
                        <a :href="route('documents.download', selectedDoc.uuid)" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-lg inline-flex items-center">
                            <ArrowDownTrayIcon class="w-5 h-5 mr-2" />
                            Télécharger pour consulter
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>