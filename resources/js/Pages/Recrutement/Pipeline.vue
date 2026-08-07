<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-xl">🎯 Pipeline de recrutement</h2>
                <div class="flex gap-3">
                    <Link :href="route('super-admin.offres.create')" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-indigo-700">
                        + Nouvelle offre
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="page-container">
                <!-- Filtres -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 mb-6 border">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <select v-model="filtreOffre" @change="applyFilters" class="w-full rounded-lg border-gray-300">
                                <option value="">Toutes les offres</option>
                                <option v-for="offre in offres" :key="offre.id" :value="offre.id">
                                    {{ offre.titre }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <select v-model="filtreType" @change="applyFilters" class="w-full rounded-lg border-gray-300">
                                <option value="">Tous les types</option>
                                <option value="emploi">Emploi</option>
                                <option value="stage">Stage</option>
                                <option value="alternance">Alternance</option>
                            </select>
                        </div>
                        <div class="flex items-end justify-end">
                            <button @click="resetFilters" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                Réinitialiser
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Kanban -->
                <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-8 gap-4">
                    <div
                        v-for="(statut, key) in statuts"
                        :key="key"
                        :data-status="key"
                        ref="columnRefs"
                        class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-3 min-h-[400px] border border-gray-200 dark:border-gray-700"
                    >
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-bold text-xs text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                {{ statut.label }}
                            </h3>
                            <!-- ✅ Compteur avec totals -->
                            <span class="bg-white dark:bg-gray-700 px-2 py-1 rounded-full text-xs font-bold text-gray-500">
                                {{ totals?.[key] ?? 0 }}
                            </span>
                        </div>

                        <div class="space-y-2 min-h-[100px]">
                            <div
                                v-for="candidature in (grouped[key] || [])"
                                :key="candidature.id"
                                :data-candidature-id="candidature.id"
                                class="bg-white dark:bg-gray-700 p-3 rounded-lg shadow-sm border border-gray-100 dark:border-gray-600 cursor-grab hover:shadow-md transition"
                                @click="openModal(candidature)"
                            >
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-bold text-sm text-gray-800 dark:text-gray-100">
                                            {{ candidature.prenom }} {{ candidature.nom }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ candidature.offre?.titre || 'Sans offre' }}
                                        </p>
                                    </div>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" :class="{
                                        'bg-blue-100 text-blue-700': candidature.type === 'emploi',
                                        'bg-green-100 text-green-700': candidature.type === 'stage',
                                        'bg-purple-100 text-purple-700': candidature.type === 'alternance',
                                    }">
                                        {{ candidature.type }}
                                    </span>
                                </div>

                                <div class="mt-2 flex flex-wrap gap-1 text-[10px] text-gray-400">
                                    <span>📧 {{ candidature.email }}</span>
                                    <span>📅 {{ new Date(candidature.created_at).toLocaleDateString() }}</span>
                                </div>

                                <div v-if="candidature.score_total" class="mt-2 flex items-center gap-2">
                                    <div class="flex-1 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-indigo-500 transition-all" :style="{ width: candidature.score_total + '%' }"></div>
                                    </div>
                                    <span class="text-xs font-bold text-indigo-600">{{ candidature.score_total }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <CandidatureModal
            v-if="selectedCandidature"
            :candidature="selectedCandidature"
            :statuts="statuts"
            @close="selectedCandidature = null"
            @updated="refresh"
        />
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';
import CandidatureModal from '@/Components/CandidatureModal.vue';
import Sortable from 'sortablejs';

const props = defineProps({
    candidatures: Array,   // Collection complète (pas paginée)
    grouped: Object,
    totals: Object,
    statuts: Object,
    offres: Array,
    filters: Object,
});

const selectedCandidature = ref(null);
const filtreOffre = ref(props.filters?.offre_id || '');
const filtreType = ref(props.filters?.type || '');
const columnRefs = ref([]);
let sortableInstances = [];

const applyFilters = () => {
    router.get(
        route('recrutement.pipeline'),
        {
            offre_id: filtreOffre.value,
            type: filtreType.value,
        },
        { preserveState: true }
    );
};

const resetFilters = () => {
    filtreOffre.value = '';
    filtreType.value = '';
    applyFilters();
};

const openModal = (candidature) => {
    selectedCandidature.value = candidature;
};

const refresh = () => {
    // Rafraîchir uniquement les données du pipeline
    router.reload({ only: ['candidatures', 'grouped', 'totals'] });
};

// Drag & drop
const initSortable = () => {
    sortableInstances.forEach(instance => instance.destroy());
    sortableInstances = [];

    columnRefs.value.forEach((columnEl) => {
        const container = columnEl.querySelector('.space-y-2');
        if (!container) return;

        const instance = Sortable.create(container, {
            animation: 150,
            ghostClass: 'opacity-50',
            group: 'candidatures',
            onEnd: (evt) => {
                const candidatureId = evt.item.dataset.candidatureId;
                const newStatus = evt.to.closest('[data-status]')?.dataset.status;
                if (!candidatureId || !newStatus) return;

                // Vérifier si le statut change
                const movedItem = props.grouped[newStatus]?.find(c => c.id === parseInt(candidatureId));
                if (movedItem && movedItem.statut !== newStatus) {
                    router.patch(
                        route('recrutement.change-statut', candidatureId),
                        { statut: newStatus },
                        {
                            preserveState: true,
                            onSuccess: refresh,
                            onError: (errors) => {
                                console.error('Erreur drag & drop :', errors);
                                alert('Erreur : impossible de changer le statut.');
                            }
                        }
                    );
                }
            }
        });

        sortableInstances.push(instance);
    });
};

onMounted(() => {
    nextTick(() => initSortable());
});

watch(() => props.candidatures, () => {
    nextTick(() => initSortable());
}, { deep: true });

onUnmounted(() => {
    sortableInstances.forEach(instance => instance.destroy());
    sortableInstances = [];
});
</script>