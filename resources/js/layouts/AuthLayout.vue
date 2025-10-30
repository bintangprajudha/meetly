<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

interface Props {
    title?: string;
}

withDefaults(defineProps<Props>(), {
    title: 'Authentication',
});
</script>

<template>
    <Head :title="title" />

    <div class="flex min-h-screen">
        <!-- Left Side - Image/Branding -->
        <div
            class="hidden items-center justify-center bg-gradient-to-br from-blue-600 to-purple-700 lg:flex lg:w-1/2"
        >
            <div class="text-center text-white">
                <h1 class="mb-4 text-4xl font-bold">Meetly</h1>
                <p class="text-xl opacity-90">Welcome to our platform</p>
                <div class="mt-8">
                    <div
                        class="mx-auto flex h-32 w-32 items-center justify-center rounded-full bg-white/10"
                    >
                        <svg
                            class="h-16 w-16"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"
                            ></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Form -->
        <div
            class="flex flex-1 items-center justify-center bg-gray-50 px-4 sm:px-6 lg:px-8 dark:bg-gray-900"
        >
            <div class="w-full max-w-md">
                <slot />
            </div>
        </div>
    </div>
</template>
