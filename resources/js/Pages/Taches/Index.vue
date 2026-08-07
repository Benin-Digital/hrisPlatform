<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import KanbanBoard from "@/Components/KanbanBoard.vue";

const props = defineProps({
    taches: {
        type: Object,
        required: true,
    },
    canCreate: {
        type: Boolean,
        default: false,
    },
});

// Mode d'affichage : 'list' ou 'kanban'
const viewMode = ref("list");

const toggleView = () => {
    viewMode.value = viewMode.value === "list" ? "kanban" : "list";
};

// Supprimer une tâche
const deleteTache = (id) => {
    if (confirm("Supprimer définitivement cette tâche ?")) {
        useForm({}).delete(`/taches/${id}`);
    }
};

// Formater le temps total en heures/minutes
const formatTotalTime = (minutes) => {
    if (!minutes) return "0 min";
    const h = Math.floor(minutes / 60);
    const m = Math.floor(minutes % 60);
    if (h > 0) {
        return `${h}h ${m}min`;
    }
    return `${m} min`;
};

// Couleur de la barre de progression
const getProgressBarColor = (progress) => {
    if (progress < 30) return "bg-red-500";
    if (progress < 70) return "bg-yellow-500";
    return "bg-green-500";
};
</script>

<template>
    <Head title="Mes Tâches" />

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                Mes Tâches
            </h1>
            <div class="flex items-center space-x-4">
                <button
                    @click="toggleView"
                    class="px-3 py-2 text-sm font-bold rounded-lg transition bg-indigo-600 text-white hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600"
                >
                    {{ viewMode === "list" ? "📊 Vue Kanban" : "📋 Vue Liste" }}
                </button>
                <Link
                    href="/dashboard"
                    class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-bold hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                >
                    ← Dashboard
                </Link>
                <Link
                    v-if="canCreate"
                    href="/taches/create"
                    class="inline-flex items-center px-5 py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 shadow-md transition"
                >
                    <svg
                        class="w-5 h-5 mr-2"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 4v16m8-8H4"
                        />
                    </svg>
                    Créer une tâche
                </Link>
            </div>
        </div>

        <!-- Vue Liste -->
        <div
            v-if="viewMode === 'list'"
            class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden"
        >
            <table
                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
            >
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                        >
                            Titre
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                        >
                            Échéance
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                        >
                            Progression
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                        >
                            Temps passé
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                        >
                            Statut
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                        >
                            Priorité
                        </th>
                        <th
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                        >
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody
                    class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700"
                >
                    <tr
                        v-for="tache in taches.data"
                        :key="tache.id"
                        class="hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                    >
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div
                                class="text-sm font-medium text-gray-900 dark:text-gray-100"
                            >
                                {{ tache.titre }}
                            </div>
                        </td>
                        <td
                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"
                        >
                            {{
                                tache.date_echeance
                                    ? new Date(
                                          tache.date_echeance,
                                      ).toLocaleDateString("fr-FR")
                                    : "—"
                            }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-20 h-2 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden"
                                >
                                    <div
                                        class="h-full transition-all duration-300"
                                        :class="
                                            getProgressBarColor(
                                                tache.progression_pourcentage,
                                            )
                                        "
                                        :style="{
                                            width:
                                                (tache.progression_pourcentage ||
                                                    0) + '%',
                                        }"
                                    ></div>
                                </div>
                                <span
                                    class="text-xs font-medium text-gray-700 dark:text-gray-300"
                                    >{{
                                        tache.progression_pourcentage || 0
                                    }}%</span
                                >
                            </div>
                        </td>
                        <td
                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            {{
                                formatTotalTime(tache.temps_passe_minutes || 0)
                            }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                :class="{
                                    'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200':
                                        tache.statut === 'terminee',
                                    'bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200':
                                        ['en_cours', 'en_attente'].includes(
                                            tache.statut,
                                        ),
                                    'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200':
                                        tache.date_echeance &&
                                        new Date(tache.date_echeance) <
                                            new Date() &&
                                        tache.statut !== 'terminee',
                                    'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300':
                                        !tache.statut,
                                }"
                            >
                                {{
                                    tache.statut
                                        ? tache.statut
                                              .replace("_", " ")
                                              .charAt(0)
                                              .toUpperCase() +
                                          tache.statut
                                              .replace("_", " ")
                                              .slice(1)
                                        : "Inconnu"
                                }}
                            </span>
                        </td>
                        <td
                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 capitalize"
                        >
                            {{ tache.priorite || "—" }}
                        </td>
                        <td
                            class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3"
                        >
                            <Link
                                :href="`/taches/${tache.id}`"
                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300"
                            >
                                Voir
                            </Link>
                            <button
                                v-if="canCreate"
                                @click="deleteTache(tache.id)"
                                class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                            >
                                Supprimer
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Message si aucune tâche -->
            <div
                v-if="!taches.data || taches.data.length === 0"
                class="text-center py-12 text-gray-500 dark:text-gray-400"
            >
                Aucune tâche pour le moment
            </div>

            <div
                v-if="taches.links && taches.links.length > 3"
                class="bg-gray-50 dark:bg-gray-700 px-6 py-4 border-t border-gray-200 dark:border-gray-600"
            >
                <div class="flex justify-center space-x-2">
                    <template v-for="link in taches.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            :class="{
                                'px-4 py-2 rounded bg-indigo-600 text-white':
                                    link.active,
                                'px-4 py-2 rounded bg-gray-200 dark:bg-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500':
                                    !link.active,
                            }"
                        />
                        <span
                            v-else
                            v-html="link.label"
                            class="px-4 py-2 rounded bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed"
                        ></span>
                    </template>
                </div>
            </div>
        </div>

        <!-- Vue Kanban -->
        <KanbanBoard v-else :taches="taches.data" />
    </div>
</template>
