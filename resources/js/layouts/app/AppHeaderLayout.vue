<script setup lang="ts"></script>

<template>
    <div class="flex min-h-screen flex-col">
        <header class="flex items-center justify-between border-b border-gray-200 px-6 py-3 bg-gray-100">
            <div class="flex items-center space-x-4">
                <a href="/dashboard" class="flex items-center space-x-3">
                    <div class="w-8 h-8 flex items-center justify-center rounded-md">
                        <!-- small logo icon, uses existing asset if available -->
                    </div>
                    <h1 class="text-4xl font-bold text-[#D84040]">MEETLY</h1>
                </a>
            </div>

            <div class="flex items-center space-x-4">
                <div class="relative">
                    <input type="text" placeholder="" aria-label="Search"
                        class="w-80 h-10 pl-4 pr-12 rounded-full bg-white shadow-md border border-gray-200 focus:outline-none text-black placeholder-black" />
                    <div class="absolute right-2 top-1/2 transform -translate-y-1/2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-auto">
            <slot />
        </div>
    </div>

</template>
