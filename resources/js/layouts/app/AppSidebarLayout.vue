<script setup lang="ts">
import { usePage, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import PostModal from '@/components/PostModal.vue';

// Definisikan tipe User yang lebih tegas
interface User {
  id: number;
  name: string;
  email?: string;
  avatar?: string;
}

const page = usePage();

// Gunakan computed property untuk mendapatkan user yang sudah diautentikasi.
// Ini lebih bersih dan reaktif. 'as User' memberitahu TypeScript untuk percaya
// bahwa user object ini pasti ada dan sesuai dengan tipenya.
const user = computed(() => page.props.auth.user as User);

const emit = defineEmits(['posted']);

const showPostModal = ref(false);
const showProfileDropdown = ref(false);

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

const toggleProfileDropdown = () => {
  showProfileDropdown.value = !showProfileDropdown.value;
};

// Close dropdown when clicking outside
const profileDropdownRef = ref<HTMLElement | null>(null);

const handleClickOutside = (event: MouseEvent) => {
  if (profileDropdownRef.value && !profileDropdownRef.value.contains(event.target as Node)) {
    showProfileDropdown.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
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
            <Link href="/dashboard"
              class="w-full flex items-center text-[#D84040] transition group px-2 py-2 hover:bg-[#FDECEC] hover:rounded-xl"
              :class="{ 'bg-[#FDECEC] rounded-xl font-semibold': $page.url === '/dashboard' }" title="Home">
              <svg class="w-6 h-6" fill="none" stroke="#D84040" stroke-width="2" viewBox="0 0 24 24">
                <path d="M3 11L12 3l9 8v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-9z" stroke-linecap="round"
                  stroke-linejoin="round" />
              </svg>

              <span class="ml-3 whitespace-nowrap hidden group-hover:block text-[#D84040] font-medium">
                Home
              </span>
            </Link>

            <div class="relative w-full">
              <Link href="/notifications"
                class="w-full flex items-center text-[#D84040] transition group px-2 py-2 hover:bg-[#FDECEC] hover:rounded-xl"
                :class="{ 'bg-[#FDECEC] rounded-xl font-semibold': $page.url.startsWith('/notifications') }"
                title="Notifications">
                <!-- Icon Lonceng -->
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-[#D84040]" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14V9a6 6 0 00-5-5.917V3a1 1 0 10-2 0v.083A6 6 0 006 9v5c0 .386-.149.735-.395 1.005L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>

                <span class="ml-3 whitespace-nowrap hidden group-hover:block text-[#D84040] font-medium">
                  Notifications
                </span>

                <!-- Badge -->
                <!-- <span v-if="notificationCount > 0"
                  class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-500 rounded-full">
                  {{ notificationCount }}
                </span> -->
              </Link>
            </div>

            <!-- Chat -->
            <Link :href="`/chat/${user.id}`"
              class="w-full flex items-center text-[#D84040] transition group px-2 py-2 hover:bg-[#FDECEC] hover:rounded-xl"
              :class="{ 'bg-[#FDECEC] rounded-xl font-semibold': $page.url.startsWith('/chat') }" title="Messages">
              <svg class="w-6 h-6" fill="none" stroke="#D84040" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 10h8m-8 4h5m1 6l-4-3H6a2 2 0 01-2-2V6a2 2 0 012-2h12a2 2 0 012 2v9a2 2 0 01-2 2h-3l-4 3z" />
              </svg>

              <span class="ml-3 whitespace-nowrap hidden group-hover:block text-[#D84040] font-medium">
                Chat
              </span>
            </Link>

            <!-- Bookmarks -->
            <Link href="/bookmarks"
              class="w-full flex items-center text-[#D84040] transition group px-2 py-2 hover:bg-[#FDECEC] hover:rounded-xl"
              :class="{ 'bg-[#FDECEC] rounded-xl font-semibold': $page.url.startsWith('/bookmarks') }"
              title="Bookmarks">
              <svg class="w-6 h-6" fill="none" stroke="#D84040" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
              </svg>

              <span class="ml-3 whitespace-nowrap hidden group-hover:block text-[#D84040] font-medium">
                Bookmarks
              </span>
            </Link>

            <!-- Profile -->
            <a :href="`/${user.name}`"
              class="w-full flex items-center text-[#D84040] transition group px-2 py-2 hover:bg-[#FDECEC] hover:rounded-xl"
              :class="{ 'bg-[#FDECEC] rounded-xl font-semibold': $page.url === '/' + user.name }" title="Profile">
              <svg class="w-6 h-6" fill="none" stroke="#D84040" viewBox="0 0 24 24">
                <circle cx="12" cy="7" r="4" stroke-width="2" />
                <path d="M5.5 21a7.5 7.5 0 0 1 13 0" stroke-width="2" />
              </svg>

              <span class="ml-3 whitespace-nowrap hidden group-hover:block text-[#D84040] font-medium">
                Profile
              </span>
            </a>

            <!-- Create Post -->
            <button @click="openPostModal"
              class="w-full flex items-center text-[#D84040] transition group px-2 py-2 hover:bg-[#FDECEC] hover:rounded-xl"
              title="Create Post">
              <svg class="w-6 h-6" fill="none" stroke="#D84040" viewBox="0 0 24 24">
                <rect x="5" y="5" width="14" height="14" rx="2" stroke-width="2" />
                <path stroke-linecap="round" stroke-width="2" d="M12 8v8m-4-4h8" />
              </svg>

              <span class="ml-3 whitespace-nowrap hidden group-hover:block text-[#D84040] font-medium">
                Create Post
              </span>
            </button>

            

          </nav>
        </div>

        <!-- Profile with Dropdown -->
          <div class="px-3 mb-3 mt-auto relative" ref="profileDropdownRef">
            <button @click="toggleProfileDropdown"
              class="w-full flex items-center text-gray-700 transition group px-2 py-2 hover:bg-gray-100 hover:rounded-xl"
              title="Profile Menu">
              <!-- Avatar -->
              <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center flex-shrink-0 overflow-hidden">
                <img v-if="user.avatar" :src="user.avatar" :alt="user.name" class="w-full h-full object-cover" />
                <span v-else class="text-sm font-medium text-gray-600">{{ user.name.charAt(0).toUpperCase() }}</span>
              </div>

              <div class="ml-1 hidden group-hover:block flex-1 text-left">
                <p class="text-sm font-medium text-gray-900 truncate">{{ user.name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ user.email }}</p>
              </div>

              <!-- Three dots icon -->
              <svg class="w-5 h-5 ml-auto hidden group-hover:block flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="5" r="2" />
                <circle cx="12" cy="12" r="2" />
                <circle cx="12" cy="19" r="2" />
              </svg>
            </button>

            <!-- Dropdown Menu -->
            <transition
              enter-active-class="transition ease-out duration-100"
              enter-from-class="transform opacity-0 scale-95"
              enter-to-class="transform opacity-100 scale-100"
              leave-active-class="transition ease-in duration-75"
              leave-from-class="transform opacity-100 scale-100"
              leave-to-class="transform opacity-0 scale-95"
            >
              <div v-if="showProfileDropdown"
                class="absolute bottom-full left-4 mb-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                
                <!-- User Info Header -->
                <div class="px-4 py-3 border-b border-gray-100">
                  <p class="text-sm font-medium text-gray-900">{{ user.name }}</p>
                  <p class="text-xs text-gray-500">{{ user.email }}</p>
                </div>

                <!-- Menu Items -->
                <Link href="/settings" 
                  class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                  <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  Settings
                </Link>

                <Link href="/help" 
                  class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                  <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Get Help
                </Link>

                <!-- Divider -->
                <div class="border-t border-gray-100 my-1"></div>

                <!-- Logout -->
                <Link href="/logout" method="post" as="button"
                  class="w-full flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition text-left">
                  <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                    </path>
                  </svg>
                  Log out
                </Link>
              </div>
            </transition>
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