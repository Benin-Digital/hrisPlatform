<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    BoltIcon,
    PlusIcon,
    EyeIcon,
    EyeSlashIcon,
    PencilIcon,
    TrashIcon,
    ArrowPathIcon,
} from '@heroicons/vue/24/outline';
import {
    ClipboardIcon,
    CheckCircleIcon,
    TargetIcon,
    UsersIcon,
} from '@heroicons/vue/24/solid';

// Mapping des identifiants d'icônes vers les composants Heroicons
const iconMap = {
    'clipboard': ClipboardIcon,
    'check-circle': CheckCircleIcon,
    'target': TargetIcon,
    'users': UsersIcon,
};

const props = defineProps({
    statistiques: Array,
});

const generating = ref(false);

const togglePublish = (stat) => {
    router.post(route('super-admin.statistiques-publiques.toggle', stat.id), {}, {
        preserveScroll: true,
    });
};

const deleteStat = (stat) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette statistique ?')) {
        router.delete(route('super-admin.statistiques-publiques.destroy', stat.id));
    }
};

const generateStats = () => {
    generating.value = true;
    router.post(route('super-admin.statistiques-publiques.generate'), {}, {
        onFinish: () => (generating.value = false),
    });
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Statistiques Publiques" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Statistiques Publiques (Page Welcome)
                </h2>
                <div class="flex gap-4">
                    <button
                        @click="generateStats"
                        :disabled="generating"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition flex items-center gap-2 disabled:opacity-50"
                    >
                        <ArrowPathIcon
                            v-if="generating"
                            class="w-5 h-5 animate-spin"
                        />
                        <BoltIcon v-else class="w-5 h-5" />
                        Générer depuis Productivité
                    </button>
                    <Link
                        :href="
                            route('super-admin.statistiques-publiques.create')
                        "
                        class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition flex items-center gap-2"
                    >
                        <PlusIcon class="w-5 h-5" />
                        Créer Manuellement
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div
                            v-if="statistiques.length === 0"
                            class="text-center py-12 text-gray-500"
                        >
                            Aucune statistique publiée. Générez-en une pour
                            commencer !
                        </div>

                        <div v-else class="grid grid-cols-1 gap-6">
                            <div
                                v-for="stat in statistiques"
                                :key="stat.id"
                                class="border rounded-xl p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 hover:shadow-md transition bg-gray-50"
                            >
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3 class="text-lg font-bold">
                                            {{ stat.titre }}
                                        </h3>
                                        <span
                                            :class="[
                                                'px-2 py-1 rounded-full text-xs font-bold uppercase tracking-widest',
                                                stat.is_published
                                                    ? 'bg-green-100 text-green-700'
                                                    : 'bg-yellow-100 text-yellow-700',
                                            ]"
                                        >
                                            {{
                                                stat.is_published
                                                    ? 'Publié'
                                                    : 'Brouillon'
                                            }}
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-500 mb-4">
                                        Créé le
                                        {{ formatDate(stat.created_at) }} •
                                        Ordre: {{ stat.ordre }}
                                    </div>

                                    <!-- Preview KPI avec icônes Heroicons -->
                                    <div class="flex gap-4 overflow-x-auto pb-2">
                                        <div
                                            v-for="(kpi, idx) in stat.data
                                                .kpis"
                                            :key="idx"
                                            class="bg-white p-3 rounded-lg border text-sm min-w-[120px] flex flex-col items-center"
                                        >
                                            <!-- ✅ Icône Heroicons -->
                                            <component
                                                :is="
                                                    iconMap[kpi.icon] ||
                                                    iconMap['clipboard']
                                                "
                                                class="w-8 h-8 text-indigo-600 mb-2"
                                            />
                                            <div
                                                class="font-bold text-indigo-600 text-lg"
                                            >
                                                {{ kpi.value }}
                                            </div>
                                            <div
                                                class="text-xs text-gray-500 truncate text-center"
                                            >
                                                {{ kpi.label }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center gap-3">
                                    <button
                                        @click="togglePublish(stat)"
                                        class="p-2 rounded-lg hover:bg-gray-200 transition"
                                        :title="
                                            stat.is_published
                                                ? 'Dépublier'
                                                : 'Publier'
                                        "
                                    >
                                        <EyeIcon
                                            v-if="stat.is_published"
                                            class="w-5 h-5 text-green-600"
                                        />
                                        <EyeSlashIcon
                                            v-else
                                            class="w-5 h-5 text-gray-400"
                                        />
                                    </button>
                                    <Link
                                        :href="
                                            route(
                                                'super-admin.statistiques-publiques.edit',
                                                stat.id,
                                            )
                                        "
                                        class="p-2 rounded-lg hover:bg-blue-100 text-blue-600 transition"
                                        title="Modifier"
                                    >
                                        <PencilIcon class="w-5 h-5" />
                                    </Link>
                                    <button
                                        @click="deleteStat(stat)"
                                        class="p-2 rounded-lg hover:bg-red-100 text-red-600 transition"
                                        title="Supprimer"
                                    >
                                        <TrashIcon class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>