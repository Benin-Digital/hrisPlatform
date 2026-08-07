<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
    formation: Object,
    categories: Array,
    entites: Array,
    users: Array,
});

const form = useForm({
    titre: props.formation.titre,
    sous_titre: props.formation.sous_titre || '',
    description: props.formation.description || '',
    objectifs: props.formation.objectifs || '',
    prerequis: props.formation.prerequis || '',
    niveau: props.formation.niveau,
    duree_minutes: props.formation.duree_minutes,
    categorie_id: props.formation.categorie_id,
    entite_id: props.formation.entite_id,
    formateur_principal_id: props.formation.formateur_principal_id,
    lien_session: props.formation.lien_session || '',
    mode_acces: props.formation.mode_acces || 'interne',
    date_debut: props.formation.date_debut ? props.formation.date_debut.split('T')[0] : '',
    date_fin: props.formation.date_fin ? props.formation.date_fin.split('T')[0] : '',
    date_limite_inscription: props.formation.date_limite_inscription ? props.formation.date_limite_inscription.split('T')[0] : '',
    capacite_max: props.formation.capacite_max,
    statut: props.formation.statut,
    fichiers: [],
    est_public: Boolean(props.formation.est_public),
});

const isDragging = ref(false);

const handleFileChange = (e) => {
    const selectedFiles = Array.from(e.target.files);
    form.fichiers = [...form.fichiers, ...selectedFiles];
};

const handleDrop = (e) => {
    isDragging.value = false;
    const droppedFiles = Array.from(e.dataTransfer.files);
    form.fichiers = [...form.fichiers, ...droppedFiles];
};

const removeNewFile = (index) => {
    form.fichiers.splice(index, 1);
};

const setStatusAndSubmit = (status) => {
    form.statut = status;
    submit();
};

const submit = () => {
    // Sécurité supplémentaire : si la cible est l'extranet, on s'assure que le mode_acces est externe
    if (form.entite_id == 999999) {
        form.mode_acces = 'externe';
    }

    // Utiliser transform pour ajouter _method PUT (spoofing) pour gérer les fichiers en multiparts
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post(route('formations.update', props.formation.id), {
        forceFormData: true,
        onSuccess: () => {
            const statusLabel = {
                'brouillon': 'Brouillon mis à jour',
                'publie': 'Formation Mise à jour & Publiée',
                'archive': 'Formation Archivée'
            }[form.statut];

            Swal.fire({
                title: statusLabel + ' !',
                text: 'Les modifications ont été enregistrées avec succès.',
                icon: 'success',
                confirmButtonText: 'CONTINUER',
                confirmButtonColor: '#4f46e5',
                customClass: {
                    popup: 'rounded-3xl',
                    confirmButton: 'rounded-2xl px-8 py-3 font-black uppercase tracking-widest'
                }
            });
        }
    });
};
</script>

<template>
    <Head :title="'Modifier: ' + formation.titre" />

    <AuthenticatedLayout>
        <div class="py-12 bg-gray-50 min-h-screen pb-32">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-8">
                    <h1 class="text-3xl font-extrabold text-gray-900 italic uppercase tracking-tighter">
                        <span class="text-indigo-600 mr-2">/</span> Modifier Formation
                    </h1>
                    <Link :href="route('formations.index')" class="text-gray-500 hover:text-gray-800 font-bold text-sm">
                        RETOUR
                    </Link>
                </div>

                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Section: Infos Générales -->
                    <div class="bg-white rounded-3xl p-8 shadow-xl border border-indigo-50">
                        <h2 class="text-xs font-black text-indigo-400 uppercase tracking-widest mb-8 border-b border-indigo-50 pb-4">01. Informations Générales</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-black text-gray-700 uppercase mb-2">Titre *</label>
                                <input v-model="form.titre" type="text" :class="{'ring-2 ring-red-500': form.errors.titre}" class="w-full bg-gray-50 border-none rounded-2xl p-4 font-medium focus:ring-2 focus:ring-indigo-500 transition">
                                <div v-if="form.errors.titre" class="text-red-600 text-[10px] font-black uppercase mt-1">/ {{ form.errors.titre }}</div>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-black text-gray-700 uppercase mb-2">Accroche</label>
                                <input v-model="form.sous_titre" type="text" class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-black text-gray-700 uppercase mb-2">Formateur Principal / Intervenant</label>
                                <select v-model="form.formateur_principal_id" class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition">
                                    <option :value="null">Assingation par défaut</option>
                                    <option v-for="user in users" :key="user.id" :value="user.id">
                                        {{ user.prenom }} {{ user.nom }} ({{ user.entite?.nom || 'Sans entité' }})
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-black text-gray-700 uppercase mb-2">Entité cible (Visibilité) *</label>
                                <select v-model="form.entite_id" :class="{'ring-2 ring-red-500': form.errors.entite_id}" class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition">
                                    <option :value="null">Sélectionner une entité</option>
                                    <option v-for="entite in entites" :key="entite.id" :value="entite.id">{{ entite.nom }}</option>
                                </select>
                                <div v-if="form.errors.entite_id" class="text-red-600 text-[10px] font-black uppercase mt-1">/ {{ form.errors.entite_id }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-black text-gray-700 uppercase mb-2">Niveau</label>
                                <select v-model="form.niveau" class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition">
                                    <option value="debutant">Débutant</option>
                                    <option value="intermediaire">Intermédiaire</option>
                                    <option value="avance">Avancé</option>
                                    <option value="expert">Expert</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Contenu détaillé -->
                    <div class="bg-white rounded-3xl p-8 shadow-xl shadow-indigo-100/50 border border-indigo-50">
                        <h2 class="text-xs font-black text-indigo-400 uppercase tracking-widest mb-8 border-b border-indigo-50 pb-4">02. Contenu & Documents</h2>
                        
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-black text-gray-700 uppercase mb-2 italic">Description</label>
                                <textarea v-model="form.description" rows="5" class="w-full bg-gray-50 border-none rounded-3xl p-6 focus:ring-2 focus:ring-indigo-500 transition"></textarea>
                            </div>

                            <!-- Documents -->
                            <div class="pt-6">
                                <label class="block text-sm font-black text-gray-700 uppercase mb-4 italic">Ajouter des documents (PDF, Docs...)</label>
                                
                                <div v-if="formation.fichiers_joints?.length" class="mb-4 space-y-2">
                                    <p class="text-xs font-extrabold text-gray-300 uppercase tracking-widest">Supports Actuels</p>
                                    <div v-for="(file, idx) in formation.fichiers_joints" :key="idx" class="flex items-center text-xs font-bold text-indigo-600 bg-indigo-50/50 px-4 py-3 rounded-2xl border border-indigo-100">
                                        <span class="mr-3 text-xl">📄</span> {{ file.name }}
                                        <span class="ml-auto text-[8px] text-indigo-400 uppercase tracking-tighter">Fichier Stocké</span>
                                    </div>
                                </div>

                                <div 
                                    @dragover.prevent="isDragging = true"
                                    @dragenter.prevent="isDragging = true"
                                    @dragleave.prevent="isDragging = false"
                                    @drop.prevent="handleDrop"
                                    :class="{'border-indigo-500 bg-indigo-50 ring-4 ring-indigo-100': isDragging}"
                                    class="p-8 border-2 border-dashed border-indigo-100 rounded-3xl bg-indigo-50/20 text-center hover:bg-indigo-50 transition cursor-pointer relative overflow-hidden group"
                                >
                                    <div class="relative z-10 pointer-events-none">
                                        <div class="text-3xl mb-2 group-hover:scale-110 transition">📥</div>
                                        <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Glissez les nouveaux supports ici</p>
                                        <p class="text-[8px] text-gray-400 mt-1 uppercase font-bold">{{ form.fichiers.length }} NOUVEAUX FICHIERS</p>
                                    </div>
                                    <input type="file" multiple @change="handleFileChange" class="absolute inset-0 opacity-0 cursor-pointer" />
                                </div>

                                <!-- Liste des nouveaux fichiers -->
                                <div v-if="form.fichiers.length > 0" class="mt-4 space-y-2">
                                    <div v-for="(file, index) in form.fichiers" :key="index" class="flex items-center justify-between p-3 bg-white rounded-2xl border border-indigo-50 shadow-sm">
                                        <div class="flex items-center">
                                            <span class="text-xl mr-3">📄</span>
                                            <div>
                                                <p class="text-[10px] font-black text-gray-700 truncate max-w-[200px]">{{ file.name }}</p>
                                                <p class="text-[9px] text-gray-400 font-bold uppercase">{{ (file.size / 1024 / 1024).toFixed(2) }} MB</p>
                                            </div>
                                        </div>
                                        <button @click="removeNewFile(index)" type="button" class="text-gray-300 hover:text-red-500 p-2 transition">
                                            ✕
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Logistique & Zoom -->
                    <div class="bg-indigo-950 rounded-3xl p-8 shadow-2xl text-white">
                        <h2 class="text-xs font-black text-indigo-300 uppercase tracking-widest mb-8 border-b border-white/10 pb-4">03. Logistique & Digital Hub</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-black text-indigo-200 uppercase mb-2 italic">Lien des sessions Direct</label>
                                <input v-model="form.lien_session" type="url" class="w-full bg-white/5 border border-white/10 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 text-white transition font-medium" placeholder="https://zoom.us/j/...">
                            </div>
                            <div>
                                <label class="block text-sm font-black text-indigo-200 uppercase mb-2 italic tracking-tighter">Public Cible (Accès)</label>
                                <div class="grid grid-cols-3 gap-2 bg-white/5 p-1 rounded-2xl border border-white/10 text-center">
                                    <button 
                                        type="button" 
                                        @click="form.mode_acces = 'interne'" 
                                        :disabled="form.entite_id == 999999"
                                        :class="[
                                            form.mode_acces === 'interne' ? 'bg-indigo-600 text-white shadow-lg' : 'text-white/40 hover:text-white',
                                            form.entite_id == 999999 ? 'opacity-30 cursor-not-allowed' : ''
                                        ]" 
                                        class="py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition"
                                    >
                                        INTRANET seul
                                    </button>
                                    <button 
                                        type="button" 
                                        @click="form.mode_acces = 'externe'" 
                                        :class="form.mode_acces === 'externe' ? 'bg-indigo-600 text-white shadow-lg' : 'text-white/40 hover:text-white'" 
                                        class="py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition"
                                    >
                                        EXTRANET seul
                                    </button>
                                    <button 
                                        type="button" 
                                        @click="form.mode_acces = 'mixte'" 
                                        :disabled="form.entite_id == 999999"
                                        :class="[
                                            form.mode_acces === 'mixte' ? 'bg-indigo-600 text-white shadow-lg' : 'text-white/40 hover:text-white',
                                            form.entite_id == 999999 ? 'opacity-30 cursor-not-allowed' : ''
                                        ]" 
                                        class="py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition"
                                    >
                                        LES DEUX
                                    </button>
                                </div>
                                <p v-if="form.entite_id == 999999" class="text-[9px] text-indigo-400 mt-2 italic">* Cible EXTRANET sélectionnée : le mode EXTRANET SEUL est imposé.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-black text-indigo-200 uppercase mb-2">Visibilité Publique</label>
                                <div class="flex items-center space-x-3 bg-white/5 p-4 rounded-2xl border border-white/10">
                                    <input v-model="form.est_public" type="checkbox" class="rounded border-none bg-white/10 text-indigo-500 focus:ring-0 w-5 h-5 cursor-pointer">
                                    <span class="text-xs font-bold text-white uppercase tracking-wider">Afficher sur la page d'accueil</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-black text-indigo-200 uppercase mb-2">Places Max</label>
                                <input v-model="form.capacite_max" type="number" class="w-full bg-white/5 border border-white/10 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 text-white font-black text-center">
                            </div>
                        </div>
                    </div>

                    <!-- Section: Actions Finales -->
                    <div class="bg-white rounded-3xl p-8 shadow-xl border border-indigo-50">
                        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                            <div class="flex items-center space-x-4 bg-gray-50 p-2 rounded-3xl">
                                <button type="button" @click="setStatusAndSubmit('brouillon')" :disabled="form.processing" :class="form.statut === 'brouillon' ? 'bg-black text-white' : 'bg-white text-gray-500'" class="px-8 py-4 rounded-2xl text-xs font-black uppercase tracking-widest transition shadow-sm">
                                    💾 Garder en Brouillon
                                </button>
                                <button type="button" @click="setStatusAndSubmit('publie')" :disabled="form.processing" :class="form.statut === 'publie' ? 'bg-green-600 text-white' : 'bg-indigo-600 text-white'" class="px-8 py-4 rounded-2xl text-xs font-black uppercase tracking-widest transition hover:bg-black shadow-xl shadow-indigo-100">
                                    🚀 Mettre à jour & Publier
                                </button>
                                <button type="button" @click="setStatusAndSubmit('archive')" :disabled="form.processing" :class="form.statut === 'archive' ? 'bg-red-600 text-white' : 'bg-red-50 text-red-400'" class="px-8 py-4 rounded-2xl text-xs font-black uppercase tracking-widest transition hover:bg-red-600 hover:text-white">
                                    📁 Archiver
                                </button>
                            </div>
                            
                            <div class="text-right">
                                <p class="text-[10px] font-black text-gray-300 uppercase tracking-widest mb-1 italic">Dernière modification</p>
                                <span class="text-xs font-bold text-gray-500">{{ new Date(formation.updated_at).toLocaleDateString() }}</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
