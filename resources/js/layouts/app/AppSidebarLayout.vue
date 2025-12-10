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
    <!-- Sidebar dengan hover effect -->
    <aside
      class="group fixed left-0 top-0 z-50 h-screen w-16 hover:w-48 bg-white shadow-lg border-r border-gray-200 transition-all duration-300 ease-in-out">
      <div class="flex flex-col h-full py-4">
        <!-- Logo -->
        <div class="px-2 mb-4 flex items-center justify-center">
          <a href="/dashboard"
            class="w-7 h-7 flex items-center justify-center flex-shrink-0 px-3 py-2.5 bg-blue-600 rounded-xl text-white hover:bg-blue-700 transition-colors">
            <div class="w-6 h-6 flex items-center justify-center font-bold text-sm flex-shrink-0">
              M
            </div>

          </a>
        </div>

        <!-- Navigation Items (Center) -->
        <div class="flex-1 flex items-center justify-start">
          <nav class="flex flex-col space-y-2 w-full px-2">
            <!-- Create Post -->
            <div>
              <button @click="openPostModal"
                class="w-full flex items-center px-3 py-2.5 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors"
                title="Create Post">
                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span class="ml-3 font-medium whitespace-nowrap hidden group-hover:block">
                  Create Post
                </span>
              </button>
            </div>

            <!-- Bookmark -->
            <div>
              <Link href="/bookmarks"
                class="w-full flex items-center px-3 py-2.5 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors"
                title="Bookmarks">
                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                </svg>
                <span class="ml-3 font-medium whitespace-nowrap hidden group-hover:block">
                  Bookmarks
                </span>
              </Link>
            </div>

            <div>
              <Link :href="`/chat/${user.id}`"
                class="w-full flex items-center px-3 py-2.5 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors"
                title="Messages">

                <!-- Icon Message -->
                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                  viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.77 9.77 0 01-4-.8l-4 1 1-3.6A7.7 7.7 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>

                <span class="ml-3 font-medium whitespace-nowrap hidden group-hover:block">
                  Messages
                </span>
              </Link>
            </div>
            

            <!-- Profile -->
            <div>
              <a :href="`/${user.name}`"
                class="w-full flex items-center px-3 py-2.5 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors"
                title="Profile">
                <div
                  class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center text-white font-medium text-xs flex-shrink-0">
                  {{ user.name.charAt(0).toUpperCase() }}
                </div>
                <span class="ml-3 font-medium whitespace-nowrap hidden group-hover:block">
                  Profile
                </span>
              </a>
            </div>
          </nav>
        </div>

        <!-- Logout (Bottom) -->
        <div class="px-2">
          <Link href="/logout" method="post" as="button"
            class="w-full flex items-center px-3 py-2.5 text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-lg transition-colors"
            title="Logout">
            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
            </svg>
            <span class="ml-3 font-medium whitespace-nowrap hidden group-hover:block">
              Logout
            </span>
          </Link>
        </div>
      </div>
    </aside>

    <!-- Main content dengan dynamic margin -->
    <main class="ml-16 flex-1 min-h-screen transition-all duration-300 ease-in-out">
      <slot />
    </main>

    <!-- Post Modal -->
    <PostModal :is-open="showPostModal" :user="user as any" @close="closePostModal" @posted="handlePostCreated" />
  </div>
</template>