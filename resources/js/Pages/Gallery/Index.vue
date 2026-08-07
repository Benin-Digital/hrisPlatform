<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    images: Array,
    canManage: {
        type: Boolean,
        default: false
    }
});

const form = useForm({
    images: [],
    title: '',
    description: '',
    order: 0,
});

const showUploadModal = ref(false);
const isDragging = ref(false);

const directUploadForm = useForm({
    images: [],
});

const handleDirectDrop = (e) => {
    isDragging.value = false;
    if (e.dataTransfer.files.length > 0) {
        directUploadForm.images = Array.from(e.dataTransfer.files);
        submitDirect();
    }
};

const handleFileSelect = (e) => {
    if (e.target.files.length > 0) {
        directUploadForm.images = Array.from(e.target.files);
        submitDirect();
    }
};

const submitDirect = () => {
    directUploadForm.post(route('super-admin.gallery.store'), {
        forceFormData: true,
        onSuccess: () => {
             directUploadForm.reset();
        }
    });
};

const handleDrop = (e) => {
    isDragging.value = false;
    if (e.dataTransfer.files.length > 0) {
        form.images = Array.from(e.dataTransfer.files);
    }
};



const submit = () => {
    form.post(route('super-admin.gallery.store'), {
        onSuccess: () => {
            showUploadModal.value = false;
            form.reset();
        },
    });
};

const deleteImage = (id) => {
    if (confirm('Voulez-vous vraiment supprimer cette image ?')) {
        useForm({}).delete(route('super-admin.gallery.destroy', id));
    }
};

const updateVisibility = (image) => {
    useForm({
        is_visible: !image.is_visible
    }).put(route('super-admin.gallery.update', image.id));
};
</script>

<template>
    <Head title="Gestion Galerie" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Galerie Photos</h2>
                <button 
                    v-if="canManage"
                    @click="showUploadModal = true"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition"
                >
                    Ajouter une image
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Direct Drag & Drop Zone -->
                <div v-if="canManage" class="mb-8">
                    <div 
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="handleDirectDrop"
                        @click="$refs.mainFileInput.click()"
                        :class="[
                            'group relative overflow-hidden rounded-[2rem] border-2 border-dashed transition-all duration-500 cursor-pointer p-10 text-center bg-white shadow-sm',
                            isDragging ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-indigo-300 hover:bg-gray-50'
                        ]"
                    >
                        <div class="relative z-10">
                            <div class="w-16 h-16 bg-indigo-100 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                                <span class="text-2xl">📸</span>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800 mb-1">Glissez vos images ici</h3>
                            <p class="text-gray-500 text-sm">Ajoutez plusieurs images instantanément à votre galerie.</p>
                        </div>
                        
                        <div v-if="directUploadForm.processing" class="absolute inset-0 bg-white/80 backdrop-blur-sm flex items-center justify-center z-20">
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 border-3 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                                <span class="font-bold text-indigo-600">Envoi en cours...</span>
                            </div>
                        </div>

                        <input 
                            ref="mainFileInput"
                            type="file" 
                            multiple 
                            class="hidden" 
                            @change="handleFileSelect"
                        />
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div v-if="images.length === 0" class="text-center py-12 text-gray-400 italic">
                        La galerie est vide. Utilisez la zone ci-dessus pour ajouter des photos.
                    </div>


                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        <div v-for="image in images" :key="image.id" class="relative group border rounded-xl overflow-hidden shadow-sm">
                            <img :src="`/storage/${image.image_path}`" class="w-full aspect-video object-cover" />
                            
                            <div class="p-4">
                                <h3 class="font-bold truncate">{{ image.title || 'Sans titre' }}</h3>
                                <div v-if="canManage" class="flex justify-between items-center mt-4">
                                    <button 
                                        @click="updateVisibility(image)"
                                        :class="image.is_visible ? 'text-green-600' : 'text-gray-400'"
                                        class="text-sm font-medium"
                                    >
                                        {{ image.is_visible ? 'Visible' : 'Masquée' }}
                                    </button>
                                    <button 
                                        @click="deleteImage(image.id)"
                                        class="text-red-600 hover:text-red-800 text-sm font-medium"
                                    >
                                        Supprimer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Upload (Simplified for now) -->
        <div v-if="showUploadModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4">
                <h3 class="text-xl font-bold mb-6">Ajouter une image</h3>
                <form @submit.prevent="submit" class="space-y-4">
                    <div 
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="handleDrop"
                        :class="isDragging ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300 bg-gray-50'"
                        class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-xl transition-all cursor-pointer hover:bg-gray-100"
                        @click="$refs.fileInput.click()"
                    >
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <span class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                    Déposez vos images ici
                                </span>
                                <p class="pl-1">ou cliquez pour parcourir</p>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, GIF jusqu'à 2MB</p>
                            <div v-if="form.images.length > 0" class="text-indigo-600 font-bold text-xs mt-2">
                                {{ form.images.length }} fichier(s) sélectionné(s)
                            </div>
                        </div>
                        <input 
                            ref="fileInput"
                            type="file" 
                            multiple
                            class="hidden"
                            @input="form.images = Array.from($event.target.files)"
                        />
                    </div>
                    <div v-if="form.errors.images" class="text-red-500 text-xs mt-1">{{ form.errors.images }}</div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Titre</label>
                        <input v-model="form.title" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea v-model="form.description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="showUploadModal = false" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">Annuler</button>
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Chargement...' : 'Enregistrer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
