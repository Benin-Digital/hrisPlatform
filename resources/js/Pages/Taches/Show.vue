<script setup>
import { Head, Link, usePage, useForm, router } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import Chronometre from "@/Components/Chronometre.vue";

const props = defineProps({
    tache: Object,
});

const page = usePage();
const authUser = computed(() => page.props.auth.user);

// Copie réactive de la tâche pour les mises à jour locales
const localTache = ref(props.tache);

const canEdit = computed(() => {
    if (!authUser.value) return false;
    const roles = ["super_admin", "admin_entite", "manager"];
    return (
        roles.includes(authUser.value.mainRole?.nom) ||
        authUser.value.id === props.tache.createur_id
    );
});

// Helper pour formater la taille en Ko/Mo
const formatFileSize = (bytes) => {
    if (!bytes) return "0 Ko";
    const kb = bytes / 1024;
    if (kb < 1024) return kb.toFixed(1) + " Ko";
    return (kb / 1024).toFixed(2) + " Mo";
};

// Helper pour choisir une icône selon le type MIME
const getFileIcon = (mime) => {
    if (!mime) return "📄";
    const m = mime.toLowerCase();
    if (m.includes("image/")) return "🖼️";
    if (m.includes("pdf")) return "📕";
    if (m.includes("word") || m.includes("doc")) return "📝";
    if (m.includes("excel") || m.includes("spreadsheet") || m.includes("sheet"))
        return "📊";
    if (m.includes("powerpoint") || m.includes("presentation")) return "📽️";
    if (m.includes("zip") || m.includes("rar")) return "📦";
    return "📎";
};

// Date d'upload formatée
const formatDate = (dateString) => {
    if (!dateString) return "—";
    return new Date(dateString).toLocaleDateString("fr-FR", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
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

// Mise à jour du temps local après arrêt du chrono
const updateTemps = (newTotal) => {
    localTache.value.temps_passe_minutes = newTotal;
};

const formProgress = useForm({
    progression_pourcentage: props.tache.progression_pourcentage ?? 0,
    statut: props.tache.statut,
});

const isAssignee = computed(() => authUser.value?.id === props.tache.assigne_a);

const updateProgress = () => {
    formProgress.post(`/taches/${props.tache.id}/progress`, {
        preserveScroll: true,
        onSuccess: () => {
            // Mettre à jour localement après validation
            localTache.value.progression_pourcentage = formProgress.progression_pourcentage;
            localTache.value.statut = formProgress.statut;
        },
    });
};

const getProgressBarColor = (progress) => {
    if (progress < 30) return "bg-red-500";
    if (progress < 70) return "bg-yellow-500";
    return "bg-green-500";
};

const deleteTache = () => {
    if (
        confirm(
            "Êtes-vous sûr de vouloir supprimer cette tâche ? Cette action est irréversible.",
        )
    ) {
        useForm({}).delete(`/taches/${props.tache.id}`);
    }
};
</script>

<template>
    <Head title="Détails de la tâche" />

    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ localTache.titre }}</h1>
            <div class="space-x-4 flex items-center">
                <Link
                    href="/taches"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-bold hover:bg-gray-200 transition"
                >
                    ← Liste des tâches
                </Link>
                <Link
                    v-if="canEdit"
                    :href="`/taches/${props.tache.id}/edit`"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold shadow-sm hover:bg-indigo-700 transition"
                >
                    Modifier
                </Link>
            </div>
        </div>

        <div
            class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100"
        >
            <!-- Header Status -->
            <div
                class="px-8 py-6 bg-gray-50/50 border-b border-gray-100 flex justify-between items-center"
            >
                <div class="flex items-center space-x-4">
                    <span
                        class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-widest"
                        :class="
                            localTache.statut === 'terminee'
                                ? 'bg-green-100 text-green-700'
                                : 'bg-indigo-100 text-indigo-700'
                        "
                    >
                        {{ localTache.statut.replace("_", " ") }}
                    </span>
                    <span class="text-xs font-bold text-gray-400"
                        >PRIORITÉ : {{ localTache.priorite.toUpperCase() }}</span
                    >
                </div>
                <div class="text-sm font-bold text-gray-500">
                    ÉCHÉANCE :
                    {{
                        localTache.date_echeance
                            ? new Date(localTache.date_echeance).toLocaleDateString(
                                  "fr-FR",
                              )
                            : "NON DÉFINIE"
                    }}
                </div>
            </div>

            <div class="px-8 py-8">
                <!-- Progression Visual -->
                <div class="mb-10">
                    <div class="flex justify-between items-end mb-2">
                        <label
                            class="text-sm font-black text-gray-700 uppercase tracking-widest"
                            >Progression de la tâche</label
                        >
                        <span class="text-2xl font-black text-gray-900"
                            >{{ localTache.progression_pourcentage }}%</span
                        >
                    </div>
                    <div
                        class="w-full bg-gray-100 rounded-full h-4 overflow-hidden shadow-inner"
                    >
                        <div
                            class="h-full transition-all duration-500 ease-out"
                            :class="
                                getProgressBarColor(
                                    localTache.progression_pourcentage,
                                )
                            "
                            :style="{
                                width: localTache.progression_pourcentage + '%',
                            }"
                        ></div>
                    </div>
                </div>

                <!-- Update Progress (Only for Assignee) -->
                <div
                    v-if="isAssignee"
                    class="mb-10 p-6 bg-indigo-50 rounded-2xl border border-indigo-100"
                >
                    <h3
                        class="text-sm font-black text-indigo-900 uppercase tracking-widest mb-4"
                    >
                        Mettre à jour mon avancement
                    </h3>
                    <form
                        @submit.prevent="updateProgress"
                        class="flex flex-col md:flex-row items-end gap-4"
                    >
                        <div class="flex-1 w-full">
                            <input
                                type="range"
                                v-model="formProgress.progression_pourcentage"
                                min="0"
                                max="100"
                                class="w-full h-2 bg-indigo-200 rounded-lg appearance-none cursor-pointer accent-indigo-600"
                            />
                        </div>
                        <div class="w-full md:w-auto">
                            <select
                                v-model="formProgress.statut"
                                class="w-full rounded-xl border-indigo-200 text-sm focus:ring-indigo-500"
                            >
                                <option value="en_attente">En attente</option>
                                <option value="en_cours">En cours</option>
                                <option value="terminee">Terminée</option>
                            </select>
                        </div>
                        <button
                            type="submit"
                            :disabled="formProgress.processing"
                            class="w-full md:w-auto px-6 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-200"
                        >
                            {{ formProgress.processing ? "..." : "Valider" }}
                        </button>
                    </form>
                </div>

                <dl class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="md:col-span-2">
                        <dt
                            class="text-xs font-black text-gray-400 uppercase tracking-widest mb-3"
                        >
                            Description
                        </dt>
                        <dd
                            class="text-gray-800 bg-gray-50 p-6 rounded-2xl border border-gray-100 whitespace-pre-line leading-relaxed"
                        >
                            {{
                                localTache.description ||
                                "Aucune description fournie."
                            }}
                        </dd>
                    </div>

                    <div class="flex items-center space-x-4">
                        <div
                            class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center text-xl"
                        >
                            👤
                        </div>
                        <div>
                            <dt
                                class="text-[10px] font-black text-gray-400 uppercase"
                            >
                                Assignée à
                            </dt>
                            <dd class="font-bold text-gray-900">
                                {{
                                    localTache.assigne
                                        ? `${localTache.assigne.prenom} ${localTache.assigne.nom}`
                                        : "Non assignée"
                                }}
                            </dd>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4">
                        <div
                            class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center text-xl"
                        >
                            ✍️
                        </div>
                        <div>
                            <dt
                                class="text-[10px] font-black text-gray-400 uppercase"
                            >
                                Créée par
                            </dt>
                            <dd class="font-bold text-gray-900">
                                {{
                                    localTache.createur
                                        ? `${localTache.createur.prenom} ${localTache.createur.nom}`
                                        : "Système"
                                }}
                            </dd>
                        </div>
                    </div>
                </dl>
            </div>

            <!-- Section Fichiers joints -->
            <div class="border-t border-gray-100 px-8 py-8 bg-gray-50/30">
                <div class="flex justify-between items-center mb-6">
                    <h2
                        class="text-xl font-black text-gray-900 uppercase tracking-tighter italic"
                    >
                        Documents Joints
                    </h2>
                    <span
                        class="px-2 py-1 bg-gray-200 text-gray-600 rounded text-[10px] font-bold"
                    >
                        {{ localTache.fichiers_joints?.length || 0 }} FICHIER(S)
                    </span>
                </div>

                <div
                    v-if="
                        !localTache.fichiers_joints ||
                        localTache.fichiers_joints.length === 0
                    "
                    class="text-gray-400 italic text-sm text-center py-6 border-2 border-dashed border-gray-100 rounded-2xl"
                >
                    Aucun document n'a été rattaché à cette tâche.
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div
                        v-for="fichier in localTache.fichiers_joints"
                        :key="fichier.nom_stocke"
                        class="flex items-center justify-between bg-white p-4 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition group"
                    >
                        <div
                            class="flex items-center space-x-4 overflow-hidden"
                        >
                            <span
                                class="text-3xl group-hover:scale-110 transition duration-300"
                                >{{ getFileIcon(fichier.mime) }}</span
                            >
                            <div class="overflow-hidden">
                                <p
                                    class="text-sm font-bold text-gray-900 truncate"
                                >
                                    {{ fichier.nom_original }}
                                </p>
                                <p
                                    class="text-[10px] text-gray-400 font-bold uppercase"
                                >
                                    {{ formatFileSize(fichier.taille) }}
                                    <span class="mx-1">•</span>
                                    {{
                                        fichier.uploaded_at
                                            ? formatDate(fichier.uploaded_at)
                                            : "Récemment"
                                    }}
                                </p>
                            </div>
                        </div>

                        <a
                            :href="`/taches/download/${props.tache.id}/${fichier.nom_stocke}`"
                            class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-xl transition shadow-inner"
                            title="Télécharger"
                        >
                            <svg
                                class="w-6 h-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chronomètre -->
        <div class="mt-6 bg-white shadow-xl rounded-2xl border border-gray-100 p-6">
            <h3 class="text-lg font-bold mb-2">⏱️ Suivi du temps</h3>

            <Chronometre
                :tache-id="props.tache.id"
                :temps-initial="localTache.temps_passe_minutes || 0"
                @time-updated="updateTemps"
            />

            <div class="mt-2 text-sm text-gray-500">
                Total enregistré :
                <span class="font-bold">{{
                    formatTotalTime(localTache.temps_passe_minutes || 0)
                }}</span>
            </div>
        </div>
    </div>
</template>