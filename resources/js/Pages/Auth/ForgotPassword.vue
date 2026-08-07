<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Mot de passe oublié - HRIS Pro" />

        <div class="mb-8 overflow-hidden">
            <h1 class="text-3xl font-black text-white tracking-tight mb-3">Récupération</h1>
            <p class="text-zinc-400 text-sm leading-relaxed">
                Pas de souci. Indiquez-nous votre adresse email et nous vous enverrons un lien de réinitialisation.
            </p>
        </div>

        <div
            v-if="status"
            class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-sm font-medium text-emerald-400"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-widest text-zinc-500 mb-2 ml-1">Adresse Email</label>
                <TextInput
                    id="email"
                    type="email"
                    class="block w-full bg-zinc-950/50 border-white/5 text-white rounded-2xl py-3.5 px-4 focus:ring-indigo-500 focus:border-indigo-500 transition-all placeholder:text-zinc-700"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="nom@entreprise.com"
                />
                <InputError class="mt-2 text-xs text-red-400/80" :message="form.errors.email" />
            </div>

            <div class="pt-2">
                <PrimaryButton
                    class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-4 rounded-2xl flex items-center justify-center gap-2 transform active:scale-[0.98] transition-all shadow-xl shadow-indigo-500/20 uppercase tracking-widest text-xs border-none"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Envoyer le lien</span>
                </PrimaryButton>
            </div>
            
            <div class="text-center pt-2">
                <Link
                    :href="route('login')"
                    class="text-sm font-bold text-zinc-500 hover:text-white transition-colors"
                >
                    Retour à la <span class="text-indigo-400 underline decoration-indigo-500/30 underline-offset-4">connexion</span>
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
