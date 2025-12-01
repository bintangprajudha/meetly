<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="bg-gray-900">
        <header class="absolute inset-x-0 top-0 z-50">
            <nav
                aria-label="Global"
                class="flex items-center justify-between p-6 lg:px-8"
            >
                <div class="flex lg:flex-1">
                    <a href="#" class="-m-1.5 p-1.5">
                        <span class="sr-only">Your Company</span>
                        <img
                            src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=500"
                            alt=""
                            class="h-8 w-auto"
                        />
                    </a>
                </div>
                <div class="flex lg:hidden">
                    <button
                        type="button"
                        command="show-modal"
                        commandfor="mobile-menu"
                        class="-m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-gray-200"
                    >
                        <span class="sr-only">Open main menu</span>
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                            data-slot="icon"
                            aria-hidden="true"
                            class="size-6"
                        >
                            <path
                                d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </button>
                </div>
                <div class="hidden lg:flex lg:gap-x-12">
                    <a href="#" class="text-sm/6 font-semibold text-white"
                        >Product</a
                    >
                    <a href="#" class="text-sm/6 font-semibold text-white"
                        >Features</a
                    >
                    <a href="#" class="text-sm/6 font-semibold text-white"
                        >Marketplace</a
                    >
                    <a href="#" class="text-sm/6 font-semibold text-white"
                        >Company</a
                    >
                </div>
                <div class="hidden lg:flex lg:flex-1 lg:justify-end">
                    <Link
                        href="{{ route('login') }}"
                        class="text-sm/6 font-semibold text-white"
                        >Log in <span aria-hidden="true">&rarr;</span></Link
                    >
                </div>
            </nav>
            <el-dialog>
                <dialog
                    id="mobile-menu"
                    class="backdrop:bg-transparent lg:hidden"
                >
                    <div tabindex="0" class="fixed inset-0 focus:outline-none">
                        <el-dialog-panel
                            class="fixed inset-y-0 right-0 z-50 w-full overflow-y-auto bg-gray-900 p-6 sm:max-w-sm sm:ring-1 sm:ring-gray-100/10"
                        >
                            <div class="flex items-center justify-between">
                                <a href="#" class="-m-1.5 p-1.5">
                                    <span class="sr-only">Your Company</span>
                                    <img
                                        src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=500"
                                        alt=""
                                        class="h-8 w-auto"
                                    />
                                </a>
                                <button
                                    type="button"
                                    command="close"
                                    commandfor="mobile-menu"
                                    class="-m-2.5 rounded-md p-2.5 text-gray-200"
                                >
                                    <span class="sr-only">Close menu</span>
                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        data-slot="icon"
                                        aria-hidden="true"
                                        class="size-6"
                                    >
                                        <path
                                            d="M6 18 18 6M6 6l12 12"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </button>
                            </div>
                            <div class="mt-6 flow-root">
                                <div class="-my-6 divide-y divide-white/10">
                                    <div class="space-y-2 py-6">
                                        <a
                                            href="#"
                                            class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-white hover:bg-white/5"
                                            >Product</a
                                        >
                                        <a
                                            href="#"
                                            class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-white hover:bg-white/5"
                                            >Features</a
                                        >
                                        <a
                                            href="#"
                                            class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-white hover:bg-white/5"
                                            >Marketplace</a
                                        >
                                        <a
                                            href="#"
                                            class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-white hover:bg-white/5"
                                            >Company</a
                                        >
                                    </div>
                                    <div class="py-6">
                                        <Link
                                            href="/login"
                                            class="-mx-3 block rounded-lg px-3 py-2.5 text-base/7 font-semibold text-white hover:bg-white/5"
                                            >Log in</Link
                                        >
                                    </div>
                                </div>
                            </div>
                        </el-dialog-panel>
                    </div>
                </dialog>
            </el-dialog>
        </header>

        <div class="relative isolate px-6 pt-14 lg:px-8">
            <div
                aria-hidden="true"
                class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80"
            >
                <div
                    style="
                        clip-path: polygon(
                            74.1% 44.1%,
                            100% 61.6%,
                            97.5% 26.9%,
                            85.5% 0.1%,
                            80.7% 2%,
                            72.5% 32.5%,
                            60.2% 62.4%,
                            52.4% 68.1%,
                            47.5% 58.3%,
                            45.2% 34.5%,
                            27.5% 76.7%,
                            0.1% 64.9%,
                            17.9% 100%,
                            27.6% 76.8%,
                            76.1% 97.7%,
                            74.1% 44.1%
                        );
                    "
                    class="relative left-[calc(50%-11rem)] aspect-1155/678 w-144.5 -translate-x-1/2 rotate-30 bg-linear-to-tr from-[#ff80b5] to-[#9089fc] opacity-30 sm:left-[calc(50%-30rem)] sm:w-288.75"
                ></div>
            </div>
            <div class="mx-auto max-w-2xl py-32 sm:py-48 lg:py-56">
                <div class="text-center">
                    <h1
                        class="text-5xl font-semibold tracking-tight text-balance text-white sm:text-7xl"
                    >
                        Meetly
                    </h1>
                    <p
                        class="mt-8 text-lg font-medium text-pretty text-gray-400 sm:text-xl/8"
                    >
                        Anim aute id magna aliqua ad ad non deserunt sunt. Qui
                        irure qui lorem cupidatat commodo. Elit sunt amet fugiat
                        veniam occaecat.
                    </p>
                    <div class="mt-10 flex items-center justify-center gap-x-6">
                        <Link
                            href="/login"
                            class="rounded-md bg-indigo-500 px-3.5 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-indigo-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500"
                            >Log In</Link
                        >
                        <Link
                            href="/register"
                            class="text-sm/6 font-semibold text-white"
                            >Register <span aria-hidden="true">→</span></Link
                        >
                    </div>
                    
                    <!-- Development Button -->
                    <div class="mt-6 flex justify-center">
                        <Link
                            href="/dashboard"
                            class="inline-flex items-center px-4 py-2 text-xs font-medium text-gray-400 bg-gray-800/50 border border-gray-600 rounded-lg hover:bg-gray-700/50 hover:text-gray-300 transition-colors"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                            </svg>
                            Dev: Preview Dashboard
                        </Link>
                    </div>
                </div>
            </div>
            <div
                aria-hidden="true"
                class="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]"
            >
                <div
                    style="
                        clip-path: polygon(
                            74.1% 44.1%,
                            100% 61.6%,
                            97.5% 26.9%,
                            85.5% 0.1%,
                            80.7% 2%,
                            72.5% 32.5%,
                            60.2% 62.4%,
                            52.4% 68.1%,
                            47.5% 58.3%,
                            45.2% 34.5%,
                            27.5% 76.7%,
                            0.1% 64.9%,
                            17.9% 100%,
                            27.6% 76.8%,
                            76.1% 97.7%,
                            74.1% 44.1%
                        );
                    "
                    class="relative left-[calc(50%+3rem)] aspect-1155/678 w-144.5 -translate-x-1/2 bg-linear-to-tr from-[#ff80b5] to-[#9089fc] opacity-30 sm:left-[calc(50%+36rem)] sm:w-288.75"
                ></div>
            </div>
        </div>
    </div>
</template>
