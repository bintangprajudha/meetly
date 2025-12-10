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
    <aside
      class="group fixed left-0 top-0 z-50 h-screen w-16 hover:w-48 bg-white border-r border-gray-200 shadow transition-all duration-300 ease-in-out">
      <div class="flex flex-col h-full py-4">

        <!-- Logo -->
        <div class="px-2 mb-6 flex items-center justify-center">
          <a href="/dashboard" class="w-10 h-10 flex items-center justify-center">
            <img src="logo.png" alt="Logo Meetly" class="w-100 h-100 object-contain" />
          </a>
        </div>

        <!-- Navigation Items -->
        <div class="flex-1 flex items-center justify-start">
          <nav class="flex flex-col space-y-8 w-full px-3">

            <!-- Home -->
            <div>
              <Link href="/dashboard" class="w-full flex items-center text-[#D84040] transition" title="Home">
                <svg class="w-6 h-6" fill="#D84040" viewBox="0 0 24 24">
                  <path d="M3 11L12 3l9 8v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-9z" />
                </svg>

                <!-- TEXT SAMPING -->
                <span class="ml-4 whitespace-nowrap hidden group-hover:block text-[#D84040] font-medium">
                  Home
                </span>
              </Link>
            </div>

            <!-- Profile -->
            <div>
              <a :href="`/${user.name}`" class="w-full flex items-center text-[#D84040] transition" title="Profile">
                <svg class="w-6 h-6" fill="none" stroke="#D84040" viewBox="0 0 24 24">
                  <circle cx="12" cy="7" r="4" stroke-width="2" />
                  <path d="M5.5 21a7.5 7.5 0 0 1 13 0" stroke-width="2" />
                </svg>

                <span class="ml-4 whitespace-nowrap hidden group-hover:block text-[#D84040] font-medium">
                  Profile
                </span>
              </a>
            </div>

            <!-- Bookmark -->
            <div>
              <Link href="/bookmarks" class="w-full flex items-center text-[#D84040] transition" title="Bookmarks">
                <svg class="w-6 h-6" fill="none" stroke="#D84040" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                </svg>

                <span class="ml-4 whitespace-nowrap hidden group-hover:block text-[#D84040] font-medium">
                  Bookmarks
                </span>
              </Link>
            </div>

            <!-- Create Post -->
            <div>
              <button @click="openPostModal" class="w-full flex items-center text-[#D84040] transition"
                title="Create Post">
                <svg class="w-6 h-6" fill="none" stroke="#D84040" viewBox="0 0 24 24">
                  <rect x="5" y="5" width="14" height="14" rx="2" stroke-width="2" />
                  <path stroke-linecap="round" stroke-width="2" d="M12 8v8m-4-4h8" />
                </svg>

                <span class="ml-4 whitespace-nowrap hidden group-hover:block text-[#D84040] font-medium">
                  Create Post
                </span>
              </button>
            </div>

            <!-- Messages -->
            <div>
              <Link href="/messages" class="w-full flex items-center text-[#D84040] transition" title="Messages">
                <svg class="w-6 h-6" fill="none" stroke="#D84040" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 10h8m-8 4h5m1 6l-4-3H6a2 2 0 01-2-2V6a2 2 0 012-2h12a2 2 0 012 2v9a2 2 0 01-2 2h-3l-4 3z" />
                </svg>

                <span class="ml-4 whitespace-nowrap hidden group-hover:block text-[#D84040] font-medium">
                  Messages
                </span>
              </Link>
            </div>

          </nav>
        </div>



        <!-- Logout -->
        <div class="px-4 mb-3 mt-auto">
          <Link href="/logout" method="post" as="button" class="w-full flex items-center text-[#D84040] transition"
            title="Logout">
            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="#D84040" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
              </path>
            </svg>
            <span class="ml-4 whitespace-nowrap hidden group-hover:block text-[#D84040] font-medium">
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

    <PostModal :is-open="showPostModal" :user="user as any" @close="closePostModal" @posted="handlePostCreated" />
  </div>
</template>
