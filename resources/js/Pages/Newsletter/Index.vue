<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    subscribers: Array,
});

const deleteSubscriber = (id) => {
    if (confirm('Voulez-vous vraiment supprimer cet abonné ?')) {
        useForm({}).delete(route('super-admin.newsletters.destroy', id));
    }
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
};
</script>

<template>
    <Head title="Gestion Newsletter" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestion de la Newsletter</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date d'inscription</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="subscriber in subscribers" :key="subscriber.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ subscriber.email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(subscriber.subscribed_at || subscriber.created_at) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="subscriber.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ subscriber.is_active ? 'Actif' : 'Inactif' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button @click="deleteSubscriber(subscriber.id)" class="text-red-600 hover:text-red-900">Supprimer</button>
                                    </td>
                                </tr>
                                <tr v-if="subscribers.length === 0">
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">Aucun abonné trouvé.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
