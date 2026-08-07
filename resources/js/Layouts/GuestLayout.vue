<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-zinc-950 flex flex-col items-center justify-center p-6 selection:bg-indigo-600 selection:text-white relative overflow-hidden font-sans">
        <!-- Abstract Background -->
        <div class="absolute inset-0 -z-10">
            <div class="absolute top-[-10%] left-[-10%] w-[50%] h-[50%] bg-indigo-900/10 rounded-full blur-[120px]"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-purple-900/10 rounded-full blur-[100px]"></div>
        </div>

        <div class="w-full sm:max-w-md">
            <!-- Brand Logo -->
            <div class="flex flex-col items-center mb-10 group">
                <Link href="/" class="flex flex-col items-center gap-4 transition-transform hover:scale-105 duration-500">
                    <div class="w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-2xl shadow-indigo-500/30 group-hover:rotate-6 transition-all">
                        <span class="text-white font-black text-3xl italic">H</span>
                    </div>
                    <span class="text-3xl font-black tracking-tighter text-white uppercase italic">HRIS<span class="text-indigo-500">Pro</span></span>
                </Link>
            </div>

            <!-- Card Container -->
            <div class="bg-zinc-900/50 backdrop-blur-3xl border border-white/5 p-8 rounded-[2.5rem] shadow-2xl shadow-black/50 relative overflow-hidden group">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-indigo-600 to-transparent opacity-50"></div>
                
                <slot />
            </div>

            <!-- Footer Links -->
            <div class="mt-8 text-center">
                <p class="text-[10px] text-zinc-600 font-bold uppercase tracking-[0.3em]">
                    © 2026 HRIS Pro Ecosystem • Secure Access
                </p>
            </div>
        </div>
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100;300;400;500;700;900&display=swap');

body {
    font-family: 'Outfit', sans-serif;
    background-color: #09090b;
}

.font-black { font-weight: 900; }
</style>
