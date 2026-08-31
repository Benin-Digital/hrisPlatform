<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ClipboardIcon,
    CheckCircleIcon,
    FlagIcon,
    UsersIcon,
    DocumentTextIcon,
    AcademicCapIcon,
    CalendarDaysIcon,
    BriefcaseIcon,
    EyeIcon,
    ChartBarIcon,
    ExclamationCircleIcon,
    ClockIcon,
    UserGroupIcon,
    BookOpenIcon,
    FolderIcon,
    SparklesIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    statistique: Object
});

// Mapping des icônes
const iconMap = {
    'clipboard': ClipboardIcon,
    'check-circle': CheckCircleIcon,
    'target': FlagIcon,
    'users': UsersIcon,
    'document-text': DocumentTextIcon,
    'academic-cap': AcademicCapIcon,
    'calendar-days': CalendarDaysIcon,
    'briefcase': BriefcaseIcon,
    'eye': EyeIcon,
    'chart-bar': ChartBarIcon,
    'exclamation': ExclamationCircleIcon,
    'clock': ClockIcon,
    'user-group': UserGroupIcon,
    'book-open': BookOpenIcon,
    'folder': FolderIcon,
    'sparkles': SparklesIcon,
};

// Liste des icônes disponibles pour le select
const availableIcons = Object.keys(iconMap).sort();

const form = useForm({
    titre: props.statistique?.titre || '',
    is_published: props.statistique?.is_published || false,
    ordre: props.statistique?.ordre || 0,
    kpis: props.statistique?.data?.kpis || [
        { label: 'Total Tâches', value: '0', icon: 'clipboard', color: 'indigo' },
        { label: 'Taux de Complétion', value: '0%', icon: 'check-circle', color: 'green' },
        { label: 'Tâches Terminées', value: '0', icon: 'target', color: 'emerald' },
        { label: 'Utilisateurs Actifs', value: '0', icon: 'users', color: 'blue' }
    ]
});

const submit = () => {
    const payload = {
        titre: form.titre,
        is_published: form.is_published,
        ordre: form.ordre,
        data: {
            kpis: form.kpis,
            updated_at: new Date().toISOString()
        }
    };

    if (props.statistique) {
        form.transform(() => payload).put(route('super-admin.statistiques-publiques.update', props.statistique.id));
    } else {
        form.transform(() => payload).post(route('super-admin.statistiques-publiques.store'));
    }
};

const addKpi = () => {
    form.kpis.push({ label: 'Nouveau KPI', value: '0', icon: 'clipboard', color: 'indigo' });
};

const removeKpi = (index) => {
    form.kpis.splice(index, 1);
};
</script>

<template>
    <Head :title="statistique ? 'Modifier Statistique' : 'Nouvelle Statistique'" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ statistique ? 'Modifier Statistique' : 'Nouvelle Statistique' }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Titre -->
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Titre</label>
                                    <input v-model="form.titre" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                                    <p class="text-xs text-red-500 mt-1" v-if="form.errors.titre">{{ form.errors.titre }}</p>
                                </div>

                                <!-- Ordre -->
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Ordre d'affichage</label>
                                    <input v-model="form.ordre" type="number" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                </div>
                            </div>

                            <!-- Publication -->
                            <div class="flex items-center">
                                <input v-model="form.is_published" type="checkbox" id="publish" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <label for="publish" class="ml-2 block text-sm text-gray-900">Publier immédiatement sur la page d'accueil</label>
                            </div>

                            <hr class="my-6 border-gray-200">

                            <!-- KPIs Editor -->
                            <div>
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">Indicateurs (KPIs)</h3>
                                    <button type="button" @click="addKpi" class="text-sm text-indigo-600 hover:text-indigo-900 font-bold">+ Ajouter un indicateur</button>
                                </div>

                                <div class="space-y-4">
                                    <div v-for="(kpi, index) in form.kpis" :key="index" class="flex gap-4 items-start p-4 bg-gray-50 rounded-lg border border-gray-200">
                                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 flex-1">
                                            <div>
                                                <label class="block text-xs font-bold text-gray-500 uppercase">Label</label>
                                                <input v-model="kpi.label" type="text" class="w-full text-sm border-gray-300 rounded-md" placeholder="Ex: Total Tâches" />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-gray-500 uppercase">Valeur</label>
                                                <input v-model="kpi.value" type="text" class="w-full text-sm border-gray-300 rounded-md font-bold" placeholder="Ex: 150" />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-gray-500 uppercase">Icône</label>
                                                <select v-model="kpi.icon" class="w-full text-sm border-gray-300 rounded-md">
                                                    <option v-for="iconName in availableIcons" :key="iconName" :value="iconName">
                                                        {{ iconName }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-gray-500 uppercase">Couleur</label>
                                                <select v-model="kpi.color" class="w-full text-sm border-gray-300 rounded-md">
                                                    <option value="indigo">Indigo</option>
                                                    <option value="green">Vert</option>
                                                    <option value="blue">Bleu</option>
                                                    <option value="emerald">Émeraude</option>
                                                    <option value="purple">Violet</option>
                                                    <option value="pink">Rose</option>
                                                    <option value="red">Rouge</option>
                                                    <option value="orange">Orange</option>
                                                    <option value="gray">Gris</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-gray-500 uppercase">Aperçu</label>
                                                <div class="flex items-center h-10">
                                                    <component 
                                                        :is="iconMap[kpi.icon] || ClipboardIcon" 
                                                        class="w-8 h-8 text-indigo-600"
                                                    />
                                                    <span class="ml-2 text-sm font-bold text-gray-700">{{ kpi.value }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" @click="removeKpi(index)" class="text-red-500 hover:text-red-700 mt-6 p-1 rounded-full hover:bg-red-50">
                                            ✖
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end gap-4 pt-4 border-t border-gray-100">
                                <Link :href="route('super-admin.statistiques-publiques.index')" class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition">
                                    Annuler
                                </Link>
                                <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50">
                                    {{ statistique ? 'Mettre à jour' : 'Enregistrer' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>