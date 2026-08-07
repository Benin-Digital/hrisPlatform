<template>
    <Head title="Demande de stage" />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 md:p-8">
                <h1 class="text-3xl font-extrabold text-gray-900 dark:text-gray-100 text-center mb-6">
                    🎓 Demande de stage
                </h1>
                <p class="text-gray-600 dark:text-gray-400 text-center mb-8">
                    Vous recherchez un stage ? Remplissez ce formulaire et nous étudierons votre candidature.
                </p>

                <div v-if="$page.props.flash?.success" class="mb-6 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl text-green-800 dark:text-green-200 font-bold">
                    {{ $page.props.flash.success }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- État civil -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Nom *</label>
                            <input v-model="form.nom" type="text" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                            <div v-if="form.errors.nom" class="text-red-500 text-xs mt-1">{{ form.errors.nom }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Prénom *</label>
                            <input v-model="form.prenom" type="text" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                            <div v-if="form.errors.prenom" class="text-red-500 text-xs mt-1">{{ form.errors.prenom }}</div>
                        </div>
                    </div>

                    <!-- Contact -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Email *</label>
                            <input v-model="form.email" type="email" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                            <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Téléphone</label>
                            <input v-model="form.telephone" type="tel" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                        </div>
                    </div>

                    <!-- Période souhaitée -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Date de début souhaitée</label>
                            <input v-model="form.periode_debut" type="date" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Date de fin souhaitée</label>
                            <input v-model="form.periode_fin" type="date" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                        </div>
                    </div>

                    <!-- Domaine -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Domaine de stage</label>
                        <input v-model="form.domaine" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700" placeholder="Ex: Développement, Marketing, RH...">
                    </div>

                    <!-- CV -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">CV (PDF, max 10 Mo) *</label>
                        <input type="file" accept=".pdf" @change="handleFileUpload" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <div v-if="form.errors.cv" class="text-red-500 text-xs mt-1">{{ form.errors.cv }}</div>
                    </div>

                    <!-- Lettre de motivation -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Lettre de motivation</label>
                        <textarea v-model="form.lettre_motivation" rows="5" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700" placeholder="Décrivez votre motivation..."></textarea>
                    </div>

                    <!-- Message complémentaire -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Message complémentaire</label>
                        <textarea v-model="form.message" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700" placeholder="Informations supplémentaires..."></textarea>
                    </div>

                    <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <Link href="/" class="px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-bold hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                            Annuler
                        </Link>
                        <button type="submit" :disabled="form.processing" class="px-6 py-3 bg-emerald-600 text-white rounded-lg font-bold hover:bg-emerald-700 transition disabled:opacity-50">
                            {{ form.processing ? 'Envoi...' : 'Envoyer ma demande' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    nom: '',
    prenom: '',
    email: '',
    telephone: '',
    cv: null,
    lettre_motivation: '',
    message: '',
    periode_debut: '',
    periode_fin: '',
    domaine: '',
});

const handleFileUpload = (e) => {
    form.cv = e.target.files[0];
};

const submit = () => {
    form.post(route('stage.demande.store'), {
        forceFormData: true,
    });
};
</script>