<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    offre: {
        type: Object,
        default: () => ({
            id: null,
            titre: '',
            description: '',
            lieu: '',
            type_contrat: 'CDI',
            departement: '',
            date_expiration: '',
            is_published: false
        })
    }
});

const isEdit = !!props.offre.id;

const form = useForm({
    titre: props.offre.titre || '',
    description: props.offre.description || '',
    lieu: props.offre.lieu || '',
    type_contrat: props.offre.type_contrat || 'CDI',
    departement: props.offre.departement || '',
    date_expiration: props.offre.date_expiration ? props.offre.date_expiration.split('T')[0] : '',
    is_published: props.offre.is_published || false
});

const submit = () => {
    if (isEdit) {
        form.put(route('super-admin.offres.update', props.offre.id));
    } else {
        form.post(route('super-admin.offres.store'));
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Modifier Offre' : 'Créer Offre'" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ isEdit ? 'Modifier l\'offre' : 'Créer une offre d\'emploi' }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Titre du poste</label>
                                    <input v-model="form.titre" type="text" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" required />
                                    <div v-if="form.errors.titre" class="text-red-600 text-sm mt-1">{{ form.errors.titre }}</div>
                                </div>

                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Type de contrat</label>
                                    <select v-model="form.type_contrat" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                        <option value="CDI">CDI</option>
                                        <option value="CDD">CDD</option>
                                        <option value="Stage">Stage</option>
                                        <option value="Alternance">Alternance</option>
                                        <option value="Freelance">Freelance</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Lieu</label>
                                    <input v-model="form.lieu" type="text" placeholder="Ex: Paris, Télétravail" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" />
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Département / Équipe</label>
                                    <input v-model="form.departement" type="text" placeholder="Ex: Marketing, IT" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" />
                                </div>
                            </div>

                            <div>
                                <label class="block font-medium text-sm text-gray-700">Description du poste</label>
                                <textarea v-model="form.description" rows="6" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" required></textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Date d'expiration</label>
                                    <input v-model="form.date_expiration" type="date" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" />
                                </div>
                                <div class="flex items-center pt-6">
                                    <label class="flex items-center space-x-3 cursor-pointer">
                                        <input v-model="form.is_published" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 h-5 w-5" />
                                        <span class="text-gray-900 font-medium">Publier immédiatement</span>
                                    </label>
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-4 gap-4">
                                <Link :href="route('super-admin.offres.index')" class="text-gray-600 hover:text-gray-900 underline">Annuler</Link>
                                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition disabled:opacity-50" :disabled="form.processing">
                                    {{ isEdit ? 'Mettre à jour' : 'Créer l\'offre' }}
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
