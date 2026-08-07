<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ResponsiveTable from '@/Components/ResponsiveTable.vue';
import {
    BuildingOfficeIcon,
    PlusIcon,
    EyeIcon,
    PencilIcon,
    TrashIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    entites: Object, // Paginated resource
});

const columns = [
    { label: 'Entité', key: 'nom', sortable: true },
    { label: 'Code', key: 'code_entite' },
    { label: 'Membres', key: 'utilisateurs_count', align: 'center' },
    { label: 'Statut', key: 'est_active', align: 'center' },
];

const deleteEntite = (id) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette entité ? Cette action est irréversible.')) {
        router.delete(route('super-admin.entites.destroy', id));
    }
};

const route = (name, params = {}) => {
    return window.route ? window.route(name, params) : `/${name}`;
};
</script>

<template>
    <Head title="Gestion des Entités" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl text-gray-800 leading-tight">
                Gestion des Entités
            </h2>
        </template>

        <div class="py-6 md:py-10">
            <div class="page-container">
                <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center">
                        <BuildingOfficeIcon class="w-8 h-8 text-indigo-600 mr-3" />
                        <div>
                            <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900">Entités & Organisations</h1>
                            <p class="text-sm text-gray-500 mt-1">Gérez les différentes structures et filiales de votre organisation.</p>
                        </div>
                    </div>
                    
                    <Link
                        :href="route('super-admin.entites.create')"
                        class="btn btn-primary self-start md:self-auto inline-flex items-center"
                    >
                        <PlusIcon class="w-5 h-5 mr-2" />
                        Nouvelle entité
                    </Link>
                </div>

                <div class="card border-0 shadow-sm overflow-hidden p-0">
                    <ResponsiveTable
                        :columns="columns"
                        :data="entites.data"
                        class="w-full"
                    >
                        <!-- Custom Name Template -->
                        <template #cell-nom="{ item }">
                            <span class="font-bold text-gray-900 uppercase tracking-tighter">{{ item.nom }}</span>
                        </template>

                        <!-- Custom Code Template -->
                        <template #cell-code_entite="{ item }">
                            <span class="text-xs font-mono font-bold text-gray-500 px-2 py-1 bg-gray-50 rounded border border-gray-100">
                                {{ item.code_entite }}
                            </span>
                        </template>

                        <!-- Custom Count Template -->
                        <template #cell-utilisateurs_count="{ item }">
                            <span class="text-sm font-medium text-gray-600">
                                {{ item.utilisateurs_count || 0 }} collaborateur(s)
                            </span>
                        </template>

                        <!-- Custom Status Template -->
                        <template #cell-est_active="{ item }">
                            <span :class="[
                                'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border',
                                item.est_active ? 'bg-success-50 text-success-700 border-success-100' : 'bg-red-50 text-red-700 border-red-100'
                            ]">
                                {{ item.est_active ? 'Active' : 'Inactive' }}
                            </span>
                        </template>

                        <!-- Actions Template -->
                        <template #actions="{ item }">
                            <div class="flex items-center gap-2">
                                <Link
                                    :href="route('super-admin.entites.show', item.id)"
                                    class="p-2 text-gray-500 hover:bg-gray-50 rounded-lg transition-colors"
                                    title="Détails"
                                >
                                    <EyeIcon class="w-5 h-5" />
                                </Link>
                                <Link
                                    :href="route('super-admin.entites.edit', item.id)"
                                    class="p-2 text-primary-600 hover:bg-primary-50 rounded-lg transition-colors"
                                    title="Modifier"
                                >
                                    <PencilIcon class="w-5 h-5" />
                                </Link>
                                <button
                                    @click="deleteEntite(item.id)"
                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Retirer"
                                >
                                    <TrashIcon class="w-5 h-5" />
                                </button>
                            </div>
                        </template>
                    </ResponsiveTable>

                    <!-- Pagination -->
                    <div v-if="entites.links.length > 3" class="mt-8 mb-8 flex justify-center">
                        <nav class="flex items-center gap-1">
                            <template v-for="(link, index) in entites.links" :key="index">
                                <div
                                    v-if="!link.url"
                                    class="px-3 py-2 text-gray-300 pointer-events-none text-sm font-bold opacity-50"
                                    v-html="link.label"
                                />
                                <Link
                                    v-else
                                    :href="link.url"
                                    :class="[
                                        'px-4 py-2 rounded-xl text-sm font-extrabold transition-all',
                                        link.active ? 'bg-primary-600 text-white shadow-lg shadow-primary-200' : 'bg-white border border-gray-100 text-gray-600 hover:bg-gray-50 hover:border-gray-200 shadow-sm'
                                    ]"
                                    v-html="link.label"
                                />
                            </template>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>