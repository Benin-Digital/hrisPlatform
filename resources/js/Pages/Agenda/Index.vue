<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';
import {
    CalendarIcon,
    FireIcon,
    ClockIcon,
    PlusIcon,
    MapPinIcon,
    CalendarDaysIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    evenements: Array,
});

const calendarOptions = ref({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin, listPlugin],
    initialView: 'dayGridMonth',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,listWeek'
    },
    events: props.evenements,
    editable: false,
    selectable: false,
    locale: 'fr',
    buttonText: {
        today: "Aujourd'hui",
        month: 'Mois',
        week: 'Semaine',
        day: 'Jour',
        list: 'Liste'
    },
    height: 'auto',
    eventClick: (info) => {
        window.location.href = `/agenda/${info.event.id}`;
    },
    eventDidMount: (info) => {
        if (info.event.extendedProps.est_epingle) {
            info.el.classList.add('fc-event-epingle');
        }
    },
});

const evenementsEpingle = computed(() => {
    return props.evenements
        .filter(e => e.extendedProps.est_epingle)
        .sort((a, b) => new Date(b.start) - new Date(a.start));
});

const evenementsProchains = computed(() => {
    const now = new Date();
    const in7Days = new Date(now);
    in7Days.setDate(now.getDate() + 7);

    return props.evenements
        .filter(e => !e.extendedProps.est_epingle && new Date(e.start) >= now && new Date(e.start) <= in7Days)
        .sort((a, b) => new Date(a.start) - new Date(b.start))
        .slice(0, 8);
});

const page = usePage();

const canCreate = computed(() => {
    const roles = page.props.auth.user.roles.map(r => r.nom);
    return !roles.includes('collaborateur') && !roles.includes('invite');
});

const formatDateShort = (dateStr) => {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'short',
        hour: 'numeric',
        minute: '2-digit'
    });
};

const route = (name, params = {}) => {
    return window.route ? window.route(name, params) : `/${name}`;
};
</script>

<template>
    <Head title="Agenda & Événements" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl text-gray-800 leading-tight">
                Agenda & Événements
            </h2>
        </template>

        <div class="py-6 md:py-10">
            <div class="page-container">
                <!-- En-tête Section -->
                <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center">
                        <CalendarIcon class="w-8 h-8 text-indigo-600 mr-3" />
                        <div>
                            <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900">Calendrier partagé</h1>
                            <p class="text-sm text-gray-500 mt-1">Réunions, formations et événements importants de l'entreprise.</p>
                        </div>
                    </div>
                    
                    <Link
                        v-if="canCreate"
                        :href="route('agenda.create')"
                        class="btn btn-primary self-start md:self-auto inline-flex items-center"
                    >
                        <PlusIcon class="w-5 h-5 mr-2" />
                        Nouvel événement
                    </Link>
                </div>

                <!-- Main Grid -->
                <div class="grid grid-cols-1 xl:grid-cols-4 gap-8">
                    <!-- Calendrier (Majorité de l'espace sur desktop) -->
                    <div class="xl:col-span-3">
                        <div class="card p-4 md:p-6 border-0 shadow-sm overflow-hidden">
                            <FullCalendar :options="calendarOptions" />
                        </div>
                    </div>

                    <!-- Sidebar: Événements prioritaires & À venir -->
                    <div class="space-y-8">
                        <!-- Prioritaires -->
                        <div>
                            <h3 class="text-xs font-black text-warning-600 uppercase tracking-widest mb-4 flex items-center">
                                <FireIcon class="w-4 h-4 text-orange-500 mr-2" />
                                Prioritaires
                            </h3>
                            <div v-if="evenementsEpingle.length === 0" class="p-6 bg-gray-50 rounded-2xl border border-dashed border-gray-200 text-center">
                                <p class="text-xs text-gray-400 font-bold uppercase">Aucune urgence</p>
                            </div>
                            <div v-else class="space-y-4">
                                <Link
                                    v-for="event in evenementsEpingle"
                                    :key="event.id"
                                    :href="`/agenda/${event.id}`"
                                    class="block p-4 bg-white rounded-2xl border-l-4 border-warning-500 shadow-sm hover:shadow-md transition-all group"
                                >
                                    <h4 class="text-sm font-extrabold text-gray-900 line-clamp-2 group-hover:text-primary-600 transition-colors">{{ event.title }}</h4>
                                    <div class="mt-2 flex items-center text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                        <CalendarDaysIcon class="w-3 h-3 mr-1.5" />
                                        {{ formatDateShort(event.start) }}
                                    </div>
                                    <div v-if="event.extendedProps.lieu" class="mt-1 flex items-center text-[10px] text-gray-500 font-bold truncate">
                                        <MapPinIcon class="w-3 h-3 mr-1.5 flex-shrink-0" />
                                        {{ event.extendedProps.lieu }}
                                    </div>
                                </Link>
                            </div>
                        </div>

                        <!-- À venir -->
                        <div>
                            <h3 class="text-xs font-black text-primary-600 uppercase tracking-widest mb-4 flex items-center">
                                <ClockIcon class="w-4 h-4 text-primary-500 mr-2" />
                                Prochains jours
                            </h3>
                            <div v-if="evenementsProchains.length === 0" class="p-6 bg-gray-50 rounded-2xl border border-dashed border-gray-200 text-center">
                                <p class="text-xs text-gray-400 font-bold uppercase">Agenda libre</p>
                            </div>
                            <div v-else class="space-y-3">
                                <Link
                                    v-for="event in evenementsProchains"
                                    :key="event.id"
                                    :href="`/agenda/${event.id}`"
                                    class="flex items-center gap-4 p-3 bg-white rounded-xl border border-gray-50 hover:border-primary-100 hover:bg-primary-50/30 transition-all group"
                                >
                                    <div class="w-10 h-10 rounded-lg bg-primary-50 text-primary-600 flex flex-col items-center justify-center flex-shrink-0 font-black">
                                      <span class="text-xs">{{ new Date(event.start).getDate() }}</span>
                                      <span class="text-[8px] uppercase">{{ new Date(event.start).toLocaleDateString('fr-FR', { month: 'short' }) }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="text-xs font-bold text-gray-800 truncate group-hover:text-primary-700">{{ event.title }}</h4>
                                        <p class="text-[10px] text-gray-400 font-medium">{{ new Date(event.start).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }) }}</p>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
/* Style global pour le calendrier pour matcher notre design */
.fc .fc-toolbar-title {
    font-size: 1.25rem !important;
    font-weight: 800 !important;
    color: #111827 !important;
}
.fc .fc-button-primary {
    background: #f9fafb !important;
    border: 1px solid #e5e7eb !important;
    color: #374151 !important;
    font-weight: 700 !important;
    text-transform: capitalize !important;
    border-radius: 0.75rem !important;
    padding: 0.5rem 1rem !important;
    font-size: 0.875rem !important;
    box-shadow: none !important;
}
.fc .fc-button-primary:hover {
    background: #f3f4f6 !important;
    border-color: #d1d5db !important;
}
.fc .fc-button-active {
    background: #4f46e5 !important;
    color: white !important;
    border-color: #4f46e5 !important;
}
.fc-theme-standard td, .fc-theme-standard th {
    border-color: #f3f4f6 !important;
}
.fc .fc-col-header-cell-cushion {
    font-size: 0.75rem !important;
    text-transform: uppercase !important;
    letter-spacing: 0.05em !important;
    font-weight: 800 !important;
    color: #9ca3af !important;
    padding: 1rem 0 !important;
}
.fc-daygrid-day-number {
    font-size: 0.875rem !important;
    font-weight: 600 !important;
    color: #4b5563 !important;
}
.fc-event {
    border-radius: 0.5rem !important;
    padding: 2px 4px !important;
    font-size: 0.75rem !important;
    font-weight: 600 !important;
    border: none !important;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
}
.fc-v-event {
    background-color: #e0e7ff !important;
    border-left: 3px solid #4f46e5 !important;
    color: #4338ca !important;
}
.fc-event-epingle {
    background-color: #fff7ed !important;
    border-left: 3px solid #f97316 !important;
    color: #9a3412 !important;
}
</style>