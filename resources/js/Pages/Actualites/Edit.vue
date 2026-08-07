<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import axios from 'axios';
import {
    SpeakerphoneIcon,
    ExclamationTriangleIcon,
    CalendarIcon,
    UsersIcon,
    DocumentTextIcon,
    GlobeAltIcon,
    BuildingOfficeIcon,
    UserIcon,
    BookmarkIcon,
    ArrowUpOnSquareIcon,
    PlusCircleIcon,
    XCircleIcon,
    PhotoIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    annonce: Object,
    visibiliteOptions: Object,
    rolesDisponibles: Array,
    directionsDisponibles: Array,
    groupesDisponibles: Array,
});

const form = ref({
    titre: props.annonce?.titre || '',
    contenu: props.annonce?.contenu || '',
    image: null,
    type_annonce: props.annonce?.type_annonce || 'information',
    cible_type: props.annonce?.cible_type || 'tous',
    direction_id: props.annonce?.direction_id ?? null,
    groupes_cibles: props.annonce?.groupes_cibles ?? [],
    utilisateurs_cibles: props.annonce?.utilisateurs_cibles || '',
    est_epingle: props.annonce?.est_epingle ?? false,
    date_epingle_jusqua: props.annonce?.date_epingle_jusqua || null,
    date_expiration: props.annonce?.date_expiration || null,
    visibilite: props.annonce?.visibilite || 'entite',
    roles_cibles: props.annonce?.roles_cibles ?? [],
    directions_cibles: props.annonce?.directions_cibles ?? [],
});

const previewImage = ref(props.annonce?.image ? '/storage/' + props.annonce.image : null);
const processing = ref(false);
const errors = ref({});

const submit = async () => {
    processing.value = true;
    errors.value = {};

    try {
        const formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('titre', form.value.titre || '');
        formData.append('contenu', form.value.contenu || '');
        formData.append('type_annonce', form.value.type_annonce || 'information');
        formData.append('cible_type', form.value.cible_type || 'tous');
        formData.append('direction_id', form.value.direction_id ?? '');
        formData.append('utilisateurs_cibles', form.value.utilisateurs_cibles || '');
        formData.append('est_epingle', form.value.est_epingle ? '1' : '0');
        formData.append('date_epingle_jusqua', form.value.date_epingle_jusqua || '');
        formData.append('date_expiration', form.value.date_expiration || '');
        formData.append('visibilite', form.value.visibilite || 'entite');
        formData.append('roles_cibles', JSON.stringify(form.value.roles_cibles || []));
        formData.append('groupes_cibles', JSON.stringify(form.value.groupes_cibles || []));
        formData.append('directions_cibles', JSON.stringify(form.value.directions_cibles || []));

        if (form.value.image) {
            formData.append('image', form.value.image);
        }

        await axios.post(
            route('actualites.update', props.annonce.id),
            formData,
            { headers: { 'Content-Type': 'multipart/form-data' } }
        );

        window.location.href = route('actualites.index');

    } catch (error) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        } else {
            console.error('Erreur inattendue :', error);
        }
    } finally {
        processing.value = false;
    }
};

const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.value.image = file;
        previewImage.value = URL.createObjectURL(file);
    }
};

watch(() => form.value.visibilite, (newVal) => {
    if (newVal !== 'roles')      form.value.roles_cibles = [];
    if (newVal !== 'groupes')    form.value.groupes_cibles = [];
    if (newVal !== 'directions') form.value.directions_cibles = [];
});
</script>

<template>
    <Head title="Modifier l'annonce" />
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 px-8 py-10 text-white">
                        <h1 class="text-3xl md:text-4xl font-bold flex items-center">
                            <DocumentTextIcon class="w-8 h-8 mr-3" />
                            Modifier l'annonce
                        </h1>
                        <p class="mt-3 text-indigo-100 flex items-center">
                            <PlusCircleIcon class="w-5 h-5 mr-2" />
                            Mettez à jour le contenu ou la diffusion de cette annonce
                        </p>
                    </div>

                    <div class="p-8">
                        <!-- Affichage des erreurs -->
                        <div v-if="Object.keys(errors).length > 0" class="mb-6 p-4 bg-red-50 rounded-xl border border-red-200 flex items-start">
                            <ExclamationTriangleIcon class="w-6 h-6 text-red-600 mr-3 flex-shrink-0 mt-0.5" />
                            <div>
                                <p class="text-red-600 font-bold">Veuillez corriger :</p>
                                <ul class="mt-2 list-disc list-inside text-red-600 text-sm">
                                    <li v-for="(msg, field) in errors" :key="field">{{ field }} : {{ msg }}</li>
                                </ul>
                            </div>
                        </div>

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
                                />
                                <div v-if="errors.titre" class="text-red-600 text-sm mt-2">{{ errors.titre }}</div>
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
                                ></textarea>
                                <div v-if="errors.contenu" class="text-red-600 text-sm mt-2">{{ errors.contenu }}</div>
                            </div>

                            <!-- Image -->
                            <div>
                                <label class="block text-lg font-semibold text-gray-800 mb-3 flex items-center">
                                    <PhotoIcon class="w-6 h-6 mr-2" />
                                    Image à la une
                                </label>
                                <div class="flex items-center justify-center w-full">
                                    <label class="flex flex-col w-full h-64 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-indigo-500 transition">
                                        <div class="flex flex-col items-center justify-center pt-10">
                                            <PhotoIcon class="w-12 h-12 text-gray-400" />
                                            <p class="mt-4 text-sm text-gray-600">Glissez-déposez une nouvelle image ou cliquez pour sélectionner</p>
                                            <p class="mt-1 text-xs text-gray-500">PNG, JPG, GIF • Max 2 Mo — laisser vide pour conserver l'image actuelle</p>
                                        </div>
                                        <input type="file" @change="handleImageChange" accept="image/*" class="hidden" />
                                    </label>
                                </div>
                                <div v-if="previewImage" class="mt-6">
                                    <img :src="previewImage" class="w-full max-h-80 object-cover rounded-xl shadow-lg" alt="Prévisualisation" />
                                </div>
                                <div v-if="errors.image" class="text-red-600 text-sm mt-2">{{ errors.image }}</div>
                            </div>

                            <!-- Type + Cible + Visibilité -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
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
                                    <div v-if="errors.visibilite" class="text-red-600 text-sm mt-2">{{ errors.visibilite }}</div>
                                </div>

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

                            <!-- Rôles / Directions conditionnels -->
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

                            <!-- Épinglage -->
                            <div class="bg-gray-50 p-6 rounded-xl space-y-6">
                                <label class="flex items-center cursor-pointer">
                                    <input v-model="form.est_epingle" type="checkbox" class="w-5 h-5 text-indigo-600 rounded" />
                                    <BookmarkIcon class="w-5 h-5 ml-3 mr-2 text-indigo-600" />
                                    <span class="text-lg font-semibold text-gray-800">Épingler cette annonce</span>
                                </label>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6" v-if="form.est_epingle">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Épinglée jusqu'au</label>
                                        <input v-model="form.date_epingle_jusqua" type="date" class="w-full px-5 py-3 border rounded-xl" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Expiration</label>
                                        <input v-model="form.date_expiration" type="date" class="w-full px-5 py-3 border rounded-xl" />
                                    </div>
                                </div>
                            </div>

                            <!-- Boutons -->
                            <div class="flex justify-end space-x-4 pt-8 border-t">
                                <Link :href="route('actualites.show', annonce.id)" class="px-8 py-4 bg-gray-200 text-gray-800 rounded-xl hover:bg-gray-300 transition font-medium flex items-center">
                                    <XCircleIcon class="w-5 h-5 mr-2" />
                                    Annuler
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="processing"
                                    class="px-8 py-4 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 disabled:opacity-50 transition font-medium shadow-lg flex items-center"
                                >
                                    <span v-if="processing" class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Enregistrement...
                                    </span>
                                    <span v-else class="flex items-center">
                                        <ArrowUpOnSquareIcon class="w-5 h-5 mr-2" />
                                        Enregistrer
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