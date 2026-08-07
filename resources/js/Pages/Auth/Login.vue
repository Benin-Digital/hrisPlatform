<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const isScrolled = ref(false);
</script>

<template>
    <Head title="Connexion - HRIS Pro" />

    <div class="min-h-screen bg-gradient-to-br from-white via-emerald-50/30 to-white text-gray-900 font-sans selection:bg-emerald-600 selection:text-white">
        <!-- Modern Light Background Elements -->
        <div class="fixed inset-0 overflow-hidden -z-10">
            <div class="absolute -top-[10%] -left-[10%] w-[50%] h-[50%] bg-emerald-500/10 rounded-full blur-[120px]"></div>
            <div class="absolute bottom-[10%] -right-[5%] w-[40%] h-[40%] bg-green-500/5 rounded-full blur-[100px]"></div>
        </div>

        <!-- Navbar -->
        <nav class="fixed top-0 w-full z-50 bg-white/80 backdrop-blur-xl border-b border-gray-200 py-3 shadow-sm px-6 lg:px-12">
            <div class="max-w-7xl mx-auto flex justify-between items-center text-gray-900">
                <Link href="/" class="flex items-center gap-3 group cursor-pointer">
                    <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/20 group-hover:scale-110 transition-all">
                        <span class="text-white font-black text-xl">H</span>
                    </div>
                    <span class="text-2xl font-black tracking-tight">HRIS<span class="text-emerald-600">Pro</span></span>
                </Link>

                <div class="flex items-center gap-4">
                    <Link
                        href="/"
                        class="px-5 py-2.5 text-gray-600 font-black hover:text-gray-900 transition uppercase text-xs tracking-widest"
                    >
                        Accueil
                    </Link>
                </div>
            </div>
        </nav>

        <!-- Login Form Section -->
        <main class="pt-40 pb-20 px-6 flex items-center justify-center min-h-screen">
            <div class="w-full max-w-md">
                <!-- Header -->
                <div class="text-center mb-10">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-100 border border-emerald-200 text-emerald-700 text-xs font-black uppercase tracking-[0.2em] mb-6">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-600 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-600"></span>
                        </span>
                        Espace Sécurisé
                    </div>
                    <h1 class="text-4xl lg:text-5xl font-black text-gray-900 mb-4">Bon retour !</h1>
                    <p class="text-gray-600 text-lg font-medium">Connectez-vous pour accéder à votre espace</p>
                </div>

                <!-- Card Container -->
                <div class="bg-white/80 backdrop-blur-xl border border-gray-200 p-8 lg:p-10 rounded-[2.5rem] shadow-xl shadow-gray-900/5 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-emerald-600 to-transparent opacity-50"></div>
                    
                    <div v-if="status" class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-sm font-medium text-emerald-700">
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label for="email" class="block text-xs font-bold uppercase tracking-widest text-gray-600 mb-2 ml-1">Adresse Email</label>
                            <div class="relative group">
                                <input
                                    id="email"
                                    type="email"
                                    class="block w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-2xl py-3.5 px-4 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all placeholder:text-gray-400"
                                    v-model="form.email"
                                    required
                                    autofocus
                                    autocomplete="username"
                                    placeholder="nom@entreprise.com"
                                />
                            </div>
                            <p v-if="form.errors.email" class="mt-2 text-xs text-red-600 font-medium">{{ form.errors.email }}</p>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2 ml-1">
                                <label for="password" class="block text-xs font-bold uppercase tracking-widest text-gray-600">Mot de passe</label>
                                <Link
                                    v-if="canResetPassword"
                                    :href="route('password.request')"
                                    class="text-xs font-bold text-emerald-600 hover:text-emerald-700 transition-colors"
                                >
                                    Mot de passe oublié ?
                                </Link>
                            </div>
                            <div class="relative group">
                                <input
                                    id="password"
                                    type="password"
                                    class="block w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-2xl py-3.5 px-4 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all placeholder:text-gray-400"
                                    v-model="form.password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="••••••••"
                                />
                            </div>
                            <p v-if="form.errors.password" class="mt-2 text-xs text-red-600 font-medium">{{ form.errors.password }}</p>
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="flex items-center group cursor-pointer">
                                <input 
                                    type="checkbox"
                                    name="remember" 
                                    v-model="form.remember" 
                                    class="rounded-lg bg-white border-gray-300 text-emerald-600 focus:ring-emerald-500 focus:ring-offset-0 w-4 h-4" 
                                />
                                <span class="ms-3 text-sm text-gray-600 group-hover:text-gray-900 transition-colors font-medium">Se souvenir de moi</span>
                            </label>
                        </div>

                        <div class="pt-2">
                            <button
                                type="submit"
                                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-black py-4 rounded-2xl flex items-center justify-center gap-2 transform active:scale-[0.98] transition-all shadow-lg shadow-emerald-500/30 uppercase tracking-widest text-xs disabled:opacity-50 disabled:cursor-not-allowed"
                                :disabled="form.processing"
                            >
                                <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>{{ form.processing ? 'Connexion...' : 'Se connecter' }}</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Back to Home Link -->
                <div class="mt-8 text-center">
                    <Link 
                        href="/" 
                        class="text-sm text-gray-600 hover:text-gray-900 transition-colors inline-flex items-center gap-2 font-medium"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Retour à l'accueil
                    </Link>
                </div>

                <!-- Footer -->
                <div class="mt-12 text-center">
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-[0.3em]">
                        © 2026 HRIS Pro Ecosystem • Accès Sécurisé
                    </p>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100;300;400;500;700;900&display=swap');

:deep(body) {
    font-family: 'Outfit', sans-serif;
}

h1, h2, h3, h4, .font-black {
    font-family: 'Outfit', sans-serif;
    font-weight: 900;
}
</style>
