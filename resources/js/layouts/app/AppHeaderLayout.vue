<script setup lang="ts"></script>

<template>
    <div class="flex min-h-screen flex-col fixed w-full py-2">
        <header class="flex items-center justify-between border-b border-gray-200 px-6 py-3 bg-gray-50">
            <div class="flex items-center space-x-4">
                <a href="/dashboard" class="flex items-center space-x-3">
                    <div class="w-8 h-8 flex items-center justify-center rounded-md">
                        <!-- small logo icon, uses existing asset if available -->
                    </div>
                    <h1 class="text-4xl font-bold text-[#D84040] -ml-8">MEETLY</h1>
                </a>
            </div>
        </header>

        <div class="flex-1 overflow-auto">
            <slot />
        </div>
    </div>
</template>
