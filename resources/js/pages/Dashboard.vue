<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const activeTab = ref(0);

const navigationItems = [
    {
        name: 'Dashboard',
        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        active: true
    },
    {
        name: 'Analytics',
        icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        active: false
    },
    {
        name: 'Users',
        icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z',
        active: false
    },
    {
        name: 'Settings',
        icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
        active: false
    }
];
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex min-h-screen bg-gray-50">
        <!-- Sidebar Kiri dengan Icon -->
        <div class="fixed left-0 top-0 z-50 h-full w-16 bg-white shadow-lg border-r border-gray-200">
            <div class="flex flex-col items-center py-4 space-y-4">
                <!-- Logo/Brand -->
                <Link href="/" class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold text-lg hover:bg-blue-700 transition-colors">
                    M
                </Link>
                
                <!-- Navigation Items -->
                <nav class="flex flex-col space-y-3">
                    <template v-for="(item, index) in navigationItems" :key="item.name">
                        <button 
                            @click="activeTab = index"
                            :class="[
                                'w-10 h-10 flex items-center justify-center rounded-xl transition-all duration-200',
                                activeTab === index 
                                    ? 'bg-blue-100 text-blue-600 shadow-md' 
                                    : 'text-gray-600 hover:bg-gray-100 hover:text-black'
                            ]"
                            :title="item.name"
                        >
                            <svg class="w-5 h-5 stroke-current" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"></path>
                            </svg>
                        </button>
                    </template>
                </nav>
                
                <!-- Bottom Actions -->
                <div class="flex-1"></div>
                <div class="flex flex-col space-y-3">
                    <!-- Profile -->
                    <div class="w-10 h-10 bg-gray-200 rounded-xl flex items-center justify-center text-gray-700 font-medium text-sm">
                        U
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 ml-16">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                            
                        </div>
                        <div class="flex items-center space-x-4">
                            <!-- Search -->
                            <div class="relative">
                                <input 
                                    type="text" 
                                    placeholder="Search..."
                                    class="w-64 px-4 py-2 pl-10 pr-4 text-gray-700 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                                <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <!-- Notifications -->
                            <button class="p-2 text-gray-600 hover:text-black hover:bg-gray-100 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <main class="p-6">
                
            </main>
        </div>
    </div>
</template>
     




