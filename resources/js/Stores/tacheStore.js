import { defineStore } from 'pinia';

export const useTacheStore = defineStore('tache', {
    state: () => ({
        taches: [],
        currentTache: null,
    }),
    actions: {
        async fetchTaches() {
            // Appel API pour récupérer les tâches
            const response = await axios.get('/api/taches');
            this.taches = response.data;
        },
        async updateTemps(tacheId, minutes) {
            const response = await axios.patch(`/taches/${tacheId}/temps`, {
                temps_passe_minutes: minutes,
            });
            // Mettre à jour la tâche dans le store
            const index = this.taches.findIndex(t => t.id === tacheId);
            if (index !== -1) {
                this.taches[index].temps_passe_minutes = response.data.new_total;
            }
            if (this.currentTache && this.currentTache.id === tacheId) {
                this.currentTache.temps_passe_minutes = response.data.new_total;
            }
        },
        setCurrentTache(tache) {
            this.currentTache = tache;
        },
    },
});