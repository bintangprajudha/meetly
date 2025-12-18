<script setup lang="ts">
import { ref } from 'vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthLayout>
        <div class="w-full max-w-2xl bg-white rounded-2xl shadow-xl p-14">

    <h2 class="text-4xl font-bold text-gray-900">Welcome Back</h2>
    <p class="text-gray-600 text-lg mb-10">
        Sign in to continue to your account
    </p>

    <form @submit.prevent="submit" class="space-y-7">

        <!-- EMAIL -->
        <div>
            <label class="text-base text-gray-700">Email</label>
            <input
                v-model="form.email"
                type="email"
                required
                class="w-full rounded-lg border border-gray-300 px-5 py-4 text-black text-lg shadow-sm
                       focus:border-red-500 focus:ring-red-500"
                placeholder="email@example.com"
            />
            <div v-if="form.errors.email" class="text-sm text-red-600 mt-1">
                {{ form.errors.email }}
            </div>
        </div>

        <!-- PASSWORD -->
        <div>
            <label class="text-base text-gray-700">Password</label>
            <div class="relative">
                <input
                    v-model="form.password"
                    :type="showPassword ? 'text' : 'password'"
                    required
                    class="w-full rounded-lg border border-gray-300 px-5 py-4 text-black pr-12 text-lg shadow-sm
                           focus:border-red-500 focus:ring-red-500"
                    placeholder="Password"
                />

                <!-- TOGGLE ICON -->
                <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500"
                >
                    <svg v-if="!showPassword" class="w-6 h-6" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke="currentColor" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943
                            9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>

                    <svg v-else class="w-6 h-6" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-width="2"
                            d="M3 3l18 18M10.73 10.73a3 3 0 104.24 4.24" />
                        <path stroke="currentColor" stroke-width="2"
                            d="M6.18 6.18A9.963 9.963 0 0112 5c4.48 0 8.27 2.94 
                            9.54 7a9.97 9.97 0 01-1.94 3.23" />
                    </svg>
                </button>
            </div>

            <div v-if="form.errors.password" class="text-sm text-red-600 mt-1">
                {{ form.errors.password }}
            </div>
        </div>

        <!-- REMEMBER + FORGOT -->
        <div class="flex items-center justify-between text-lg">
            <label class="flex items-center">
                <input
                    v-model="form.remember"
                    type="checkbox"
                    class="h-5 w-5 rounded border-gray-300 text-red-600 focus:ring-red-500"
                />
                <span class="ml-2 text-gray-700">Remember Me</span>
            </label>

            <Link href="#" class="text-red-600 hover:text-red-500">
                Forgot Password?
            </Link>
        </div>

        <!-- LOGIN BUTTON -->
        <button
            type="submit"
            :disabled="form.processing"
            class="w-full rounded-lg bg-[#e54c4c] px-5 py-4 text-lg text-white font-semibold shadow
                   hover:bg-red-700 disabled:opacity-50"
        >
            <span v-if="form.processing">Processing...</span>
            <span v-else>Login</span>
        </button>
    </form>

    <!-- SOCIAL -->
    <div class="mt-12 text-center">
        <p class="text-base text-gray-500 mb-4">Or continue with</p>

        <div class="flex justify-center space-x-5">
            <button class="w-12 h-12 rounded-full bg-white border shadow flex items-center justify-center text-blue-600 text-xl">F</button>
            <button class="w-12 h-12 rounded-full bg-white border shadow flex items-center justify-center text-red-500 text-xl">G</button>
            <button class="w-12 h-12 rounded-full bg-white border shadow flex items-center justify-center text-blue-800 text-xl">in</button>
        </div>

        <p class="mt-8 text-lg text-gray-600">
            Don't have an account?
            <Link href="/register" class="text-red-600 font-medium hover:text-red-700">
                Register now
            </Link>
        </p>
    </div>

</div>
    </AuthLayout>
</template>