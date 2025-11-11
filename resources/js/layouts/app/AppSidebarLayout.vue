<script setup lang="ts">
import { usePage, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PostModal from '@/components/PostModal.vue';

// Definisikan tipe User yang lebih tegas
interface User {
  id: number;
  name: string;
  email?: string;
}

const page = usePage();

// Gunakan computed property untuk mendapatkan user yang sudah diautentikasi.
// Ini lebih bersih dan reaktif. 'as User' memberitahu TypeScript untuk percaya
// bahwa user object ini pasti ada dan sesuai dengan tipenya.
const user = computed(() => page.props.auth.user as User);

const emit = defineEmits(['posted']);

const showPostModal = ref(false);

const openPostModal = () => {
  showPostModal.value = true;
};
const closePostModal = () => {
  showPostModal.value = false;
};

const handlePostCreated = () => {
  closePostModal();
  emit('posted');
};
</script>

<template>
  <div class="min-h-screen w-screen flex bg-gray-50">
    <aside class="fixed left-0 top-0 z-50 h-screen w-16 bg-white shadow-lg border-r border-gray-200">
      <div class="flex flex-col h-full py-4">
        <div class="flex items-center justify-center">
          <a href="/dashboard" class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold text-lg hover:bg-blue-700 transition-colors">M</a>
        </div>

        <div class="flex-1 flex items-center justify-center">
          <nav class="flex flex-col space-y-3 items-center">
            <div class="mt-2">
              <a :href="`/${user.name}`" class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-medium text-sm hover:bg-blue-600 transition-colors">
                {{ user.name.charAt(0).toUpperCase() }}
              </a>
            </div>

            <div>
              <button @click="openPostModal" class="w-10 h-10 bg-blue-500 hover:bg-blue-600 text-white rounded-xl flex items-center justify-center transition-colors shadow-lg" title="Create Post">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
              </button>
            </div>
          </nav>
        </div>

        <div class="flex items-center justify-center">
          <Link 
            href="/logout" 
            method="post" 
            as="button" 
            class="w-10 h-10 flex items-center justify-center text-gray-500 hover:bg-red-100 hover:text-red-600 rounded-lg transition-colors"
            title="Logout"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
            </svg>
          </Link>
        </div>
      </div>
    </aside>

    <!-- Margin kiri tetap ada karena user selalu login -->
    <main class="ml-16 flex-1 min-h-screen">
      <slot />
    </main>

    <!-- v-if="user" dihapus -->
    <PostModal 
      :is-open="showPostModal" 
      :user="user as any" 
      @close="closePostModal"
      @posted="handlePostCreated"
    />
  </div>
</template>