<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import {
    SpeakerphoneIcon,
    ExclamationTriangleIcon,
    CalendarIcon,
    UsersIcon,
    DocumentTextIcon,
    GlobeAltIcon,
    BuildingOfficeIcon,
    UserGroupIcon,
    UserIcon,
    BookmarkIcon,
    SparklesIcon,
    PhotoIcon,
} from '@heroicons/vue/24/outline';

defineProps({
    visibiliteOptions: Object,
    rolesDisponibles: Array,
    directionsDisponibles: Array,
    groupesDisponibles: Array,
});

const form = useForm({
    titre: '',
    contenu: '',
    image: null,
    type_annonce: 'information',
    cible_type: 'tous',
    direction_id: null,
    groupes_cibles: [],
    utilisateurs_cibles: '',
    est_epingle: false,
    date_epingle_jusqua: null,
    date_expiration: null,
    visibilite: 'entite',
    roles_cibles: [],
    directions_cibles: [],
});

const previewImage = ref(null);

const submit = () => {
    form.post(route('actualites.store'), {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            previewImage.value = null;
        },
        onError: (errors) => {
            console.error('Erreurs de validation :', errors);
        },
    });
};

const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.image = file;
        previewImage.value = URL.createObjectURL(file);
    }
};

watch(() => form.visibilite, (newVal) => {
    if (newVal !== 'roles')      form.roles_cibles = [];
    if (newVal !== 'groupes')    form.groupes_cibles = [];
    if (newVal !== 'directions') form.directions_cibles = [];
});
</script>

<template>
    <Head title="Nouvelle annonce" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 px-8 py-10 text-white flex items-center">
                        <SpeakerphoneIcon class="w-10 h-10 mr-4" />
                        <div>
                            <h1 class="text-3xl md:text-4xl font-bold">Publier une nouvelle annonce</h1>
                            <p class="mt-3 text-indigo-100">Créez et diffusez votre message à l'équipe ou à toute la plateforme</p>
                        </div>
                    </div>

                    <div class="p-8">
                        <form @submit.prevent="submit" class="space-y-8">
                            <!-- Titre -->
                            <div>
                                <label class="block text-lg font-semibold text-gray-800 mb-2">
                                    Titre <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    v-model="form.titre" 
                                    type="text" 
                                    required 
                                    class="w-full px-5 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                                    placeholder="Titre accrocheur de l'annonce..."
                                />
                                <div v-if="form.errors.titre" class="text-red-600 text-sm mt-2">
                                    {{ form.errors.titre }}
                                </div>
                            </div>

                            <!-- Contenu -->
                            <div>
                                <label class="block text-lg font-semibold text-gray-800 mb-2">
                                    Contenu <span class="text-red-500">*</span>
                                </label>
                                <textarea
                                    v-model="form.contenu"
                                    rows="10"
                                    required
                                    class="w-full px-5 py-4 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm font-sans resize-y"
                                    placeholder="Votre message ici..."
                                ></textarea>
                                <div v-if="form.errors.contenu" class="text-red-600 text-sm mt-2">
                                    {{ form.errors.contenu }}
                                </div>
                            </div>

                            <!-- Image -->
                            <div>
                                <label class="block text-lg font-semibold text-gray-800 mb-3 flex items-center">
                                    <PhotoIcon class="w-6 h-6 mr-2 text-gray-600" />
                                    Image à la une (optionnelle)
                                </label>
                                <div class="flex items-center justify-center w-full">
                                    <label class="flex flex-col w-full h-64 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-indigo-500 transition">
                                        <div class="flex flex-col items-center justify-center pt-10">
                                            <PhotoIcon class="w-12 h-12 text-gray-400" />
                                            <p class="mt-4 text-sm text-gray-600">Glissez-déposez une image ou cliquez pour sélectionner</p>
                                            <p class="mt-1 text-xs text-gray-500">PNG, JPG, GIF • Max 2 Mo</p>
                                        </div>
                                        <input type="file" @change="handleImageChange" accept="image/*" class="hidden" />
                                    </label>
                                </div>

                                <div v-if="previewImage" class="mt-6">
                                    <img :src="previewImage" class="w-full max-h-80 object-cover rounded-xl shadow-lg" alt="Prévisualisation" />
                                </div>

                                <div v-if="form.errors.image" class="text-red-600 text-sm mt-2">
                                    {{ form.errors.image }}
                                </div>
                            </div>

                            <!-- Type + Cible + Visibilité -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <!-- Type d'annonce -->
                                <div>
                                    <label class="block text-lg font-semibold text-gray-800 mb-2 flex items-center">
                                        <SpeakerphoneIcon class="w-5 h-5 mr-2" />
                                        Type
                                    </label>
                                    <select v-model="form.type_annonce" class="w-full px-5 py-3 border border-gray-300 rounded-xl focus:ring-indigo-500">
                                        <option value="information">Information</option>
                                        <option value="urgent">Urgent</option>
                                        <option value="evenement">Événement</option>
                                        <option value="rh">RH</option>
                                        <option value="autre">Autre</option>
                                    </select>
                                </div>

                                <!-- Visibilité -->
                                <div>
                                    <label class="block text-lg font-semibold text-gray-800 mb-2 flex items-center">
                                        <GlobeAltIcon class="w-5 h-5 mr-2" />
                                        Visibilité <span class="text-red-500">*</span>
                                    </label>
                                    <select v-model="form.visibilite" class="w-full px-5 py-3 border border-gray-300 rounded-xl focus:ring-indigo-500">
                                        <option v-for="(label, value) in visibiliteOptions" :key="value" :value="value">
                                            {{ label }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.visibilite" class="text-red-600 text-sm mt-2">
                                        {{ form.errors.visibilite }}
                                    </div>
                                </div>

                                <!-- Cible principale (compatibilité) -->
                                <div>
                                    <label class="block text-lg font-semibold text-gray-800 mb-2 flex items-center">
                                        <UsersIcon class="w-5 h-5 mr-2" />
                                        Cible principale
                                    </label>
                                    <select v-model="form.cible_type" class="w-full px-5 py-3 border border-gray-300 rounded-xl focus:ring-indigo-500">
                                        <option value="tous">Tous</option>
                                        <option value="direction">Direction</option>
                                        <option value="groupes">Groupes</option>
                                        <option value="utilisateurs">Personnes spécifiques</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Champs conditionnels de visibilité -->
                            <div v-if="form.visibilite === 'roles'" class="mt-6">
                                <label class="block text-lg font-semibold text-gray-800 mb-3 flex items-center">
                                    <UsersIcon class="w-5 h-5 mr-2" />
                                    Rôles ciblés
                                </label>
                                <select multiple v-model="form.roles_cibles" class="w-full h-32 px-4 py-3 border border-gray-300 rounded-xl focus:ring-indigo-500">
                                    <option v-for="role in rolesDisponibles" :key="role.nom" :value="role.nom">
                                        {{ role.nom_affichage || role.nom }}
                                    </option>
                                </select>
                            </div>

                            <div v-if="form.visibilite === 'directions'" class="mt-6">
                                <label class="block text-lg font-semibold text-gray-800 mb-3 flex items-center">
                                    <BuildingOfficeIcon class="w-5 h-5 mr-2" />
                                    Directions ciblées
                                </label>
                                <select multiple v-model="form.directions_cibles" class="w-full h-32 px-4 py-3 border border-gray-300 rounded-xl focus:ring-indigo-500">
                                    <option v-for="dir in directionsDisponibles" :key="dir.id" :value="dir.id">
                                        {{ dir.nom }}
                                    </option>
                                </select>
                            </div>

                            <!-- Épinglage + Dates -->
                            <div class="bg-gray-50 p-6 rounded-xl space-y-6">
                                <label class="flex items-center cursor-pointer">
                                    <input v-model="form.est_epingle" type="checkbox" class="w-5 h-5 text-indigo-600 rounded" />
                                    <BookmarkIcon class="w-5 h-5 ml-3 mr-2 text-indigo-600" />
                                    <span class="text-lg font-semibold text-gray-800">Épingler cette annonce (priorité haute)</span>
                                </label>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6" v-if="form.est_epingle">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Épinglée jusqu'au</label>
                                        <input v-model="form.date_epingle_jusqua" type="date" class="w-full px-5 py-3 border rounded-xl" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Expiration de l'annonce</label>
                                        <input v-model="form.date_expiration" type="date" class="w-full px-5 py-3 border rounded-xl" />
                                    </div>
                                </div>
                            </div>

                            <!-- Boutons -->
                            <div class="flex justify-end space-x-4 pt-8 border-t">
                                <Link :href="route('actualites.index')" class="px-8 py-4 bg-gray-200 text-gray-800 rounded-xl hover:bg-gray-300 transition font-medium">
                                    Annuler
                                </Link>
                                <button 
                                    type="submit" 
                                    :disabled="form.processing"
                                    class="px-8 py-4 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 disabled:opacity-50 transition font-medium shadow-lg flex items-center"
                                >
                                    <span v-if="form.processing" class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Publication...
                                    </span>
                                    <span v-else class="flex items-center">
                                        <SparklesIcon class="w-5 h-5 mr-2" />
                                        Publier
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>