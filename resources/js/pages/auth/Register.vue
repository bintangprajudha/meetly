<script setup lang="ts">
import { ref } from 'vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
<AuthLayout>

    <!-- CARD FORM BESAR -->
    <div class="bg-white rounded-xl shadow-lg p-12 w-full max-w-2xl mx-auto">

        <h2 class="text-2xl font-bold text-gray-900">Daftarkan Akunmu</h2>
        <p class="mt-1 text-gray-600 mb-6">
            Lengkapi data di bawah ini untuk mendaftarkan akunmu
        </p>

        <form @submit.prevent="submit" class="space-y-4">

            <!-- NAMA DEPAN / BELAKANG -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm text-gray-700">Nama Depan</label>
                    <input v-model="form.first_name" type="text" required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-black shadow-sm focus:border-red-500 focus:ring-red-500"
                        placeholder="Nama Depan">
                    <div v-if="form.errors.first_name" class="text-sm text-red-600">{{ form.errors.first_name }}</div>
                </div>

                <div>
                    <label class="text-sm text-gray-700">Nama Belakang</label>
                    <input v-model="form.last_name" type="text" required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-black shadow-sm focus:border-red-500 focus:ring-red-500"
                        placeholder="Nama Belakang">
                    <div v-if="form.errors.last_name" class="text-sm text-red-600">{{ form.errors.last_name }}</div>
                </div>
            </div>

            <!-- EMAIL -->
            <div>
                <label class="text-sm text-gray-700">Email</label>
                <input v-model="form.email" type="email" required
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-black shadow-sm focus:border-red-500 focus:ring-red-500"
                    placeholder="email@example.com">
                <div v-if="form.errors.email" class="text-sm text-red-600">{{ form.errors.email }}</div>
            </div>

            <!-- TELEPON -->
            <div>
                <label class="text-sm text-gray-700">Nomor Telepon</label>
                <input v-model="form.phone" type="tel"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-black shadow-sm focus:border-red-500 focus:ring-red-500"
                    placeholder="+62 81234567890">
                <div v-if="form.errors.phone" class="text-sm text-red-600">{{ form.errors.phone }}</div>
            </div>

            <!-- PASSWORD -->
            <div>
                <label class="text-sm text-gray-700">Password</label>
                <div class="relative">
                    <input :type="showPassword ? 'text' : 'password'" v-model="form.password" required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 pr-10 text-black shadow-sm focus:border-red-500 focus:ring-red-500"
                        placeholder="Password">

                    <button type="button" @click="showPassword = !showPassword"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500">
                        <svg v-if="!showPassword" class="w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke="currentColor" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943
                                   9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7
                                   a9.963 9.963 0 012.14-3.506M6.18 6.18A9.963 9.963 0 0112 5c4.478 
                                   0 8.268 2.943 9.542 7a9.97 9.97 0 01-1.94 3.227M3 3l18 18" />
                        </svg>
                    </button>
                </div>
                <div v-if="form.errors.password" class="text-sm text-red-600">{{ form.errors.password }}</div>
            </div>

            <!-- CONFIRM PASSWORD -->
            <div>
                <label class="text-sm text-gray-700">Konfirmasi Password</label>
                <input :type="showPassword ? 'text' : 'password'" v-model="form.password_confirmation" required
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-black shadow-sm focus:border-red-500 focus:ring-red-500"
                    placeholder="Konfirmasi Password">
                <div v-if="form.errors.password_confirmation" class="text-sm text-red-600">{{ form.errors.password_confirmation }}</div>
            </div>

            <!-- TERMS -->
            <div class="flex items-start gap-2">
                <input type="checkbox" v-model="form.terms" class="h-4 w-4 text-red-600">
                <label class="text-sm text-gray-700">
                    Dengan mendaftar, saya menyetujui
                    <a href="#" class="text-red-600 underline">Syarat & Ketentuan</a>
                    dan
                    <a href="#" class="text-red-600 underline">Kebijakan Privasi</a>.
                </label>
            </div>

            <button type="submit" :disabled="form.processing"
                class="w-full rounded-lg bg-[#e54c4c] px-4 py-3 text-white font-medium shadow hover:bg-red-700">
                <span v-if="form.processing">Mendaftarkan...</span>
                <span v-else>Register</span>
            </button>
        </form>

        <!-- LINK LOGIN -->
        <div class="mt-6 text-center">
            <div class="flex items-center">
                <div class="flex-1 border-t border-gray-300"></div>
                <span class="px-3 text-gray-500 text-sm">Sudah punya akun?</span>
                <div class="flex-1 border-t border-gray-300"></div>
            </div>

            <Link href="/login" class="mt-4 inline-block text-sm font-medium text-red-600 hover:text-red-500">
                Masuk ke akun Anda →
            </Link>
        </div>

    </div>

</AuthLayout>
</template>
