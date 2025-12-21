<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-[#F8F2DE] flex flex-col items-center justify-center relative overflow-hidden px-4">
        <!-- Background Blur Elements -->
        <div class="absolute top-0 right-0 w-64 h-64 sm:w-96 sm:h-96 bg-pink-400 blur-3xl opacity-40"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 sm:w-96 sm:h-96 bg-purple-400 blur-3xl opacity-40"></div>

        <!-- Additional Blurs -->
        <div class="absolute top-1/2 left-5 sm:left-10 w-48 h-48 sm:w-72 sm:h-72 bg-red-300 blur-3xl opacity-100"></div>
        <div class="absolute bottom-10 right-10 sm:right-20 w-56 h-56 sm:w-80 sm:h-80 bg-blue-300 blur-3xl opacity-100"></div>
        <div class="absolute top-20 left-1/4 sm:left-1/3 w-48 h-48 sm:w-64 sm:h-64 bg-pink-300 blur-3xl opacity-100"></div>
        <div class="absolute bottom-1/2 right-1/4 sm:right-1/3 w-56 h-56 sm:w-72 sm:h-72 bg-purple-300 blur-3xl opacity-20"></div>


        <!-- Logo -->
        <div class="absolute top-4 left-4 sm:top-6 sm:left-6">
            <img src="logo.png" alt="Logo Meetly" class="w-16 h-auto sm:w-20 md:w-25" />
        </div>



        <!-- Title -->
        <h1 class="text-5xl sm:text-6xl md:text-7xl lg:text-8xl font-extrabold text-[#D84040] tracking-wide text-center">MEETLY</h1>
        <p class="text-center font-extrabold mt-4 text-[#D84040] max-w-xs sm:max-w-lg md:max-w-2xl lg:max-w-6xl text-sm sm:text-base md:text-lg px-4">
            Welcome to Meetly — a digital space for sharing inspiration, fostering friendships, and building the future.
        </p>


        <!-- Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 mt-10 w-full max-w-xs sm:max-w-none sm:w-auto">
            <button class="px-8 py-3 rounded-md bg-[#D84040] text-white font-semibold shadow-md hover:opacity-90 transition-all w-full sm:w-auto">
                <Link href="/login">
                    Login
                </Link>
            </button>
            <button
                class="px-8 py-3 rounded-md border border-[#D84040] text-[#D84040] font-semibold hover:bg-[#D84040] hover:text-white transition-all w-full sm:w-auto">
                <Link href="/register">
                    Register →
                </Link>
            </button>
        </div>
    </div>
</template>