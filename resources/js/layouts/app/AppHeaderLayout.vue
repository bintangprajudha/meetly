<script setup lang="ts"></script>

<template>
    <div class="flex min-h-screen flex-col">
    <header
        class="flex items-center justify-between border-b border-gray-200 px-6 py-3 shadow-sm"
        style="background-color:#D84040;"
    >
        <div class="flex items-center space-x-4">
            <h1 class="text-xl font-semibold">
                <a href="/dashboard" class="text-white">MEETLY</a>
            </h1>
        </div>

        <div class="flex items-center space-x-4">

            <div class="relative">
                <input
                    type="text"
                    placeholder="Search..."
                    class="w-64 px-4 py-1 pl-8 pr-4 rounded-lg shadow-sm focus:outline-none"
                    style="background-color:#F8F2DE; color:#D84040;"
                />
                <svg
                    class="absolute left-2 top-2 w-4 h-4"
                    style="color:#D84040;"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>

        </div>
    </header>

    <div class="flex-1 overflow-auto">
        <slot />
    </div>
</div>

</template>
