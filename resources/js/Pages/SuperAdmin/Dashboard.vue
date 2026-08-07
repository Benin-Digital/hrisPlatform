<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import TaskManagementWidget from "@/Components/Widgets/TaskManagementWidget.vue";
import { computed } from "vue";
import {
    WrenchIcon,
    UsersIcon,
    CheckCircleIcon,
    ChartBarIcon,
    BuildingOfficeIcon,
    LockClosedIcon,
    DocumentTextIcon,
    AcademicCapIcon,
    BriefcaseIcon,
    NewspaperIcon,
    ClipboardDocumentListIcon,
    CogIcon,
    ExclamationTriangleIcon,
    CalendarDaysIcon,
    ArrowRightIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    stats: {
        type: Object,
        required: true,
        default: () => ({}),
    },
    tachesStats: {
        type: Object,
        default: () => ({}),
    },
    mesTachesRecentes: {
        type: Array,
        default: () => [],
    },
    recentAnnonces: {
        type: Array,
        default: () => [],
    },
    upcomingEvents: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const pageProps = computed(() => page.props);
const tachesEntite = computed(() => pageProps.value.tachesEntite);

// Configuration des actions rapides avec leurs icônes
const adminActions = [
    {
        name: "Utilisateurs",
        href: "/collaborateurs",
        icon: UsersIcon,
        color: "bg-primary-50 text-primary-600",
    },
    {
        name: "Rôles",
        href: "/super-admin/roles",
        icon: LockClosedIcon,
        color: "bg-indigo-50 text-indigo-600",
    },
    {
        name: "Entités",
        href: "/super-admin/entites",
        icon: BuildingOfficeIcon,
        color: "bg-blue-50 text-blue-600",
    },
    {
        name: "Statistiques",
        href: "/super-admin/statistiques-publiques",
        icon: ChartBarIcon,
        color: "bg-success-50 text-success-600",
    },
    {
        name: "Documents",
        href: "/documents",
        icon: DocumentTextIcon,
        color: "bg-warning-50 text-warning-600",
    },
    {
        name: "Formations",
        href: "/formations",
        icon: AcademicCapIcon,
        color: "bg-purple-50 text-purple-600",
    },
    {
        name: "Recrutement",
        href: "/super-admin/offres",
        icon: BriefcaseIcon,
        color: "bg-pink-50 text-pink-600",
    },
    {
        name: "Actualités",
        href: "/actualites",
        icon: NewspaperIcon,
        color: "bg-blue-400/10 text-blue-600",
    },
];

// Helper route
const route = (name, params = {}) => {
    return window.route ? window.route(name, params) : `/${name}`;
};
</script>

<template>
    <Head title="Console Super Admin" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                Console d'Administration
            </h2>
        </template>

        <div class="py-6 md:py-10">
            <div class="page-container">
                <!-- Admin Header Banner -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-3xl p-6 md:p-8 mb-8 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col md:flex-row md:items-center justify-between gap-6 overflow-hidden relative"
                >
                    <div class="relative z-10 flex items-center">
                        <WrenchIcon class="w-10 h-10 text-primary-600 dark:text-primary-400 mr-4" />
                        <div>
                            <h1
                                class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-gray-100"
                            >
                                Console de Contrôle
                            </h1>
                            <p
                                class="text-gray-500 dark:text-gray-400 mt-2 text-sm md:text-base max-w-xl"
                            >
                                Accès complet au système. Gérez les entités, les
                                documents, les rôles et supervisez l'activité
                                globale de la plateforme.
                            </p>
                        </div>
                    </div>
                    <div
                        class="flex items-center space-x-3 text-sm bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 px-5 py-2.5 rounded-2xl font-bold border border-primary-100 dark:border-primary-800 self-start md:self-auto shadow-sm"
                    >
                        <span class="relative flex h-3 w-3 mr-1">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"
                            ></span>
                            <span
                                class="relative inline-flex rounded-full h-3 w-3 bg-primary-600"
                            ></span>
                        </span>
                        Système en ligne
                    </div>
                    <div
                        class="absolute -right-10 -bottom-10 w-40 h-40 bg-primary-50 dark:bg-primary-900/20 rounded-full blur-3xl opacity-50"
                    ></div>
                </div>

                <!-- Statistiques globales -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center">
                                <UsersIcon class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                            </div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Total</span>
                        </div>
                        <h3 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-gray-100 mb-1">
                            {{ stats.totalUsers || 0 }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Utilisateurs</p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-green-50 dark:bg-green-900/30 flex items-center justify-center">
                                <CheckCircleIcon class="w-6 h-6 text-green-600 dark:text-green-400" />
                            </div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Actifs</span>
                        </div>
                        <h3 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-gray-100 mb-1">
                            {{ stats.activeUsers || 0 }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Utilisateurs actifs</p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                                <ChartBarIcon class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Nouveaux</span>
                        </div>
                        <h3 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-gray-100 mb-1">
                            {{ stats.newThisMonth || 0 }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Nouveaux ce mois</p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center">
                                <BuildingOfficeIcon class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                            </div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Entités</span>
                        </div>
                        <h3 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-gray-100 mb-1">
                            {{ stats.entities || 0 }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Entités</p>
                    </div>
                </div>

                <!-- Module Navigation Grid -->
                <div class="mb-10">
                    <h3
                        class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4 ml-1"
                    >
                        Modules d'Administration
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <Link
                            v-for="action in adminActions"
                            :key="action.name"
                            :href="action.href"
                            class="card p-4 md:p-6 hover-lift border-0 shadow-sm text-center md:text-left flex flex-col items-center md:items-start group transition-all duration-300"
                        >
                            <div
                                :class="[
                                    'w-12 h-12 md:w-14 md:h-14 rounded-2xl flex items-center justify-center text-2xl mb-4 shadow-sm group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300',
                                    action.color,
                                ]"
                            >
                                <component :is="action.icon" class="w-7 h-7" />
                            </div>
                            <span
                                class="text-sm md:text-base font-bold text-gray-800 dark:text-gray-200 group-hover:text-primary-700 dark:group-hover:text-primary-400 transition-colors"
                                >{{ action.name }}</span
                            >
                            <span
                                class="hidden md:block text-[10px] text-gray-400 dark:text-gray-500 mt-1 uppercase font-semibold"
                                >Gérer ce module</span
                            >
                        </Link>
                    </div>
                </div>

                <!-- Statistiques personnelles (tâches) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-gray-50 dark:bg-gray-700 flex items-center justify-center">
                                <ClipboardDocumentListIcon class="w-6 h-6 text-gray-600 dark:text-gray-400" />
                            </div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Total</span>
                        </div>
                        <h3 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-gray-100 mb-1">
                            {{ tachesStats.total || 0 }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Mes tâches</p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                                <CogIcon class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">En cours</span>
                        </div>
                        <h3 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-gray-100 mb-1">
                            {{ tachesStats.enCours || 0 }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">En cours</p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-green-50 dark:bg-green-900/30 flex items-center justify-center">
                                <CheckCircleIcon class="w-6 h-6 text-green-600 dark:text-green-400" />
                            </div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Terminées</span>
                        </div>
                        <h3 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-gray-100 mb-1">
                            {{ tachesStats.terminees || 0 }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Terminées</p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-red-50 dark:bg-red-900/30 flex items-center justify-center">
                                <ExclamationTriangleIcon class="w-6 h-6 text-red-600 dark:text-red-400" />
                            </div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Retard</span>
                        </div>
                        <h3 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-gray-100 mb-1">
                            {{ tachesStats.enRetard || 0 }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">En retard</p>
                    </div>
                </div>

                <!-- Mes tâches récentes -->
                <div v-if="mesTachesRecentes.length > 0" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center">
                        <ClipboardDocumentListIcon class="w-6 h-6 text-indigo-600 dark:text-indigo-400 mr-2" />
                        Mes tâches récentes
                    </h2>
                    <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                        <li v-for="tache in mesTachesRecentes.slice(0, 5)" :key="tache.id" class="py-3 flex justify-between items-center">
                            <div>
                                <p class="font-medium text-gray-800 dark:text-gray-200">{{ tache.titre }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Assignée à : {{ tache.assigne?.prenom }} {{ tache.assigne?.nom }}
                                </p>
                            </div>
                            <span class="text-xs font-bold px-3 py-1 rounded-full"
                                :class="{
                                    'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200': tache.statut === 'en_attente',
                                    'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200': tache.statut === 'en_cours',
                                    'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200': tache.statut === 'terminee',
                                    'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300': tache.statut === 'annulee',
                                }"
                            >
                                {{ tache.statut?.replace('_', ' ') || 'Inconnu' }}
                            </span>
                        </li>
                    </ul>
                    <div class="mt-4 text-right">
                        <Link href="/taches" class="text-indigo-600 dark:text-indigo-400 hover:underline text-sm flex items-center justify-end gap-1">
                            Voir toutes mes tâches
                            <ArrowRightIcon class="w-4 h-4" />
                        </Link>
                    </div>
                </div>

                <!-- Global Task Follow-up -->
                <div v-if="tachesEntite" class="mb-10">
                    <TaskManagementWidget
                        title="Suivi Global des Activités"
                        :task-data="tachesEntite"
                    />
                </div>

                <!-- Flux d'Informations : Actualités et Événements -->
                <div class="mt-10">
                    <h3
                        class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-6 ml-1"
                    >
                        Flux d'Informations
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Actualités Récentes -->
                        <div
                            class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-gray-700"
                        >
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="text-lg font-bold text-gray-800 dark:text-gray-200 flex items-center">
                                    <NewspaperIcon class="w-6 h-6 text-indigo-600 dark:text-indigo-400 mr-2" />
                                    Actualités Récentes
                                </h4>
                                <Link
                                    :href="route('actualites.index')"
                                    class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1"
                                >
                                    Voir tout
                                    <ArrowRightIcon class="w-4 h-4" />
                                </Link>
                            </div>

                            <div
                                v-if="
                                    recentAnnonces && recentAnnonces.length > 0
                                "
                                class="space-y-3"
                            >
                                <div
                                    v-for="annonce in recentAnnonces.slice(
                                        0,
                                        3,
                                    )"
                                    :key="annonce.id"
                                    class="pb-3 border-b border-gray-100 dark:border-gray-700 last:border-0"
                                >
                                    <p class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ annonce.titre }}
                                    </p>
                                    <p
                                        class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2"
                                    >
                                        {{ annonce.contenu }}
                                    </p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                        {{
                                            new Date(
                                                annonce.created_at,
                                            ).toLocaleDateString("fr-FR")
                                        }}
                                    </p>
                                </div>
                            </div>
                            <div v-else class="text-center text-gray-500 dark:text-gray-400 py-6">
                                Aucune actualité récente.
                            </div>
                        </div>
                        <!-- Événements à Venir -->
                        <div
                            class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-gray-700"
                        >
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="text-lg font-bold text-gray-800 dark:text-gray-200 flex items-center">
                                    <CalendarDaysIcon class="w-6 h-6 text-indigo-600 dark:text-indigo-400 mr-2" />
                                    Événements à Venir
                                </h4>
                                <Link
                                    :href="route('agenda.index')"
                                    class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1"
                                >
                                    Calendrier
                                    <ArrowRightIcon class="w-4 h-4" />
                                </Link>
                            </div>

                            <div
                                v-if="
                                    upcomingEvents && upcomingEvents.length > 0
                                "
                                class="space-y-3"
                            >
                                <div
                                    v-for="event in upcomingEvents.slice(0, 3)"
                                    :key="event.id"
                                    class="flex items-center gap-3 pb-3 border-b border-gray-100 dark:border-gray-700 last:border-0"
                                >
                                    <div
                                        class="w-12 h-12 rounded-lg flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                                        :style="{
                                            backgroundColor:
                                                event.couleur || '#6366f1',
                                        }"
                                    >
                                        {{
                                            new Date(
                                                event.date_debut,
                                            ).toLocaleDateString("fr-FR", {
                                                day: "2-digit",
                                                month: "short",
                                            })
                                        }}
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ event.titre }}
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{
                                                event.type_evenement ||
                                                "Événement"
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center text-gray-500 dark:text-gray-400 py-6">
                                Aucun événement à venir.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>