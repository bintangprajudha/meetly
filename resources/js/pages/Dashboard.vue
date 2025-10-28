<script setup lang="ts">
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex min-h-screen bg-gray-100">
        <!-- Sidebar Kiri dengan Icon -->
        <div
            class="fixed top-0 left-0 z-50 h-full w-16 border-r border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900"
        >
            <div class="flex flex-col items-center space-y-4 py-4">
                <!-- Logo/Brand -->
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600 text-lg font-bold text-white"
                >
                    M
                </div>

                <!-- Navigation Items -->
                <nav class="flex flex-col space-y-3">
                    <!-- Dashboard (Active) -->
                    <Link
                        href="/dashboard"
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600 transition-colors hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400"
                    >
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                            ></path>
                        </svg>
                    </Link>

                    <!-- Home -->
                    <Link
                        href="/"
                        class="flex h-10 w-10 items-center justify-center rounded-lg text-gray-600 transition-colors hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800"
                    >
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                            ></path>
                        </svg>
                    </Link>

                    <!-- Analytics -->
                    <button
                        class="flex h-10 w-10 items-center justify-center rounded-lg text-gray-600 transition-colors hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800"
                    >
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                            ></path>
                        </svg>
                    </button>

                    <!-- Users -->
                    <button
                        class="flex h-10 w-10 items-center justify-center rounded-lg text-gray-600 transition-colors hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800"
                    >
                        <svg
                            class="h-5 w-5"
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
                    </button>
                </nav>

                <!-- Bottom Actions -->
                <div class="flex-1"></div>
                <div class="flex flex-col space-y-3">
                    <!-- Settings -->
                    <button
                        class="flex h-10 w-10 items-center justify-center rounded-lg text-gray-600 transition-colors hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800"
                    >
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                            ></path>
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                            ></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content Area (dengan margin left untuk sidebar) -->
        <div class="ml-16 flex-1">
            <!-- Header -->
            <header
                class="border-b border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800"
            >
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1
                                class="text-2xl font-bold text-gray-900 dark:text-white"
                            >
                                Dashboard
                            </h1>
                            <p class="text-gray-600 dark:text-gray-400">
                                Welcome back! Here's what's happening.
                            </p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <!-- Notifications -->
                            <button
                                class="p-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                            >
                                <svg
                                    class="h-6 w-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 17h5l-5 5-5-5h5z"
                                    ></path>
                                </svg>
                            </button>
                            <!-- Profile -->
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-sm font-medium text-white"
                            >
                                U
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <main class="p-6">
                <!-- Stats Cards -->
                <div
                    class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4"
                >
                    <div
                        class="rounded-lg bg-white p-6 shadow dark:bg-gray-800"
                    >
                        <div class="flex items-center">
                            <div
                                class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/20"
                            >
                                <svg
                                    class="h-6 w-6 text-blue-600 dark:text-blue-400"
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
                            <div class="ml-4">
                                <p
                                    class="text-sm font-medium text-gray-600 dark:text-gray-400"
                                >
                                    Total Users
                                </p>
                                <p
                                    class="text-2xl font-bold text-gray-900 dark:text-white"
                                >
                                    1,234
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="rounded-lg bg-white p-6 shadow dark:bg-gray-800"
                    >
                        <div class="flex items-center">
                            <div
                                class="rounded-lg bg-green-100 p-2 dark:bg-green-900/20"
                            >
                                <svg
                                    class="h-6 w-6 text-green-600 dark:text-green-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                    ></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-sm font-medium text-gray-600 dark:text-gray-400"
                                >
                                    Revenue
                                </p>
                                <p
                                    class="text-2xl font-bold text-gray-900 dark:text-white"
                                >
                                    $12,345
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="rounded-lg bg-white p-6 shadow dark:bg-gray-800"
                    >
                        <div class="flex items-center">
                            <div
                                class="rounded-lg bg-yellow-100 p-2 dark:bg-yellow-900/20"
                            >
                                <svg
                                    class="h-6 w-6 text-yellow-600 dark:text-yellow-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
                                    ></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-sm font-medium text-gray-600 dark:text-gray-400"
                                >
                                    Growth
                                </p>
                                <p
                                    class="text-2xl font-bold text-gray-900 dark:text-white"
                                >
                                    +23%
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="rounded-lg bg-white p-6 shadow dark:bg-gray-800"
                    >
                        <div class="flex items-center">
                            <div
                                class="rounded-lg bg-purple-100 p-2 dark:bg-purple-900/20"
                            >
                                <svg
                                    class="h-6 w-6 text-purple-600 dark:text-purple-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"
                                    ></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-sm font-medium text-gray-600 dark:text-gray-400"
                                >
                                    Tasks
                                </p>
                                <p
                                    class="text-2xl font-bold text-gray-900 dark:text-white"
                                >
                                    89
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts and Tables Section -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Chart Area -->
                    <div
                        class="rounded-lg bg-white p-6 shadow dark:bg-gray-800"
                    >
                        <h3
                            class="mb-4 text-lg font-medium text-gray-900 dark:text-white"
                        >
                            Analytics Overview
                        </h3>
                        <div
                            class="flex h-64 items-center justify-center rounded-lg bg-gray-50 dark:bg-gray-700"
                        >
                            <p class="text-gray-500 dark:text-gray-400">
                                Chart placeholder
                            </p>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div
                        class="rounded-lg bg-white p-6 shadow dark:bg-gray-800"
                    >
                        <h3
                            class="mb-4 text-lg font-medium text-gray-900 dark:text-white"
                        >
                            Recent Activity
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-center space-x-3">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/20"
                                >
                                    <span
                                        class="text-xs font-medium text-blue-600 dark:text-blue-400"
                                        >U</span
                                    >
                                </div>
                                <div class="flex-1">
                                    <p
                                        class="text-sm font-medium text-gray-900 dark:text-white"
                                    >
                                        New user registered
                                    </p>
                                    <p
                                        class="text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        2 minutes ago
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20"
                                >
                                    <span
                                        class="text-xs font-medium text-green-600 dark:text-green-400"
                                        >$</span
                                    >
                                </div>
                                <div class="flex-1">
                                    <p
                                        class="text-sm font-medium text-gray-900 dark:text-white"
                                    >
                                        Payment received
                                    </p>
                                    <p
                                        class="text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        5 minutes ago
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900/20"
                                >
                                    <span
                                        class="text-xs font-medium text-yellow-600 dark:text-yellow-400"
                                        >!</span
                                    >
                                </div>
                                <div class="flex-1">
                                    <p
                                        class="text-sm font-medium text-gray-900 dark:text-white"
                                    >
                                        System update completed
                                    </p>
                                    <p
                                        class="text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        1 hour ago
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
