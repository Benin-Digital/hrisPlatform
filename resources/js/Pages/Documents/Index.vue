<script setup>
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useForm, Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import {
    CheckCircleIcon,
    ExclamationTriangleIcon,
    PlusIcon,
    HomeIcon,
    GlobeAltIcon,
    BuildingOfficeIcon,
    FolderIcon,
    DocumentTextIcon,
    PhotoIcon,
    DocumentIcon,
    PaperClipIcon,
    EyeIcon,
    ArrowDownTrayIcon,
    ClockIcon,
    LinkIcon,
    TrashIcon,
    FolderOpenIcon,
    ArrowRightOnRectangleIcon,
    PauseIcon,
    PlayIcon,
    ArrowLeftOnRectangleIcon,
} from '@heroicons/vue/24/outline'

const page = usePage()

const success = computed(() => page.props.flash?.success)
const error = computed(() => page.props.errors?.error || page.props.flash?.error)

const route = window.route

const props = defineProps({
    dossierCourant: Object,
    dossiers: Array,
    documents: Array,
    breadcrumb: Array,
    cheminComplet: String,
    users: Array,
    entites: Array,
    canCreate: Boolean,
})

const files = ref([])
const isDragging = ref(false)
const showCreateFolder = ref(false)
const newFolderName = ref('')
const newFolderVisibility = ref('entite')

const handleDragOver = (e) => {
    e.preventDefault()
    isDragging.value = true
}

const handleDragLeave = () => {
    isDragging.value = false
}

const handleDrop = (e) => {
    e.preventDefault()
    isDragging.value = false
    files.value = Array.from(e.dataTransfer.files)
    uploadFiles()
}

const handleFileInput = (e) => {
    files.value = Array.from(e.target.files)
    uploadFiles()
}

const uploadTargetEntite = ref(page.props.auth.user.entite_id || 'global')

const uploadFiles = () => {
    if (files.value.length === 0) return

    const formData = new FormData()
    files.value.forEach((file) => {
        formData.append('documents[]', file)
    })
    
    if (page.props.auth.user.roles.some(r => r.nom === 'super_admin')) {
        formData.append('entite_id', uploadTargetEntite.value)
    }

    const dossierId = props.dossierCourant?.id || 'root'

    router.post(route('documents.upload', dossierId), formData, {
        forceFormData: true,
        onSuccess: () => {
            files.value = []
        },
        onError: (errors) => {
            console.log(errors)
        }
    })
}

const createFolder = () => {
    if (!newFolderName.value.trim()) return

    router.post(route('documents.storeDossier'), {
        nom: newFolderName.value,
        dossier_parent_id: props.dossierCourant?.id || null,
        visibilite: newFolderVisibility.value,
    }, {
        onSuccess: () => {
            newFolderName.value = ''
            showCreateFolder.value = false
        }
    })
}

const deleteDocument = (uuid) => {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce document ?\nCette action est irréversible.')) {
        return
    }

    router.delete(route('documents.destroy', uuid), {
        preserveScroll: true,
        onSuccess: () => {
            // Flash success géré par le controller
        },
        onError: () => {
            alert('Erreur lors de la suppression')
        }
    })
}

const formatSize = (bytes) => {
    if (bytes === 0) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const historyModalOpen = ref(false)
const currentDocument = ref(null)
const history = ref([])

const openHistoryModal = async (doc) => {
    currentDocument.value = doc
    // Simulation – à remplacer plus tard par une vraie requête
    history.value = [
        { action: 'creation', utilisateur: 'Moussa Moussa', date: '2025-12-22 10:00', details: 'Upload depuis le navigateur' },
        { action: 'visualisation', utilisateur: 'Moussa Moussa', date: '2025-12-22 10:02', details: null },
        { action: 'telechargement', utilisateur: 'Moussa Moussa', date: '2025-12-22 10:05', details: null },
    ]
    historyModalOpen.value = true
}

// ───────────────────────────────────────────────
// Prévisualisation
// ───────────────────────────────────────────────
const isPreviewOpen = ref(false)
const selectedDoc = ref(null)

const openPreview = (doc) => {
    selectedDoc.value = doc
    isPreviewOpen.value = true
}

const closePreview = () => {
    isPreviewOpen.value = false
    selectedDoc.value = null
}

const isPDF = (doc) => doc.extension?.toLowerCase() === 'pdf'
const isImage = (doc) => ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(doc.extension?.toLowerCase())

// ───────────────────────────────────────────────
// Partage
// ───────────────────────────────────────────────
const shareModalOpen = ref(false)
const shareForm = useForm({
    type: 'utilisateur',
    id: '',
    permissions: 'lecture',
})

const openShareModal = (doc) => {
    currentDocument.value = doc
    shareForm.reset()
    shareForm.type = 'utilisateur'
    shareForm.id = ''
    shareForm.permissions = 'lecture'
    shareModalOpen.value = true
}

const submitShare = () => {
    // Si non-admin et type entite, forcer l'ID à l'entité de l'utilisateur
    if (shareForm.type === 'entite' && !page.props.auth.user.roles.some(r => r.nom === 'super_admin')) {
        shareForm.id = page.props.auth.user.entite_id
    }

    shareForm.post(route('documents.partager', currentDocument.value.uuid), {
        preserveScroll: true,
        onSuccess: () => {
            shareModalOpen.value = false
        },
        onError: () => {
            // Erreurs affichées via shareForm.errors
        }
    })
}
</script>

<template>
    <Head title="Documents" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl text-gray-800 leading-tight">
                Gestion Documentaire
            </h2>
        </template>

        <div class="py-6 md:py-10">
            <div class="page-container">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-4 md:p-8">
                        <!-- Messages flash -->
                        <div v-if="success" class="mb-6 p-4 bg-success-50 border border-success-200 text-success-700 rounded-2xl flex items-center gap-3">
                            <CheckCircleIcon class="w-6 h-6 text-success-600" />
                            <span class="font-medium">{{ success }}</span>
                        </div>
                        <div v-if="error" class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl flex items-center gap-3">
                            <ExclamationTriangleIcon class="w-6 h-6 text-red-600" />
                            <span class="font-medium">{{ error }}</span>
                        </div>

                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                            <div>
                                <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900">Espace documentaire</h1>
                                <p class="text-sm text-gray-500 mt-1">Gérez, partagez et consultez vos documents professionnels.</p>
                            </div>
                            <button v-if="canCreate" @click="showCreateFolder = true" class="btn btn-primary inline-flex items-center">
                                <PlusIcon class="w-5 h-5 mr-2" />
                                Nouveau dossier
                            </button>
                        </div>

                        <!-- Breadcrumb -->
                        <nav v-if="breadcrumb.length > 0" class="flex mb-8 items-center overflow-x-auto pb-2 custom-scrollbar" aria-label="Breadcrumb">
                            <ol class="inline-flex items-center space-x-1 md:space-x-3 whitespace-nowrap">
                                <li class="inline-flex items-center">
                                    <Link :href="route('documents.index')" :class="!cheminComplet ? 'text-primary-700 font-bold' : 'text-gray-500 hover:text-primary-600'" class="flex items-center">
                                        <HomeIcon class="w-5 h-5 mr-2" />
                                        Racine
                                    </Link>
                                </li>
                                <li v-for="(crumb, index) in breadcrumb" :key="crumb.id">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 text-gray-300 mx-1 md:mx-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        <Link :href="route('documents.index', crumb.chemin)" class="text-gray-600 hover:text-primary-600 font-medium">
                                            {{ crumb.nom }}
                                        </Link>
                                    </div>
                                </li>
                            </ol>
                        </nav>

                        <!-- Modal création dossier -->
                        <div v-if="showCreateFolder" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                            <div class="bg-white rounded-lg p-8 w-96 shadow-xl">
                                <h2 class="text-xl font-bold mb-6">Créer un nouveau dossier</h2>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nom du dossier</label>
                                    <input v-model="newFolderName" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="Ex: Contrats 2025" />
                                </div>
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Visibilité</label>
                                    <select v-model="newFolderVisibility" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                        <option value="entite">Toute l'entité</option>
                                        <option value="direction">Ma direction seulement</option>
                                        <option value="prive">Privé (moi seulement)</option>
                                    </select>
                                </div>
                                <div class="flex justify-end space-x-4">
                                    <button @click="showCreateFolder = false" class="text-gray-600 hover:text-gray-800 font-medium">
                                        Annuler
                                    </button>
                                    <button @click="createFolder" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">
                                        Créer le dossier
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Sélecteur d'entité pour Upload (SuperAdmin seulement) -->
                        <div v-if="canCreate && $page.props.auth.user?.roles?.some(r => r.nom === 'super_admin')" class="mb-8 p-4 bg-gray-50 rounded-2xl border border-gray-100 max-w-sm">
                            <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">Cible de destination</label>
                            <select v-model="uploadTargetEntite" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition shadow-sm">
                                <option value="global">🌍 Espace Global (Tous)</option>
                                <option v-for="entite in entites" :key="entite.id" :value="entite.id">
                                    🏢 {{ entite.nom }}
                                </option>
                            </select>
                        </div>

                        <!-- Zone drag & drop (seulement si autorisé) -->
                        <div v-if="canCreate" @dragover="handleDragOver" @dragleave="handleDragLeave" @drop="handleDrop"
                             :class="isDragging ? 'border-primary-500 bg-primary-50 ring-4 ring-primary-100' : 'border-gray-200 bg-gray-50/50'"
                             class="border-2 border-dashed rounded-[2rem] p-8 md:p-16 text-center cursor-pointer mb-10 transition-all duration-300 group hover:border-primary-400 hover:bg-white">
                            <div class="w-16 h-16 md:w-20 md:h-20 bg-white rounded-2xl shadow-sm border border-gray-100 flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300">
                                <svg class="h-10 w-10 text-primary-500" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            <p class="text-lg md:text-xl font-bold text-gray-900">Déposez vos documents ici</p>
                            <p class="text-gray-500 mt-2 text-sm">Formats acceptés : PDF, Word, Excel, images (max 50 Mo)</p>
                            <label class="mt-8 inline-block btn btn-primary cursor-pointer">
                                Parcourir les fichiers
                                <input type="file" multiple @change="handleFileInput" class="hidden" />
                            </label>
                        </div>

                        <!-- Dossiers -->
                        <div v-if="dossiers.length > 0" class="mb-12">
                            <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6 ml-1">Dossiers récents</h2>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 md:gap-6">
                                <div v-for="dossier in dossiers" :key="dossier.id"
                                     @click="router.get(route('documents.index', dossier.chemin_complet))"
                                     class="bg-white rounded-2xl p-6 border border-gray-100 hover:border-warning-200 transition-all duration-300 cursor-pointer shadow-sm hover:shadow-lg hover:shadow-warning-50/50 group text-center">
                                    <FolderIcon class="w-14 h-14 text-warning-400 mx-auto mb-4 group-hover:scale-110 group-hover:-rotate-3 transition-transform duration-300" />
                                    <p class="font-bold text-gray-900 truncate px-2">{{ dossier.nom }}</p>
                                    <p class="text-[10px] font-black text-gray-400 uppercase mt-2 tracking-tighter">{{ dossier.documents?.length || 0 }} fichiers</p>
                                </div>
                            </div>
                        </div>

                        <!-- Documents -->
                        <div v-if="documents.length > 0">
                            <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6 ml-1">Documents</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                                <div v-for="doc in documents" :key="doc.uuid" class="card p-6 border-0 group relative overflow-hidden">
                                    <!-- File Icon / Type -->
                                    <div class="flex justify-between items-start mb-6">
                                        <div :class="[
                                            'w-14 h-14 rounded-2xl flex items-center justify-center shadow-sm border',
                                            doc.extension === 'pdf' ? 'bg-red-50 border-red-100' : 
                                            ['jpg','jpeg','png','gif'].includes(doc.extension.toLowerCase()) ? 'bg-blue-50 border-blue-100' :
                                            ['xls','xlsx'].includes(doc.extension.toLowerCase()) ? 'bg-success-50 border-success-100' : 'bg-gray-50 border-gray-100'
                                        ]">
                                            <component 
                                                :is="doc.extension === 'pdf' ? DocumentTextIcon : 
                                                    ['jpg','jpeg','png','gif'].includes(doc.extension.toLowerCase()) ? PhotoIcon :
                                                    ['doc','docx'].includes(doc.extension.toLowerCase()) ? DocumentIcon :
                                                    ['xls','xlsx'].includes(doc.extension.toLowerCase()) ? DocumentIcon : PaperClipIcon"
                                                class="w-7 h-7 text-gray-700"
                                            />
                                        </div>
                                        
                                        <!-- Actions Menu Button (subtle) -->
                                        <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button @click="openPreview(doc)" class="p-2 text-primary-500 hover:bg-primary-50 rounded-lg" title="Aperçu rapide">
                                                <EyeIcon class="w-5 h-5" />
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Content -->
                                    <h3 class="text-sm font-extrabold text-gray-900 truncate leading-tight mb-2 group-hover:text-primary-600 transition-colors" :title="doc.nom_original">
                                        {{ doc.nom_original }}
                                    </h3>
                                    <div class="flex items-center gap-2 text-[10px] text-gray-400 font-black uppercase tracking-tighter">
                                        <span>{{ formatSize(doc.taille_octets) }}</span>
                                        <span>•</span>
                                        <span>{{ formatDate(doc.created_at) }}</span>
                                    </div>
                                    
                                    <div class="mt-4 flex items-center pt-4 border-t border-gray-50">
                                        <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-[8px] font-black text-gray-400 mr-2 border border-gray-100">
                                            {{ doc.proprietaire?.prenom?.charAt(0) }}{{ doc.proprietaire?.nom?.charAt(0) }}
                                        </div>
                                        <span class="text-[10px] font-bold text-gray-500 truncate">{{ doc.proprietaire?.prenom }} {{ doc.proprietaire?.nom }}</span>
                                    </div>

                                    <!-- Hover Actions Overlay -->
                                    <div class="mt-6 flex flex-wrap gap-2 pt-2">
                                        <a :href="route('documents.download', doc.uuid)" class="flex-1 btn bg-gray-50 hover:bg-gray-100 border-gray-100 text-gray-700 text-[10px] py-1.5 px-2 inline-flex items-center justify-center gap-1">
                                            <ArrowDownTrayIcon class="w-4 h-4" />
                                            Télécharger
                                        </a>
                                        <button @click="openHistoryModal(doc)" class="btn bg-gray-50 hover:bg-gray-100 border-gray-100 text-gray-700 text-[10px] py-1.5 px-2 inline-flex items-center justify-center gap-1">
                                            <ClockIcon class="w-4 h-4" />
                                            Log
                                        </button>
                                        <button v-if="canCreate" @click="openShareModal(doc)" class="btn bg-primary-50 hover:bg-primary-100 border-primary-100 text-primary-600 text-[10px] py-1.5 px-2 inline-flex items-center justify-center gap-1">
                                            <LinkIcon class="w-4 h-4" />
                                            Partager
                                        </button>
                                        <button v-if="canCreate" @click="deleteDocument(doc.uuid)" class="btn bg-red-50 hover:bg-red-500 hover:text-white border-red-50 text-red-500 text-[10px] py-1.5 px-2 inline-flex items-center justify-center gap-1">
                                            <TrashIcon class="w-4 h-4" />
                                            Supprimer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Message vide -->
                        <div v-if="dossiers.length === 0 && documents.length === 0" class="text-center py-24">
                            <FolderOpenIcon class="w-24 h-24 mx-auto mb-6 text-gray-300 opacity-30" />
                            <p class="text-2xl font-extrabold text-gray-400">Ce dossier est vide</p>
                            <p class="text-gray-400 mt-2 max-w-sm mx-auto font-medium">Organisez votre travail en créant des dossiers ou en téléversant vos premiers fichiers dès maintenant.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Historique -->
        <div v-if="historyModalOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-screen overflow-hidden">
                <div class="p-6 border-b">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-bold">Historique - {{ currentDocument?.nom_original }}</h2>
                        <button @click="historyModalOpen = false" class="text-gray-500 hover:text-gray-700 text-2xl">
                            ×
                        </button>
                    </div>
                </div>
                <div class="p-6 overflow-y-auto max-h-96">
                    <div v-if="history.length === 0" class="text-center text-gray-500 py-8">
                        Aucune action enregistrée
                    </div>
                    <div v-else class="space-y-4">
                        <div v-for="entry in history" :key="entry.date" class="border-l-4 border-indigo-500 pl-4 py-2">
                            <div class="flex justify-between">
                                <span class="font-medium capitalize">{{ entry.action }}</span>
                                <span class="text-sm text-gray-500">{{ formatDate(entry.date) }}</span>
                            </div>
                            <p class="text-sm text-gray-700">Par {{ entry.utilisateur }}</p>
                            <p v-if="entry.details" class="text-xs text-gray-600 mt-1">{{ entry.details }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Partage – avec option "Extranet" -->
        <div v-if="shareModalOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4">
                <div class="p-6 border-b flex justify-between items-center">
                    <h2 class="text-xl font-bold">
                        Partager : {{ currentDocument?.nom_original || currentDocument?.titre || 'Document' }}
                    </h2>
                    <button @click="shareModalOpen = false" class="text-gray-500 hover:text-gray-700 text-2xl leading-none">
                        ×
                    </button>
                </div>

                <div class="p-6">
                    <div v-if="shareForm.errors && Object.keys(shareForm.errors).length" class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                        <p v-for="(err, key) in shareForm.errors" :key="key" class="mb-1">
                            {{ err }}
                        </p>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Partager avec</label>
                            <select v-model="shareForm.type" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                <option value="utilisateur">Un utilisateur spécifique</option>
                                <option value="direction">Toute une direction</option>
                                <option value="entite">
                                    {{ $page.props.auth.user?.roles?.some(r => r.nom === 'super_admin') ? "Une entité spécifique" : "Toute mon entité" }}
                                </option>
                                <option v-if="$page.props.auth.user?.roles?.some(r => r.nom === 'super_admin')" value="global">
                                    🌍 Tous les utilisateurs (Global)
                                </option>
                                <option value="extranet">Extranet (tous les invités)</option>
                            </select>
                        </div>

                        <!-- Sélection Entité pour SuperAdmin -->
                        <div v-if="shareForm.type === 'entite' && $page.props.auth.user?.roles?.some(r => r.nom === 'super_admin')">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Sélectionner l'entité</label>
                            <select v-model="shareForm.id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                <option value="" disabled>Choisir une entité</option>
                                <option v-for="entite in entites" :key="entite.id" :value="entite.id">
                                    {{ entite.nom }}
                                </option>
                            </select>
                        </div>

                        <!-- Autres types (Utilisateur/Direction) -->
                        <div v-if="shareForm.type === 'utilisateur' || shareForm.type === 'direction'">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ shareForm.type === 'utilisateur' ? "Utilisateur" : "ID de la direction" }}
                            </label>
                            
                            <!-- Select pour Utilisateur -->
                            <select 
                                v-if="shareForm.type === 'utilisateur'"
                                v-model="shareForm.id" 
                                required 
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                            >
                                <option value="" disabled>Choisir un utilisateur</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">
                                    {{ user.nom }} {{ user.prenom }} {{ $page.props.auth.user?.roles?.some(r => r.nom === 'super_admin') ? '(' + user.entite_id + ')' : '' }}
                                </option>
                            </select>

                            <!-- Input simple pour la direction -->
                            <input 
                                v-else
                                v-model="shareForm.id" 
                                type="number" 
                                required 
                                min="1"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                placeholder="ID de la direction (Ex: 3)"
                            />
                        </div>

                        <!-- Auto-remplissage ID entité pour non-admins si type=entite -->
                        <div v-if="shareForm.type === 'entite' && !$page.props.auth.user?.roles?.some(r => r.nom === 'super_admin')" class="p-3 bg-blue-50 text-blue-700 text-xs rounded-lg font-bold">
                            ℹ️ Le document sera partagé avec tous les membres de votre entité.
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Niveau de permission</label>
                            <select v-model="shareForm.permissions" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                <option value="lecture">Lecture seule (visualisation)</option>
                                <option value="telechargement">Lecture + Téléchargement</option>
                            </select>
                        </div>

                        <div class="flex justify-end gap-4 mt-8">
                            <button 
                                type="button" 
                                @click="shareModalOpen = false" 
                                class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
                            >
                                Annuler
                            </button>
                            <button 
                                type="button"
                                @click="submitShare"
                                :disabled="shareForm.processing"
                                :class="{ 'opacity-50 cursor-not-allowed': shareForm.processing }"
                                class="px-6 py-2.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition flex items-center gap-2"
                            >
                                <span v-if="shareForm.processing">Partage en cours...</span>
                                <span v-else>Partager</span>
                            </button>
                        </div>
                    </div>
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
                            <p class="text-xs text-gray-500 uppercase">{{ selectedDoc.extension }} • {{ formatSize(selectedDoc.taille_octets) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <a :href="route('documents.download', selectedDoc.uuid)" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Télécharger">
                            <ArrowDownTrayIcon class="w-6 h-6" />
                        </a>
                        <button @click="closePreview" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
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