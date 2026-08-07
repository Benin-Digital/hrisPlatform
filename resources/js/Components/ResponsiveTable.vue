<template>
  <div class="responsive-table-container">
    <!-- Desktop/Tablet View (Hidden on mobile) -->
    <div class="hidden md:block overflow-x-auto custom-scrollbar bg-white rounded-xl shadow-sm border border-gray-200">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th 
              v-for="col in columns" 
              :key="col.key"
              scope="col" 
              :class="[
                'px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider',
                col.class || ''
              ]"
            >
              {{ col.label }}
            </th>
            <th v-if="$slots.actions" scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-100">
          <tr 
            v-for="(item, index) in data" 
            :key="item.id || index"
            class="hover:bg-gray-50 transition-colors duration-150"
          >
            <td 
              v-for="col in columns" 
              :key="col.key"
              :class="['px-6 py-4 whitespace-nowrap text-sm text-gray-700', col.tdClass || '']"
            >
              <slot :name="`col-${col.key}`" :item="item">
                {{ item[col.key] || '-' }}
              </slot>
            </td>
            <td v-if="$slots.actions" class="px-6 py-4 whitespace-nowrap text-right text-sm">
              <slot name="actions" :item="item" />
            </td>
          </tr>
          <tr v-if="data.length === 0">
            <td :colspan="columns.length + ($slots.actions ? 1 : 0)" class="px-6 py-12 text-center text-gray-500 italic">
              {{ emptyMessage }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Mobile View (Hidden on desktop/tablet) -->
    <div class="md:hidden space-y-4">
      <div 
        v-for="(item, index) in data" 
        :key="item.id || index"
        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
      >
        <div class="p-4 space-y-3">
          <div 
            v-for="col in columns" 
            :key="col.key"
            class="flex flex-col"
          >
            <span class="text-xs font-bold text-gray-400 uppercase tracking-tight mb-1">{{ col.label }}</span>
            <div class="text-sm text-gray-800">
              <slot :name="`col-${col.key}`" :item="item">
                {{ item[col.key] || '-' }}
              </slot>
            </div>
          </div>
        </div>
        
        <!-- Mobile Actions Bar -->
        <div v-if="$slots.actions" class="bg-gray-50 p-3 flex justify-end items-center gap-3 border-t border-gray-100">
          <slot name="actions" :item="item" />
        </div>
      </div>
      
      <div v-if="data.length === 0" class="bg-white rounded-xl border border-gray-200 p-12 text-center text-gray-500 italic">
        {{ emptyMessage }}
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  columns: {
    type: Array,
    required: true,
    // Format: { key: 'name', label: 'Nom', class: '', tdClass: '' }
  },
  data: {
    type: Array,
    required: true
  },
  emptyMessage: {
    type: String,
    default: 'Aucune donnée disponible'
  }
});
</script>

<style scoped>
.responsive-table-container {
  width: 100%;
}
</style>
