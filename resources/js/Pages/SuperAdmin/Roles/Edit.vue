<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    role: {
        type: Object,
        required: true
    },
    permissions: {
        type: Object,
        required: true
    },
    rolePermissions: {
        type: Object,
        required: true
    },
});

const form = ref({
    nom: props.role.nom,
    nom_affichage: props.role.nom_affichage,
    description: props.role.description || '',
    niveau: props.role.niveau,
    permissions: { ...props.rolePermissions },
});

const submit = () => {
    router.put(route('super-admin.roles.update', props.role.id), form.value);
};

const route = (name, params = {}) => {
    return window.route ? window.route(name, params) : `/${name}`;
};
</script>

<template>
    <Head :title="`Édition : ${props.role.nom_affichage}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl text-gray-800 leading-tight">
                Éditer le Rôle
            </h2>
        </template>

        <div class="py-6 md:py-10">
            <div class="page-container max-w-4xl">
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900">🛡️ Modifier : {{ props.role.nom_affichage }}</h1>
                        <p class="text-sm text-gray-500 mt-1">Ajustez les privilèges et les informations du rôle.</p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-8">
                    <div class="card border-0 shadow-sm p-6 md:p-10">
                        <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6">Informations de base</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">Nom affiché</label>
                                <input
                                    v-model="form.nom_affichage"
                                    type="text"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-medium focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition shadow-sm"
                                    required
                                />
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">Code technique</label>
                                <input
                                    v-model="form.nom"
                                    type="text"
                                    class="w-full bg-gray-100 border border-gray-200 rounded-xl px-4 py-3 text-sm font-bold text-gray-500 cursor-not-allowed font-mono"
                                    disabled
                                />
                                <p class="mt-2 text-[8px] text-gray-400 italic">L'identifiant unique ne peut pas être modifié.</p>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">Niveau hiérarchique</label>
                                <input
                                    v-model.number="form.niveau"
                                    type="number"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-medium focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition shadow-sm"
                                    required
                                />
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">Description</label>
                                <textarea
                                    v-model="form.description"
                                    rows="1"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-medium focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition shadow-sm"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Permissions -->
                    <div class="card border-0 shadow-sm p-6 md:p-10">
                        <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-8">Gestion des permissions</h2>

                        <div class="space-y-10">
                            <div v-for="(perms, categorie) in props.permissions" :key="categorie" class="border-b border-gray-50 last:border-0 pb-8 last:pb-0">
                                <h3 class="text-xs font-extrabold text-primary-600 uppercase mb-4">{{ categorie }}</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div v-for="perm in perms" :key="perm.id" 
                                         class="flex items-center p-3 rounded-xl hover:bg-gray-50 transition-colors border border-transparent hover:border-gray-100 group">
                                        <div class="relative flex items-center justify-center">
                                          <input
                                              type="checkbox"
                                              :id="'perm-' + perm.id"
                                              v-model="form.permissions[perm.id]"
                                              true-value="1"
                                              false-value="0"
                                              class="h-5 w-5 text-primary-600 rounded-lg border-gray-300 focus:ring-primary-500 transition-all cursor-pointer"
                                          />
                                        </div>
                                        <label :for="'perm-' + perm.id" class="ml-3 text-xs font-bold text-gray-700 cursor-pointer group-hover:text-gray-900 transition-colors">
                                            {{ perm.nom_affichage }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="flex flex-col sm:flex-row justify-end gap-4 pt-4">
                        <Link
                            :href="route('super-admin.roles.index')"
                            class="btn border-gray-200 text-gray-600 hover:bg-gray-50 text-center order-2 sm:order-1"
                        >
                            Annuler
                        </Link>
                        <button
                            type="submit"
                            class="btn btn-primary order-1 sm:order-2"
                        >
                            Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>