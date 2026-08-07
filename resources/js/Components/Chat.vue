<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
    entiteId: {
        type: Number,
        required: true
    }
});

// Exemple de messages statiques (ou vide)
const messages = ref([
    { id: 1, user: 'Alice', text: 'Bonjour tout le monde !', time: '10:30' },
    { id: 2, user: 'Bob', text: 'Salut Alice !', time: '10:32' },
    { id: 3, user: 'Vous', text: 'Tout va bien ici ', time: '10:35' },
]);

const newMessage = ref('');

const sendMessage = () => {
    if (newMessage.value.trim()) {
        messages.value.push({
            id: messages.value.length + 1,
            user: 'Vous',
            text: newMessage.value,
            time: new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })
        });
        newMessage.value = '';
    }
};

// Plus aucune référence à Echo → plus d'erreur
onMounted(() => {
    console.log('Chat affiché pour l\'entité :', props.entiteId);
    // Ici vous pourriez charger des messages via une requête Inertia/Axios plus tard
});
</script>
<template>
    <div class="bg-white rounded-lg shadow h-full flex flex-col">
        <div class="p-4 border-b">
            <h3 class="font-semibold text-lg">Chat de l'entité</h3>
            <p class="text-sm text-gray-500">Discussion interne</p>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-3">
            <div v-for="message in messages" :key="message.id" class="flex flex-col">
                <div class="text-xs text-gray-500 mb-1">{{ message.user }} • {{ message.time }}</div>
                <div class="bg-gray-100 rounded-lg px-4 py-2 max-w-xs">
                    {{ message.text }}
                </div>
            </div>
        </div>

        <div class="p-4 border-t">
            <form @submit.prevent="sendMessage" class="flex gap-2">
                <input
                    v-model="newMessage"
                    type="text"
                    placeholder="Écrivez un message..."
                    class="flex-1 border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
                <button
                    type="submit"
                    class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700"
                >
                    Envoyer
                </button>
            </form>
        </div>
    </div>
</template>