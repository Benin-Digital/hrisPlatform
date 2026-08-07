<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const authUser = page.props.auth.user;

const isOpen = ref(false);
const contacts = ref([]);
const selectedContact = ref(null);
const messages = ref([]);
const newMessage = ref('');
const loading = ref(false);
const messageContainer = ref(null);

const fetchContacts = async () => {
    const res = await axios.get(route('messages.contacts'));
    contacts.value = res.data;
};

const selectContact = async (contact) => {
    selectedContact.value = contact;
    loading.value = true;
    const res = await axios.get(route('messages.index', contact.id));
    messages.value = res.data;
    loading.value = false;
    scrollToBottom();
    
    // Écouter le canal privé de l'utilisateur pour les nouveaux messages
    window.Echo.private(`user.${authUser.id}`)
        .listen('.message.sent', (e) => {
            if (e.message.utilisateur_id === selectedContact.value?.id) {
                messages.value.push(e.message);
                scrollToBottom();
            }
        });
};

const sendMessage = async () => {
    if (!newMessage.value.trim() || !selectedContact.value) return;

    const content = newMessage.value;
    newMessage.value = '';

    try {
        const res = await axios.post(route('messages.store'), {
            destinataire_id: selectedContact.value.id,
            contenu: content
        });
        messages.value.push(res.data);
        scrollToBottom();
    } catch (e) {
        console.error('Échec envoi message', e);
    }
};

const scrollToBottom = () => {
    nextTick(() => {
        if (messageContainer.value) {
            messageContainer.value.scrollTop = messageContainer.value.scrollHeight;
        }
    });
};

const toggleChat = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value && contacts.value.length === 0) {
        fetchContacts();
    }
};

onMounted(() => {
    // Écoute globale pour les notifications/badges si nécessaire
});

onUnmounted(() => {
    if (authUser) {
        window.Echo.leave(`user.${authUser.id}`);
    }
});
</script>

<template>
    <div class="fixed bottom-4 right-4 z-50">
        <!-- Bouton flottant -->
        <button
            @click="toggleChat"
            class="bg-indigo-600 text-white p-4 rounded-full shadow-2xl hover:bg-indigo-700 transition-all transform hover:scale-110 flex items-center justify-center"
        >
            <svg v-if="!isOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Fenêtre de Chat -->
        <div v-if="isOpen" class="absolute bottom-16 right-0 w-80 md:w-96 h-[500px] bg-white rounded-2xl shadow-2xl border flex flex-col overflow-hidden animate-in slide-in-from-bottom-4 duration-300">
            <!-- Header -->
            <div class="bg-indigo-600 p-4 text-white flex justify-between items-center">
                <h3 class="font-bold">Messagerie HRIS</h3>
                <button v-if="selectedContact" @click="selectedContact = null" class="text-xs bg-white/20 px-2 py-1 rounded hover:bg-white/30">
                    Retour aux contacts
                </button>
            </div>

            <!-- Liste des contacts -->
            <div v-if="!selectedContact" class="flex-1 overflow-y-auto">
                <div v-for="contact in contacts" :key="contact.id" 
                    @click="selectContact(contact)"
                    class="p-4 hover:bg-gray-50 cursor-pointer flex items-center border-b transition"
                >
                    <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold mr-3">
                        {{ contact.initials }}
                    </div>
                    <div>
                        <div class="font-medium text-gray-900">{{ contact.name }}</div>
                        <div class="text-xs text-gray-500 uppercase">{{ contact.type }}</div>
                    </div>
                </div>
            </div>

            <!-- Conversation -->
            <template v-else>
                <div class="p-2 bg-gray-50 border-b flex items-center">
                    <span class="text-sm font-medium text-gray-700 ml-2">Discussion avec {{ selectedContact.name }}</span>
                </div>
                
                <div ref="messageContainer" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50/50">
                    <div v-if="loading" class="text-center text-gray-400 mt-20 italic">Chargement...</div>
                    <div v-for="msg in messages" :key="msg.id" 
                        :class="['flex', msg.utilisateur_id === authUser.id ? 'justify-end' : 'justify-start']"
                    >
                        <div :class="[
                            'max-w-[75%] px-4 py-2 rounded-2xl text-sm shadow-sm',
                            msg.utilisateur_id === authUser.id ? 'bg-indigo-600 text-white rounded-tr-none' : 'bg-white text-gray-800 border rounded-tl-none'
                        ]">
                            {{ msg.contenu }}
                        </div>
                    </div>
                </div>

                <!-- Input -->
                <div class="p-4 border-t bg-white">
                    <form @submit.prevent="sendMessage" class="flex gap-2">
                        <input
                            v-model="newMessage"
                            type="text"
                            placeholder="Message..."
                            class="flex-1 border-gray-300 rounded-full px-4 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                        />
                        <button type="submit" class="bg-indigo-600 text-white p-2 rounded-full hover:bg-indigo-700 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </button>
                    </form>
                </div>
            </template>
        </div>
    </div>
</template>
