<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import Swal from 'sweetalert2';
import { useForm } from '@inertiajs/vue3';

// ✅ Définition de route (helper Ziggy)
const route = window.route;

const props = defineProps({
    formation: {
        type: Object,
        required: true
    },
    inscription: {
        type: Object,
        nullable: true
    },
    canManage: {
        type: Boolean,
        default: false
    }
});

const isRegistered = computed(() => !!props.inscription);

const formatDate = (date) => {
    if (!date) return 'À définir';
    return new Date(date).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

const deleteFormation = () => {
    if (confirm('Voulez-vous vraiment supprimer cette formation ? Cette action est irréversible.')) {
        router.delete(route('formations.destroy', props.formation.id));
    }
};

const inscrireFormation = () => {
    Swal.fire({
        title: 'Confirmer l\'inscription ?',
        text: "En vous inscrivant, vous aurez accès à tous les contenus et sessions de cette formation.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'OUI, M\'INSCRIRE',
        cancelButtonText: 'ANNULER',
        customClass: {
            popup: 'rounded-3xl',
            confirmButton: 'rounded-2xl px-6 py-3 font-black uppercase tracking-widest',
            cancelButton: 'rounded-2xl px-6 py-3 font-black uppercase tracking-widest'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('formations.inscrire', props.formation.id), {}, {
                onSuccess: () => {
                    Swal.fire({
                        title: 'Félicitations !',
                        text: 'Vous êtes maintenant inscrit à la formation. Vous pouvez accéder aux contenus ci-dessous.',
                        icon: 'success',
                        confirmButtonColor: '#4f46e5',
                        customClass: {
                            popup: 'rounded-3xl',
                            confirmButton: 'rounded-2xl px-6 py-3 font-black uppercase tracking-widest'
                        }
                    });
                },
                onError: (errors) => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: errors.message || 'Une erreur est survenue lors de l\'inscription.',
                        confirmButtonColor: '#ef4444'
                    });
                }
            });
        }
    });
};

const evalForm = useForm({
    note: 0,
    commentaire: ''
});

const submitEvaluation = () => {
    if (evalForm.note === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Note manquante',
            text: 'Merci de sélectionner une note (étoiles) avant d\'envoyer.',
            confirmButtonColor: '#4f46e5'
        });
        return;
    }

    evalForm.post(route('formations.evaluation', props.formation.id), {
        preserveScroll: true,
        onSuccess: () => {
            Swal.fire({
                icon: 'success',
                title: 'Merci !',
                text: 'Votre avis a bien été enregistré.',
                confirmButtonColor: '#4f46e5',
                customClass: { popup: 'rounded-3xl' }
            });
            evalForm.reset();
        },
        onError: (errors) => {
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: errors.message || 'Une erreur est survenue lors de l\'envoi de l\'évaluation.',
                confirmButtonColor: '#ef4444'
            });
        }
    });
};
</script>

<template>
    <Head :title="formation.titre" />

    <AuthenticatedLayout>
        <div class="py-12 bg-gray-50 dark:bg-gray-900 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Management Bar -->
                <div v-if="canManage" class="mb-6 flex justify-end space-x-4">
                    <Link 
                        :href="route('formations.edit', formation.id)"
                        class="px-6 py-2 bg-white dark:bg-gray-800 border-2 border-indigo-600 dark:border-indigo-400 text-indigo-600 dark:text-indigo-400 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600 dark:hover:text-white transition shadow-sm"
                    >
                        📝 Modifier la formation
                    </Link>
                    <button 
                        @click="deleteFormation"
                        class="px-6 py-2 bg-white dark:bg-gray-800 border-2 border-red-500 text-red-500 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-red-500 hover:text-white transition shadow-sm"
                    >
                        🗑️ Supprimer
                    </button>
                </div>

                <!-- Header Banner -->
                <div class="relative bg-indigo-900 rounded-3xl overflow-hidden mb-12 shadow-2xl">
                    <div class="absolute inset-0 opacity-30 bg-gradient-to-r from-indigo-900 to-purple-800"></div>
                    <img 
                        v-if="formation.image_couverture" 
                        :src="'/storage/' + formation.image_couverture" 
                        class="absolute inset-0 w-full h-full object-cover mix-blend-overlay"
                    />
                    
                    <div class="relative p-10 md:p-16 z-10 flex flex-col justify-end min-h-[400px]">
                        <span class="inline-flex px-4 py-1.5 rounded-full bg-indigo-500 text-white text-xs font-bold uppercase tracking-widest mb-6 w-fit">
                            {{ formation.categorie?.nom || 'Formation' }}
                        </span>
                        <h1 class="text-4xl md:text-6xl font-black text-white leading-tight mb-4">
                            {{ formation.titre }}
                        </h1>
                        <p class="text-xl text-indigo-100 font-medium max-w-2xl mb-8">
                            {{ formation.sous_titre }}
                        </p>
                        
                        <div class="flex flex-wrap items-center gap-6">
                            <div class="flex items-center text-white/90">
                                <span class="bg-white/10 p-2 rounded-lg mr-3">🕒</span>
                                <div>
                                    <p class="text-[10px] uppercase font-bold text-indigo-300">Durée</p>
                                    <p class="font-bold">{{ formation.duree_minutes }} min</p>
                                </div>
                            </div>
                            <div class="flex items-center text-white/90 border-l border-white/20 pl-6">
                                <span class="bg-white/10 p-2 rounded-lg mr-3">🏆</span>
                                <div>
                                    <p class="text-[10px] uppercase font-bold text-indigo-300">Niveau</p>
                                    <p class="font-bold capitalize">{{ formation.niveau }}</p>
                                </div>
                            </div>
                            <div class="flex items-center text-white/90 border-l border-white/20 pl-6">
                                <span class="bg-white/10 p-2 rounded-lg mr-3">👤</span>
                                <div>
                                    <p class="text-[10px] uppercase font-bold text-indigo-300">Formateur</p>
                                    <p class="font-bold">{{ formation.formateur?.prenom }} {{ formation.formateur?.nom }}</p>
                                </div>
                            </div>
                            <div v-if="formation.note_moyenne" class="flex items-center text-white/90 border-l border-white/20 pl-6">
                                <span class="text-yellow-400 text-2xl mr-2">★</span>
                                <div>
                                    <p class="text-[10px] uppercase font-bold text-indigo-300">Avis</p>
                                    <p class="font-bold">{{ Number(formation.note_moyenne).toFixed(1) }} / 5 <span class="text-xs opacity-70">({{ formation.nombre_evaluations }})</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                    <!-- Left Column: Details -->
                    <div class="lg:col-span-2 space-y-12">
                        <section class="bg-white dark:bg-gray-800 rounded-3xl p-10 shadow-sm border border-gray-100 dark:border-gray-700">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6 flex items-center">
                                <span class="w-2 h-8 bg-indigo-600 rounded-full mr-4"></span>
                                À propos de cette formation
                            </h2>
                            <div class="prose prose-indigo max-w-none text-gray-600 dark:text-gray-300 leading-relaxed" v-html="formation.description"></div>
                            
                            <div v-if="formation.objectifs" class="mt-10">
                                <h3 class="text-xl font-bold text-gray-800 dark:text-gray-200 mb-4">Objectifs pédagogiques</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div v-for="(obj, i) in formation.objectifs.split('\n')" :key="i" class="flex items-start">
                                        <span class="text-green-500 dark:text-green-400 mr-3 mt-1">✓</span>
                                        <p class="text-gray-600 dark:text-gray-300 text-sm">{{ obj }}</p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="bg-white dark:bg-gray-800 rounded-3xl p-10 shadow-sm border border-gray-100 dark:border-gray-700">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-8">Programme de la formation</h2>
                            <div class="space-y-6">
                                <div v-for="seq in formation.sequences" :key="seq.id" class="border border-gray-100 dark:border-gray-700 rounded-2xl overflow-hidden">
                                    <div class="bg-gray-50/50 dark:bg-gray-700/50 p-6 flex justify-between items-center border-b border-gray-100 dark:border-gray-700">
                                        <h3 class="font-bold text-gray-800 dark:text-gray-200">{{ seq.titre }}</h3>
                                        <span class="text-xs font-bold text-gray-400 dark:text-gray-500">{{ seq.lecons?.length || 0 }} LEÇONS</span>
                                    </div>
                                    <div class="p-6 space-y-4">
                                        <div v-for="lecon in seq.lecons" :key="lecon.id" class="flex items-center group">
                                            <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-black mr-4 group-hover:bg-indigo-600 group-hover:text-white transition">▶</div>
                                            <div class="flex-1">
                                                <p class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ lecon.titre }}</p>
                                                <p class="text-[10px] text-gray-400 dark:text-gray-500 uppercase font-black tracking-widest">{{ lecon.type || 'Contenu' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="formation.sequences?.length === 0" class="text-center py-10 italic text-gray-400 dark:text-gray-500">
                                    Le programme n'a pas encore été publié.
                                </div>
                            </div>
                        </section>

                        <!-- Evaluation Section -->
                        <section v-if="isRegistered" class="bg-white dark:bg-gray-800 rounded-3xl p-10 shadow-sm border border-gray-100 dark:border-gray-700">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6 flex items-center">
                                <span class="w-2 h-8 bg-indigo-600 rounded-full mr-4"></span>
                                Évaluation & Feedback
                            </h2>
                            <form @submit.prevent="submitEvaluation" class="space-y-6">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">Votre Note</label>
                                    <div class="flex space-x-2">
                                        <button 
                                            v-for="star in 5" 
                                            :key="star" 
                                            type="button"
                                            @click="evalForm.note = star"
                                            class="text-3xl transition transform hover:scale-110 focus:outline-none"
                                            :class="evalForm.note >= star ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'"
                                        >
                                            ★
                                        </button>
                                    </div>
                                    <div v-if="evalForm.errors.note" class="text-red-500 text-xs mt-1">{{ evalForm.errors.note }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">Votre Avis</label>
                                    <textarea 
                                        v-model="evalForm.commentaire" 
                                        class="w-full rounded-2xl border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 bg-white p-4 text-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                        rows="4"
                                        placeholder="Partagez votre expérience avec cette formation..."
                                    ></textarea>
                                </div>
                                <button 
                                    type="submit" 
                                    :disabled="evalForm.processing"
                                    class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-indigo-700 transition shadow-lg shadow-indigo-200 dark:shadow-indigo-900/50 transform hover:-translate-y-0.5"
                                >
                                    Envoyer mon avis
                                </button>
                            </form>
                        </section>
                    </div>

                    <!-- Right Column: Sidebar / Action -->
                    <div class="space-y-8">
                        <!-- Enrollment Card -->
                        <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-xl border-t-4 border-indigo-600 sticky top-12">
                            <div class="mb-8">
                                <p class="text-gray-400 dark:text-gray-500 font-bold text-xs uppercase tracking-widest mb-2 text-center">Accès Session</p>
                                <div v-if="isRegistered" class="bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 p-4 rounded-2xl flex items-center justify-center font-black text-sm mb-6">
                                    <span class="mr-2">✨</span> VOUS ÊTES INSCRIT
                                </div>
                                <div v-else class="text-4xl font-black text-gray-900 dark:text-gray-100 text-center mb-6">
                                    {{ formation.cout ? formation.cout + ' ' + formation.devise : 'GRATUIT' }}
                                </div>
                            </div>

                            <div class="space-y-4 mb-8">
                                <div class="flex items-center justify-between text-sm py-2 border-b border-gray-50 dark:border-gray-700">
                                    <span class="text-gray-500 dark:text-gray-400">Date début</span>
                                    <span class="font-bold text-gray-900 dark:text-gray-100">{{ formatDate(formation.date_debut) }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm py-2 border-b border-gray-50 dark:border-gray-700">
                                    <span class="text-gray-500 dark:text-gray-400">Date limite</span>
                                    <span class="font-bold text-gray-900 dark:text-gray-100">{{ formatDate(formation.date_limite_inscription) }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm py-2">
                                    <span class="text-gray-500 dark:text-gray-400">Places dispo</span>
                                    <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ formation.capacite_max - formation.nombre_inscrits }} / {{ formation.capacite_max }}</span>
                                </div>
                            </div>

                            <button 
                                v-if="!isRegistered"
                                @click="inscrireFormation"
                                class="w-full py-5 bg-indigo-600 text-white rounded-2xl font-black text-lg hover:bg-indigo-700 shadow-lg shadow-indigo-200 dark:shadow-indigo-900/50 transition-all transform hover:-translate-y-1"
                            >
                                S'INSCRIRE MAINTENANT
                            </button>
                            
                            <!-- Area specific to Registered Users -->
                            <div v-if="isRegistered" class="space-y-6 mt-8 pt-8 border-t-2 border-dashed border-gray-100 dark:border-gray-700">
                                <div v-if="formation.lien_session">
                                    <p class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3">Lien de la session Direct</p>
                                    <a 
                                        :href="formation.lien_session" 
                                        target="_blank"
                                        class="flex items-center p-4 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-2xl border border-blue-100 dark:border-blue-800 hover:bg-blue-100 dark:hover:bg-blue-900/50 transition"
                                    >
                                        <span class="mr-3 text-xl">🎥</span>
                                        <span class="font-black truncate">Rejoindre sur Zoom / Teams</span>
                                    </a>
                                </div>

                                <div v-if="formation.fichiers_joints">
                                    <p class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3">Documents partagés</p>
                                    <div class="space-y-2">
                                        <a 
                                            v-for="(file, idx) in formation.fichiers_joints" 
                                            :key="idx" 
                                            :href="route('formations.download', { id: formation.id, fileName: file.name })"
                                            class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-600 transition cursor-pointer group"
                                        >
                                            <span class="text-xl mr-3">📄</span>
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400">{{ file.name }}</span>
                                            <span class="ml-auto text-[10px] text-gray-400 dark:text-gray-500 font-bold">TÉLÉCHARGER</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Prerequis -->
                        <div v-if="formation.prerequis" class="bg-amber-50 dark:bg-amber-900/30 rounded-3xl p-8 border border-amber-100 dark:border-amber-800 mt-8">
                            <h4 class="text-amber-800 dark:text-amber-200 font-bold mb-4 flex items-center">
                                <span class="mr-2">⚠️</span> Prérequis
                            </h4>
                            <p class="text-amber-700/80 dark:text-amber-300/80 text-sm leading-relaxed">
                                {{ formation.prerequis }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>