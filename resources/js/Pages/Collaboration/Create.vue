<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    entites: {
        type: Array,
        required: true
    }
});

const form = useForm({
    nom: '',
    description: '',
    est_prive: true,
    entite_id: null,
});

const submit = () => {
    form.post('/collaboration', {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Créer un espace" />

    <div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto">
            <!-- Breadcrumb -->
            <nav class="flex mb-8 text-sm" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                    <li>
                        <Link href="/collaboration" class="text-gray-500 hover:text-indigo-600 transition-colors">Collaboration</Link>
                    </li>
                    <li class="flex items-center">
                        <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span class="ml-2 text-gray-900 font-medium">Nouvel Espace</span>
                    </li>
                </ol>
            </nav>

            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="p-8 sm:p-12">
                    <header class="mb-10 flex justify-between items-center">
                        <div class="text-left">
                            <h1 class="text-3xl font-extrabold text-gray-900">Nouvel Espace de Travail</h1>
                            <p class="mt-2 text-gray-600">Définissez le cadre de votre prochaine collaboration.</p>
                        </div>
                        <Link href="/collaboration" class="px-5 py-3 bg-gray-100 text-gray-700 rounded-2xl font-bold hover:bg-gray-200 transition">
                            ← Retour
                        </Link>
                    </header>

                    <form @submit.prevent="submit" class="space-y-8">
                        <!-- Nom -->
                        <div>
                            <label for="nom" class="block text-sm font-bold text-gray-700 mb-2">Nom de l'espace</label>
                            <input
                                id="nom"
                                v-model="form.nom"
                                type="text"
                                required
                                placeholder="Ex: Projet Refonte Site Web"
                                class="w-full px-4 py-4 bg-gray-50 border-transparent rounded-2xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                                :class="{ 'ring-2 ring-red-500': form.errors.nom }"
                            />
                            <p v-if="form.errors.nom" class="mt-2 text-sm text-red-600">{{ form.errors.nom }}</p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-bold text-gray-700 mb-2">Description (facultatif)</label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                placeholder="Quel est l'objectif de cet espace ?"
                                class="w-full px-4 py-4 bg-gray-50 border-transparent rounded-2xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                            ></textarea>
                        </div>

                        <!-- Entité -->
                        <div>
                            <label for="entite_id" class="block text-sm font-bold text-gray-700 mb-2">Entité organisatrice</label>
                            <select
                                id="entite_id"
                                v-model="form.entite_id"
                                class="w-full px-4 py-4 bg-gray-50 border-transparent rounded-2xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                            >
                                <option :value="null">Global / Inter-entités</option>
                                <option v-for="entite in entites" :key="entite.id" :value="entite.id">
                                    {{ entite.nom }}
                                </option>
                            </select>
                        </div>

                        <!-- Visibilité -->
                        <div class="bg-gray-50 p-6 rounded-3xl space-y-4">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Confidentialité</label>
                            
                            <div 
                                class="flex items-center p-4 rounded-2xl border-2 transition-all cursor-pointer"
                                :class="form.est_prive ? 'bg-white border-indigo-500 shadow-sm' : 'border-transparent hover:bg-white'"
                                @click="form.est_prive = true"
                            >
                                <div class="flex-shrink-0 w-10 h-10 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <div class="flex-grow">
                                    <div class="font-bold text-gray-900">Espace Privé</div>
                                    <div class="text-xs text-gray-500">Uniquement accessible aux membres invités.</div>
                                </div>
                                <div v-if="form.est_prive" class="text-indigo-600">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>

                            <div 
                                class="flex items-center p-4 rounded-2xl border-2 transition-all cursor-pointer"
                                :class="!form.est_prive ? 'bg-white border-green-500 shadow-sm' : 'border-transparent hover:bg-white'"
                                @click="form.est_prive = false"
                            >
                                <div class="flex-shrink-0 w-10 h-10 bg-green-100 text-green-600 rounded-xl flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.065M15 2a8.001 8.001 0 106.342 13.004A8 8 0 0115 2z" />
                                    </svg>
                                </div>
                                <div class="flex-grow">
                                    <div class="font-bold text-gray-900">Espace Public</div>
                                    <div class="text-xs text-gray-500">Visible et accessible à toute l'entité.</div>
                                </div>
                                <div v-if="!form.est_prive" class="text-green-600">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="pt-4 flex items-center justify-between">
                            <Link href="/collaboration" class="text-gray-500 font-bold hover:text-gray-700 transition-colors">Annuler</Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-10 py-4 bg-indigo-600 text-white font-bold rounded-2xl shadow-lg hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200 transition-all transform hover:-translate-y-1 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span v-if="form.processing">Création en cours...</span>
                                <span v-else>Lancer l'Espace</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
