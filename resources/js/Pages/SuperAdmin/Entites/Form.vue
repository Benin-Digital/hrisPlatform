<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    entite: {
        type: Object,
        default: () => ({})
    }
});

const form = useForm({
    nom: props.entite.nom || '',
    code_entite: props.entite.code_entite || '',
    description: props.entite.description || '',
    adresse: props.entite.adresse || '',
    telephone: props.entite.telephone || '',
    email: props.entite.email || '',
    logo: null,
    couleur_theme: props.entite.couleur_theme || '',
    est_active: props.entite.est_active ?? true,
});

const submit = () => {
    if (props.entite.id) {
        form.put(route('super-admin.entites.update', props.entite.id));
    } else {
        form.post(route('super-admin.entites.store'));
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <!-- Champs du formulaire ici (nom, code, etc.) -->
        <!-- Upload logo -->
        <div>
            <label>Logo</label>
            <input type="file" @input="form.logo = $event.target.files[0]" />
            <div v-if="entite.logo" class="mt-2">
                <img :src="'/storage/' + entite.logo" class="h-20" />
            </div>
        </div>

        <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded">
            {{ entite.id ? 'Mettre à jour' : 'Créer' }}
        </button>
    </form>
</template>