<template>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
        <FullCalendar ref="calendarRef" :options="calendarOptions" />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';
import axios from 'axios';

const calendarRef = ref(null);

const calendarOptions = {
    plugins: [dayGridPlugin, interactionPlugin, timeGridPlugin],
    initialView: 'dayGridMonth',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
    locale: 'fr',
    buttonText: {
        today: 'Aujourd\'hui',
        month: 'Mois',
        week: 'Semaine',
        day: 'Jour'
    },
    events: async (fetchInfo, successCallback, failureCallback) => {
        try {
            const response = await axios.get('/calendrier/evenements');
            successCallback(response.data);
        } catch (error) {
            failureCallback(error);
        }
    },
    eventClick: (info) => {
        alert(`Titre: ${info.event.title}\nType: ${info.event.extendedProps.type || 'Non spécifié'}`);
    }
};

onMounted(() => {
    setTimeout(() => {
        if (calendarRef.value) {
            calendarRef.value.getApi().refetchEvents();
        }
    }, 500);
});
</script>