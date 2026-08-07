<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps({
    tache: Object,
    utilisateurs: Array,
    entites: Array,
});

const selectedFiles = ref([]);
const fileInput = ref(null);

const form = useForm({
    titre: props.tache.titre,
    description: props.tache.description,
    entite_id: props.tache.entite_id,
    assigne_a: props.tache.assigne_a,
    date_debut: props.tache.date_debut ? new Date(props.tache.date_debut).toISOString().split('T')[0] : null,
    date_echeance: props.tache.date_echeance ? new Date(props.tache.date_echeance).toISOString().split('T')[0] : null,
    priorite: props.tache.priorite,
    statut: props.tache.statut,
    progression_pourcentage: props.tache.progression_pourcentage,
    fichiers: [], // Pour les nouveaux fichiers uniquement
    _method: 'PUT', // Pour simuler PUT via POST (nécessaire pour Multipart FormData)
});

const onFileChange = (e) => {
    const files = Array.from(e.target.files);
    selectedFiles.value = files;
    form.fichiers = files;
};

const submit = () => {
    form.post(route('taches.update', props.tache.id), {
        forceFormData: true,
        onSuccess: () => {
            selectedFiles.value = [];
            if (fileInput.value) fileInput.value.value = '';
        },
    });
};
</script>

<template>
    <Head title="Modifier la tâche" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 italic uppercase tracking-tighter">Édition de Tâche</h1>
                <Link :href="route('taches.show', props.tache.id)" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-bold hover:bg-gray-200 transition">
                    ← Détails
                </Link>
            </div>

            <form @submit.prevent="submit" class="bg-white shadow-2xl rounded-3xl p-10 border border-gray-100">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Titre du Projet / Tâche</label>
                        <input
                            v-model="form.titre"
                            type="text"
                            required
                            class="w-full rounded-2xl border-gray-200 focus:ring-indigo-500 font-bold"
                            placeholder="Entrez le titre..."
                        />
                        <div v-if="form.errors.titre" class="text-red-500 text-[10px] font-bold mt-1 uppercase">{{ form.errors.titre }}</div>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Assigné à</label>
                        <select v-model="form.assigne_a" class="w-full rounded-2xl border-gray-200 font-medium">
                            <option :value="null">— Non assignée —</option>
                            <option v-for="u in utilisateurs" :key="u.id" :value="u.id">
                                {{ u.prenom }} {{ u.nom }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Priorité</label>
                        <select v-model="form.priorite" class="w-full rounded-2xl border-gray-200 font-medium">
                            <option value="basse">Basse</option>
                            <option value="moyenne">Moyenne</option>
                            <option value="haute">Haute</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Date de début</label>
                        <input v-model="form.date_debut" type="date" class="w-full rounded-2xl border-gray-200" />
                    </div>

                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Échéance finale</label>
                        <input v-model="form.date_echeance" type="date" class="w-full rounded-2xl border-gray-200" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Description & Objectifs</label>
                        <textarea
                            v-model="form.description"
                            rows="5"
                            class="w-full rounded-2xl border-gray-200 leading-relaxed font-normal"
                            placeholder="Décrivez les attendus..."
                        ></textarea>
                    </div>

                    <!-- Zone d'upload additionnel -->
                    <div class="md:col-span-2 p-6 bg-gray-50 rounded-3xl border-2 border-dashed border-gray-200">
                        <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-4">Ajouter de nouveaux documents</label>
                        <input
                            ref="fileInput"
                            type="file"
                            multiple
                            @change="onFileChange"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                        />
                        
                        <div v-if="selectedFiles.length > 0" class="mt-4 space-y-2">
                            <div v-for="(file, index) in selectedFiles" :key="index" class="text-xs font-bold text-gray-600 bg-white p-2 rounded-lg flex justify-between">
                                <span>{{ file.name }}</span>
                                <button type="button" @click="selectedFiles.splice(index, 1); form.fichiers = selectedFiles" class="text-red-500">Supprimer</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-10 flex justify-end space-x-4">
                    <Link :href="route('taches.show', props.tache.id)" class="px-8 py-3 bg-gray-100 text-gray-600 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-gray-200 transition">
                        Annuler
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-8 py-3 bg-black text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl hover:bg-gray-800 transition disabled:opacity-50"
                    >
                        {{ form.processing ? 'SYNCHRONISATION...' : 'ENREGISTRER LES MODIFICATIONS' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>