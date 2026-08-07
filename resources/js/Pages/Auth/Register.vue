<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    prenom: '',
    nom: '',
    matricule: '',          // Optionnel : laissé vide → auto-généré côté serveur
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,           // Si tu veux garder les CGU (optionnel)
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Création de compte - HRIS Pro" />

        <div class="mb-8 text-center sm:text-left">
            <h1 class="text-3xl font-black text-white tracking-tight mb-2">Rejoignez-nous</h1>
            <p class="text-zinc-400 text-sm">Créez votre compte pour commencer l'aventure.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Prénom -->
                <div>
                    <label for="prenom" class="block text-xs font-bold uppercase tracking-widest text-zinc-500 mb-2 ml-1">Prénom</label>
                    <TextInput
                        id="prenom"
                        type="text"
                        class="block w-full bg-zinc-950/50 border-white/5 text-white rounded-2xl py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 transition-all placeholder:text-zinc-700"
                        v-model="form.prenom"
                        required
                        autofocus
                        autocomplete="given-name"
                        placeholder="John"
                    />
                    <InputError class="mt-1.5 text-xs text-red-400/80" :message="form.errors.prenom" />
                </div>

                <!-- Nom -->
                <div>
                    <label for="nom" class="block text-xs font-bold uppercase tracking-widest text-zinc-500 mb-2 ml-1">Nom</label>
                    <TextInput
                        id="nom"
                        type="text"
                        class="block w-full bg-zinc-950/50 border-white/5 text-white rounded-2xl py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 transition-all placeholder:text-zinc-700"
                        v-model="form.nom"
                        required
                        autocomplete="family-name"
                        placeholder="Doe"
                    />
                    <InputError class="mt-1.5 text-xs text-red-400/80" :message="form.errors.nom" />
                </div>
            </div>

            <!-- Matricule -->
            <div>
                <label for="matricule" class="block text-xs font-bold uppercase tracking-widest text-zinc-500 mb-2 ml-1">Matricule (facultatif)</label>
                <TextInput
                    id="matricule"
                    type="text"
                    class="block w-full bg-zinc-950/50 border-white/5 text-white rounded-2xl py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 transition-all placeholder:text-zinc-700/50 italic text-sm"
                    v-model="form.matricule"
                    placeholder="Laissé vide → généré automatiquement"
                />
                <InputError class="mt-1.5 text-xs text-red-400/80" :message="form.errors.matricule" />
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-widest text-zinc-500 mb-2 ml-1">Email professionnel</label>
                <TextInput
                    id="email"
                    type="email"
                    class="block w-full bg-zinc-950/50 border-white/5 text-white rounded-2xl py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 transition-all placeholder:text-zinc-700"
                    v-model="form.email"
                    required
                    autocomplete="username"
                    placeholder="john.doe@entreprise.com"
                />
                <InputError class="mt-1.5 text-xs text-red-400/80" :message="form.errors.email" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Mot de passe -->
                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-widest text-zinc-500 mb-2 ml-1">Mot de passe</label>
                    <TextInput
                        id="password"
                        type="password"
                        class="block w-full bg-zinc-950/50 border-white/5 text-white rounded-2xl py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 transition-all placeholder:text-zinc-700"
                        v-model="form.password"
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                    />
                    <InputError class="mt-1.5 text-xs text-red-400/80" :message="form.errors.password" />
                </div>

                <!-- Confirmation -->
                <div>
                    <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-widest text-zinc-500 mb-2 ml-1">Confirmation</label>
                    <TextInput
                        id="password_confirmation"
                        type="password"
                        class="block w-full bg-zinc-950/50 border-white/5 text-white rounded-2xl py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 transition-all placeholder:text-zinc-700"
                        v-model="form.password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                    />
                    <InputError class="mt-1.5 text-xs text-red-400/80" :message="form.errors.password_confirmation" />
                </div>
            </div>

            <!-- Boutons -->
            <div class="pt-4 flex flex-col gap-4">
                <PrimaryButton
                    class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-4 rounded-2xl flex items-center justify-center gap-2 transform active:scale-[0.98] transition-all shadow-xl shadow-indigo-500/20 uppercase tracking-widest text-xs border-none"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Créer mon compte</span>
                </PrimaryButton>

                <div class="text-center">
                    <Link
                        :href="route('login')"
                        class="text-sm font-bold text-zinc-500 hover:text-white transition-colors"
                    >
                        Déjà inscrit ? <span class="text-indigo-400 underline decoration-indigo-500/30 underline-offset-4">Connectez-vous</span>
                    </Link>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>