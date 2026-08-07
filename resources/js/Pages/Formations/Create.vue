<script setup>
import { ref } from 'vue';
import Swal from 'sweetalert2';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    categories: Array,
    entites: Array,
    users: Array,
});

const form = useForm({
    titre: '',
    sous_titre: '',
    description: '',
    objectifs: '',
    prerequis: '',
    niveau: 'debutant',
    duree_minutes: 60,
    categorie_id: null,
    entite_id: null,
    formateur_principal_id: null,
    lien_session: '',
    mode_acces: 'interne',
    date_debut: '',
    date_fin: '',
    date_limite_inscription: '',
    capacite_max: 20,
    statut: 'brouillon',
    fichiers: [],
    est_public: false,
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

const removeFile = (index) => {
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
    
    form.post(route('formations.store'), {
        forceFormData: true,
        onSuccess: () => {
            const isDraft = form.statut === 'brouillon';
            Swal.fire({
                title: isDraft ? 'Brouillon Enregistré !' : 'Formation Publiée !',
                text: isDraft 
                    ? 'La formation a été enregistrée de manière sécurisée en tant que brouillon.' 
                    : 'La formation est maintenant accessible aux collaborateurs de l\'entité cible.',
                icon: 'success',
                confirmButtonText: 'PARFAIT',
                confirmButtonColor: '#4f46e5',
                customClass: {
                    popup: 'rounded-3xl',
                    confirmButton: 'rounded-2xl px-8 py-3 font-black uppercase tracking-widest'
                }
            });
        },
    });
};

</script>

<template>
    <Head title="Nouvelle Formation" />

    <AuthenticatedLayout>
        <div class="py-12 bg-gray-50 min-h-screen pb-32">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-8">
                    <h1 class="text-3xl font-extrabold text-gray-900 italic uppercase tracking-tighter">
                        <span class="text-indigo-600 mr-2">/</span> Programmer une formation
                    </h1>
                    <Link :href="route('formations.index')" class="text-gray-500 hover:text-gray-800 font-bold text-sm">
                        ANNULER
                    </Link>
                </div>

                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Section: Infos Générales -->
                    <div class="bg-white rounded-3xl p-8 shadow-xl shadow-indigo-100/50 border border-indigo-50">
                        <h2 class="text-xs font-black text-indigo-400 uppercase tracking-widest mb-8 border-b border-indigo-50 pb-4">01. Informations Générales</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-black text-gray-700 uppercase mb-2 italic">Titre de la formation *</label>
                                <input v-model="form.titre" type="text" :class="{'ring-2 ring-red-500': form.errors.titre}" class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition font-medium" placeholder="Ex: Maîtriser le Management Hybride">
                                <div v-if="form.errors.titre" class="text-red-600 text-[10px] font-black uppercase mt-1">/ {{ form.errors.titre }}</div>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-black text-gray-700 uppercase mb-2">Sous-titre / Accroche</label>
                                <input v-model="form.sous_titre" type="text" class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition" placeholder="Les clés pour réussir vos réunions à distance">
                            </div>

                            <div>
                                <label class="block text-sm font-black text-gray-700 uppercase mb-2">Formateur Principal / Intervenant</label>
                                <select v-model="form.formateur_principal_id" class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition">
                                    <option :value="null">Moi-même (Auto-assignation)</option>
                                    <option v-for="user in users" :key="user.id" :value="user.id">
                                        {{ user.prenom }} {{ user.nom }} ({{ user.entite?.nom || 'Sans entité' }})
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-black text-gray-700 uppercase mb-2 italic">Entité cible (Visibilité) *</label>
                                <select v-model="form.entite_id" :class="{'ring-2 ring-red-500': form.errors.entite_id}" class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition">
                                    <option :value="null">Sélectionner une entité</option>
                                    <option v-for="entite in entites" :key="entite.id" :value="entite.id">{{ entite.nom }}</option>
                                </select>
                                <div v-if="form.errors.entite_id" class="text-red-600 text-[10px] font-black uppercase mt-1">/ {{ form.errors.entite_id }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-black text-gray-700 uppercase mb-2">Niveau</label>
                                <select v-model="form.niveau" class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition font-bold text-indigo-600">
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
                        <h2 class="text-xs font-black text-indigo-400 uppercase tracking-widest mb-8 border-b border-indigo-50 pb-4">02. Contenu & Objectifs</h2>
                        
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-black text-gray-700 uppercase mb-2 italic">Description complète</label>
                                <textarea v-model="form.description" rows="6" class="w-full bg-gray-50 border-none rounded-3xl p-6 focus:ring-2 focus:ring-indigo-500 transition"></textarea>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div>
                                    <label class="block text-sm font-black text-gray-700 uppercase mb-2 italic">Objectifs (un par ligne)</label>
                                    <textarea v-model="form.objectifs" rows="4" class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition text-sm"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-black text-gray-700 uppercase mb-2 italic">Prérequis</label>
                                    <textarea v-model="form.prerequis" rows="4" class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition text-sm"></textarea>
                                </div>
                            </div>

                            <!-- Documents à partager -->
                            <div class="pt-6">
                                <label class="block text-sm font-black text-gray-700 uppercase mb-4 italic">Documents de formation à partager (PDF, Docs...)</label>
                                <div 
                                    @dragover.prevent="isDragging = true"
                                    @dragenter.prevent="isDragging = true"
                                    @dragleave.prevent="isDragging = false"
                                    @drop.prevent="handleDrop"
                                    :class="{'border-indigo-500 bg-indigo-50 ring-4 ring-indigo-100': isDragging}"
                                    class="p-8 border-2 border-dashed border-indigo-100 rounded-3xl bg-indigo-50/20 text-center group hover:bg-indigo-50 transition cursor-pointer relative overflow-hidden"
                                >
                                    <div class="relative z-10 pointer-events-none">
                                        <div class="text-4xl mb-2 group-hover:scale-110 transition">📎</div>
                                        <p class="text-xs font-black text-indigo-400 uppercase tracking-widest">Cliquez ou glissez vos fichiers ici</p>
                                        <p class="text-[10px] text-gray-400 mt-1 uppercase font-bold">{{ form.fichiers.length }} FICHIER(S) SELECTIONNÉ(S)</p>
                                    </div>
                                    <input type="file" multiple @change="handleFileChange" class="absolute inset-0 opacity-0 cursor-pointer" />
                                </div>

                                <!-- Liste des fichiers sélectionnés -->
                                <div v-if="form.fichiers.length > 0" class="mt-4 space-y-2">
                                    <div v-for="(file, index) in form.fichiers" :key="index" class="flex items-center justify-between p-3 bg-white rounded-2xl border border-indigo-50 shadow-sm">
                                        <div class="flex items-center">
                                            <span class="text-xl mr-3">📄</span>
                                            <div>
                                                <p class="text-xs font-black text-gray-700 truncate max-w-[200px]">{{ file.name }}</p>
                                                <p class="text-[10px] text-gray-400 uppercase font-bold">{{ (file.size / 1024 / 1024).toFixed(2) }} MB</p>
                                            </div>
                                        </div>
                                        <button @click="removeFile(index)" type="button" class="text-gray-300 hover:text-red-500 p-2 transition">
                                            ✕
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Logistique & Zoom -->
                    <div class="bg-indigo-950 rounded-3xl p-8 shadow-2xl text-white">
                        <h2 class="text-xs font-black text-indigo-300 uppercase tracking-widest mb-8 border-b border-white/10 pb-4">03. Logistique & Digital Learning</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-black text-indigo-200 uppercase mb-2 italic tracking-tighter">Lien de la session (Zoom / Teams / Meet)</label>
                                <div class="relative">
                                    <span class="absolute left-6 top-4 text-indigo-400 font-extrabold text-[10px] uppercase tracking-widest">Digital Hub</span>
                                    <input v-model="form.lien_session" type="url" class="w-full bg-white/5 border border-white/10 rounded-2xl p-4 pl-24 focus:ring-2 focus:ring-indigo-500 transition text-white placeholder-white/10 font-medium" placeholder="https://zoom.us/j/...">
                                </div>
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

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-black text-indigo-200 uppercase mb-2">Durée (min)</label>
                                    <input v-model="form.duree_minutes" type="number" class="w-full bg-white/5 border border-white/10 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition font-black text-center">
                                </div>
                                <div>
                                    <label class="block text-sm font-black text-indigo-200 uppercase mb-2">Places Max</label>
                                    <input v-model="form.capacite_max" type="number" class="w-full bg-white/5 border border-white/10 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition font-black text-center">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Programmation & Actions -->
                    <div class="bg-white rounded-3xl p-8 shadow-xl shadow-indigo-100/50 border border-indigo-50">
                        <h2 class="text-xs font-black text-indigo-400 uppercase tracking-widest mb-8 border-b border-indigo-50 pb-4">04. Calendrier & Session</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                            <div>
                                <label class="block text-sm font-black text-gray-700 uppercase mb-2">Date de début</label>
                                <input v-model="form.date_debut" type="date" :class="{'ring-2 ring-red-500': form.errors.date_debut}" class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition font-bold">
                                <div v-if="form.errors.date_debut" class="text-red-600 text-[10px] font-black uppercase mt-1">/ {{ form.errors.date_debut }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-black text-gray-700 uppercase mb-2">Date de fin</label>
                                <input v-model="form.date_fin" type="date" :class="{'ring-2 ring-red-500': form.errors.date_fin}" class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition font-bold text-gray-400">
                                <div v-if="form.errors.date_fin" class="text-red-600 text-[10px] font-black uppercase mt-1">/ {{ form.errors.date_fin }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-black text-gray-700 uppercase mb-2">Fin Inscriptions</label>
                                <input v-model="form.date_limite_inscription" type="date" :class="{'ring-2 ring-red-500': form.errors.date_limite_inscription}" class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition font-bold text-red-400">
                                <div v-if="form.errors.date_limite_inscription" class="text-red-600 text-[10px] font-black uppercase mt-1">/ {{ form.errors.date_limite_inscription }}</div>
                            </div>
                        </div>

                        <!-- Main Actions Bar -->
                        <div class="pt-10 border-t border-gray-50 flex flex-col md:flex-row items-center justify-between gap-6">
                            <div class="flex items-center space-x-4 bg-gray-50 p-2 rounded-3xl">
                                <button type="button" @click="setStatusAndSubmit('brouillon')" :disabled="form.processing" class="px-8 py-4 rounded-2xl text-xs font-black uppercase tracking-widest transition bg-white text-gray-600 hover:bg-black hover:text-white shadow-sm disabled:opacity-50">
                                    💾 Enregistrer Brouillon
                                </button>
                                <button type="button" @click="setStatusAndSubmit('publie')" :disabled="form.processing" class="px-8 py-4 rounded-2xl text-xs font-black uppercase tracking-widest transition bg-indigo-600 text-white hover:bg-black shadow-xl shadow-indigo-200 disabled:opacity-50">
                                    🚀 Publier la formation
                                </button>
                            </div>
                            
                            <div class="text-right">
                                <p class="text-[10px] font-black text-gray-300 uppercase tracking-[0.2em] mb-1">Status Actuel</p>
                                <span class="px-4 py-1.5 bg-black text-white text-[10px] font-black uppercase tracking-tighter rounded-full">
                                    {{ form.statut === 'publie' ? 'Publication imminente' : 'Mode Brouillon' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
