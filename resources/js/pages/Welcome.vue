<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div
        class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]"
    >
        <header
            class="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-4xl"
        >
            <nav class="flex items-center justify-end gap-4">
                <h1 class="flex-grow text-lg font-semibold">
                    <Link href="https://example.com" class="hover:underline"
                        >Meetly</Link
                    >
                </h1>
            </nav>
        </header>
        <div
            class="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0"
        >
            <main
                class="flex w-full max-w-sm items-center justify-center overflow-hidden rounded-lg lg:max-w-lg"
            >
                <div
                    class="flex min-h-full w-full flex-col justify-center px-6 py-12 lg:px-8"
                >
                    <div class="sm:mx-auto sm:w-full sm:max-w-sm">
                        <img
                            src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=600"
                            alt="Your Company"
                            class="mx-auto h-10 w-auto"
                        />
                        <h2
                            class="mt-10 text-center text-2xl/9 font-bold tracking-tight text-gray-900"
                        >
                            Meetly
                        </h2>
                    </div>

                    <div class="mt-10 mb-0 sm:mx-auto sm:w-full sm:max-w-sm">
                        <form action="#" method="POST" class="space-y-6">
                            <div>
                                <div class="mt-2">
                                    <input
                                        id="email"
                                        type="email"
                                        name="email"
                                        required
                                        autocomplete="email"
                                        placeholder="Email"
                                        class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                                    />
                                </div>
                            </div>

                            <div>
                                <div
                                    class="flex items-center justify-between"
                                ></div>
                                <div class="mt-0">
                                    <input
                                        id="password"
                                        type="password"
                                        name="password"
                                        required
                                        autocomplete="current-password"
                                        placeholder="Password"
                                        class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                                    />
                                </div>
                            </div>

                            <div
                                class="flex flex-col items-center justify-center gap-4"
                            >
                                <button
                                    type="submit"
                                    class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                                >
                                    Sign in
                                </button>
                                <div class="text-sm">
                                    <a
                                        href="#"
                                        class="font-semibold text-indigo-600 hover:text-indigo-500"
                                        >Forgot password?</a
                                    >
                                </div>
                            </div>
                        </form>

                        <p class="mt-10 text-center text-sm/6 text-gray-500">
                            Belum punya akun?
                            <a
                                href="#"
                                class="font-semibold text-indigo-600 hover:text-indigo-500"
                                >Sign Up</a
                            >
                        </p>

                        <!-- Development Link to Dashboard -->
                        <p class="mt-4 text-center text-sm/6 text-gray-400">
                            <Link
                                href="/dashboard"
                                class="font-semibold text-gray-600 hover:text-gray-800"
                                >Preview Dashboard</Link
                            >
                        </p>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
