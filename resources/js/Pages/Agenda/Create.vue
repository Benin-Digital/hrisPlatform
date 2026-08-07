<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    visibiliteOptions: Object,
    rolesDisponibles: Array,
    directionsDisponibles: Array,
    // groupesDisponibles: Array, // à ajouter si tu as un modèle Groupe
});

const form = useForm({
    titre: '',
    description: '',
    date_debut: '',
    date_fin: '',
    fuseau_horaire: 'Africa/Porto-Novo',
    duree_minutes: null,
    type_evenement: 'reunion',
    categorie: '',
    couleur: '#3B82F6',
    lieu: '',
    lien_virtuel: '',
    type_lieu: 'presentiel',
    organisateur_id: null, // optionnel, par défaut l'utilisateur connecté
    capacite_max: null,
    inscription_requise: false,
    est_recurrent: false,
    recurrence_pattern: null,
    date_fin_recurrence: null,
    statut: 'publie',
    // Visibilité et ciblage
    visibilite: 'entite',
    roles_cibles: [],
    groupes_cibles: [],
    directions_cibles: [],
    // Épinglage
    est_epingle: false,
    date_epingle_jusqua: null,
    entite_id: null, // optionnel, par défaut l'entité de l'utilisateur connecté    
    nombre_participants: null, // 
    nombre_inscrits: 0, // 
});

const submit = () => {
    form.post(route('agenda.store'), {
        onSuccess: () => form.reset(),
        preserveScroll: true,
    });
};

// Reset champs conditionnels quand visibilite change
watch(() => form.visibilite, (newVal) => {
    if (newVal !== 'roles') form.roles_cibles = [];
    if (newVal !== 'groupes') form.groupes_cibles = [];
    if (newVal !== 'directions') form.directions_cibles = [];
});
</script>

<template>
    <Head title="Créer un événement" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 px-8 py-10 text-white">
                        <h1 class="text-3xl md:text-4xl font-bold">Créer un nouvel événement</h1>
                        <p class="mt-3 text-indigo-100">Ajoutez une réunion, formation, événement social ou autre</p>
                    </div>

                    <div class="p-8 lg:p-12">
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
                                    placeholder="Ex : Réunion d'équipe mensuelle"
                                />
                                <div v-if="form.errors.titre" class="text-red-600 text-sm mt-2">
                                    {{ form.errors.titre }}
                                </div>
                            </div>

                            <!-- Dates -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-lg font-semibold text-gray-800 mb-2">
                                        Date et heure de début <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.date_debut"
                                        type="datetime-local"
                                        required
                                        class="w-full px-5 py-3 border border-gray-300 rounded-xl focus:ring-indigo-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-lg font-semibold text-gray-800 mb-2">
                                        Date et heure de fin
                                    </label>
                                    <input
                                        v-model="form.date_fin"
                                        type="datetime-local"
                                        class="w-full px-5 py-3 border border-gray-300 rounded-xl focus:ring-indigo-500"
                                    />
                                </div>
                            </div>

                            <!-- Type + Couleur + Lieu -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-lg font-semibold text-gray-800 mb-2">Type</label>
                                    <select v-model="form.type_evenement" class="w-full px-5 py-3 border rounded-xl focus:ring-indigo-500">
                                        <option value="reunion">Réunion</option>
                                        <option value="formation">Formation</option>
                                        <option value="evenement_social">Événement social</option>
                                        <option value="rendez_vous">Rendez-vous</option>
                                        <option value="autre">Autre</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-lg font-semibold text-gray-800 mb-2">Couleur</label>
                                    <input
                                        v-model="form.couleur"
                                        type="color"
                                        class="w-full h-12 border rounded-xl cursor-pointer"
                                    />
                                </div>

                                <div>
                                    <label class="block text-lg font-semibold text-gray-800 mb-2">Lieu / Type</label>
                                    <select v-model="form.type_lieu" class="w-full px-5 py-3 border rounded-xl focus:ring-indigo-500">
                                        <option value="presentiel">Présentiel</option>
                                        <option value="virtuel">Virtuel</option>
                                        <option value="hybride">Hybride</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Lieu + Lien virtuel -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-lg font-semibold text-gray-800 mb-2">Lieu physique</label>
                                    <input
                                        v-model="form.lieu"
                                        type="text"
                                        class="w-full px-5 py-3 border rounded-xl focus:ring-indigo-500"
                                        placeholder="Salle de conférence A, 3ème étage"
                                    />
                                </div>

                                <div>
                                    <label class="block text-lg font-semibold text-gray-800 mb-2">Lien virtuel (Zoom, Teams...)</label>
                                    <input
                                        v-model="form.lien_virtuel"
                                        type="url"
                                        class="w-full px-5 py-3 border rounded-xl focus:ring-indigo-500"
                                        placeholder="https://zoom.us/j/123456789"
                                    />
                                </div>
                            </div>

                            <!-- Visibilité -->
                            <div>
                                <label class="block text-lg font-semibold text-gray-800 mb-2">
                                    Visibilité <span class="text-red-500">*</span>
                                </label>
                                <select v-model="form.visibilite" class="w-full px-5 py-3 border rounded-xl focus:ring-indigo-500">
                                    <option v-for="(label, value) in visibiliteOptions" :key="value" :value="value">
                                        {{ label }}
                                    </option>
                                </select>
                            </div>

                            <!-- Champs conditionnels visibilité -->
                            <div v-if="form.visibilite === 'roles'" class="mt-4">
                                <label class="block text-lg font-semibold text-gray-800 mb-2">Rôles ciblés</label>
                                <select multiple v-model="form.roles_cibles" class="w-full h-32 px-4 py-3 border rounded-xl">
                                    <option v-for="role in rolesDisponibles" :key="role.nom" :value="role.nom">
                                        {{ role.nom_affichage || role.nom }}
                                    </option>
                                </select>
                            </div>

                            <div v-if="form.visibilite === 'directions'" class="mt-4">
                                <label class="block text-lg font-semibold text-gray-800 mb-2">Directions ciblées</label>
                                <select multiple v-model="form.directions_cibles" class="w-full h-32 px-4 py-3 border rounded-xl">
                                    <option v-for="dir in directionsDisponibles" :key="dir.id" :value="dir.id">
                                        {{ dir.nom }}
                                    </option>
                                </select>
                            </div>

                            <!-- Épinglage -->
                            <div class="bg-gray-50 p-6 rounded-xl">
                                <label class="flex items-center cursor-pointer">
                                    <input v-model="form.est_epingle" type="checkbox" class="w-5 h-5 text-indigo-600 rounded" />
                                    <span class="ml-3 text-lg font-semibold text-gray-800">📌 Épingler cet événement (priorité haute)</span>
                                </label>

                                <div v-if="form.est_epingle" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Épinglé jusqu'au</label>
                                        <input v-model="form.date_epingle_jusqua" type="date" class="w-full px-5 py-3 border rounded-xl" />
                                    </div>
                                </div>
                            </div>

                            <!-- Boutons -->
                            <div class="flex justify-end gap-4 pt-8 border-t">
                                <Link :href="route('agenda.index')" class="px-8 py-4 bg-gray-200 text-gray-800 rounded-xl hover:bg-gray-300 transition">
                                    Annuler
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-8 py-4 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 disabled:opacity-50 transition flex items-center shadow-lg"
                                >
                                    <span v-if="form.processing">
                                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Création...
                                    </span>
                                    <span v-else>Créer l'événement</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>