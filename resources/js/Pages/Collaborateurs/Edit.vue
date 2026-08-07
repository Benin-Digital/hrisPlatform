<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import Swal from 'sweetalert2';
import {
    BuildingOfficeIcon,
    GlobeAltIcon,
    ArrowLeftIcon,
    EyeIcon,
    CheckIcon,
    UserCircleIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    entites: {
        type: Array,
        required: true
    },
    roles: {
        type: Array,
        required: true
    },
    directions: {
        type: Array,
        required: true
    },
    collaborateur: {
        type: Object,
        required: true
    },
    availablePermissions: {
        type: Object,
        default: () => ({})
    },
    userPermissions: {
        type: Array,
        default: () => []
    }
});

const form = useForm({
    prenom: props.collaborateur.prenom || '',
    nom: props.collaborateur.nom || '',
    email: props.collaborateur.email || '',
    matricule: props.collaborateur.matricule || '',
    poste: props.collaborateur.poste || '',
    direction_id: props.collaborateur.direction_id || null,
    date_embauche: props.collaborateur.date_embauche || null,
    statut: props.collaborateur.statut || 'actif',
    entite_id: props.collaborateur.entite_id || null,
    role_ids: props.collaborateur.roles ? props.collaborateur.roles.map(r => r.id) : [],
    permission_ids: props.userPermissions || [],
    type: props.collaborateur.entite_id ? 'interne' : (props.collaborateur.direction_id ? 'interne' : 'externe'),
    password: '',
    password_confirmation: '',
    dashboard_preference: props.collaborateur.dashboard_preference || null,
    date_naissance: props.collaborateur.date_naissance || null,
    telephone: props.collaborateur.telephone || '',
});

// Rôles affichés selon le type sélectionné
const filteredRoles = computed(() => {
    if (form.type === 'externe') {
        return props.roles.filter(role => role.nom === 'invite');
    }
    return props.roles.filter(role => role.nom !== 'invite');
});

watch(() => form.type, (newType) => {
    if (newType === 'externe') {
        form.entite_id = null;
        form.direction_id = null;
        if (filteredRoles.value.length === 1) {
            form.role_ids = [filteredRoles.value[0].id];
        } else {
            form.role_ids = [];
        }
    } else {
        if (props.entites.length > 0 && !form.entite_id) {
            form.entite_id = props.entites[0].id;
        }
    }
});

const submit = () => {
    form.patch(route('collaborateurs.update', props.collaborateur.id), {
        preserveScroll: true,
        onSuccess: () => {
            Swal.fire({
                title: 'Profil mis à jour',
                text: 'Les modifications ont été enregistrées avec succès.',
                icon: 'success',
                timer: 2500,
                showConfirmButton: false
            });
        },
        onError: () => {
             Swal.fire({
                title: 'Erreur',
                text: 'Une erreur est survenue lors de la mise à jour.',
                icon: 'error'
            });
        }
    });
};

const route = (name, params = {}) => {
    return window.route ? window.route(name, params) : `/${name}`;
};
</script>

<template>
    <Head :title="`Modifier : ${collaborateur.prenom} ${collaborateur.nom}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl text-gray-800 leading-tight">
                Gestion des Collaborateurs
            </h2>
        </template>

        <div class="py-6 md:py-10">
            <div class="page-container max-w-5xl">
                <!-- Header Title -->
                <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div 
                            v-if="!collaborateur.photo_profil"
                            class="h-16 w-16 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white font-black text-xl shadow-lg"
                        >
                            {{ collaborateur.prenom?.[0] }}{{ collaborateur.nom?.[0] }}
                        </div>
                        <img 
                            v-else
                            :src="'/storage/' + collaborateur.photo_profil"
                            class="h-16 w-16 rounded-2xl object-cover shadow-lg border-2 border-white"
                        />
                        <div>
                            <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 tracking-tight">Modifier le profil</h1>
                            <p class="text-sm text-gray-500 font-medium">{{ collaborateur.prenom }} {{ collaborateur.nom }} • {{ collaborateur.matricule || 'Sans matricule' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <Link :href="route('collaborateurs.show', collaborateur.id)" class="btn bg-white border-gray-200 text-gray-600 hover:bg-gray-50 inline-flex items-center">
                            <EyeIcon class="w-4 h-4 mr-2" />
                            Voir le profil
                        </Link>
                        <Link :href="route('collaborateurs.index')" class="btn border-transparent text-gray-400 hover:text-gray-900 inline-flex items-center">
                            <ArrowLeftIcon class="w-4 h-4 mr-2" />
                            Annuler
                        </Link>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Section: Type & Statut -->
                    <div class="card border-0 shadow-sm p-6 md:p-8">
                        <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-8">Type de compte & Statut</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">Type d'accès <span class="text-red-500">*</span></label>
                                <div class="grid grid-cols-2 gap-3">
                                    <button 
                                        type="button"
                                        @click="form.type = 'interne'"
                                        class="flex flex-col items-center justify-center p-4 rounded-2xl border-2 transition-all"
                                        :class="form.type === 'interne' ? 'border-primary-500 bg-primary-50/50 text-primary-700' : 'border-gray-100 bg-gray-50 text-gray-400 grayscale'"
                                    >
                                        <BuildingOfficeIcon class="w-8 h-8 mb-2" />
                                        <span class="text-xs font-black uppercase">Interne</span>
                                    </button>
                                    <button 
                                        type="button"
                                        @click="form.type = 'externe'"
                                        class="flex flex-col items-center justify-center p-4 rounded-2xl border-2 transition-all"
                                        :class="form.type === 'externe' ? 'border-primary-500 bg-primary-50/50 text-primary-700' : 'border-gray-100 bg-gray-50 text-gray-400 grayscale'"
                                    >
                                        <GlobeAltIcon class="w-8 h-8 mb-2" />
                                        <span class="text-xs font-black uppercase">Externe</span>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">Statut du collaborateur <span class="text-red-500">*</span></label>
                                <select 
                                    v-model="form.statut"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-primary-500 transition shadow-sm"
                                >
                                    <option value="actif">Actif</option>
                                    <option value="inactif">Inactif</option>
                                    <option value="suspendu">Suspendu</option>
                                    <option value="conges">En congés</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Identité & Contact -->
                    <div class="card border-0 shadow-sm p-6 md:p-8">
                        <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-8">Identité & Contact</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">Prénom <span class="text-red-500">*</span></label>
                                <input v-model="form.prenom" type="text" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-medium focus:ring-2 focus:ring-primary-500 transition shadow-sm" required />
                                <div v-if="form.errors.prenom" class="text-red-500 text-[10px] mt-1 font-bold">{{ form.errors.prenom }}</div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">Nom <span class="text-red-500">*</span></label>
                                <input v-model="form.nom" type="text" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-medium focus:ring-2 focus:ring-primary-500 transition shadow-sm" required />
                                <div v-if="form.errors.nom" class="text-red-500 text-[10px] mt-1 font-bold">{{ form.errors.nom }}</div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">Email professionnel <span class="text-red-500">*</span></label>
                                <input v-model="form.email" type="email" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-medium focus:ring-2 focus:ring-primary-500 transition shadow-sm font-bold text-gray-700" required />
                                <div v-if="form.errors.email" class="text-red-500 text-[10px] mt-1 font-bold">{{ form.errors.email }}</div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">Téléphone</label>
                                <input v-model="form.telephone" type="text" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-medium focus:ring-2 focus:ring-primary-500 transition shadow-sm" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">Date de naissance</label>
                                <input v-model="form.date_naissance" type="date" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-medium focus:ring-2 focus:ring-primary-500 transition shadow-sm" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">Matricule</label>
                                <input v-model="form.matricule" type="text" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-primary-500 transition shadow-sm font-mono tracking-tighter" />
                            </div>
                        </div>
                    </div>

                    <!-- Section: Poste & Organisation (Si Interne) -->
                    <div v-if="form.type === 'interne'" class="card border-0 shadow-sm p-6 md:p-8">
                        <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-8">Organisation & Poste</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">Intitulé du poste <span class="text-red-500">*</span></label>
                                <input v-model="form.poste" type="text" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-medium focus:ring-2 focus:ring-primary-500 transition shadow-sm" :required="form.type === 'interne'" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">Entité de rattachement <span class="text-red-500">*</span></label>
                                <select v-model="form.entite_id" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-primary-500 transition shadow-sm" :required="form.type === 'interne'">
                                    <option v-for="entite in props.entites" :key="entite.id" :value="entite.id">{{ entite.nom }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">Direction</label>
                                <select v-model="form.direction_id" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-primary-500 transition shadow-sm">
                                    <option :value="null">Aucune direction</option>
                                    <option v-for="dir in props.directions" :key="dir.id" :value="dir.id">{{ dir.nom }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">Date d'embauche</label>
                                <input v-model="form.date_embauche" type="date" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-medium focus:ring-2 focus:ring-primary-500 transition shadow-sm" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">Préférence Dashboard</label>
                                <select v-model="form.dashboard_preference" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-primary-500 transition shadow-sm">
                                    <option :value="null">Automatique (basé sur le rôle)</option>
                                    <option value="collaborateur">Collaborateur</option>
                                    <option value="manager">Manager</option>
                                    <option value="responsable_rh">RH</option>
                                    <option value="admin_entite">Admin Entité</option>
                                    <option value="super_admin">Super Admin</option>
                                    <option value="formateur">Formateur</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Rôles & Accès -->
                    <div class="card border-0 shadow-sm p-6 md:p-8">
                        <div class="flex items-center justify-between mb-8">
                            <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest">Permissions & Rôles</h2>
                            <span class="text-[10px] bg-amber-50 text-amber-700 px-3 py-1 rounded-full font-black uppercase tracking-wider">Droits d'accès</span>
                        </div>
                        
                        <div class="space-y-6">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Rôles attribués <span class="text-red-500">*</span></label>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div v-for="role in filteredRoles" :key="role.id" class="relative group">
                                    <input
                                        type="checkbox"
                                        :id="'role-' + role.id"
                                        v-model="form.role_ids"
                                        :value="role.id"
                                        class="peer hidden"
                                    />
                                    <label 
                                        :for="'role-' + role.id"
                                        class="flex flex-col h-full bg-gray-50 border-2 border-gray-100 rounded-2xl p-4 cursor-pointer transition-all hover:bg-white hover:border-primary-200 peer-checked:border-primary-500 peer-checked:bg-primary-50/30"
                                    >
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm font-black text-gray-900">{{ role.nom_affichage }}</span>
                                            <div class="w-5 h-5 rounded-full border-2 border-gray-200 flex items-center justify-center group-hover:border-primary-300 peer-checked:bg-primary-500 peer-checked:border-primary-500">
                                                <span class="text-white text-[10px] hidden peer-checked:block">✓</span>
                                            </div>
                                        </div>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tight">{{ role.nom }}</p>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Permissions directes -->
                            <div v-if="Object.keys(availablePermissions).length > 0" class="mt-12 pt-8 border-t border-gray-50">
                                <h3 class="text-sm font-black text-gray-900 mb-6">Permissions spécifiques (Optionnel)</h3>
                                
                                <div class="space-y-8">
                                    <div v-for="(perms, category) in availablePermissions" :key="category">
                                        <h4 class="text-[10px] font-black text-primary-600 uppercase tracking-widest mb-4 flex items-center gap-2">
                                            <span class="w-2 h-2 bg-primary-500 rounded-full"></span>
                                            {{ category }}
                                        </h4>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                                            <div v-for="permission in perms" :key="permission.id" class="flex items-center gap-3 bg-gray-50/50 p-2.5 rounded-xl border border-gray-100">
                                                <input
                                                    type="checkbox"
                                                    :id="'perm-' + permission.id"
                                                    v-model="form.permission_ids"
                                                    :value="permission.id"
                                                    class="w-4 h-4 text-primary-600 rounded-lg border-gray-200 focus:ring-primary-500"
                                                />
                                                <label :for="'perm-' + permission.id" class="text-xs font-bold text-gray-600 cursor-pointer">{{ permission.nom_affichage }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Sécurité (Changement de mot de passe) -->
                    <div class="card border-0 shadow-sm p-6 md:p-8">
                        <div class="flex items-center justify-between mb-8">
                            <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest">Sécurité du compte</h2>
                            <span class="text-[10px] bg-blue-50 text-blue-700 px-3 py-1 rounded-full font-black uppercase tracking-wider">Confidentialité</span>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8 mt-2">
                            <div class="md:col-span-2 p-4 bg-amber-50 rounded-2xl border border-amber-100 border-dashed mb-4">
                                <p class="text-xs text-amber-800 font-medium">
                                    Laissez les champs vides si vous ne souhaitez pas modifier le mot de passe actuel.
                                </p>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">Nouveau mot de passe</label>
                                <input v-model="form.password" type="password" placeholder="••••••••" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary-500 transition shadow-sm" />
                                <div v-if="form.errors.password" class="text-red-500 text-[10px] mt-1 font-bold">{{ form.errors.password }}</div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">Confirmer le mot de passe</label>
                                <input v-model="form.password_confirmation" type="password" placeholder="••••••••" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary-500 transition shadow-sm" />
                            </div>
                        </div>
                    </div>

                    <!-- Footer Submission -->
                    <div class="flex items-center justify-end gap-6 pt-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="btn btn-primary px-10 py-4 text-base font-black tracking-widest shadow-xl shadow-primary-200 group relative overflow-hidden inline-flex items-center"
                        >
                            <span v-if="form.processing" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Mise à jour...
                            </span>
                            <span v-else class="flex items-center gap-2">
                                Enregistrer les modifications
                                <CheckIcon class="w-5 h-5 group-hover:scale-110 transition-transform" />
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Focus States for inputs */
input:focus, select:focus {
    @apply border-primary-500 ring-4 ring-primary-500/10;
}
</style>