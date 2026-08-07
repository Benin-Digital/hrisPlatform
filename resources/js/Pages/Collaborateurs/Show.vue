<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    UserIcon,
    AcademicCapIcon,
    ClipboardDocumentListIcon,
    DocumentTextIcon,
    ClockIcon,
    PencilIcon,
    ArrowLeftIcon,
    ArrowDownTrayIcon,
    CheckCircleIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    collaborateur: Object,
    historique: Array,
    canEdit: Boolean,
});

const activeTab = ref('infos');

const tabs = [
    { key: 'infos', label: 'Informations', icon: UserIcon },
    { key: 'formations', label: 'Formations', icon: AcademicCapIcon },
    { key: 'taches', label: 'Tâches', icon: ClipboardDocumentListIcon },
    { key: 'documents', label: 'Documents', icon: DocumentTextIcon },
    { key: 'historique', label: 'Historique', icon: ClockIcon },
];

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
};

const getStatusColor = (statut) => {
    const colors = {
        en_attente: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200',
        en_cours: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200',
        terminee: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200',
        annulee: 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
        valide: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200',
        en_attente_validation: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200',
        rejete: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200',
    };
    return colors[statut] || 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300';
};

const formatStatusLabel = (statut) => {
    const labels = {
        en_attente: 'En attente',
        en_cours: 'En cours',
        terminee: 'Terminée',
        annulee: 'Annulée',
        valide: 'Validé',
        en_attente_validation: 'En attente de validation',
        rejete: 'Rejeté',
    };
    return labels[statut] || statut;
};

// Mapping des icônes pour l'historique
const historyIconMap = {
    document: DocumentTextIcon,
    tache: CheckCircleIcon,
    formation: AcademicCapIcon,
};
</script>

<template>
    <Head :title="`${collaborateur.prenom} ${collaborateur.nom}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center w-full">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-lg">
                        {{ collaborateur.prenom?.[0] }}{{ collaborateur.nom?.[0] }}
                    </div>
                    <div>
                        <h2 class="font-bold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                            {{ collaborateur.prenom }} {{ collaborateur.nom }}
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ collaborateur.poste || 'Poste non défini' }} • {{ collaborateur.matricule || 'Sans matricule' }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('collaborateurs.index')" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 inline-flex items-center gap-1">
                        <ArrowLeftIcon class="w-4 h-4" />
                        Retour
                    </Link>
                    <Link v-if="canEdit" :href="route('collaborateurs.edit', collaborateur.id)" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 transition inline-flex items-center gap-2">
                        <PencilIcon class="w-4 h-4" />
                        Modifier
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="page-container">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <!-- Tabs -->
                    <div class="border-b border-gray-200 dark:border-gray-700 px-6">
                        <nav class="flex space-x-4 overflow-x-auto" aria-label="Tabs">
                            <button
                                v-for="tab in tabs"
                                :key="tab.key"
                                @click="activeTab = tab.key"
                                class="py-4 px-2 text-sm font-medium border-b-2 transition whitespace-nowrap inline-flex items-center gap-2"
                                :class="activeTab === tab.key
                                    ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                    : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600'
                                "
                            >
                                <component :is="tab.icon" class="w-5 h-5" />
                                {{ tab.label }}
                            </button>
                        </nav>
                    </div>

                    <!-- Contenu des onglets -->
                    <div class="p-6">
                        <!-- Onglet Informations -->
                        <div v-if="activeTab === 'infos'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-4">Identité</h3>
                                <dl class="space-y-3">
                                    <div class="flex justify-between border-b border-gray-100 dark:border-gray-700 pb-2">
                                        <dt class="text-sm text-gray-500 dark:text-gray-400">Nom complet</dt>
                                        <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ collaborateur.prenom }} {{ collaborateur.nom }}</dd>
                                    </div>
                                    <div class="flex justify-between border-b border-gray-100 dark:border-gray-700 pb-2">
                                        <dt class="text-sm text-gray-500 dark:text-gray-400">Matricule</dt>
                                        <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ collaborateur.matricule || '—' }}</dd>
                                    </div>
                                    <div class="flex justify-between border-b border-gray-100 dark:border-gray-700 pb-2">
                                        <dt class="text-sm text-gray-500 dark:text-gray-400">Email</dt>
                                        <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ collaborateur.email }}</dd>
                                    </div>
                                    <div class="flex justify-between border-b border-gray-100 dark:border-gray-700 pb-2">
                                        <dt class="text-sm text-gray-500 dark:text-gray-400">Téléphone</dt>
                                        <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ collaborateur.telephone || '—' }}</dd>
                                    </div>
                                    <div class="flex justify-between border-b border-gray-100 dark:border-gray-700 pb-2">
                                        <dt class="text-sm text-gray-500 dark:text-gray-400">Date de naissance</dt>
                                        <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ formatDate(collaborateur.date_naissance) }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <div>
                                <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-4">Organisation</h3>
                                <dl class="space-y-3">
                                    <div class="flex justify-between border-b border-gray-100 dark:border-gray-700 pb-2">
                                        <dt class="text-sm text-gray-500 dark:text-gray-400">Poste</dt>
                                        <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ collaborateur.poste || '—' }}</dd>
                                    </div>
                                    <div class="flex justify-between border-b border-gray-100 dark:border-gray-700 pb-2">
                                        <dt class="text-sm text-gray-500 dark:text-gray-400">Entité</dt>
                                        <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ collaborateur.entite?.nom || '—' }}</dd>
                                    </div>
                                    <div class="flex justify-between border-b border-gray-100 dark:border-gray-700 pb-2">
                                        <dt class="text-sm text-gray-500 dark:text-gray-400">Direction</dt>
                                        <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ collaborateur.direction?.nom || '—' }}</dd>
                                    </div>
                                    <div class="flex justify-between border-b border-gray-100 dark:border-gray-700 pb-2">
                                        <dt class="text-sm text-gray-500 dark:text-gray-400">Date d'embauche</dt>
                                        <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ formatDate(collaborateur.date_embauche) }}</dd>
                                    </div>
                                    <div class="flex justify-between border-b border-gray-100 dark:border-gray-700 pb-2">
                                        <dt class="text-sm text-gray-500 dark:text-gray-400">Statut</dt>
                                        <dd class="text-sm font-medium">
                                            <span :class="['px-2 py-1 rounded-full text-xs font-bold', getStatusColor(collaborateur.statut)]">
                                                {{ collaborateur.statut || 'Inactif' }}
                                            </span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <div class="md:col-span-2">
                                <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-4">Rôles & Permissions</h3>
                                <div class="flex flex-wrap gap-2">
                                    <span v-for="role in collaborateur.roles" :key="role.id" class="px-3 py-1 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-full text-xs font-bold">
                                        {{ role.nom_affichage || role.nom }}
                                    </span>
                                    <span v-if="!collaborateur.roles || collaborateur.roles.length === 0" class="text-sm text-gray-400 dark:text-gray-500">Aucun rôle attribué</span>
                                </div>
                            </div>
                        </div>

                        <!-- Onglet Formations -->
                        <div v-if="activeTab === 'formations'">
                            <div v-if="collaborateur.formations && collaborateur.formations.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div v-for="formation in collaborateur.formations" :key="formation.id" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-100 dark:border-gray-600">
                                    <Link :href="route('formations.show', formation.id)" class="font-bold text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400">
                                        {{ formation.titre }}
                                    </Link>
                                    <div class="mt-2 flex items-center gap-4 text-sm">
                                        <span :class="['px-2 py-0.5 rounded-full text-xs font-bold', getStatusColor(formation.pivot?.statut)]">
                                            {{ formatStatusLabel(formation.pivot?.statut) }}
                                        </span>
                                        <span class="text-gray-500 dark:text-gray-400">Progression : {{ formation.pivot?.progression_pourcentage || 0 }}%</span>
                                    </div>
                                    <div class="w-full h-1.5 bg-gray-200 dark:bg-gray-600 rounded-full mt-2 overflow-hidden">
                                        <div class="h-full bg-indigo-500 transition-all" :style="{ width: (formation.pivot?.progression_pourcentage || 0) + '%' }"></div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-12 text-gray-500 dark:text-gray-400">
                                Ce collaborateur n'est inscrit à aucune formation.
                            </div>
                        </div>

                        <!-- Onglet Tâches -->
                        <div v-if="activeTab === 'taches'">
                            <div v-if="collaborateur.taches && collaborateur.taches.length > 0" class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Titre</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Statut</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Priorité</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Échéance</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Progression</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <tr v-for="tache in collaborateur.taches" :key="tache.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                                <Link :href="route('taches.show', tache.id)" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                                    {{ tache.titre }}
                                                </Link>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span :class="['px-2 py-1 rounded-full text-xs font-bold', getStatusColor(tache.statut)]">
                                                    {{ formatStatusLabel(tache.statut) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 capitalize">{{ tache.priorite }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ formatDate(tache.date_echeance) }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ tache.progression_pourcentage || 0 }}%</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div v-else class="text-center py-12 text-gray-500 dark:text-gray-400">
                                Ce collaborateur n'a aucune tâche assignée.
                            </div>
                        </div>

                        <!-- Onglet Documents -->
                        <div v-if="activeTab === 'documents'">
                            <div v-if="collaborateur.documents_proprietaire && collaborateur.documents_proprietaire.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div v-for="doc in collaborateur.documents_proprietaire" :key="doc.id" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-100 dark:border-gray-600 flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ doc.nom_original }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ doc.extension }} • {{ (doc.taille_octets / 1024).toFixed(1) }} Ko</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ formatDate(doc.created_at) }}</p>
                                    </div>
                                    <Link :href="route('documents.download', doc.uuid)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                                        <ArrowDownTrayIcon class="w-5 h-5" />
                                    </Link>
                                </div>
                            </div>
                            <div v-else class="text-center py-12 text-gray-500 dark:text-gray-400">
                                Ce collaborateur n'a aucun document.
                            </div>
                        </div>

                        <!-- Onglet Historique -->
                        <div v-if="activeTab === 'historique'">
                            <div v-if="historique && historique.length > 0" class="space-y-4">
                                <div v-for="(item, index) in historique" :key="index" class="flex items-start gap-4 pb-4 border-b border-gray-100 dark:border-gray-700 last:border-0">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0"
                                        :class="{
                                            'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300': item.type === 'document',
                                            'bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-300': item.type === 'tache',
                                            'bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-300': item.type === 'formation',
                                        }"
                                    >
                                        <component :is="historyIconMap[item.type] || DocumentTextIcon" class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-800 dark:text-gray-200">{{ item.description }}</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ formatDate(item.date) }}</p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-12 text-gray-500 dark:text-gray-400">
                                Aucune activité récente.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>