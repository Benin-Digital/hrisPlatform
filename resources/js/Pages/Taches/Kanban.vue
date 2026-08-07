<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import KanbanBoard from '@/Components/KanbanBoard.vue';
import {onMounted} from 'vue';

const props = defineProps({
    taches: {
        type: Array,
        required: true,
        default: () => [],
    },
    canCreate: {
        type: Boolean,
        default: false,
    },
});
onMounted(() => {
    console.log('Tâches reçues dans Kanban.vue:', props.taches);
    //const hasTaches = props.taches && props.taches.length > 0;
    console.log('Nombre de tâches :', props.taches?.length);
});
</script>

<template>
    <Head title="Kanban des tâches" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center w-full">
                <h2 class="font-bold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    Kanban des tâches
                </h2>
                <div class="space-x-3">
                    <Link href="/taches" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 bg-white dark:bg-gray-800 px-4 py-2 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                        📋 Vue Liste
                    </Link>
                    <Link v-if="canCreate" href="/taches/create" class="text-sm bg-indigo-600 text-white px-4 py-2 rounded-lg shadow-sm hover:bg-indigo-700">
                        + Nouvelle tâche
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="page-container">
                <KanbanBoard :taches="taches" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>