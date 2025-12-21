<script setup lang="ts">
import PostModal from '@/components/PostModal.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, onUnmounted, ref } from 'vue';

interface User {
    id: number;
    name: string;
    email?: string;
    avatar?: string | null;
    is_admin?: boolean;
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
const unreadNotifications = computed(
    () => (page.props as any).unreadNotifications || 0,
);

// State untuk unread messages
const chats = ref<Chat[]>([]);
const loadingChats = ref(false);

// Mobile menu state
const showMobileMenu = ref(false);

// Computed: Jumlah user unik yang mengirim pesan belum dibaca
const unreadMessages = computed(() => {
    return chats.value.filter(
        (chat) => chat.unread_count && chat.unread_count > 0,
    ).length;
});

// Helper functions for avatar
const getInitials = (name: string) => {
    if (!name) return 'U';
    return name
        .split(' ')
        .map((word) => word.charAt(0))
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

const getAvatarColor = (name: string) => {
    if (!name) return '#6B7280';
    const colors = [
        '#EF4444',
        '#F97316',
        '#F59E0B',
        '#EAB308',
        '#84CC16',
        '#22C55E',
        '#10B981',
        '#14B8A6',
        '#06B6D4',
        '#0EA5E9',
        '#3B82F6',
        '#6366F1',
        '#8B5CF6',
        '#A855F7',
        '#D946EF',
        '#EC4899',
    ];
    const hash = name
        .split('')
        .reduce((acc, char) => acc + char.charCodeAt(0), 0);
    return colors[hash % colors.length];
};

// Computed: Get user avatar or initial
const userAvatar = computed(() => {
    if (user.value?.avatar) {
        return user.value.avatar.startsWith('http')
            ? user.value.avatar
            : `/storage/${user.value.avatar}`;
    }
    return null;
});

const userInitials = computed(() => {
    return getInitials(user.value?.name || '');
});

const userAvatarColor = computed(() => {
    return getAvatarColor(user.value?.name || '');
});

const emit = defineEmits(['posted']);

const showPostModal = ref(false);
const showProfileDropdown = ref(false);

const openPostModal = () => {
    showPostModal.value = true;
    showMobileMenu.value = false;
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

const toggleMobileMenu = () => {
    showMobileMenu.value = !showMobileMenu.value;
};

const closeMobileMenu = () => {
    showMobileMenu.value = false;
};

const profileDropdownRef = ref<HTMLElement | null>(null);

const handleClickOutside = (event: MouseEvent) => {
    if (
        profileDropdownRef.value &&
        !profileDropdownRef.value.contains(event.target as Node)
    ) {
        showProfileDropdown.value = false;
    }
};

// Fetch unread messages count
const fetchUnreadMessages = async () => {
    if (loadingChats.value) return;

    loadingChats.value = true;
    try {
        const res = await axios.get('/api/messages');
        chats.value = res.data.filter(
            (chat: Chat) => chat.last_message !== null,
        );
    } catch (e) {
        console.error('Failed to fetch unread messages:', e);
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
    showProfileDropdown.value = false;
    showMobileMenu.value = false;
    showLogoutModal.value = true;
};

const closeLogoutModal = () => {
    showLogoutModal.value = false;
};

const confirmLogout = () => {
    router.post(
        '/logout',
        {},
        {
            preserveState: false,
            preserveScroll: false,
            onStart: () => {
                showLogoutModal.value = false;
            },
            onError: (errors) => {
                console.error('Logout failed:', errors);
                alert('Failed to logout. Please try again.');
            },
        },
    );
};
</script>

<template>
    <div class="flex min-h-screen w-screen bg-gray-50">
        <!-- Desktop Sidebar -->
        <aside
            class="group fixed top-0 left-0 z-[60] hidden h-screen w-16 border-r border-gray-200 bg-white shadow transition-all duration-300 ease-in-out hover:w-64 lg:block">
            <div class="flex h-full flex-col py-4">
                <!-- Logo -->
                <div class="mb-6 flex items-center justify-center px-2">
                    <a href="/dashboard" class="flex h-10 w-10 items-center justify-center">
                        <img src="/logo.png" alt="Logo Meetly" class="h-full w-full object-contain" />
                    </a>
                </div>

                <!-- Navigation Items -->
                <div class="flex flex-1 items-center justify-start">
                    <nav class="flex w-full flex-col space-y-6 px-3">
                        <!-- Home -->
                        <Link href="/dashboard"
                            class="group flex w-full items-center px-2 py-2 text-[#D84040] transition hover:rounded-xl hover:bg-[#FDECEC]"
                            :class="{
                                'rounded-xl bg-[#FDECEC] font-semibold':
                                    $page.url === '/dashboard',
                            }" title="Home">
                            <svg class="h-6 w-6 flex-shrink-0" fill="none" stroke="#D84040" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <polyline points="9 22 9 12 15 12 15 22" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                            <span class="ml-3 hidden font-medium whitespace-nowrap text-[#D84040] group-hover:block">
                                Home
                            </span>
                        </Link>

                        <!-- Explore -->
                        <Link href="/explore"
                            class="group flex w-full items-center px-2 py-2 text-[#D84040] transition hover:rounded-xl hover:bg-[#FDECEC]"
                            :class="{
                                'rounded-xl bg-[#FDECEC] font-semibold':
                                    $page.url.startsWith('/explore'),
                            }" title="Explore">
                            <svg class="h-6 w-6 flex-shrink-0" fill="none" stroke="#D84040" viewBox="0 0 24 24" stroke-width="2">
                                <circle cx="10.5" cy="10.5" r="7.5" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M21 21l-5.2-5.2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span class="ml-3 hidden font-medium whitespace-nowrap text-[#D84040] group-hover:block">
                                Explore
                            </span>
                        </Link>

                        <!-- Notifications with Badge -->
                        <div class="relative w-full">
                            <Link href="/notifications"
                                class="group flex w-full items-center px-2 py-2 text-[#D84040] transition hover:rounded-xl hover:bg-[#FDECEC]"
                                :class="{
                                    'rounded-xl bg-[#FDECEC] font-semibold':
                                        $page.url.startsWith('/notifications'),
                                }" title="Notifications">
                                <div class="relative flex-shrink-0">
                                    <svg class="h-6 w-6" fill="none" stroke="#D84040" viewBox="0 0 24 24"
                                        stroke-width="2">
                                        <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                        <path d="M13.73 21a2 2 0 01-3.46 0" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                    <span v-if="unreadNotifications > 0"
                                        class="absolute -top-1.5 -right-1.5 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">
                                        {{ unreadNotifications > 99 ? '99+' : unreadNotifications }}
                                    </span>
                                </div>
                                <span
                                    class="ml-3 hidden flex-1 items-center font-medium whitespace-nowrap text-[#D84040] group-hover:flex">
                                    Notifications
                                </span>
                            </Link>
                        </div>

                        <!-- Messages with Badge -->
                        <div class="relative w-full">
                            <Link href="/chat"
                                class="group flex w-full items-center px-2 py-2 text-[#D84040] transition hover:rounded-xl hover:bg-[#FDECEC]"
                                :class="{
                                    'rounded-xl bg-[#FDECEC] font-semibold':
                                        $page.url.startsWith('/chat'),
                                }" title="Messages">
                                <div class="relative flex-shrink-0">
                                    <svg class="h-6 w-6" fill="none" stroke="#D84040" viewBox="0 0 24 24"
                                        stroke-width="2">
                                        <path
                                            d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 13.8214 2.48697 15.5291 3.33782 17L2.5 21.5L7 20.6622C8.47087 21.513 10.1786 22 12 22Z"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span v-if="unreadMessages > 0"
                                        class="absolute -top-1.5 -right-1.5 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">
                                        {{ unreadMessages > 99 ? '99+' : unreadMessages }}
                                    </span>
                                </div>
                                <span
                                    class="ml-3 hidden flex-1 items-center font-medium whitespace-nowrap text-[#D84040] group-hover:flex">
                                    Messages
                                </span>
                            </Link>
                        </div>

                        <!-- Bookmarks -->
                        <Link href="/bookmarks"
                            class="group flex w-full items-center px-2 py-2 text-[#D84040] transition hover:rounded-xl hover:bg-[#FDECEC]"
                            :class="{
                                'rounded-xl bg-[#FDECEC] font-semibold':
                                    $page.url.startsWith('/bookmarks'),
                            }" title="Bookmarks">
                            <svg class="h-6 w-6 flex-shrink-0" fill="none" stroke="#D84040" viewBox="0 0 24 24" stroke-width="2">
                                <path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                            <span class="ml-3 hidden font-medium whitespace-nowrap text-[#D84040] group-hover:block">
                                Bookmarks
                            </span>
                        </Link>

                        <!-- Admin Dashboard -->
                        <Link v-if="user.is_admin" href="/admin/dashboard"
                            class="group flex w-full items-center px-2 py-2 text-[#D84040] transition hover:rounded-xl hover:bg-[#FDECEC]"
                            :class="{
                                'rounded-xl bg-[#FDECEC] font-semibold':
                                    $page.url.startsWith('/admin'),
                            }"
                            title="Admin"
                        >
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                stroke="#D84040"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                            >
                                <path
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span
                                class="ml-3 hidden font-medium whitespace-nowrap text-[#D84040] group-hover:block"
                            >
                                Admin
                            </span>
                        </Link>

                        <!-- Profile -->
                        <a :href="`/@${user.name}`"
                            class="group flex w-full items-center px-2 py-2 text-[#D84040] transition hover:rounded-xl hover:bg-[#FDECEC]"
                            :class="{
                                'rounded-xl bg-[#FDECEC] font-semibold':
                                    $page.url === '/' + user.name,
                            }" title="Profile">
                            <svg class="h-6 w-6 flex-shrink-0" fill="none" stroke="#D84040" viewBox="0 0 24 24" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <circle cx="12" cy="7" r="4" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span class="ml-3 hidden font-medium whitespace-nowrap text-[#D84040] group-hover:block">
                                Profile
                            </span>
                        </a>

                        <!-- Create Post -->
                        <button @click="openPostModal"
                            class="group flex w-full items-center px-2 py-2 text-[#D84040] transition hover:rounded-xl hover:bg-[#FDECEC]"
                            title="Create Post">
                            <svg class="h-6 w-6 flex-shrink-0" fill="none" stroke="#D84040" viewBox="0 0 24 24" stroke-width="2">
                                <path d="M12 5v14m-7-7h14" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span class="ml-3 hidden font-medium whitespace-nowrap text-[#D84040] group-hover:block">
                                Post
                            </span>
                        </button>
                    </nav>
                </div>

                <!-- Profile with Dropdown -->
                <div class="relative mt-auto mb-3 px-3" ref="profileDropdownRef">
                    <button @click="toggleProfileDropdown"
                        class="group flex w-full items-center px-2 py-2 text-gray-700 transition hover:rounded-xl hover:bg-gray-100"
                        title="Profile Menu">
                        <div
                            class="flex h-8 w-8 flex-shrink-0 items-center justify-center overflow-hidden rounded-full"
                            :style="{ backgroundColor: userAvatarColor }">
                            <img
                                v-if="userAvatar"
                                :src="userAvatar"
                                :alt="user.name"
                                class="h-full w-full object-cover"
                            />
                            <span v-else class="text-sm font-semibold text-white">{{ userInitials }}</span>
                        </div>

                        <div class="ml-3 hidden flex-1 text-left group-hover:block">
                            <p class="truncate text-sm font-medium text-gray-900">
                                {{ user.name }}
                            </p>
                            <p class="truncate text-xs text-gray-500">
                                {{ user.email }}
                            </p>
                        </div>

                        <svg class="ml-auto hidden h-5 w-5 flex-shrink-0 group-hover:block" fill="currentColor"
                            viewBox="0 0 24 24">
                            <circle cx="12" cy="5" r="2" />
                            <circle cx="12" cy="12" r="2" />
                            <circle cx="12" cy="19" r="2" />
                        </svg>
                    </button>

                    <!-- Desktop Dropdown Menu -->
                    <transition enter-active-class="transition ease-out duration-100"
                        enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-75"
                        leave-from-class="transform opacity-100 scale-100"
                        leave-to-class="transform opacity-0 scale-95">
                        <div v-if="showProfileDropdown"
                            class="absolute bottom-full left-4 z-50 mb-2 w-56 rounded-lg border border-gray-200 bg-white py-1 shadow-lg">
                            <div class="border-b border-gray-100 px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 flex-shrink-0 items-center justify-center overflow-hidden rounded-full"
                                        :style="{ backgroundColor: userAvatarColor }">
                                        <img
                                            v-if="userAvatar"
                                            :src="userAvatar"
                                            :alt="user.name"
                                            class="h-full w-full object-cover"
                                        />
                                        <span v-else class="text-base font-semibold text-white">{{ userInitials }}</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="truncate text-sm font-medium text-gray-900">
                                            {{ user.name }}
                                        </p>
                                        <p class="truncate text-xs text-gray-500">
                                            {{ user.email }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="my-1 border-t border-gray-100"></div>

                            <button @click="handleLogout"
                                class="flex w-full items-center px-4 py-2 text-left text-sm text-gray-700 transition hover:bg-gray-50">
                                <svg class="mr-3 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

        <!-- Mobile Bottom Navigation -->
        <nav class="fixed bottom-0 left-0 right-0 z-40 border-t border-gray-200 bg-white shadow-lg lg:hidden">
            <div class="flex items-center justify-around px-2 py-2">
                <!-- Home -->
                <Link href="/dashboard"
                    class="flex flex-col items-center justify-center px-3 py-2 text-[#D84040] transition"
                    :class="{ 'font-semibold': $page.url === '/dashboard' }">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke-linecap="round"
                            stroke-linejoin="round" />
                        <polyline points="9 22 9 12 15 12 15 22" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span class="mt-1 text-xs">Home</span>
                </Link>

                <!-- Explore -->
                <Link href="/explore"
                    class="flex flex-col items-center justify-center px-3 py-2 text-[#D84040] transition"
                    :class="{ 'font-semibold': $page.url.startsWith('/explore') }">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <circle cx="10.5" cy="10.5" r="7.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M21 21l-5.2-5.2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span class="mt-1 text-xs">Explore</span>
                </Link>

                <!-- Post Button (Center) -->
                <button @click="openPostModal"
                    class="relative -mt-6 flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-r from-[#D84040] to-[#c23636] text-white shadow-lg transition-transform hover:scale-105">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path d="M12 5v14m-7-7h14" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>

                <!-- Notifications -->
                <Link href="/notifications"
                    class="relative flex flex-col items-center justify-center px-3 py-2 text-[#D84040] transition"
                    :class="{ 'font-semibold': $page.url.startsWith('/notifications') }">
                    <div class="relative">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path d="M13.73 21a2 2 0 01-3.46 0" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <span v-if="unreadNotifications > 0"
                            class="absolute -top-1 -right-1 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">
                            {{ unreadNotifications > 9 ? '9+' : unreadNotifications }}
                        </span>
                    </div>
                    <span class="mt-1 text-xs">Notifications</span>
                </Link>

                <!-- Menu -->
                <button @click="toggleMobileMenu"
                    class="flex flex-col items-center justify-center px-3 py-2 text-[#D84040] transition">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span class="mt-1 text-xs">Menu</span>
                </button>
            </div>
        </nav>

        <!-- Mobile Menu Overlay -->
        <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showMobileMenu" @click="closeMobileMenu"
                class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm lg:hidden">
            </div>
        </Transition>

        <!-- Mobile Menu Drawer -->
        <Transition enter-active-class="transition-transform duration-300" enter-from-class="translate-x-full"
            enter-to-class="translate-x-0" leave-active-class="transition-transform duration-300"
            leave-from-class="translate-x-0" leave-to-class="translate-x-full">
            <div v-if="showMobileMenu"
                class="fixed top-0 right-0 bottom-0 z-50 w-80 max-w-[85vw] bg-white shadow-2xl lg:hidden">
                <div class="flex h-full flex-col">
                    <!-- Header -->
                    <div class="flex items-center justify-between border-b border-gray-200 p-4">
                        <h2 class="text-lg font-semibold text-gray-900">Menu</h2>
                        <button @click="closeMobileMenu"
                            class="rounded-lg p-2 text-gray-500 transition hover:bg-gray-100">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- User Info -->
                    <div class="border-b border-gray-100 p-4">
                        <a :href="`/@${user.name}`" @click="closeMobileMenu" class="flex items-center gap-3">
                            <div
                                class="flex h-12 w-12 flex-shrink-0 items-center justify-center overflow-hidden rounded-full"
                                :style="{ backgroundColor: userAvatarColor }">
                                <img v-if="userAvatar" :src="userAvatar" :alt="user.name"
                                    class="h-full w-full object-cover" />
                                <span v-else class="text-lg font-semibold text-white">{{ userInitials }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="truncate text-base font-semibold text-gray-900">
                                    {{ user.name }}
                                </p>
                                <p class="truncate text-sm text-gray-500">
                                    {{ user.email }}
                                </p>
                            </div>
                        </a>
                    </div>

                    <!-- Menu Items -->
                    <nav class="flex-1 overflow-y-auto p-4">
                        <div class="space-y-2">
                            <!-- Messages -->
                            <Link href="/chat" @click="closeMobileMenu"
                                class="flex items-center justify-between rounded-xl px-4 py-3 text-gray-700 transition hover:bg-gray-100"
                                :class="{ 'bg-[#FDECEC] text-[#D84040] font-semibold': $page.url.startsWith('/chat') }">
                                <div class="flex items-center gap-3">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        stroke-width="2">
                                        <path
                                            d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 13.8214 2.48697 15.5291 3.33782 17L2.5 21.5L7 20.6622C8.47087 21.513 10.1786 22 12 22Z"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span class="font-medium">Messages</span>
                                </div>
                                <span v-if="unreadMessages > 0"
                                    class="flex h-6 min-w-[24px] items-center justify-center rounded-full bg-red-500 px-2 text-xs font-bold text-white">
                                    {{ unreadMessages > 99 ? '99+' : unreadMessages }}
                                </span>
                            </Link>

                            <!-- Bookmarks -->
                            <Link href="/bookmarks" @click="closeMobileMenu"
                                class="flex items-center gap-3 rounded-xl px-4 py-3 text-gray-700 transition hover:bg-gray-100"
                                :class="{ 'bg-[#FDECEC] text-[#D84040] font-semibold': $page.url.startsWith('/bookmarks') }">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    stroke-width="2">
                                    <path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                                <span class="font-medium">Bookmarks</span>
                            </Link>

                            <!-- Admin Dashboard -->
                            <Link v-if="user.is_admin" href="/admin/dashboard" @click="closeMobileMenu"
                                class="flex items-center gap-3 rounded-xl px-4 py-3 text-gray-700 transition hover:bg-gray-100"
                                :class="{ 'bg-[#FDECEC] text-[#D84040] font-semibold': $page.url.startsWith('/admin') }">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    stroke-width="2">
                                    <path
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <span class="font-medium">Admin Dashboard</span>
                            </Link>

                            <!-- Profile -->
                            <a :href="`/@${user.name}`" @click="closeMobileMenu"
                                class="flex items-center gap-3 rounded-xl px-4 py-3 text-gray-700 transition hover:bg-gray-100"
                                :class="{ 'bg-[#FDECEC] text-[#D84040] font-semibold': $page.url === '/' + user.name }">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <circle cx="12" cy="7" r="4" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <span class="font-medium">Profile</span>
                            </a>
                        </div>
                    </nav>

                    <!-- Logout Button -->
                    <div class="border-t border-gray-200 p-4">
                        <button @click="handleLogout"
                            class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-gray-700 transition hover:bg-gray-100">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                </path>
                            </svg>
                            <span class="font-medium">Log out</span>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Main Content -->
        <main class="mb-16 min-h-screen flex-1 lg:ml-16 lg:mb-0">
            <slot />
        </main>

        <PostModal :is-open="showPostModal" :user="user as any" @close="closePostModal" @posted="handlePostCreated" />

        <!-- Logout Confirmation Modal -->
        <Teleport to="body">
            <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0"
                enter-to-class="opacity-100" leave-active-class="transition-opacity duration-200"
                leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="showLogoutModal"
                    class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
                    @click.self="closeLogoutModal">
                    <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100" leave-active-class="transition-all duration-200"
                        leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                        <div v-if="showLogoutModal"
                            class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">
                            <!-- Header -->
                            <div class="bg-gradient-to-r from-red-500 to-red-600 p-6">
                                <div class="mb-4 flex items-center justify-center">
                                    <div class="rounded-full bg-white/20 p-3 backdrop-blur-sm">
                                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                    </div>
                                </div>
                                <h3 class="text-center text-2xl font-bold text-white">
                                    Confirm Logout
                                </h3>
                            </div>

                            <!-- Body -->
                            <div class="p-6">
                                <p class="mb-6 text-center text-gray-600">
                                    Are you sure you want to log out? You'll need to sign in again to access your
                                    account.
                                </p>

                                <!-- Actions -->
                                <div class="flex gap-3">
                                    <button @click="closeLogoutModal"
                                        class="flex-1 rounded-xl bg-gray-100 px-4 py-3 font-medium text-gray-700 transition-colors duration-200 hover:bg-gray-200">
                                        Cancel
                                    </button>
                                    <button @click="confirmLogout"
                                        class="flex-1 rounded-xl bg-gradient-to-r from-red-500 to-red-600 px-4 py-3 font-medium text-white shadow-lg transition-all duration-200 hover:from-red-600 hover:to-red-700 hover:shadow-xl">
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