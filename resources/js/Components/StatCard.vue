<template>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ title }}
                </p>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">
                    {{ value }}
                </p>
                <p v-if="subtitle" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ subtitle }}
                </p>
            </div>
            <div class="p-3 rounded-2xl" :class="iconBg">
                <span class="text-2xl">{{ icon }}</span>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        required: true,
    },
    icon: {
        type: String,
        default: '📊',
    },
    iconBg: {
        type: String,
        default: 'bg-indigo-50 dark:bg-indigo-900/30',
    },
    subtitle: {
        type: String,
        default: null,
    },
});
</script>