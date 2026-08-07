<template>
    <div class="flex flex-wrap items-center gap-2">
        <!-- Arrivée -->
        <button
            v-if="!hasArrive"
            @click="action('arrivee')"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition disabled:opacity-50 shadow-md inline-flex items-center"
            :disabled="processing"
        >
            <ArrowRightOnRectangleIcon class="w-5 h-5 mr-2" />
            Arrivée
        </button>

        <!-- Début pause -->
        <button
            v-else-if="hasArrive && !hasStartPause"
            @click="action('pause_debut')"
            class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition disabled:opacity-50 shadow-md inline-flex items-center"
            :disabled="processing"
        >
            <PauseIcon class="w-5 h-5 mr-2" />
            Pause
        </button>

        <!-- Fin pause -->
        <button
            v-else-if="hasStartPause && !hasEndPause"
            @click="action('pause_fin')"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50 shadow-md inline-flex items-center"
            :disabled="processing"
        >
            <PlayIcon class="w-5 h-5 mr-2" />
            Reprise
        </button>

        <!-- Sortie -->
        <button
            v-else-if="!hasSortie"
            @click="action('sortie')"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition disabled:opacity-50 shadow-md inline-flex items-center"
            :disabled="processing"
        >
            <ArrowLeftOnRectangleIcon class="w-5 h-5 mr-2" />
            Sortie
        </button>

        <!-- Journée terminée -->
        <span v-else-if="hasSortie" class="px-4 py-2 bg-gray-200 text-gray-600 rounded-lg font-bold inline-flex items-center">
            <CheckCircleIcon class="w-5 h-5 mr-2 text-green-600" />
            Journée terminée
        </span>

        <span v-if="statusMessage" class="text-sm font-bold ml-2" :class="statusClass">
            {{ statusMessage }}
        </span>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import axios from 'axios';
import Swal from 'sweetalert2';
import {
    ArrowRightOnRectangleIcon,
    PauseIcon,
    PlayIcon,
    ArrowLeftOnRectangleIcon,
    CheckCircleIcon,
} from '@heroicons/vue/24/outline';

const page = usePage();
const user = computed(() => page.props.auth?.user);

// On récupère le pointage du jour depuis les props ou on le laisse vide
const props = defineProps({
    pointage: {
        type: Object,
        default: null,
    },
});

const processing = ref(false);
const statusMessage = ref('');
const statusClass = ref('');

//  Gestion correcte du cas pointage === null (pas encore de pointage créé)
const hasArrive = computed(() => !!props.pointage?.heure_entree);
const hasStartPause = computed(() => !!props.pointage?.pause_debut);
const hasEndPause = computed(() => !!props.pointage?.pause_fin);
const hasSortie = computed(() => !!props.pointage?.heure_sortie);

const action = async (type) => {
    processing.value = true;
    statusMessage.value = '';
    statusClass.value = '';

    try {
        const response = await axios.post(route('pointages.badgeuse'), { action: type });
        Swal.fire({
            icon: 'success',
            title: response.data.message || 'Action effectuée',
            timer: 2000,
            showConfirmButton: false,
        });
        router.reload();
    } catch (error) {
        const msg = error.response?.data?.message || 'Erreur lors de l\'action.';
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: msg,
        });
        statusMessage.value = msg;
        statusClass.value = 'text-red-600';
    } finally {
        processing.value = false;
    }
};
</script>