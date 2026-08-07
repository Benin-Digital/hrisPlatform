<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    show: Boolean,
    offre: Object
});

const emit = defineEmits(['close']);

const form = useForm({
    nom: '',
    prenom: '',
    email: '',
    telephone: '',
    cv: null,
    lettre_motivation: ''
});

const submit = () => {
    form.post(route('candidatures.store', props.offre.id), {
        onSuccess: () => {
            form.reset();
            emit('close');
            // Notification handled by global flash message watcher
        },
    });
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="$emit('close')"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Postuler à : {{ offre?.titre }}
                            </h3>
                            <div class="mt-2 text-sm text-gray-500 mb-4">
                                Remplissez ce formulaire pour envoyer votre candidature.
                            </div>

                            <form @submit.prevent="submit" class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Nom</label>
                                        <input v-model="form.nom" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                        <div v-if="form.errors.nom" class="text-red-600 text-xs mt-1">{{ form.errors.nom }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Prénom</label>
                                        <input v-model="form.prenom" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                        <div v-if="form.errors.prenom" class="text-red-600 text-xs mt-1">{{ form.errors.prenom }}</div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email</label>
                                    <input v-model="form.email" type="email" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                    <div v-if="form.errors.email" class="text-red-600 text-xs mt-1">{{ form.errors.email }}</div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Téléphone</label>
                                    <input v-model="form.telephone" type="tel" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">CV (PDF, DOCX - Max 2Mo)</label>
                                    <input @input="form.cv = $event.target.files[0]" type="file" required accept=".pdf,.doc,.docx" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                                    <div v-if="form.errors.cv" class="text-red-600 text-xs mt-1">{{ form.errors.cv }}</div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Lettre de motivation (Optionnel)</label>
                                    <textarea v-model="form.lettre_motivation" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                </div>
                                
                                <progress v-if="form.progress" :value="form.progress.percentage" max="100" class="w-full h-2 rounded overflow-hidden mt-2">
                                    {{ form.progress.percentage }}%
                                </progress>

                                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                    <button type="submit" :disabled="form.processing" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                                        Envoyer ma candidature
                                    </button>
                                    <button type="button" @click="$emit('close')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                                        Annuler
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
