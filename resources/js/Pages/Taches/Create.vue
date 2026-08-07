<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    utilisateurs: Array,
    entites: Array,
});

// Pour gérer l'aperçu des fichiers sélectionnés
const selectedFiles = ref([]);

// Référence vers l'input file (pour le réinitialiser après soumission)
const fileInput = ref(null);

const form = useForm({
    titre: '',
    description: '',
    entite_id: null,
    assigne_a: null,
    date_debut: null,
    date_echeance: null,
    priorite: 'moyenne',
    statut: 'en_attente',
    progression_pourcentage: 0,
    fichiers: [], // Renommé pour correspondre au controller
});

const onFileChange = (e) => {
    const files = Array.from(e.target.files);
    selectedFiles.value = files;
    form.fichiers = files; 
};

const submit = () => {
    form.post('/taches', {
        // Obligatoire pour envoyer des fichiers avec Inertia
        forceFormData: true,

        onSuccess: () => {
            form.reset();
            selectedFiles.value = [];
            // Réinitialise l'input file visuellement
            if (fileInput.value) {
                fileInput.value.value = '';
            }
        },

        onError: () => {
            // Optionnel : scroll vers le haut pour voir les erreurs
            window.scrollTo(0, 0);
        },
    });
};
</script>

<template>
    <Head title="Créer une tâche" />

    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Créer une nouvelle tâche</h1>
            <Link href="/taches" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-bold hover:bg-gray-200 transition">
                ← Retour à la liste
            </Link>
        </div>

        <form @submit.prevent="submit" class="bg-white shadow rounded-lg p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Titre <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.titre"
                        type="text"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <div v-if="form.errors.titre" class="text-red-600 text-sm mt-1">{{ form.errors.titre }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Priorité</label>
                    <select v-model="form.priorite" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="basse">Basse</option>
                        <option value="moyenne">Moyenne</option>
                        <option value="haute">Haute</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Assignée à</label>
                    <select v-model="form.assigne_a" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option :value="null">— Non assignée —</option>
                        <option v-for="u in utilisateurs" :key="u.id" :value="u.id">
                            {{ u.prenom }} {{ u.nom }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Entité</label>
                    <select v-model="form.entite_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option :value="null">— Aucune —</option>
                        <option v-for="e in entites" :key="e.id" :value="e.id">
                            {{ e.nom }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Date de début</label>
                    <input v-model="form.date_debut" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Date d'échéance</label>
                    <input v-model="form.date_echeance" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea
                        v-model="form.description"
                        rows="4"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                    ></textarea>
                </div>

                <!-- Zone d'upload de fichiers -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fichiers joints (optionnel)</label>

                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path
                                    d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4v-8m0 0V12a4 4 0 00-4-4H24l-4 4h-8"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label
                                    for="file-upload"
                                    class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500"
                                >
                                    <span>Sélectionner des fichiers</span>
                                    <input
                                        ref="fileInput"
                                        id="file-upload"
                                        type="file"
                                        multiple
                                        class="sr-only"
                                        @change="onFileChange"
                                    />
                                </label>
                                <p class="pl-1">ou glissez-déposez</p>
                            </div>
                            <p class="text-xs text-gray-500">PDF, images, documents Office, ZIP ⋅ Max 10 Mo par fichier</p>
                        </div>
                    </div>

                    <!-- Aperçu des fichiers sélectionnés -->
                    <div v-if="selectedFiles.length > 0" class="mt-4">
                        <p class="text-sm font-medium text-gray-900">Fichiers à envoyer ({{ selectedFiles.length }}) :</p>
                        <ul class="mt-2 divide-y divide-gray-200 border border-gray-200 rounded-md">
                            <li v-for="(file, index) in selectedFiles" :key="index" class="py-3 px-4 flex justify-between items-center">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ file.name }}</p>
                                    <p class="text-xs text-gray-500">{{ (file.size / 1024 / 1024).toFixed(2) }} Mo</p>
                                </div>
                                <button
                                    type="button"
                                    @click="selectedFiles.splice(index, 1); form.fichiers = selectedFiles"
                                    class="ml-4 text-red-600 hover:text-red-800 text-sm"
                                >
                                    Supprimer
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div v-if="form.errors.fichiers" class="text-red-600 text-sm mt-2">
                        {{ form.errors.fichiers }}
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end space-x-4">
                <Link
                    href="/taches"
                    class="px-6 py-3 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300"
                >
                    Annuler
                </Link>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {{ form.processing ? 'Création en cours...' : 'Créer la tâche' }}
                </button>
            </div>
        </form>
    </div>
</template>