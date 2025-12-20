<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';

// Types
interface Actor {
    id: number;
    name: string;
    email: string;
    avatar: string;
}

interface Notification {
    id: number;
    type: string;
    actor: Actor;
    created_at: string;
    read_at: string | null;
    data?: {
        message?: string;
    };
}

// State
const showDropdown = ref<boolean>(false);
const notifications = ref<Notification[]>([]);
const unreadCount = ref<number>(0);
const loading = ref<boolean>(false);

// Toggle dropdown
const toggleDropdown = async (): Promise<void> => {
    showDropdown.value = !showDropdown.value;
    if (showDropdown.value) {
        await fetchNotifications();
    }
};

// Close dropdown
const closeDropdown = (): void => {
    showDropdown.value = false;
};

// Fetch notifications
const fetchNotifications = async (): Promise<void> => {
    loading.value = true;
    try {
        const response = await axios.get<{ notifications: Notification[]; count: number }>('/notifications/unread');
        notifications.value = response.data.notifications;
        unreadCount.value = response.data.count;
    } catch (error) {
        console.error('Error fetching notifications:', error);
    } finally {
        loading.value = false;
    }
};

// Fetch unread count only
const fetchUnreadCount = async (): Promise<void> => {
    try {
        const response = await axios.get<{ count: number }>('/notifications/unread-count');
        unreadCount.value = response.data.count;
    } catch (error) {
        console.error('Error fetching unread count:', error);
    }
};

// Mark all as read
const markAllAsRead = async (): Promise<void> => {
    try {
        await axios.post('/notifications/read-all');
        notifications.value = notifications.value.map(n => ({ ...n, read_at: new Date().toISOString() }));
        unreadCount.value = 0;
    } catch (error) {
        console.error('Error marking all as read:', error);
    }
};

// Handle notification click
const handleNotificationClick = async (notif: Notification): Promise<void> => {
    // Mark as read
    if (!notif.read_at) {
        try {
            await axios.post(`/notifications/${notif.id}/read`);
            notif.read_at = new Date().toISOString();
            unreadCount.value = Math.max(0, unreadCount.value - 1);
        } catch (error) {
            console.error('Error marking as read:', error);
        }
    }

    // Navigate based on notification type
    if (notif.type === 'follow') {
        router.visit(`/profile/${notif.actor.name}`);
    }

    closeDropdown();
};

// Delete notification
const deleteNotification = async (id: number): Promise<void> => {
    try {
        await axios.delete(`/notifications/${id}`);
        notifications.value = notifications.value.filter(n => n.id !== id);
        await fetchUnreadCount();
    } catch (error) {
        console.error('Error deleting notification:', error);
    }
};

// Get notification message based on type
const getNotificationMessage = (notif: Notification): string => {
    switch (notif.type) {
        case 'follow':
            return 'mulai mengikuti Anda';
        case 'like':
            return 'menyukai postingan Anda';
        case 'comment':
            return 'mengomentari postingan Anda';
        case 'repost':
            return 'me-repost postingan Anda';
        default:
            return notif.data?.message || '';
    }
};

// Get initials from name
const getInitials = (name: string): string => {
    return name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

// Format time
const formatTime = (date: string): string => {
    const now = new Date();
    const notifDate = new Date(date);
    const diff = Math.floor((now.getTime() - notifDate.getTime()) / 1000); // seconds

    if (diff < 60) return 'Baru saja';
    if (diff < 3600) return `${Math.floor(diff / 60)} menit yang lalu`;
    if (diff < 86400) return `${Math.floor(diff / 3600)} jam yang lalu`;
    if (diff < 604800) return `${Math.floor(diff / 86400)} hari yang lalu`;

    return notifDate.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: notifDate.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
    });
};

// Click away handler
const clickAwayHandler = (e: MouseEvent): void => {
    const dropdown = document.querySelector('.notification-dropdown-container');
    if (dropdown && !dropdown.contains(e.target as Node)) {
        closeDropdown();
    }
};

// Polling for new notifications
let pollInterval: ReturnType<typeof setInterval> | null = null;

onMounted(() => {
    fetchUnreadCount();

    // Poll every 30 seconds
    pollInterval = setInterval(() => {
        fetchUnreadCount();
    }, 30000);

    // Add click away listener
    document.addEventListener('click', clickAwayHandler);
});

onUnmounted(() => {
    if (pollInterval) {
        clearInterval(pollInterval);
    }
    document.removeEventListener('click', clickAwayHandler);
});
</script>

<template>
  <div class="relative notification-dropdown-container">
    <!-- Bell Button -->
    <button
      @click="toggleDropdown"
      class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-full transition-colors"
      :class="{ 'bg-gray-100': showDropdown }"
    >
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
      </svg>
      
      <!-- Badge Unread Count -->
      <span
        v-if="unreadCount > 0"
        class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center animate-pulse"
      >
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </button>

    <!-- Dropdown -->
    <transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="showDropdown"
        class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-xl border border-gray-200 z-50 max-h-[500px] overflow-hidden flex flex-col"
      >
        <!-- Header -->
        <div class="p-4 border-b border-gray-200 flex items-center justify-between bg-gradient-to-r from-blue-500 to-purple-600">
          <h3 class="font-bold text-lg text-white">Notifikasi</h3>
          <button
            v-if="unreadCount > 0"
            @click="markAllAsRead"
            class="text-sm bg-white text-blue-600 px-3 py-1 rounded-full hover:bg-gray-100 transition-colors font-medium"
          >
            Tandai Semua
          </button>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="p-8 text-center text-gray-500">
          <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-500 mx-auto"></div>
          <p class="mt-3">Memuat notifikasi...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="notifications.length === 0" class="p-8 text-center text-gray-500">
          <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
          </svg>
          <p class="text-lg font-medium">Tidak ada notifikasi</p>
          <p class="text-sm mt-1">Notifikasi baru akan muncul di sini</p>
        </div>

        <!-- Notification List -->
        <div v-else class="overflow-y-auto flex-1">
          <div
            v-for="notif in notifications"
            :key="notif.id"
            @click="handleNotificationClick(notif)"
            class="p-4 hover:bg-gray-50 transition-colors cursor-pointer border-b border-gray-100 last:border-b-0"
            :class="{ 'bg-blue-50': !notif.read_at }"
          >
            <div class="flex items-start gap-3">
              <!-- Avatar -->
              <div class="flex-shrink-0">
                <div
                  v-if="notif.actor.avatar"
                  class="w-12 h-12 rounded-full overflow-hidden"
                >
                  <img :src="notif.actor.avatar" :alt="notif.actor.name" class="w-full h-full object-cover">
                </div>
                <div
                  v-else
                  class="w-12 h-12 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full flex items-center justify-center text-white font-bold"
                >
                  {{ getInitials(notif.actor.name) }}
                </div>
              </div>

              <!-- Content -->
              <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-900">
                  <span class="font-semibold">{{ notif.actor.name }}</span>
                  {{ getNotificationMessage(notif) }}
                </p>
                <p class="text-xs text-gray-500 mt-1">
                  {{ formatTime(notif.created_at) }}
                </p>
              </div>

              <!-- Unread Indicator -->
              <div v-if="!notif.read_at" class="flex-shrink-0">
                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
              </div>
            </div>

            <!-- Action Buttons (for follow notifications) -->
            <div v-if="notif.type === 'follow'" class="flex gap-2 mt-3 ml-15">
              <Link
                :href="`/profile/${notif.actor.name}`"
                class="flex-1 text-center bg-blue-600 text-white px-3 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors"
              >
                Lihat Profil
              </Link>
              <button
                @click.stop="deleteNotification(notif.id)"
                class="flex-1 text-center bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm font-medium hover:bg-gray-300 transition-colors"
              >
                Hapus
              </button>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="p-3 border-t border-gray-200 bg-gray-50">
          <Link
            href="/notifications"
            class="block text-center text-blue-600 hover:text-blue-700 font-medium text-sm"
          >
            Lihat Semua Notifikasi
          </Link>
        </div>
      </div>
    </transition>
  </div>
</template>