<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    evenement: Object,
    visibiliteOptions: Object,
    rolesDisponibles: Array,
    directionsDisponibles: Array,
});

const form = useForm({
    titre: props.evenement.titre,
    description: props.evenement.description,
    date_debut: props.evenement.date_debut,
    date_fin: props.evenement.date_fin,
    type_evenement: props.evenement.type_evenement,
    categorie: props.evenement.categorie,
    couleur: props.evenement.couleur,
    lieu: props.evenement.lieu,
    lien_virtuel: props.evenement.lien_virtuel,
    type_lieu: props.evenement.type_lieu,
    statut: props.evenement.statut,
    visibilite: props.evenement.visibilite,
    // Add other fields as needed
});

const submit = () => {
    form.put(route('agenda.update', props.evenement.id), {
        onSuccess: () => {},
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Modifier l'événement" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 px-8 py-10 text-white">
                        <h1 class="text-3xl md:text-4xl font-bold">Modifier l'événement</h1>
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
                                        <option value="physique">Physique</option>
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
                                    />
                                </div>

                                <div>
                                    <label class="block text-lg font-semibold text-gray-800 mb-2">Lien virtuel</label>
                                    <input
                                        v-model="form.lien_virtuel"
                                        type="url"
                                        class="w-full px-5 py-3 border rounded-xl focus:ring-indigo-500"
                                    />
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
                                    <span v-if="form.processing">Modification...</span>
                                    <span v-else>Enregistrer les modifications</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
