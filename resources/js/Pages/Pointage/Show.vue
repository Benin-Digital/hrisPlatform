<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import {
    ArrowLeftIcon,
    CheckCircleIcon,
    ClockIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    pointage: Object,
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

// ✅ Vérifier si l'utilisateur peut valider
const canValider = computed(() => {
    const roles = user.value?.roles?.map(r => r.nom) || [];
    return roles.some(r => ['super_admin', 'responsable_rh', 'manager'].includes(r));
});

// ✅ Formateur d'heure
const formatHeure = (val) => {
    if (!val) return '-';
    if (/^\d{2}:\d{2}(:\d{2})?$/.test(val)) {
        return val.substring(0, 5);
    }
    try {
        const d = new Date(val);
        if (!isNaN(d.getTime())) {
            return d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        }
    } catch (e) {}
    return val.toString().substring(0, 5);
};

// ✅ Labels des statuts
const statutLabels = {
    present: 'Présent',
    absent: 'Absent',
    retard: 'Retard',
    conges: 'Congés',
    ferie: 'Férié',
};

const getStatutLabel = (statut) => statutLabels[statut] || statut;

// ✅ Formateur minutes en heures
const formatMinutes = (mins) => {
    if (!mins || mins === 0) return '-';
    const h = Math.floor(mins / 60);
    const m = mins % 60;
    if (h === 0) return `${m}min`;
    return `${h}h${m > 0 ? m + 'min' : ''}`;
};

// ✅ Valider le pointage
const validerPointage = (id) => {
    if (confirm('Valider ce pointage ?')) {
        router.patch(route('pointages.valider', id), {}, {
            onSuccess: () => {
                router.reload({ only: ['pointage'] });
            },
        });
    }
};
</script>

<template>
    <Head title="Détail du pointage" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center w-full">
                <h2 class="font-bold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    Détail du pointage
                </h2>
                <Link :href="route('pointages.index')" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 inline-flex items-center gap-1">
                    <ArrowLeftIcon class="w-4 h-4" />
                    Retour à la liste
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="page-container max-w-3xl">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                    <!-- Infos utilisateur et date -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Utilisateur</p>
                            <p class="font-bold text-gray-900 dark:text-gray-100">
                                {{ pointage.utilisateur?.prenom }} {{ pointage.utilisateur?.nom }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Date</p>
                            <p class="font-bold text-gray-900 dark:text-gray-100">
                                {{ new Date(pointage.date).toLocaleDateString('fr-FR') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Heure d'entrée</p>
                            <p class="font-bold text-gray-900 dark:text-gray-100">
                                {{ formatHeure(pointage.heure_entree) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Heure de sortie</p>
                            <p class="font-bold text-gray-900 dark:text-gray-100">
                                {{ formatHeure(pointage.heure_sortie) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Début de pause</p>
                            <p class="font-bold text-gray-900 dark:text-gray-100">
                                {{ formatHeure(pointage.pause_debut) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Fin de pause</p>
                            <p class="font-bold text-gray-900 dark:text-gray-100">
                                {{ formatHeure(pointage.pause_fin) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Statut</p>
                            <span :class="{
                                'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200': pointage.statut === 'present',
                                'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200': pointage.statut === 'absent',
                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200': pointage.statut === 'retard',
                                'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200': pointage.statut === 'conges',
                                'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-200': pointage.statut === 'ferie',
                            }" class="px-3 py-1 rounded-full text-xs font-bold inline-block">
                                {{ getStatutLabel(pointage.statut) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Commentaire</p>
                            <p class="font-medium text-gray-900 dark:text-gray-100">
                                {{ pointage.commentaire || 'Aucun' }}
                            </p>
                        </div>
                    </div>

                    <!-- Détails supplémentaires -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                        <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase mb-3">Détails</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Temps travaillé</p>
                                <p class="font-bold text-gray-900 dark:text-gray-100">
                                    {{ formatMinutes(pointage.minutes_travaillees) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Minutes de retard</p>
                                <p class="font-bold" :class="pointage.minutes_retard > 0 ? 'text-yellow-600' : 'text-gray-900 dark:text-gray-100'">
                                    {{ formatMinutes(pointage.minutes_retard) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Heures supplémentaires</p>
                                <p class="font-bold" :class="pointage.minutes_supplementaires > 0 ? 'text-blue-600' : 'text-gray-900 dark:text-gray-100'">
                                    {{ formatMinutes(pointage.minutes_supplementaires) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- ✅ Section validation avec bouton -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                        <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase mb-3">Validation</h3>

                        <div v-if="pointage.valide" class="flex items-center space-x-2">
                            <CheckCircleIcon class="w-5 h-5 text-green-600" />
                            <span class="text-green-600 font-bold text-sm">Validé</span>
                            <span v-if="pointage.valide_at" class="text-xs text-gray-400">
                                le {{ new Date(pointage.valide_at).toLocaleDateString('fr-FR') }}
                            </span>
                            <span v-if="pointage.valide_par" class="text-xs text-gray-400">
                                par {{ pointage.valide_par?.prenom || '' }} {{ pointage.valide_par?.nom || '' }}
                            </span>
                        </div>

                        <div v-else-if="canValider" class="flex items-center gap-4">
                            <button
                                @click="validerPointage(pointage.id)"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-bold hover:bg-green-700 transition inline-flex items-center gap-2"
                            >
                                <CheckCircleIcon class="w-4 h-4" />
                                Valider ce pointage
                            </button>
                            <span class="text-gray-400 text-sm inline-flex items-center gap-1">
                                <ClockIcon class="w-4 h-4" />
                                En attente de validation
                            </span>
                        </div>

                        <div v-else class="inline-flex items-center gap-1 text-gray-400 text-sm">
                            <ClockIcon class="w-4 h-4" />
                            En attente de validation
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>