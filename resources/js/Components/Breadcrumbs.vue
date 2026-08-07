<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const breadcrumbs = computed(() => {
    const url = page.url;
    const parts = url.split('/').filter(p => p !== '');
    
    // Déterminer la racine selon l'URL
    const isExtranet = url.startsWith('/extranet');
    const rootName = isExtranet ? 'Extranet' : 'Dashboard';
    const rootHref = isExtranet ? '/extranet' : '/dashboard';
    
    let result = [{ name: rootName, href: rootHref }];
    
    let currentPath = isExtranet ? '/extranet' : '';
    parts.forEach((part, index) => {
        // Skip root parts
        if (part === 'dashboard' || (isExtranet && part === 'extranet')) return;
        
        currentPath += `/${part}`;
        
        // Formatter le nom
        let name = part.charAt(0).toUpperCase() + part.slice(1).replace(/-/g, ' ');
        if (name === 'Collaboration') name = 'Espaces Collaboratifs';
        
        result.push({
            name: name,
            href: currentPath
        });
    });
    
    return result;
});
</script>

<template>
    <nav class="flex px-5 py-3 text-gray-700 border border-gray-200 rounded-lg bg-white shadow-sm mb-6 overflow-x-auto whitespace-nowrap scrollbar-hide" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li v-for="(crumb, index) in breadcrumbs" :key="crumb.href" class="inline-flex items-center">
                <div class="flex items-center">
                    <svg v-if="index > 0" class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                    
                    <Link
                        v-if="index < breadcrumbs.length - 1"
                        :href="crumb.href"
                        class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors"
                        :class="[index > 0 ? 'ml-1 md:ml-2' : '']"
                    >
                        <span v-if="index === 0" class="mr-2">🏠</span>
                        {{ crumb.name }}
                    </Link>
                    <span 
                        v-else 
                        class="text-sm font-bold text-indigo-600 line-clamp-1"
                        :class="[index > 0 ? 'ml-1 md:ml-2' : '']"
                    >
                        <span v-if="index === 0" class="mr-2">🏠</span>
                        {{ crumb.name }}
                    </span>
                </div>
            </li>
        </ol>
    </nav>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>