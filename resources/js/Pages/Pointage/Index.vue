<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import BadgeuseButton from "@/Components/BadgeuseButton.vue";
import { usePage } from "@inertiajs/vue3";
import {
    DocumentIcon,
    ChartBarIcon,
    CheckCircleIcon,
} from "@heroicons/vue/24/outline";

const page = usePage();
const user = computed(() => page.props.auth?.user);

const props = defineProps({
    pointages: Object,
    utilisateurs: Array,
    filters: Object,
    pointage_du_jour: Object,
});

// Vérifier si l'utilisateur peut valider
const canValider = computed(() => {
    return user.value?.hasAnyRole?.(['super_admin', 'responsable_rh', 'manager']) ?? false;
});

const search = ref(props.filters?.date || "");
const utilisateurFilter = ref(props.filters?.utilisateur_id || "");
const statutFilter = ref(props.filters?.statut || "");

const applyFilters = () => {
    router.get(
        route("pointages.index"),
        {
            date: search.value,
            utilisateur_id: utilisateurFilter.value,
            statut: statutFilter.value,
        },
        { preserveState: true },
    );
};

const resetFilters = () => {
    search.value = "";
    utilisateurFilter.value = "";
    statutFilter.value = "";
    applyFilters();
};

// Formateur d'heure robuste
const formatHeure = (val) => {
    if (!val) return "-";
    if (/^\d{2}:\d{2}(:\d{2})?$/.test(val)) {
        return val.substring(0, 5);
    }
    try {
        const d = new Date(val);
        if (!isNaN(d.getTime())) {
            return d.toLocaleTimeString("fr-FR", { hour: "2-digit", minute: "2-digit" });
        }
    } catch (e) {}
    return val.toString().substring(0, 5);
};

// Labels des statuts
const statutLabels = {
    present: "Présent",
    absent: "Absent",
    retard: "Retard",
    conges: "Congés",
    ferie: "Férié",
};

const getStatutLabel = (statut) => statutLabels[statut] || statut;

// Valider une journée
const validerJournee = (id) => {
    if (confirm("Valider cette journée de présence ?")) {
        router.patch(route("pointages.valider", id), {}, {
            onSuccess: () => {
                // Rechargement automatique
            },
        });
    }
};
</script>

<template>
    <Head title="Pointage & Présence" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center w-full">
                <h2 class="font-bold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    Pointage & Présence
                </h2>
                <BadgeuseButton :pointage="pointage_du_jour" />
            </div>
        </template>

        <div class="py-6">
            <div class="page-container">
                <!-- Filtres -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 mb-6 border border-gray-200 dark:border-gray-700">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <input v-model="search" type="date" @input="applyFilters"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700" />
                        </div>
                        <div>
                            <select v-model="utilisateurFilter" @change="applyFilters"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                <option value="">Tous les utilisateurs</option>
                                <option v-for="u in utilisateurs" :key="u.id" :value="u.id">
                                    {{ u.prenom }} {{ u.nom }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <select v-model="statutFilter" @change="applyFilters"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                <option value="">Tous les statuts</option>
                                <option value="present">Présent</option>
                                <option value="absent">Absent</option>
                                <option value="retard">Retard</option>
                                <option value="conges">Congés</option>
                                <option value="ferie">Férié</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2 flex-wrap">
                            <button @click="resetFilters"
                                class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                Réinitialiser
                            </button>
                            <a :href="`/pointages/export-pdf?date=${search}&utilisateur_id=${utilisateurFilter}&statut=${statutFilter}`"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-bold hover:bg-green-700 transition inline-flex items-center gap-1.5"
                                target="_blank">
                                <DocumentIcon class="w-4 h-4" />
                                PDF
                            </a>
                            <a :href="`/pointages/export-excel?date=${search}&utilisateur_id=${utilisateurFilter}&statut=${statutFilter}`"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 transition inline-flex items-center gap-1.5"
                                target="_blank">
                                <ChartBarIcon class="w-4 h-4" />
                                Excel
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tableau -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">Utilisateur</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">Entrée</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">Sortie</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">Pause</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">Reprise</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">Statut</th>
                                <th v-if="canValider" class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">Validé</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-400 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="p in pointages.data" :key="p.id"
                                class="border-t border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-4 py-3 text-sm font-medium">
                                    {{ p.utilisateur?.prenom }} {{ p.utilisateur?.nom }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ new Date(p.date).toLocaleDateString("fr-FR") }}
                                </td>
                                <td class="px-4 py-3 text-sm">{{ formatHeure(p.heure_entree) }}</td>
                                <td class="px-4 py-3 text-sm">{{ formatHeure(p.heure_sortie) }}</td>
                                <td class="px-4 py-3 text-sm">{{ formatHeure(p.pause_debut) }}</td>
                                <td class="px-4 py-3 text-sm">{{ formatHeure(p.pause_fin) }}</td>
                                <td class="px-4 py-3">
                                    <span :class="{
                                        'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200': p.statut === 'present',
                                        'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200': p.statut === 'absent',
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200': p.statut === 'retard',
                                        'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200': p.statut === 'conges',
                                        'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-200': p.statut === 'ferie',
                                    }" class="px-2 py-1 rounded-full text-xs font-bold">
                                        {{ getStatutLabel(p.statut) }}
                                    </span>
                                </td>
                                <td v-if="canValider" class="px-4 py-3">
                                    <span v-if="p.valide" class="text-green-600 font-bold text-xs inline-flex items-center gap-1">
                                        <CheckCircleIcon class="w-4 h-4" />
                                        Validé
                                    </span>
                                    <button v-else @click="validerJournee(p.id)"
                                        class="px-3 py-1 bg-indigo-600 text-white rounded-lg text-xs font-bold hover:bg-indigo-700 transition">
                                        Valider
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Link :href="route('pointages.show', p.id)" class="text-indigo-600 hover:underline text-sm">
                                        Voir
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="pointages.data.length === 0">
                                <td colspan="9" class="text-center py-6 text-gray-500">Aucun pointage trouvé.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="pointages.links && pointages.links.length > 0"
                    class="mt-8 flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="text-center md:text-left">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Affichage</p>
                        <p class="text-sm font-bold text-gray-700">
                            <span class="text-primary-600">{{ pointages.from || 0 }}</span>
                            à <span class="text-primary-600">{{ pointages.to || 0 }}</span>
                            sur <span class="text-primary-600">{{ pointages.total }}</span> pointages
                        </p>
                    </div>
                    <div class="flex justify-center space-x-2">
                        <template v-for="link in pointages.links" :key="link.label">
                            <Link v-if="link.url" :href="link.url" v-html="link.label" :class="{
                                'px-4 py-2 rounded bg-indigo-600 text-white': link.active,
                                'px-4 py-2 rounded bg-gray-200 dark:bg-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500': !link.active,
                            }" />
                            <span v-else v-html="link.label"
                                class="px-4 py-2 rounded bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed">
                            </span>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>