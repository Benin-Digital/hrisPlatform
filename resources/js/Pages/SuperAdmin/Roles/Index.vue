<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ResponsiveTable from '@/Components/ResponsiveTable.vue';
import {
    ShieldCheckIcon,
    PlusIcon,
    PencilIcon,
    TrashIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    roles: Array,
});

const columns = [
    { label: 'Rôle', key: 'nom_affichage', sortable: true },
    { label: 'Niveau', key: 'niveau', align: 'center' },
    { label: 'Membres', key: 'utilisateurs_count', align: 'center' },
];

const deleteRole = (roleId) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce rôle ?')) {
        router.delete(route('super-admin.roles.destroy', roleId));
    }
};

const route = (name, params = {}) => {
    return window.route ? window.route(name, params) : `/${name}`;
};
</script>

<template>
    <Head title="Gestion des Rôles" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl text-gray-800 leading-tight">
                Gestion des Rôles
            </h2>
        </template>

        <div class="py-6 md:py-10">
            <div class="page-container">
                <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center">
                        <ShieldCheckIcon class="w-8 h-8 text-indigo-600 mr-3" />
                        <div>
                            <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900">Rôles & Permissions</h1>
                            <p class="text-sm text-gray-500 mt-1">Configurez les niveaux d'accès et les responsabilités des utilisateurs.</p>
                        </div>
                    </div>
                    
                    <Link
                        :href="route('super-admin.roles.create')"
                        class="btn btn-primary self-start md:self-auto inline-flex items-center"
                    >
                        <PlusIcon class="w-5 h-5 mr-2" />
                        Nouveau rôle
                    </Link>
                </div>

                <div class="card border-0 shadow-sm overflow-hidden p-0">
                    <ResponsiveTable
                        :columns="columns"
                        :data="roles"
                        class="w-full"
                    >
                        <!-- Custom Name Template -->
                        <template #cell-nom_affichage="{ item }">
                            <div class="flex flex-col">
                                <span class="font-bold text-gray-900">{{ item.nom_affichage }}</span>
                                <span class="text-[10px] text-gray-400 font-black uppercase tracking-widest">{{ item.nom }}</span>
                            </div>
                        </template>

                        <!-- Custom Level Template -->
                        <template #cell-niveau="{ item }">
                            <span class="px-2.5 py-1 rounded-lg bg-gray-100 text-gray-700 text-xs font-bold border border-gray-200">
                                Niv. {{ item.niveau }}
                            </span>
                        </template>

                        <!-- Custom Count Template -->
                        <template #cell-utilisateurs_count="{ item }">
                            <span class="text-sm font-medium text-gray-600">
                                {{ item.utilisateurs_count || 0 }} utilisateur(s)
                            </span>
                        </template>

                        <!-- Actions Template -->
                        <template #actions="{ item }">
                            <div class="flex items-center gap-2">
                                <Link
                                    :href="route('super-admin.roles.edit', item.id)"
                                    class="p-2 text-primary-600 hover:bg-primary-50 rounded-lg transition-colors"
                                    title="Éditer"
                                >
                                    <PencilIcon class="w-5 h-5" />
                                </Link>
                                <button
                                    @click="deleteRole(item.id)"
                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Supprimer"
                                >
                                    <TrashIcon class="w-5 h-5" />
                                </button>
                            </div>
                        </template>
                    </ResponsiveTable>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>