<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import ResponsiveTable from "@/Components/ResponsiveTable.vue";
import {
    UsersIcon,
    MagnifyingGlassIcon,
    RocketLaunchIcon,
    FolderIcon,
    EyeIcon,
    PencilIcon,
    TrashIcon,
    PlusIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    collaborateurs: Object,
    entites: Array,
    directions: Array,
    filters: Object,
});

const search = ref(props.filters?.search || "");
const entiteFilter = ref(props.filters?.entite_id || "");
const directionFilter = ref(props.filters?.direction_id || "");

// Recherche instantanée
const searchCollaborateurs = () => {
    router.get(
        route("collaborateurs.index"),
        {
            search: search.value,
            entite_id: entiteFilter.value || null,
            direction_id: directionFilter.value || null,
        },
        { preserveState: true, replace: true, preserveScroll: true },
    );
};

// Suppression avec confirmation
const supprimer = (id, nomComplet) => {
    if (
        confirm(
            `Êtes-vous sûr de vouloir supprimer ${nomComplet} ? Cette action est irréversible.`,
        )
    ) {
        router.delete(route("collaborateurs.destroy", id), {
            onSuccess: () => {
                // La page se recharge automatiquement grâce à Inertia
            },
        });
    }
};

const hasCollaborateurs = computed(
    () => props.collaborateurs.data && props.collaborateurs.data.length > 0,
);

// Configuration des colonnes pour la table responsive
const tableColumns = [
    { key: "photo", label: "Collaborateur", tdClass: "w-auto min-w-[200px]" },
    { key: "infos", label: "Poste & Direction" },
    { key: "date_embauche", label: "Embauche", hideOnMobile: true },
    { key: "statut", label: "Statut" },
];

// Helper route
const route = (name, params = {}) => {
    return window.route ? window.route(name, params) : `/${name}`;
};
</script>

<template>
    <Head title="Collaborateurs" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl text-gray-800 leading-tight">
                Gestion des Collaborateurs
            </h2>
        </template>

        <div class="py-6 md:py-10">
            <div class="page-container">
                <!-- Header Section -->
                <div
                    class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8"
                >
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <UsersIcon class="w-8 h-8 text-primary-600" />
                            <h1
                                class="text-2xl md:text-3xl font-extrabold text-gray-900 tracking-tight"
                            >
                                Effectifs & Collaborateurs
                            </h1>
                        </div>
                        <p
                            class="text-sm md:text-base text-gray-500 font-medium"
                        >
                            Gérez les accès, les profils et l'organisation de
                            vos équipes.
                        </p>
                    </div>

                    <Link
                        :href="route('collaborateurs.create')"
                        class="btn btn-primary group shadow-lg shadow-primary-200 inline-flex items-center"
                    >
                        <PlusIcon class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform" />
                        Ajouter un collaborateur
                    </Link>
                </div>

                <!-- Search & Filters Container -->
                <div class="card border-0 shadow-sm p-4 md:p-6 mb-8">
                    <div class="flex flex-col lg:flex-row items-center gap-4">
                        <!-- Search -->
                        <div class="w-full lg:flex-1 relative group">
                            <div
                                class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none"
                            >
                                <MagnifyingGlassIcon class="w-5 h-5 text-gray-400 group-focus-within:text-primary-500 transition-colors" />
                            </div>
                            <input
                                v-model="search"
                                @input="searchCollaborateurs"
                                type="text"
                                placeholder="Rechercher par nom, matricule, email..."
                                class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-11 pr-4 py-3 text-sm font-medium focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all outline-none"
                            />
                        </div>

                        <div
                            class="flex flex-col sm:flex-row w-full lg:w-auto gap-4"
                        >
                            <!-- Entité Filter -->
                            <div class="w-full sm:w-48">
                                <select
                                    v-model="entiteFilter"
                                    @change="searchCollaborateurs"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-bold text-gray-700 focus:ring-2 focus:ring-primary-500 transition-all outline-none"
                                >
                                    <option value="">Toutes les entités</option>
                                    <option
                                        v-for="entite in props.entites"
                                        :key="entite.id"
                                        :value="entite.id"
                                    >
                                        {{ entite.nom }}
                                    </option>
                                </select>
                            </div>

                            <!-- Direction Filter -->
                            <div class="w-full sm:w-48">
                                <select
                                    v-model="directionFilter"
                                    @change="searchCollaborateurs"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-bold text-gray-700 focus:ring-2 focus:ring-primary-500 transition-all outline-none"
                                >
                                    <option value="">Toutes les directions</option>
                                    <option
                                        v-for="dir in props.directions"
                                        :key="dir.id"
                                        :value="dir.id"
                                    >
                                        {{ dir.nom }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Summary -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                    <div
                        class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm"
                    >
                        <p
                            class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1"
                        >
                            Total
                        </p>
                        <p class="text-2xl font-black text-gray-900">
                            {{ collaborateurs.total }}
                        </p>
                    </div>
                    <div
                        class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm"
                    >
                        <p
                            class="text-[10px] font-black text-green-400 uppercase tracking-widest mb-1"
                        >
                            Actifs
                        </p>
                        <p class="text-2xl font-black text-green-600">
                            {{
                                collaborateurs.data.filter(
                                    (u) => u.statut === "actif",
                                ).length
                            }}
                        </p>
                    </div>
                </div>

                <!-- Vue Tableau (desktop) -->
                <div v-if="hasCollaborateurs" class="hidden md:block">
                    <div class="card border-0 shadow-sm overflow-hidden">
                        <ResponsiveTable
                            :columns="tableColumns"
                            :data="collaborateurs.data"
                            empty-message="Aucun collaborateur à afficher"
                        >
                            <!-- Photo Column -->
                            <template #col-photo="{ item }">
                                <div class="flex items-center gap-4">
                                    <img
                                        v-if="item.photo_profil"
                                        :src="'/storage/' + item.photo_profil"
                                        class="h-11 w-11 rounded-xl object-cover ring-2 ring-gray-50 shadow-sm flex-shrink-0"
                                        :alt="item.prenom + ' ' + item.nom"
                                    />
                                    <div
                                        v-else
                                        class="h-11 w-11 rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white font-black text-xs shadow-sm flex-shrink-0"
                                    >
                                        {{ item.prenom?.[0]
                                        }}{{ item.nom?.[0] }}
                                    </div>
                                    <div class="min-w-0">
                                        <div
                                            class="font-bold text-gray-900 truncate"
                                        >
                                            {{ item.prenom }} {{ item.nom }}
                                        </div>
                                        <div
                                            class="text-[10px] text-gray-400 font-black uppercase tracking-tight truncate"
                                        >
                                            {{
                                                item.matricule ||
                                                "Sans matricule"
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Infos Column -->
                            <template #col-infos="{ item }">
                                <div class="font-bold text-gray-700 text-xs">
                                    {{ item.poste || "Poste non défini" }}
                                </div>
                                <div
                                    class="text-[10px] font-black text-primary-600 uppercase mt-0.5"
                                >
                                    {{
                                        item.direction?.nom || "Sans direction"
                                    }}
                                </div>
                            </template>

                            <!-- Embauche Column -->
                            <template #col-date_embauche="{ item }">
                                <div class="text-xs font-bold text-gray-600">
                                    {{
                                        item.date_embauche
                                            ? new Date(
                                                  item.date_embauche,
                                              ).toLocaleDateString("fr-FR", {
                                                  day: "2-digit",
                                                  month: "short",
                                                  year: "numeric",
                                              })
                                            : "-"
                                    }}
                                </div>
                            </template>

                            <!-- Statut Column -->
                            <template #col-statut="{ item }">
                                <span
                                    class="badge"
                                    :class="{
                                        'badge-success':
                                            item.statut === 'actif',
                                        'badge-danger':
                                            item.statut === 'inactif' ||
                                            item.statut === 'suspendu',
                                        'badge-warning':
                                            item.statut === 'conges',
                                    }"
                                >
                                    {{ item.statut }}
                                </span>
                            </template>

                            <!-- Actions Slot -->
                            <template #actions="{ item }">
                                <div
                                    class="flex items-center justify-end gap-1"
                                >
                                    <Link
                                        :href="
                                            route(
                                                'collaborateurs.show',
                                                item.id,
                                            )
                                        "
                                        class="p-2 text-primary-600 hover:bg-primary-50 rounded-lg transition-colors"
                                        title="Voir le profil"
                                    >
                                        <EyeIcon class="w-5 h-5" />
                                    </Link>
                                    <Link
                                        :href="
                                            route(
                                                'collaborateurs.edit',
                                                item.id,
                                            )
                                        "
                                        class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors"
                                        title="Modifier"
                                    >
                                        <PencilIcon class="w-5 h-5" />
                                    </Link>
                                    <button
                                        @click="
                                            supprimer(
                                                item.id,
                                                item.prenom + ' ' + item.nom,
                                            )
                                        "
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

                <!-- Vue Cartes (mobile) -->
                <div v-if="hasCollaborateurs" class="md:hidden space-y-4">
                    <div
                        v-for="item in collaborateurs.data"
                        :key="item.id"
                        class="bg-white rounded-xl shadow-sm border border-gray-200 p-4"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <img
                                    v-if="item.photo_profil"
                                    :src="'/storage/' + item.photo_profil"
                                    class="h-12 w-12 rounded-xl object-cover ring-2 ring-gray-50 shadow-sm"
                                    :alt="item.prenom + ' ' + item.nom"
                                />
                                <div
                                    v-else
                                    class="h-12 w-12 rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white font-black text-sm shadow-sm"
                                >
                                    {{ item.prenom?.[0] }}{{ item.nom?.[0] }}
                                </div>
                                <div>
                                    <div
                                        class="font-bold text-gray-900 text-sm"
                                    >
                                        {{ item.prenom }} {{ item.nom }}
                                    </div>
                                    <div
                                        class="text-[10px] text-gray-400 font-black uppercase tracking-tight"
                                    >
                                        {{ item.matricule || "Sans matricule" }}
                                    </div>
                                </div>
                            </div>
                            <span
                                class="badge"
                                :class="{
                                    'badge-success': item.statut === 'actif',
                                    'badge-danger':
                                        item.statut === 'inactif' ||
                                        item.statut === 'suspendu',
                                    'badge-warning': item.statut === 'conges',
                                }"
                            >
                                {{ item.statut }}
                            </span>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <p class="text-gray-400 font-bold">Poste</p>
                                <p class="font-medium text-gray-800">
                                    {{ item.poste || "Non défini" }}
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-400 font-bold">Direction</p>
                                <p class="font-medium text-gray-800">
                                    {{ item.direction?.nom || "Sans direction" }}
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-400 font-bold">Embauche</p>
                                <p class="font-medium text-gray-800">
                                    {{
                                        item.date_embauche
                                            ? new Date(
                                                  item.date_embauche,
                                              ).toLocaleDateString("fr-FR", {
                                                  day: "2-digit",
                                                  month: "short",
                                                  year: "numeric",
                                              })
                                            : "-"
                                    }}
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-400 font-bold">Email</p>
                                <p class="font-medium text-gray-800 truncate">
                                    {{ item.email }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="mt-4 flex justify-end gap-2 border-t border-gray-100 pt-3"
                        >
                            <Link
                                :href="route('collaborateurs.show', item.id)"
                                class="px-3 py-1.5 bg-primary-50 text-primary-700 rounded-lg text-xs font-bold hover:bg-primary-100 transition inline-flex items-center"
                            >
                                <EyeIcon class="w-4 h-4 mr-1" />
                                Voir
                            </Link>
                            <Link
                                :href="route('collaborateurs.edit', item.id)"
                                class="px-3 py-1.5 bg-amber-50 text-amber-700 rounded-lg text-xs font-bold hover:bg-amber-100 transition inline-flex items-center"
                            >
                                <PencilIcon class="w-4 h-4 mr-1" />
                                Modifier
                            </Link>
                            <button
                                @click="
                                    supprimer(
                                        item.id,
                                        item.prenom + ' ' + item.nom,
                                    )
                                "
                                class="px-3 py-1.5 bg-red-50 text-red-700 rounded-lg text-xs font-bold hover:bg-red-100 transition inline-flex items-center"
                            >
                                <TrashIcon class="w-4 h-4 mr-1" />
                                Supprimer
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Message si aucune tâche -->
                <div
                    v-if="!hasCollaborateurs"
                    class="card border-0 shadow-sm py-20 text-center bg-gray-50/50"
                >
                    <UsersIcon class="w-20 h-20 text-gray-300 mx-auto mb-4" />
                    <h3 class="text-xl font-bold text-gray-900 mb-2">
                        Aucun résultat trouvé
                    </h3>
                    <p class="text-gray-500 max-w-sm mx-auto font-medium">
                        Ajustez vos filtres ou effectuez une nouvelle recherche.
                    </p>
                </div>

                <!-- Pagination -->
                <div
                    v-if="hasCollaborateurs && collaborateurs.links && collaborateurs.links.length > 0"
                    class="mt-8 flex flex-col md:flex-row justify-between items-center gap-6"
                >
                    <div class="text-center md:text-left">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">
                            Affichage
                        </p>
                        <p class="text-sm font-bold text-gray-700">
                            <span class="text-primary-600">{{ collaborateurs.from || 0 }}</span>
                            à
                            <span class="text-primary-600">{{ collaborateurs.to || 0 }}</span>
                            sur
                            <span class="text-primary-600">{{ collaborateurs.total }}</span>
                            collaborateurs
                        </p>
                    </div>
                    <div class="flex justify-center space-x-2">
                        <template v-for="link in collaborateurs.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                v-html="link.label"
                                :class="{
                                    'px-4 py-2 rounded bg-indigo-600 text-white': link.active,
                                    'px-4 py-2 rounded bg-gray-200 dark:bg-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500': !link.active,
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
        </div>
    </AuthenticatedLayout>
</template>

<style>
/* Custom Pagination Styling */
.pagination-container nav {
    @apply flex flex-wrap justify-center md:justify-end gap-1.5;
}

.pagination-container span[aria-current="page"],
.pagination-container a,
.pagination-container .active {
    @apply px-4 py-2 text-sm font-bold rounded-xl border border-gray-100 transition-all shadow-sm;
}

.pagination-container a {
    @apply bg-white text-gray-600 hover:bg-primary-50 hover:text-primary-700 hover:border-primary-100;
}

.pagination-container span[aria-current="page"],
.pagination-container .active {
    @apply bg-primary-600 text-white border-primary-600 shadow-lg shadow-primary-100;
}

.pagination-container .disabled,
.pagination-container span[aria-disabled="true"] {
    @apply bg-gray-50 text-gray-300 border-gray-100 cursor-not-allowed shadow-none;
}
</style>