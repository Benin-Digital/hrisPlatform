<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    rubriques: Array
});

const showModal = ref(false);
const editingRubrique = ref(null);

const form = useForm({
    nom: '',
    description: '',
    icone: '📁',
    couleur: '#4F46E5',
    ordre: 0,
    est_actif: true
});

const openModal = (rubrique = null) => {
    editingRubrique.value = rubrique;
    if (rubrique) {
        form.nom = rubrique.nom;
        form.description = rubrique.description;
        form.icone = rubrique.icone;
        form.couleur = rubrique.couleur;
        form.ordre = rubrique.ordre;
        form.est_actif = rubrique.est_actif;
    } else {
        form.reset();
    }
    showModal.value = true;
};

const submit = () => {
    if (editingRubrique.value) {
        form.put(route('rubriques.update', editingRubrique.value.id), {
            onSuccess: () => closeModal()
        });
    } else {
        form.post(route('rubriques.store'), {
            onSuccess: () => closeModal()
        });
    }
};

const closeModal = () => {
    showModal.value = false;
    editingRubrique.value = null;
    form.reset();
};

const deleteRubrique = (id) => {
    if (confirm('Supprimer cette rubrique ?')) {
        form.delete(route('rubriques.destroy', id));
    }
};
</script>

<template>
    <Head title="Gestion des Rubriques" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-8 border border-gray-100">
                    <div class="flex justify-between items-center mb-8">
                        <div>
                            <h1 class="text-3xl font-extrabold text-gray-900">Gestion des Rubriques</h1>
                            <p class="text-gray-500 mt-1">Organisez les thèmes de votre plateforme collaborative.</p>
                        </div>
                        <button @click="openModal()" class="px-6 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">
                            + Nouvelle Rubrique
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="rubrique in rubriques" :key="rubrique.id" class="p-6 border rounded-2xl hover:shadow-md transition relative group">
                            <div class="flex items-center space-x-4">
                                <span class="text-4xl p-3 rounded-xl" :style="{ backgroundColor: rubrique.couleur + '20' }">
                                    {{ rubrique.icone || '📁' }}
                                </span>
                                <div class="flex-1">
                                    <h3 class="font-bold text-lg text-gray-900">{{ rubrique.nom }}</h3>
                                    <p class="text-xs text-gray-400 uppercase tracking-widest">{{ rubrique.est_actif ? 'Active' : 'Inactive' }}</p>
                                </div>
                            </div>
                            <p class="mt-4 text-sm text-gray-500 line-clamp-2 min-h-[2.5rem]">{{ rubrique.description || 'Pas de description.' }}</p>
                            
                            <div class="mt-6 flex justify-end space-x-2">
                                <button @click="openModal(rubrique)" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
                                    Modifier
                                </button>
                                <button @click="deleteRubrique(rubrique.id)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition">
                                    Supprimer
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-if="rubriques.length === 0" class="text-center py-20 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                        <p class="text-gray-400 italic">Aucune rubrique créée pour le moment.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal CRUD -->
        <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl animate-in zoom-in duration-200">
                <form @submit.prevent="submit" class="p-8">
                    <h2 class="text-2xl font-bold mb-6 text-gray-900">{{ editingRubrique ? 'Modifier' : 'Créer' }} la Rubrique</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                            <input v-model="form.nom" type="text" class="w-full rounded-xl border-gray-200 focus:ring-indigo-500" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Icône (Emoji)</label>
                            <input v-model="form.icone" type="text" class="w-full rounded-xl border-gray-200" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Couleur</label>
                            <input v-model="form.couleur" type="color" class="w-full h-10 rounded-xl border-gray-200 p-1" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea v-model="form.description" class="w-full rounded-xl border-gray-200" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end space-x-4">
                        <button type="button" @click="closeModal" class="text-gray-500 hover:text-gray-700">Annuler</button>
                        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition shadow-lg">
                            {{ editingRubrique ? 'Sauvegarder' : 'Créer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
