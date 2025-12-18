<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

// interface Props {
//     title?: string;
// }

// withDefaults(defineProps<Props>(), {
//     title: 'Authentication',
// });
</script>

<template>
    <Head :title="title" />

    <div class="flex min-h-screen">

        <!-- LEFT SIDE (Red Panel) -->
        <div class="hidden lg:flex lg:w-1/2 items-center justify-center bg-[#e54c4c] relative overflow-hidden">
            
            <!-- Logo box -->
            <div class="w-100 h-100 rounded-xl shadow-xl flex items-center justify-center z-10 bg-[#f7f3ea]">
                <img src="logo.png" alt="Logo Meetly" class="w-75 h-75"/>
            </div>

            <!-- Decorative circle -->
            <div class="absolute -bottom-48 -right-48 w-96 h-96 bg-[#f7f3ea] rounded-full opacity-60"></div>

        </div>

        <!-- RIGHT SIDE (Cream Panel, slot form) -->
        <div class="flex flex-1 items-center justify-center bg-[#f7f3ea] px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-md">
                <slot />
            </div>
        </div>

    </div>
</template>
