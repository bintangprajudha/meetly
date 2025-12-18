<script setup lang="ts">
import { usePage, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import PostModal from '@/components/PostModal.vue';
import axios from 'axios';

interface User {
  id: number;
  name: string;
  email?: string;
  avatar?: string;
}

interface Chat {
  user: User;
  last_message: string | null;
  last_message_at: string | null;
  is_read: boolean;
  unread_count?: number;
}

const page = usePage();
const user = computed(() => (page.props as any).auth?.user as User);
const unreadNotifications = computed(() => (page.props as any).unreadNotifications || 0);

// State untuk unread messages
const chats = ref<Chat[]>([]);
const loadingChats = ref(false);

// Computed: Jumlah user unik yang mengirim pesan belum dibaca
const unreadMessages = computed(() => {
  return chats.value.filter(chat => chat.unread_count && chat.unread_count > 0).length;
});

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

const profileDropdownRef = ref<HTMLElement | null>(null);

const handleClickOutside = (event: MouseEvent) => {
  if (profileDropdownRef.value && !profileDropdownRef.value.contains(event.target as Node)) {
    showProfileDropdown.value = false;
  }
};

// Fetch unread messages count
const fetchUnreadMessages = async () => {
  if (loadingChats.value) return;
  
  loadingChats.value = true;
  try {
    const res = await axios.get('/api/messages');
    chats.value = res.data.filter((chat: Chat) => chat.last_message !== null);
  } catch (e) {
    console.error("Failed to fetch unread messages:", e);
  } finally {
    loadingChats.value = false;
  }
};

// Setup real-time listener
const setupRealtimeListener = () => {
  if (window.Echo && user.value?.id) {
    window.Echo.private(`chat.${user.value.id}`)
      .listen('NewMessage', () => {
        fetchUnreadMessages();
      })
      .listen('MessageRead', () => {
        fetchUnreadMessages();
      });
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  
  // Fetch unread messages on mount
  fetchUnreadMessages();
  
  // Setup real-time updates
  setupRealtimeListener();
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});

// Logout Modal
const showLogoutModal = ref(false);

const handleLogout = (event: Event) => {
    event.preventDefault();
    event.stopPropagation();
    showProfileDropdown.value = false; // Close dropdown
    showLogoutModal.value = true;
};

const closeLogoutModal = () => {
    showLogoutModal.value = false;
};

const confirmLogout = () => {
    // Use Inertia router for logout
    router.post('/logout', {}, {
        preserveState: false,
        preserveScroll: false,
        onStart: () => {
            showLogoutModal.value = false;
        },
        onError: (errors) => {
            console.error('Logout failed:', errors);
            alert('Failed to logout. Please try again.');
        }
    });
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
            <Link href="/dashboard"
              class="w-full flex items-center text-[#D84040] transition group px-2 py-2 hover:bg-[#FDECEC] hover:rounded-xl"
              :class="{ 'bg-[#FDECEC] rounded-xl font-semibold': $page.url === '/dashboard' }" title="Home">
              <svg class="w-6 h-6" fill="none" stroke="#D84040" stroke-width="2" viewBox="0 0 24 24">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke-linecap="round" stroke-linejoin="round" />
                <polyline points="9 22 9 12 15 12 15 22" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              <span class="ml-3 whitespace-nowrap hidden group-hover:block text-[#D84040] font-medium">
                Home
              </span>
            </Link>

            <!-- Explore -->
            <Link href="/explore"
              class="w-full flex items-center text-[#D84040] transition group px-2 py-2 hover:bg-[#FDECEC] hover:rounded-xl"
              :class="{ 'bg-[#FDECEC] rounded-xl font-semibold': $page.url.startsWith('/explore') }" title="Explore">
              <svg class="w-6 h-6" fill="none" stroke="#D84040" viewBox="0 0 24 24" stroke-width="2">
                <circle cx="10.5" cy="10.5" r="7.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M21 21l-5.2-5.2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              <span class="ml-3 whitespace-nowrap hidden group-hover:block text-[#D84040] font-medium">
                Explore
              </span>
            </Link>

            <!-- Notifications with Badge -->
            <div class="relative w-full">
              <Link href="/notifications"
                class="w-full flex items-center text-[#D84040] transition group px-2 py-2 hover:bg-[#FDECEC] hover:rounded-xl"
                :class="{ 'bg-[#FDECEC] rounded-xl font-semibold': $page.url.startsWith('/notifications') }"
                title="Notifications">
                <div class="relative">
                  <svg class="w-6 h-6" fill="none" stroke="#D84040" viewBox="0 0 24 24" stroke-width="2">
                    <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M13.73 21a2 2 0 01-3.46 0" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                  <!-- Badge for collapsed sidebar -->
                  <span v-if="unreadNotifications > 0" 
                    class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-[10px] font-bold rounded-full min-w-[16px] h-4 flex items-center justify-center px-1">
                    {{ unreadNotifications > 99 ? '99+' : unreadNotifications }}
                  </span>
                </div>
                <span class="ml-3 whitespace-nowrap hidden group-hover:flex items-center text-[#D84040] font-medium flex-1">
                  Notifications
                </span>
              </Link>
            </div>

            <!-- Messages with Badge (User Count) -->
            <div class="relative w-full">
              <Link href="/chat"
                class="w-full flex items-center text-[#D84040] transition group px-2 py-2 hover:bg-[#FDECEC] hover:rounded-xl"
                :class="{ 'bg-[#FDECEC] rounded-xl font-semibold': $page.url.startsWith('/chat') }" title="Messages">
                <div class="relative">
                  <svg class="w-6 h-6" fill="none" stroke="#D84040" viewBox="0 0 24 24" stroke-width="2">
                      <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 13.8214 2.48697 15.5291 3.33782 17L2.5 21.5L7 20.6622C8.47087 21.513 10.1786 22 12 22Z" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                  <!-- Badge showing number of users with unread messages -->
                  <span v-if="unreadMessages > 0" 
                    class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-[10px] font-bold rounded-full min-w-[16px] h-4 flex items-center justify-center px-1">
                    {{ unreadMessages > 99 ? '99+' : unreadMessages }}
                  </span>
                </div>
                <span class="ml-3 whitespace-nowrap hidden group-hover:flex items-center text-[#D84040] font-medium flex-1">
                  Messages
                </span>
              </Link>
            </div>

            <!-- Bookmarks -->
            <Link href="/bookmarks"
              class="w-full flex items-center text-[#D84040] transition group px-2 py-2 hover:bg-[#FDECEC] hover:rounded-xl"
              :class="{ 'bg-[#FDECEC] rounded-xl font-semibold': $page.url.startsWith('/bookmarks') }"
              title="Bookmarks">
              <svg class="w-6 h-6" fill="none" stroke="#D84040" viewBox="0 0 24 24" stroke-width="2">
                <path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              <span class="ml-3 whitespace-nowrap hidden group-hover:block text-[#D84040] font-medium">
                Bookmarks
              </span>
            </Link>

            <!-- Profile -->
            <a :href="`/${user.name}`"
              class="w-full flex items-center text-[#D84040] transition group px-2 py-2 hover:bg-[#FDECEC] hover:rounded-xl"
              :class="{ 'bg-[#FDECEC] rounded-xl font-semibold': $page.url === '/' + user.name }" title="Profile">
              <svg class="w-6 h-6" fill="none" stroke="#D84040" viewBox="0 0 24 24" stroke-width="2">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" stroke-linecap="round" stroke-linejoin="round" />
                <circle cx="12" cy="7" r="4" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              <span class="ml-3 whitespace-nowrap hidden group-hover:block text-[#D84040] font-medium">
                Profile
              </span>
            </a>

            <!-- Create Post -->
            <button @click="openPostModal"
              class="w-full flex items-center text-[#D84040] transition group px-2 py-2 hover:bg-[#FDECEC] hover:rounded-xl"
              title="Create Post">
              <svg class="w-6 h-6" fill="none" stroke="#D84040" viewBox="0 0 24 24" stroke-width="2">
                <path d="M12 5v14m-7-7h14" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              <span class="ml-3 whitespace-nowrap hidden group-hover:block text-[#D84040] font-medium">
                Post
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
            <div
              class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center flex-shrink-0 overflow-hidden">
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
          <transition enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95">
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
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Settings
              </Link>

              <!-- Divider -->
              <div class="border-t border-gray-100 my-1"></div>

              <!-- Logout Button (Updated) -->
              <button 
                @click="handleLogout"
                class="w-full flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition text-left">
                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                  </path>
                </svg>
                Log out
              </button>
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

    <!-- Logout Confirmation Modal -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showLogoutModal"
                class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
                @click.self="closeLogoutModal"
            >
                <Transition
                    enter-active-class="transition-all duration-200"
                    enter-from-class="opacity-0 scale-95"
                    enter-to-class="opacity-100 scale-100"
                    leave-active-class="transition-all duration-200"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95"
                >
                    <div
                        v-if="showLogoutModal"
                        class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden"
                    >
                        <!-- Header -->
                        <div class="bg-gradient-to-r from-red-500 to-red-600 p-6">
                            <div class="flex items-center justify-center mb-4">
                                <div class="bg-white/20 backdrop-blur-sm rounded-full p-3">
                                    <svg 
                                        class="w-8 h-8 text-white" 
                                        fill="none" 
                                        stroke="currentColor" 
                                        viewBox="0 0 24 24"
                                    >
                                        <path 
                                            stroke-linecap="round" 
                                            stroke-linejoin="round" 
                                            stroke-width="2"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                                        />
                                    </svg>
                                </div>
                            </div>
                            <h3 class="text-2xl font-bold text-white text-center">
                                Confirm Logout
                            </h3>
                        </div>

                        <!-- Body -->
                        <div class="p-6">
                            <p class="text-gray-600 text-center mb-6">
                                Are you sure you want to log out? You'll need to sign in again to access your account.
                            </p>

                            <!-- Actions -->
                            <div class="flex gap-3">
                                <button
                                    @click="closeLogoutModal"
                                    class="flex-1 px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition-colors duration-200"
                                >
                                    Cancel
                                </button>
                                <button
                                    @click="confirmLogout"
                                    class="flex-1 px-4 py-3 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl"
                                >
                                    Log Out
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
  </div>
</template>