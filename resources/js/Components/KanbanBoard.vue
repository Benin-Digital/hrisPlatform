<template>
    <div v-if="hasTaches" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div
            v-for="(column, status) in columns"
            :key="status"
            ref="columnRefs"
            :data-status="status"
            class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 min-h-[400px] border border-gray-200 dark:border-gray-700"
        >
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-gray-700 dark:text-gray-300 uppercase text-xs tracking-wider">
                    {{ column.label }}
                </h3>
                <span class="bg-white dark:bg-gray-700 px-2 py-1 rounded-full text-xs font-bold text-gray-500 dark:text-gray-400 shadow-sm">
                    {{ column.tasks.length }}
                </span>
            </div>

            <!-- Conteneur des cartes (Zone de dépôt) -->
            <div class="space-y-2 min-h-[100px]">
                <div
                    v-for="task in column.tasks"
                    :key="task.id"
                    :data-task-id="task.id"
                    class="bg-white dark:bg-gray-700 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-600 hover:shadow-md transition group cursor-grab"
                >
                    <Link :href="`/taches/${task.id}`" class="block">
                        <div class="flex justify-between items-start">
                            <h4 class="font-bold text-sm text-gray-800 dark:text-gray-100">{{ task.titre }}</h4>
                            <span
                                class="text-[10px] font-bold px-2 py-1 rounded-full"
                                :class="{
                                    'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300': task.priorite === 'haute',
                                    'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300': task.priorite === 'moyenne',
                                    'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300': task.priorite === 'basse',
                                }"
                            >
                                {{ task.priorite }}
                            </span>
                        </div>
                        <div class="mt-2 flex justify-between items-center text-xs text-gray-400 dark:text-gray-500">
                            <span>{{ task.assigne?.prenom }} {{ task.assigne?.nom }}</span>
                            <span>{{ task.progression_pourcentage || 0 }}%</span>
                        </div>
                        <div class="w-full h-1 bg-gray-100 dark:bg-gray-600 rounded-full mt-2 overflow-hidden">
                            <div class="h-full bg-indigo-500 transition-all" :style="{ width: (task.progression_pourcentage || 0) + '%' }"></div>
                        </div>
                    </Link>

                    <!-- Boutons de changement de statut (optionnel) -->
                    <div class="mt-3 flex flex-wrap gap-1 border-t border-gray-100 dark:border-gray-600 pt-3">
                        <button
                            v-for="(label, key) in statusList"
                            :key="key"
                            @click="moveTask(task.id, key)"
                            class="px-2 py-1 text-[10px] font-bold rounded-lg transition"
                            :class="{
                                'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-200 dark:hover:bg-indigo-800': key !== task.statut,
                                'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 cursor-default': key === task.statut,
                            }"
                            :disabled="key === task.statut"
                        >
                            {{ label }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div v-else class="text-center py-12 text-gray-500 dark:text-gray-400">
        Aucune tâche à afficher dans le Kanban.
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, watch, onMounted, nextTick, onUnmounted } from 'vue';
import Sortable from 'sortablejs';

const props = defineProps({
    taches: {
        type: Array,
        required: true,
        default: () => [],
    },
});

const hasTaches = ref(false);
const columnRefs = ref([]);
let sortableInstances = [];

const statusList = {
    en_attente: '📋 En attente',
    en_cours: '⚙️ En cours',
    terminee: '✅ Terminée',
    annulee: '🚫 Annulée',
};

const columns = {
    en_attente: { label: '📋 En attente', tasks: [] },
    en_cours: { label: '⚙️ En cours', tasks: [] },
    terminee: { label: '✅ Terminée', tasks: [] },
    annulee: { label: '🚫 Annulée', tasks: [] },
};

// Fonction pour déplacer une tâche (appelée par les boutons ou le drag & drop)
const moveTask = async (taskId, newStatus) => {
    try {
        await axios.patch(`/taches/${taskId}/statut`, { statut: newStatus });
        // Mise à jour locale
        initColumns();
    } catch (error) {
        console.error('Erreur lors du changement de statut', error);
        alert('Erreur lors du changement de statut.');
    }
};

// Initialiser les colonnes avec les tâches
const initColumns = () => {
    Object.keys(columns).forEach(key => {
        columns[key].tasks = [];
    });

    if (props.taches && props.taches.length > 0) {
        props.taches.forEach(task => {
            const status = task.statut || 'en_attente';
            if (columns[status]) {
                columns[status].tasks.push(task);
            } else {
                columns.en_attente.tasks.push(task);
            }
        });
        hasTaches.value = true;
    } else {
        hasTaches.value = false;
    }
};

// Initialiser Sortable sur chaque colonne
const initSortable = () => {
    // Nettoyer les anciennes instances
    sortableInstances.forEach(instance => instance.destroy());
    sortableInstances = [];

    nextTick(() => {
        if (!columnRefs.value || columnRefs.value.length === 0) return;

        columnRefs.value.forEach((columnEl) => {
            const status = columnEl.dataset.status;
            if (!status) return;

            const container = columnEl.querySelector('.space-y-2');
            if (!container) return;

            // Créer l'instance Sortable
            const instance = Sortable.create(container, {
                animation: 150,
                ghostClass: 'opacity-50',
                group: 'tasks', // Permet le drag & drop entre colonnes
                onEnd: (evt) => {
                    const taskId = evt.item.dataset.taskId;
                    if (!taskId) return;

                    const newStatus = status;

                    // Vérifier si la tâche existe dans la colonne cible (pour éviter les mises à jour inutiles)
                    const movedTask = columns[newStatus]?.tasks.find(t => t.id === parseInt(taskId));
                    if (movedTask && movedTask.statut !== newStatus) {
                        moveTask(movedTask.id, newStatus);
                    }
                },
            });

            sortableInstances.push(instance);
        });
    });
};

// Surveiller les changements de tâches pour réinitialiser les colonnes et Sortable
watch(() => props.taches, () => {
    initColumns();
    // Réinitialiser Sortable après que le DOM soit mis à jour
    setTimeout(() => {
        initSortable();
    }, 100);
}, { deep: true });

// Cycle de vie
onMounted(() => {
    initColumns();
    initSortable();
});

onUnmounted(() => {
    // Nettoyer les instances Sortable
    sortableInstances.forEach(instance => instance.destroy());
    sortableInstances = [];
});
</script>

<style scoped>
.cursor-grab {
    cursor: grab;
}
.cursor-grab:active {
    cursor: grabbing;
}
</style>