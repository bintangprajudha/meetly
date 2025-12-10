<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-[#F8F2DE] flex flex-col items-center justify-center relative overflow-hidden">
        <!-- Background Blur Elements -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-pink-400 blur-3xl opacity-40"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-purple-400 blur-3xl opacity-40"></div>

        <!-- Additional Blurs -->
        <div class="absolute top-1/2 left-10 w-72 h-72 bg-red-300 blur-3xl opacity-100"></div>
        <div class="absolute bottom-10 right-20 w-80 h-80 bg-blue-300 blur-3xl opacity-100"></div>
        <div class="absolute top-20 left-1/3 w-64 h-64 bg-pink-300 blur-3xl opacity-100"></div>
        <div class="absolute bottom-1/2 right-1/3 w-72 h-72 bg-purple-300 blur-3xl opacity-20"></div>


        <!-- Logo -->
        <div class="absolute top-6 left-6">
            <img src="Group 1.png" alt="Logo Meetly" class="w-25 h-auto" />
        </div>



        <!-- Title -->
        <h1 class="text-8xl font-extrabold text-[#D84040] tracking-wide">MEETLY</h1>
        <p class="text-center font-extrabold mt-4 text-[#D84040] max-w-6xl text-lg">
            Selamat datang di Meetly — ruang digital untuk berbagi inspirasi, meningkatkan pertemanan,
            dan membangun masa depan.
        </p>


        <!-- Buttons -->
        <div class="flex gap-4 mt-10">
            <button class="px-8 py-2 rounded-md bg-[#D84040] text-white font-semibold shadow-md hover:opacity-90">
                <Link href="/login">
                    Login
                </Link>
            </button>
            <button
                class="px-8 py-2 rounded-md border border-[#D84040] text-[#D84040] font-semibold hover:bg-[#D84040] hover:text-white transition-all">
                <Link href="/register">
                    Register →
                </Link>
            </button>
        </div>
    </div>
</template>
