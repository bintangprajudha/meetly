<script setup lang="ts">
import { router } from '@inertiajs/vue3';

const goHome = () => {
    router.visit('/');
};
</script>

<template>
    <nav class="sticky top-0 z-50 bg-white border-b border-gray-200">
        <div class="mx-auto max-w-6xl px-4">
            <div class="flex h-14 items-center justify-between">
                <button @click="goHome" class="flex items-center gap-2 text-3xl font-black transition"
                    style="color: #df6060">
                    MEETLY
                </button>

            </div>
        </div>
    </nav>
</template>
