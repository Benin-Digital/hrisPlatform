<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl">📅 Planifier un entretien</h2>
        </template>

        <div class="py-6">
            <div class="page-container max-w-2xl">
                <div class="bg-white p-6 rounded-xl shadow-sm">
                    <h3 class="text-lg font-bold mb-4">
                        Pour : {{ candidature.prenom }} {{ candidature.nom }}
                    </h3>

                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold">Date *</label>
                                <input v-model="form.date_entretien" type="date" required class="w-full rounded-lg border-gray-300">
                            </div>
                            <div>
                                <label class="block text-sm font-bold">Heure *</label>
                                <input v-model="form.heure_entretien" type="time" required class="w-full rounded-lg border-gray-300">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold">Lieu</label>
                                <input v-model="form.lieu_entretien" type="text" class="w-full rounded-lg border-gray-300" placeholder="Salle, adresse, lien visio...">
                            </div>
                            <div>
                                <label class="block text-sm font-bold">Type *</label>
                                <select v-model="form.type" required class="w-full rounded-lg border-gray-300">
                                    <option value="presentiel">Présentiel</option>
                                    <option value="visio">Visio</option>
                                    <option value="telephonique">Téléphonique</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold">Recruteur *</label>
                                <select v-model="form.recruteur_id" required class="w-full rounded-lg border-gray-300">
                                    <option value="">Sélectionner</option>
                                    <option v-for="r in recruteurs" :key="r.id" :value="r.id">
                                        {{ r.prenom }} {{ r.nom }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-4">
                            <Link :href="route('recrutement.pipeline')" class="px-4 py-2 bg-gray-200 rounded-lg">Annuler</Link>
                            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50">
                                {{ form.processing ? 'Envoi...' : 'Planifier' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { useForm, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    candidature: Object,
    recruteurs: Array,
});

const form = useForm({
    date_entretien: '',
    heure_entretien: '',
    lieu_entretien: '',
    type: 'presentiel',
    recruteur_id: '',
});

const submit = () => {
    form.post(route('recrutement.planifier-entretien', props.candidature.id), {
        onSuccess: () => {
            router.visit(route('recrutement.pipeline'));
        },
    });
};
</script>