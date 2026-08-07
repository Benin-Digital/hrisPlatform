<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    nom: '',
    code_entite: '',
    description: '',
    adresse: '',
    telephone: '',
    email: '',
    logo: null,
    couleur_theme: '#4F46E5',
    est_active: true,
});

const submit = () => {
    form.post(route('super-admin.entites.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
};

const route = (name, params = {}) => {
    return window.route ? window.route(name, params) : `/${name}`;
};
</script>

<template>
    <Head title="Nouvelle Entité" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl text-gray-800 leading-tight">
                Gestion des Entités
            </h2>
        </template>

        <div class="py-6 md:py-10">
            <div class="page-container max-w-4xl">
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900">🏢 Ajouter une entité</h1>
                        <p class="text-sm text-gray-500 mt-1">Créez une nouvelle structure organisationnelle au sein de la plateforme.</p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Informations de base -->
                    <div class="card border-0 shadow-sm p-6 md:p-10">
                        <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-8">Informations Générales</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                            <div class="md:col-span-1">
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">Nom de l'entité <span class="text-red-500">*</span></label>
                                <input
                                    v-model="form.nom"
                                    type="text"
                                    required
                                    placeholder="Ex: Siège Social"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-medium focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition shadow-sm"
                                />
                                <div v-if="form.errors.nom" class="text-red-500 text-[10px] mt-1 font-bold">{{ form.errors.nom }}</div>
                            </div>

                            <div class="md:col-span-1">
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">Code Identifiant <span class="text-red-500">*</span></label>
                                <input
                                    v-model="form.code_entite"
                                    type="text"
                                    required
                                    placeholder="Ex: SIEGE-01"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition shadow-sm font-mono tracking-tight"
                                />
                                <div v-if="form.errors.code_entite" class="text-red-500 text-[10px] mt-1 font-bold">{{ form.errors.code_entite }}</div>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">Description</label>
                                <textarea
                                    v-model="form.description"
                                    rows="2"
                                    placeholder="Brève description de l'activité de l'entité..."
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-medium focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition shadow-sm"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Contact & Localisation -->
                    <div class="card border-0 shadow-sm p-6 md:p-10">
                        <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-8">Contact & Localisation</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">Email de contact</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    placeholder="contact@entite.com"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-medium focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition shadow-sm"
                                />
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">Téléphone</label>
                                <input
                                    v-model="form.telephone"
                                    type="text"
                                    placeholder="+221 ..."
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-medium focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition shadow-sm"
                                />
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">Adresse Physique</label>
                                <textarea
                                    v-model="form.adresse"
                                    rows="1"
                                    placeholder="Rue, Ville, Code Postal..."
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-medium focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition shadow-sm"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Identité Visuelle & Paramètres -->
                    <div class="card border-0 shadow-sm p-6 md:p-10">
                        <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-8">Personnalisation & Statut</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">Logo de l'entité</label>
                                <div class="mt-1 flex items-center gap-4 p-4 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                                    <div class="w-12 h-12 rounded-lg bg-gray-200 flex items-center justify-center text-xl grayscale opacity-50">🖼️</div>
                                    <input
                                        type="file"
                                        @input="form.logo = $event.target.files[0]"
                                        accept="image/*"
                                        class="text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 cursor-pointer"
                                    />
                                </div>
                                <div v-if="form.errors.logo" class="text-red-500 text-[10px] mt-1 font-bold">{{ form.errors.logo }}</div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">Couleur Thème</label>
                                <div class="flex items-center gap-3">
                                    <input
                                        v-model="form.couleur_theme"
                                        type="color"
                                        class="h-11 w-11 rounded-lg border border-gray-200 p-1 cursor-pointer"
                                    />
                                    <input
                                        v-model="form.couleur_theme"
                                        type="text"
                                        class="flex-1 bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-xs font-mono font-bold focus:ring-2 focus:ring-primary-500 transition"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="mt-10 pt-8 border-t border-gray-50">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input
                                        v-model="form.est_active"
                                        id="est_active"
                                        type="checkbox"
                                        class="h-5 w-5 text-primary-600 border-gray-300 rounded-lg focus:ring-primary-500 transition cursor-pointer"
                                    />
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="est_active" class="font-bold text-gray-900 cursor-pointer select-none">Entité Active</label>
                                    <p class="text-[10px] text-gray-400 uppercase tracking-wider font-black">Si décoché, les utilisateurs de cette entité ne pourront plus se connecter.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row justify-end gap-4 pt-4">
                        <Link
                            :href="route('super-admin.entites.index')"
                            class="btn border-gray-200 text-gray-600 hover:bg-gray-50 text-center order-2 sm:order-1"
                        >
                            Annuler
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="btn btn-primary order-1 sm:order-2 flex items-center justify-center gap-2"
                        >
                            <span v-if="form.processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                            {{ form.processing ? 'Création en cours...' : 'Créer l\'entité' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
