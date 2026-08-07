<template>
    <div class="relative inline-block">
        <!-- Icône cloche -->
        <button
            @click="togglePanel"
            class="relative p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none transition"
            aria-label="Notifications"
        >
            <svg class="h-6 w-6 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <!-- Badge -->
            <span
                v-if="unreadCount > 0"
                class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-500 rounded-full"
            >
                {{ unreadCount }}
            </span>
        </button>

        <!-- Panneau déroulant -->
        <div
            v-if="isOpen"
            class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 z-50 max-h-96 overflow-y-auto"
        >
            <div class="p-3 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center sticky top-0 bg-white dark:bg-gray-800 z-10">
                <h3 class="font-semibold text-gray-800 dark:text-gray-200">Notifications</h3>
                <button
                    v-if="unreadCount > 0"
                    @click="markAllAsRead"
                    class="text-xs text-blue-600 dark:text-blue-400 hover:underline"
                >
                    Tout marquer comme lu
                </button>
            </div>

            <div v-if="notifications.length === 0" class="p-6 text-center text-gray-500 dark:text-gray-400 text-sm">
                Aucune notification pour le moment.
            </div>

            <div
                v-for="notif in notifications"
                :key="notif.id"
                @click="markAsRead(notif.id)"
                class="p-3 border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition"
                :class="!notif.read_at ? 'bg-blue-50 dark:bg-blue-900/20' : ''"
            >
                <div class="flex justify-between items-start gap-2">
                    <div class="flex-1">
                        <p class="text-sm text-gray-800 dark:text-gray-200" v-html="notif.data.message || 'Nouvelle notification'"></p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ notif.created_at }}</p>
                    </div>
                    <span v-if="!notif.read_at" class="inline-block w-2 h-2 bg-blue-500 rounded-full flex-shrink-0 mt-1.5"></span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

const page = usePage();
const isOpen = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);
let pollInterval = null;

// Récupérer les notifications
const fetchNotifications = async () => {
    try {
        const response = await axios.get(route('notifications.index'));
        notifications.value = response.data.notifications;
        unreadCount.value = response.data.unread_count;
    } catch (error) {
        console.error('Erreur chargement notifications', error);
    }
};

// Ouvrir / fermer le panneau
const togglePanel = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        fetchNotifications();
    }
};

// Marquer une notification comme lue
const markAsRead = async (id) => {
    try {
        await axios.patch(route('notifications.mark-read', id));
        const notif = notifications.value.find(n => n.id === id);
        if (notif) {
            notif.read_at = new Date().toISOString();
            unreadCount.value--;
        }
    } catch (error) {
        console.error('Erreur marquage', error);
    }
};

// Marquer toutes comme lues
const markAllAsRead = async () => {
    try {
        await axios.patch(route('notifications.mark-all-read'));
        notifications.value.forEach(n => n.read_at = new Date().toISOString());
        unreadCount.value = 0;
    } catch (error) {
        console.error('Erreur marquage toutes', error);
    }
};

// Gestion du cycle de vie
onMounted(() => {
    fetchNotifications();
    pollInterval = setInterval(fetchNotifications, 30000);

    const user = page.props.auth?.user;
    if (window.Echo && user) {
        const channel = window.Echo.private(`App.Models.Utilisateur.${user.id}`);

        // 1) Écoute des notifications Laravel (via notifiable->notify())
        channel.notification((notification) => {
            notifications.value.unshift({
                id: notification.id || Date.now(),
                data: notification.data || { message: notification.message || 'Nouvelle notification' },
                read_at: null,
                created_at: 'À l\'instant',
                type: notification.type || '',
            });
            unreadCount.value++;
        });

        // 2) Écoute de l'événement personnalisé NouvelleNotification (via broadcast())
        channel.listen('NouvelleNotification', (e) => {
            // e est l'objet retourné par broadcastWith()
            notifications.value.unshift({
                id: e.id || Date.now(),
                data: {
                    message: e.message || e.data?.message || 'Nouvelle notification',
                    titre: e.titre || e.data?.titre || 'Notification'
                },
                read_at: null,
                created_at: 'À l\'instant',
                type: 'NouvelleNotification',
            });
            unreadCount.value++;
        });
    }
});

onUnmounted(() => {
    if (pollInterval) {
        clearInterval(pollInterval);
    }
});
</script>