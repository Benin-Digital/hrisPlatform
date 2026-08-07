<template>
    <Head title="Candidature spontanée" />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 md:p-8">
                <div class="flex items-center justify-center mb-4">
                    <DocumentPlusIcon class="w-12 h-12 text-indigo-600 dark:text-indigo-400" />
                </div>
                <h1 class="text-3xl font-extrabold text-gray-900 dark:text-gray-100 text-center mb-6">
                    Candidature spontanée
                </h1>
                <p class="text-gray-600 dark:text-gray-400 text-center mb-8">
                    Vous souhaitez rejoindre notre équipe ? Déposez votre candidature même si aucune offre n'est publiée.
                </p>

                <div v-if="$page.props.flash?.success" class="mb-6 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl text-green-800 dark:text-green-200 font-bold flex items-center gap-2">
                    <CheckCircleIcon class="w-5 h-5" />
                    {{ $page.props.flash.success }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">
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

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2">
                            <ClipboardDocumentListIcon class="w-5 h-5 text-gray-500" />
                            Type de candidature *
                        </label>
                        <select v-model="form.type" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                            <option value="emploi">Emploi</option>
                            <option value="stage">Stage</option>
                            <option value="alternance">Alternance</option>
                            <option value="spontanee">Spontanée</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2">
                            <DocumentTextIcon class="w-5 h-5 text-gray-500" />
                            CV (PDF, max 10 Mo) *
                        </label>
                        <input type="file" accept=".pdf" @change="handleFileUpload" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <div v-if="form.errors.cv" class="text-red-500 text-xs mt-1">{{ form.errors.cv }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2">
                            <EnvelopeIcon class="w-5 h-5 text-gray-500" />
                            Lettre de motivation
                        </label>
                        <textarea v-model="form.lettre_motivation" rows="5" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700" placeholder="Décrivez votre motivation..."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2">
                            <ChatBubbleLeftEllipsisIcon class="w-5 h-5 text-gray-500" />
                            Message complémentaire
                        </label>
                        <textarea v-model="form.message" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700" placeholder="Informations supplémentaires..."></textarea>
                    </div>

                    <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <Link href="/" class="px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-bold hover:bg-gray-300 dark:hover:bg-gray-600 transition flex items-center gap-2">
                            <XMarkIcon class="w-5 h-5" />
                            Annuler
                        </Link>
                        <button type="submit" :disabled="form.processing" class="px-6 py-3 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700 transition disabled:opacity-50 flex items-center gap-2">
                            <PaperAirplaneIcon class="w-5 h-5" />
                            {{ form.processing ? 'Envoi...' : 'Envoyer ma candidature' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    DocumentPlusIcon,
    CheckCircleIcon,
    ClipboardDocumentListIcon,
    DocumentTextIcon,
    EnvelopeIcon,
    ChatBubbleLeftEllipsisIcon,
    XMarkIcon,
    PaperAirplaneIcon,
} from '@heroicons/vue/24/outline';

const form = useForm({
    nom: '',
    prenom: '',
    email: '',
    telephone: '',
    cv: null,
    lettre_motivation: '',
    message: '',
    type: 'stage',
});

const handleFileUpload = (e) => {
    form.cv = e.target.files[0];
};

const submit = () => {
    form.post(route('candidatures.spontanee.store'), {
        forceFormData: true,
    });
};
</script>