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

  <!-- Sidebar -->
  <aside class="group fixed left-0 top-0 z-50 h-screen w-16 hover:w-48 bg-[#D84040] shadow-xl transition-all duration-300 ease-in-out">
    <div class="flex flex-col h-full py-4">

      <!-- Logo -->
      <div class="px-2 mb-6 flex items-center justify-center">
        <a href="/dashboard" class="w-10 h-10 flex items-center justify-center bg-[#F8F2DE] rounded-xl shadow text-[#D84040] font-bold hover:scale-105 transition">
          <img src="Group 1.png" alt="Logo Meetly" class="w-25 h-auto" />
        </a>
      </div>

      <!-- Navigation Items -->
      <div class="flex-1 flex items-center justify-start">
        <nav class="flex flex-col space-y-4 w-full px-2">

          <!-- Create Post -->
          <div>
            <button 
              @click="openPostModal" 
              class="w-full flex items-center px-3 py-2.5 text-white rounded-lg transition-colors"
              title="Create Post"
            >
              <svg class="w-8 h-8 p-1.5 bg-[#F8F2DE] rounded-xl text-[#D84040] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
              <span class="ml-3 font-medium whitespace-nowrap hidden group-hover:block text-white">
                Create Post
              </span>
            </button>
          </div>

          <!-- Bookmark -->
          <div>
            <Link 
              href="/bookmarks" 
              class="w-full flex items-center px-3 py-2.5 text-white rounded-lg transition-colors"
              title="Bookmarks"
            >
              <svg class="w-8 h-8 p-1.5 bg-[#F8F2DE] rounded-xl text-[#D84040] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
              </svg>
              <span class="ml-3 font-medium whitespace-nowrap hidden group-hover:block text-white">
                Bookmarks
              </span>
            </Link>
          </div>

          <!-- Profile -->
          <div>
            <a 
              :href="`/${user.name}`" 
              class="w-full flex items-center px-3 py-2.5 text-white rounded-lg transition-colors"
              title="Profile"
            >
              <div class="w-8 h-8 bg-[#F8F2DE] rounded-xl flex items-center justify-center text-[#D84040] font-bold text-base flex-shrink-0">
                {{ user.name.charAt(0).toUpperCase() }}
              </div>
              <span class="ml-3 font-medium whitespace-nowrap hidden group-hover:block text-white">
                Profile
              </span>
            </a>
          </div>

        </nav>
      </div>

      <!-- Logout -->
      <div class="px-2 mb-2">
        <Link 
          href="/logout" 
          method="post" 
          as="button" 
          class="w-full flex items-center px-3 py-2.5 text-white rounded-lg transition-colors"
          title="Logout"
        >
          <svg class="w-8 h-8 p-1.5 bg-[#F8F2DE] rounded-xl text-[#D84040] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
          </svg>
          <span class="ml-3 font-medium whitespace-nowrap hidden group-hover:block text-white">
            Logout
          </span>
        </Link>
      </div>

    </div>
  </aside>

  <!-- Main Content -->
  <main class="ml-16 flex-1 min-h-screen transition-all duration-300 ease-in-out">
    <slot />
  </main>

  <PostModal 
    :is-open="showPostModal" 
    :user="user as any" 
    @close="closePostModal"
    @posted="handlePostCreated"
  />
</div>
</template>
