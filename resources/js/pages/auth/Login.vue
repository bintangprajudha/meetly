<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthLayout title="Login">
        <div class="rounded-lg bg-white px-6 py-8 shadow-lg dark:bg-gray-800">
            <!-- Header -->
            <div class="mb-8 text-center">
                <Link href="/" class="mb-4 inline-block">
                    <img
                        src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=600"
                        alt="Meetly"
                        class="mx-auto h-12 w-auto"
                    />
                </Link>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Welcome back
                </h2>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Sign in to your account to continue
                </p>
            </div>

            <!-- Login Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Email -->
                <div>
                    <label
                        for="email"
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                    >
                        Email address
                    </label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autocomplete="email"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 placeholder-gray-400 shadow-sm focus:border-transparent focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                        placeholder="Enter your email"
                    />
                    <div
                        v-if="form.errors.email"
                        class="mt-2 text-sm text-red-600"
                    >
                        {{ form.errors.email }}
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label
                        for="password"
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                    >
                        Password
                    </label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                        autocomplete="current-password"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 placeholder-gray-400 shadow-sm focus:border-transparent focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                        placeholder="Enter your password"
                    />
                    <div
                        v-if="form.errors.password"
                        class="mt-2 text-sm text-red-600"
                    >
                        {{ form.errors.password }}
                    </div>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input
                            id="remember"
                            v-model="form.remember"
                            type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        />
                        <label
                            for="remember"
                            class="ml-2 block text-sm text-gray-700 dark:text-gray-300"
                        >
                            Remember me
                        </label>
                    </div>
                    <Link
                        href="#"
                        class="text-sm font-medium text-blue-600 hover:text-blue-500"
                    >
                        Forgot password?
                    </Link>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="flex w-full justify-center rounded-lg border border-transparent bg-blue-600 px-4 py-3 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <svg
                        v-if="form.processing"
                        class="mr-3 -ml-1 h-5 w-5 animate-spin text-white"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"
                        ></circle>
                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                        ></path>
                    </svg>
                    <span v-if="form.processing">Signing in...</span>
                    <span v-else>Sign in</span>
                </button>
            </form>

            <!-- Divider -->
            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div
                            class="w-full border-t border-gray-300 dark:border-gray-600"
                        ></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span
                            class="bg-white px-2 text-gray-500 dark:bg-gray-800 dark:text-gray-400"
                        >
                            New to Meetly?
                        </span>
                    </div>
                </div>
            </div>

            <!-- Register Link -->
            <div class="mt-6 text-center">
                <Link
                    href="/register"
                    class="font-medium text-blue-600 transition-colors hover:text-blue-500"
                >
                    Create an account
                    <span aria-hidden="true"> →</span>
                </Link>
            </div>
        </div>
    </AuthLayout>
</template>
