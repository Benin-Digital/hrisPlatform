<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, nextTick, computed } from 'vue';
import axios from 'axios';
import TaskAnalytics from '@/Components/Collaboration/TaskAnalytics.vue';

const props = defineProps({
    espace: {
        type: Object,
        required: true
    },
    isMembre: Boolean,
    isAdmin: Boolean,
    usersToInvite: Array,
});

const page = usePage();
const authUser = computed(() => page.props.auth?.user);

const getInitials = (name) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
};

const currentTab = ref('discussion'); // 'discussion', 'documents', 'tasks', 'analyse'

// --- CHAT LOGIC ---
const messages = ref([]);
const messageContainer = ref(null);
const newMessage = ref('');
const loadingMessages = ref(false);

const scrollToBottom = () => {
    nextTick(() => {
        if (messageContainer.value) {
            messageContainer.value.scrollTop = messageContainer.value.scrollHeight;
        }
    });
};

const fetchMessages = async () => {
    loadingMessages.value = true;
    try {
        const res = await axios.get(route('messages.index', { id: 0 }), {
            params: { espace_id: props.espace.id }
        });
        messages.value = res.data;
        scrollToBottom();
    } catch (e) {
        console.error('Erreur chargement messages', e);
    } finally {
        loadingMessages.value = false;
    }
};

const sendMessage = async () => {
    if (!newMessage.value.trim()) return;
    const content = newMessage.value;
    newMessage.value = '';

    try {
        const res = await axios.post(route('messages.store'), {
            espace_id: props.espace.id,
            contenu: content
        });
        messages.value.push(res.data);
        scrollToBottom();
    } catch (e) {
        console.error('Erreur envoi message', e);
    }
};

onMounted(() => {
    if (props.isMembre) {
        fetchMessages();
        
        console.log('Tentative de connexion au canal :', `espace.${props.espace.id}`);
        
        window.Echo.private(`espace.${props.espace.id}`)
            .listen('.message.sent', (e) => {
                console.log('Message reçu via Echo :', e);
                if (e.message.utilisateur_id !== authUser.value?.id) {
                    messages.value.push(e.message);
                    scrollToBottom();
                }
            })
            .error((error) => {
                console.error('Erreur Echo sur le canal privé :', error);
            });
            
        // Log global connection state
        window.Pusher.instances.forEach(instance => {
            instance.connection.bind('state_change', (states) => {
                console.log('État de la connexion Reverb :', states.current);
            });
        });
    }
});

onUnmounted(() => {
    window.Echo.leave(`espace.${props.espace.id}`);
});
// ------------------

const showInviteModal = ref(false);
const inviteForm = useForm({
    utilisateur_id: '',
    role: 'membre',
});

// Note: On aurait normalement besoin d'une liste d'utilisateurs à inviter (passée en props)
// Pour cette phase, on va simuler ou simplifier si la liste n'est pas dispo.
const usersToInviteLocal = computed(() => props.usersToInvite);

const submitInvite = () => {
    inviteForm.post(`/collaboration/${props.espace.uuid}/inviter`, {
        onSuccess: () => {
            showInviteModal.value = false;
            inviteForm.reset();
        },
    });
};

// --- DOCUMENTS LOGIC ---
const showDocModal = ref(false);
const docForm = useForm({
    titre: '',
    documents: [],
    espace_id: props.espace.id,
});

const submitDoc = () => {
    docForm.post(route('documents.upload'), {
        onSuccess: () => {
            showDocModal.value = false;
            docForm.reset();
        },
    });
};

// --- TASKS LOGIC ---
const showTaskModal = ref(false);
const taskForm = useForm({
    titre: '',
    description: '',
    priorite: 'moyenne',
    statut: 'en_attente',
    assigne_a: '',
    date_echeance: '',
    espace_id: props.espace.id,
});

const submitTask = () => {
    taskForm.post(route('taches.store'), {
        onSuccess: () => {
            showTaskModal.value = false;
            taskForm.reset();
        },
    });
};
</script>

<template>
    <Head :title="espace.nom" />

    <div class="min-h-screen bg-gray-50 pb-12">
        <!-- Banner/Header Section -->
        <div class="bg-indigo-700 h-64 relative overflow-hidden">
            <div class="absolute inset-0 opacity-20">
                <div class="absolute -left-10 -top-10 w-64 h-64 bg-white rounded-full mix-blend-overlay blur-3xl"></div>
                <div class="absolute right-20 bottom-10 w-96 h-96 bg-purple-500 rounded-full mix-blend-overlay blur-3xl"></div>
            </div>
            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex flex-col justify-end pb-10 relative z-10">
                <div class="flex justify-between items-start mb-6">
                    <nav class="flex text-sm text-indigo-200" aria-label="Breadcrumb">
                        <ol class="flex items-center space-x-2">
                            <li>
                                <Link href="/collaboration" class="hover:text-white transition-colors">Collaboration</Link>
                            </li>
                            <li class="flex items-center">
                                <svg class="h-4 w-4 text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span class="ml-2 text-white font-medium">{{ espace.nom }}</span>
                            </li>
                        </ol>
                    </nav>
                    <Link
                        href="/collaboration"
                        class="px-4 py-2 bg-white/10 backdrop-blur-md border border-white/20 text-white rounded-xl text-xs font-bold hover:bg-white/20 transition"
                    >
                        ← Tous les espaces
                    </Link>
                </div>

                <div class="flex items-center gap-6">
                    <div class="w-24 h-24 bg-white rounded-3xl shadow-2xl flex items-center justify-center text-indigo-700 font-extrabold text-3xl border-4 border-indigo-500/50">
                        {{ getInitials(espace.nom) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-3xl font-black text-white tracking-tight">{{ espace.nom }}</h1>
                            <span v-if="espace.est_prive" class="px-3 py-1 bg-white/10 backdrop-blur-md border border-white/20 text-white text-xs font-bold rounded-full">Privé</span>
                            <span v-else class="px-3 py-1 bg-green-400/20 backdrop-blur-md border border-green-400/30 text-green-100 text-xs font-bold rounded-full">Public</span>
                        </div>
                        <p class="mt-2 text-indigo-100 max-w-2xl">{{ espace.description || 'Bienvenue dans votre nouvel espace de collaboration.' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left Column: Tabs/Features (Placeholder for Phase 2/3) -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Tab Navigation Header -->
                    <div class="flex gap-4 p-2 bg-indigo-50/50 rounded-2xl w-full lg:w-fit overflow-x-auto whitespace-nowrap scrollbar-hide">
                        <button 
                            @click="currentTab = 'discussion'"
                            :class="[
                                'px-6 py-3 rounded-xl font-bold text-sm transition-all',
                                currentTab === 'discussion' ? 'bg-indigo-600 text-white shadow-lg' : 'text-indigo-600 hover:bg-white'
                            ]"
                        >
                            Discussion
                        </button>
                        <button 
                            @click="currentTab = 'documents'"
                            :class="[
                                'px-6 py-3 rounded-xl font-bold text-sm transition-all',
                                currentTab === 'documents' ? 'bg-indigo-600 text-white shadow-lg' : 'text-indigo-600 hover:bg-white'
                            ]"
                        >
                            Documents ({{ espace.documents.length }})
                        </button>
                        <button 
                            @click="currentTab = 'tasks'"
                            :class="[
                                'px-6 py-3 rounded-xl font-bold text-sm transition-all',
                                currentTab === 'tasks' ? 'bg-indigo-600 text-white shadow-lg' : 'text-indigo-600 hover:bg-white'
                            ]"
                        >
                            Tâches ({{ espace.taches.length }})
                        </button>
                        <button 
                            v-if="isAdmin"
                            @click="currentTab = 'analyse'"
                            :class="[
                                'px-6 py-3 rounded-xl font-bold text-sm transition-all',
                                currentTab === 'analyse' ? 'bg-indigo-600 text-white shadow-lg' : 'text-indigo-600 hover:bg-white'
                            ]"
                        >
                            Analyse 📈
                        </button>
                    </div>

                    <!-- Discussion Tab -->
                    <div v-if="currentTab === 'discussion'" class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col h-[500px]">
                        <div class="px-8 py-6 border-b border-gray-50 flex items-center justify-between bg-white z-10">
                            <h2 class="text-xl font-bold text-gray-900">Fil de l'Espace</h2>
                            <span class="px-3 py-1 bg-green-50 text-green-600 text-[10px] font-black uppercase rounded-lg">En direct</span>
                        </div>
                        
                        <!-- Message Central Scrollable -->
                        <div ref="messageContainer" class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50/30">
                            <div v-if="loadingMessages" class="text-center py-10 text-gray-400">
                                Chargement des échanges...
                            </div>
                            <div v-else-if="messages.length === 0" class="text-center py-20">
                                <p class="text-gray-400 italic">Soyez le premier à poster un message ici !</p>
                            </div>
                            
                            <div v-for="msg in messages" :key="msg.id" 
                                :class="['flex flex-col', msg.utilisateur_id === authUser?.id ? 'items-end' : 'items-start']"
                            >
                                <div class="flex items-center gap-2 mb-1">
                                    <span v-if="msg.utilisateur_id !== authUser?.id" class="text-[10px] font-bold text-gray-500">
                                        {{ msg.auteur.prenom }} {{ msg.auteur.nom }}
                                    </span>
                                </div>
                                <div :class="[
                                    'max-w-[85%] px-5 py-3 rounded-2xl text-sm shadow-sm transition-all',
                                    msg.utilisateur_id === authUser?.id 
                                        ? 'bg-indigo-600 text-white rounded-tr-none' 
                                        : 'bg-white text-gray-800 border-gray-100 border rounded-tl-none'
                                ]">
                                    {{ msg.contenu }}
                                </div>
                                <span class="text-[8px] text-gray-400 mt-1 uppercase font-bold tracking-tighter">
                                    {{ new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}
                                </span>
                            </div>
                        </div>

                        <!-- Chat Input -->
                        <div v-if="isMembre" class="p-4 border-t border-gray-100 bg-white">
                            <form @submit.prevent="sendMessage" class="flex gap-3">
                                <input
                                    v-model="newMessage"
                                    type="text"
                                    placeholder="Partagez un message avec l'espace..."
                                    class="flex-1 bg-gray-50 border-transparent rounded-2xl px-6 py-4 text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                />
                                <button 
                                    type="submit" 
                                    class="bg-indigo-600 text-white p-4 rounded-2xl hover:bg-indigo-700 transition-all shadow-md group active:scale-95"
                                    :disabled="!newMessage.trim()"
                                >
                                    <svg class="w-5 h-5 transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                        <div v-else class="p-6 bg-amber-50 text-amber-700 text-center text-sm font-medium border-t border-amber-100">
                            Rejoignez cet espace pour participer à la discussion.
                        </div>
                    </div>

                    <div v-if="currentTab === 'documents'" class="bg-white rounded-3xl shadow-sm border border-gray-100 min-h-[400px]">
                        <div class="px-8 py-6 border-b border-gray-50 flex items-center justify-between">
                            <h2 class="text-xl font-bold text-gray-900">Documents Partagés</h2>
                            <button 
                                @click="showDocModal = true"
                                class="flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-600 rounded-xl font-bold text-sm hover:bg-indigo-100 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Ajouter
                            </button>
                        </div>
                        
                        <div v-if="espace.documents.length === 0" class="p-20 text-center">
                            <div class="w-16 h-16 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h3 class="text-gray-900 font-bold">Aucun document</h3>
                            <p class="text-gray-400 text-sm">Partagez des fichiers avec l'équipe pour les retrouver ici.</p>
                        </div>

                        <div v-else class="divide-y divide-gray-50">
                            <div v-for="doc in espace.documents" :key="doc.id" class="p-4 hover:bg-gray-50 flex items-center gap-4 group transition-colors">
                                <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="flex-grow">
                                    <h4 class="text-sm font-bold text-gray-900">{{ doc.titre }}</h4>
                                    <p class="text-xs text-gray-400">{{ (doc.taille_octets / 1024 / 1024).toFixed(2) }} MB • {{ doc.extension.toUpperCase() }}</p>
                                </div>
                                <a :href="route('documents.download', doc.uuid)" class="p-2 text-gray-400 hover:text-indigo-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Tasks Tab -->
                    <div v-if="currentTab === 'tasks'" class="bg-white rounded-3xl shadow-sm border border-gray-100 min-h-[400px]">
                        <div class="px-8 py-6 border-b border-gray-50 flex items-center justify-between">
                            <h2 class="text-xl font-bold text-gray-900">Tâches de l'Espace</h2>
                            <button 
                                @click="showTaskModal = true"
                                class="flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-600 rounded-xl font-bold text-sm hover:bg-indigo-100 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Créer
                            </button>
                        </div>

                        <div v-if="espace.taches.length === 0" class="p-20 text-center">
                            <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="text-gray-900 font-bold">Zéro tâche</h3>
                            <p class="text-gray-400 text-sm">Organisez le travail de l'espace en créant des tâches.</p>
                        </div>

                        <div v-else class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="task in espace.taches" :key="task.id" class="p-5 border border-gray-100 rounded-2xl hover:shadow-md transition-all group">
                                <div class="flex justify-between items-start mb-3">
                                    <span :class="[
                                        'px-2 py-1 text-[8px] font-black uppercase rounded-md',
                                        task.priorite === 'haute' ? 'bg-red-50 text-red-600' : 
                                        task.priorite === 'moyenne' ? 'bg-amber-50 text-amber-600' : 'bg-green-50 text-green-600'
                                    ]">
                                        {{ task.priorite }}
                                    </span>
                                    <span class="text-[10px] font-bold text-gray-400">{{ task.statut.replace('_', ' ') }}</span>
                                </div>
                                <h4 class="text-sm font-bold text-gray-900 mb-2 truncate">{{ task.titre }}</h4>
                                <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-50">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 bg-indigo-100 rounded-full flex items-center justify-center text-[8px] font-bold text-indigo-600">
                                            {{ task.assigne ? getInitials(task.assigne.prenom + ' ' + task.assigne.nom) : '?' }}
                                        </div>
                                        <span class="text-[10px] text-gray-600">{{ task.assigne ? task.assigne.prenom : 'Non assignée' }}</span>
                                    </div>
                                    <span class="text-[10px] text-gray-400">{{ new Date(task.date_echeance).toLocaleDateString() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Analyse Tab -->
                    <div v-if="currentTab === 'analyse'" class="animate-in fade-in duration-300">
                        <TaskAnalytics :espace-id="espace.id" />
                    </div>
                </div>

                <!-- Right Column: Sidebar / Members -->
                <div class="space-y-8">
                    <!-- Members Card -->
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between">
                            <h2 class="font-bold text-gray-900">Membres ({{ espace.membres.length }})</h2>
                            <button 
                                v-if="isAdmin"
                                @click="showInviteModal = true"
                                class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-xl transition-colors"
                                title="Inviter un membre"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                            </button>
                        </div>
                        <div class="p-4 space-y-2">
                            <div 
                                v-for="membre in espace.membres" 
                                :key="membre.id"
                                class="flex items-center gap-3 p-3 hover:bg-gray-50 rounded-2xl transition-colors group"
                            >
                                <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600 font-bold text-xs">
                                    {{ getInitials(membre.prenom + ' ' + membre.nom) }}
                                </div>
                                <div class="flex-grow">
                                    <div class="text-sm font-bold text-gray-900 line-clamp-1">{{ membre.prenom }} {{ membre.nom }}</div>
                                    <div class="text-[10px] text-gray-400 uppercase tracking-wider font-bold">{{ membre.pivot.role }}</div>
                                </div>
                                <div v-if="membre.id === espace.createur_id" title="Créateur">
                                    <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Space Settings / Info -->
                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                        <h2 class="font-bold text-gray-900 mb-4">Informations</h2>
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-[10px] text-gray-400 uppercase font-black tracking-widest">Créé par</dt>
                                <dd class="text-sm font-bold text-gray-700">{{ espace.createur.prenom }} {{ espace.createur.nom }}</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] text-gray-400 uppercase font-black tracking-widest">Date de création</dt>
                                <dd class="text-sm font-bold text-gray-700">{{ new Date(espace.created_at).toLocaleDateString() }}</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] text-gray-400 uppercase font-black tracking-widest">Type</dt>
                                <dd class="text-sm font-bold text-gray-700 capitalize">{{ espace.est_prive ? 'Privé (Invités uniquement)' : 'Public (Ouvert à l\'entité)' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Task Modal -->
        <div v-if="showTaskModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showTaskModal = false"></div>
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg relative z-10 overflow-hidden animate-in zoom-in-95 duration-200">
                <div class="bg-indigo-600 px-8 py-6 text-white flex justify-between items-center">
                    <h3 class="text-xl font-bold">Nouvelle Tâche</h3>
                    <button @click="showTaskModal = false" class="hover:bg-white/20 p-2 rounded-full transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <form @submit.prevent="submitTask" class="p-8 space-y-6">
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Titre de la tâche</label>
                        <input v-model="taskForm.titre" type="text" class="w-full bg-gray-50 border-transparent rounded-2xl px-6 py-4 focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-all" required placeholder="Ex: Finaliser le rapport annuel" />
                    </div>
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Assigner à</label>
                        <select v-model="taskForm.assigne_a" class="w-full bg-gray-50 border-transparent rounded-2xl px-6 py-4 focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-all">
                            <option value="">Non assignée</option>
                            <option v-for="membre in espace.membres" :key="membre.id" :value="membre.id">
                                {{ membre.prenom }} {{ membre.nom }}
                            </option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Priorité</label>
                            <select v-model="taskForm.priorite" class="w-full bg-gray-50 border-transparent rounded-2xl px-6 py-4 focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-all">
                                <option value="basse">Basse</option>
                                <option value="moyenne">Moyenne</option>
                                <option value="haute">Haute</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Échéance</label>
                            <input v-model="taskForm.date_echeance" type="date" class="w-full bg-gray-50 border-transparent rounded-2xl px-6 py-4 focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-all" required />
                        </div>
                    </div>
                    <button type="submit" :disabled="taskForm.processing" class="w-full bg-indigo-600 text-white font-bold py-4 rounded-2xl hover:bg-indigo-700 shadow-lg shadow-indigo-600/30 transition-all active:scale-95 disabled:opacity-50">
                        {{ taskForm.processing ? 'Création...' : 'Créer la tâche' }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Upload Document Modal -->
        <div v-if="showDocModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showDocModal = false"></div>
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg relative z-10 overflow-hidden animate-in zoom-in-95 duration-200">
                <div class="bg-amber-500 px-8 py-6 text-white flex justify-between items-center">
                    <h3 class="text-xl font-bold">Ajouter un Document</h3>
                    <button @click="showDocModal = false" class="hover:bg-white/20 p-2 rounded-full transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <form @submit.prevent="submitDoc" class="p-8 space-y-6">
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Titre du document</label>
                        <input v-model="docForm.titre" type="text" class="w-full bg-gray-50 border-transparent rounded-2xl px-6 py-4 focus:bg-white focus:ring-2 focus:ring-amber-500 transition-all" required placeholder="Ex: Guide de bienvenue.pdf" />
                    </div>
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Fichiers</label>
                        <input @input="docForm.documents = $event.target.files" type="file" multiple class="w-full bg-gray-50 border-transparent rounded-2xl px-6 py-4 focus:bg-white focus:ring-2 focus:ring-amber-500 transition-all file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100" />
                    </div>
                    <button type="submit" :disabled="docForm.processing" class="w-full bg-amber-500 text-white font-bold py-4 rounded-2xl hover:bg-amber-600 shadow-lg shadow-amber-500/30 transition-all active:scale-95 disabled:opacity-50">
                        {{ docForm.processing ? 'Upload...' : 'Uploader le document' }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Invite Member Modal (Existing) -->
        <div v-if="showInviteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showInviteModal = false"></div>
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg relative z-10 overflow-hidden animate-in zoom-in-95 duration-200">
                <div class="bg-indigo-600 px-8 py-6 text-white flex justify-between items-center">
                    <h3 class="text-xl font-bold">Inviter un Collaborateur</h3>
                    <button @click="showInviteModal = false" class="hover:bg-white/20 p-2 rounded-full transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <form @submit.prevent="submitInvite" class="p-8 space-y-6">
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Choisir un membre</label>
                        <select v-model="inviteForm.utilisateur_id" class="w-full bg-gray-50 border-transparent rounded-2xl px-6 py-4 focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-all text-sm" required>
                            <option value="" disabled>Sélectionner...</option>
                            <option v-for="user in usersToInviteLocal" :key="user.id" :value="user.id">
                                {{ user.prenom }} {{ user.nom }} ({{ user.entite?.nom }})
                            </option>
                        </select>
                        <p class="mt-2 text-[10px] text-gray-400 italic font-medium">Note: Seuls les collaborateurs non membres apparaissent ici.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Rôle dans l'espace</label>
                        <div class="grid grid-cols-2 gap-3">
                            <button type="button" @click="inviteForm.role = 'membre'" :class="['px-6 py-3 rounded-xl font-bold text-xs transition-all border-2', inviteForm.role === 'membre' ? 'border-indigo-600 bg-indigo-50 text-indigo-600' : 'border-gray-100 text-gray-400 hover:border-gray-200']">MEMBRE</button>
                            <button type="button" @click="inviteForm.role = 'admin'" :class="['px-1 py-3 rounded-xl font-bold text-xs transition-all border-2', inviteForm.role === 'admin' ? 'border-indigo-600 bg-indigo-50 text-indigo-600' : 'border-gray-100 text-gray-400 hover:border-gray-200']">ADMINISTRATEUR</button>
                        </div>
                    </div>
                    <button type="submit" :disabled="inviteForm.processing" class="w-full bg-indigo-600 text-white font-bold py-4 rounded-2xl hover:bg-indigo-700 shadow-lg shadow-indigo-600/30 transition-all active:scale-95 disabled:opacity-50">
                        {{ inviteForm.processing ? 'Invitation...' : 'Envoyer l\'invitation' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
