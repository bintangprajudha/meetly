<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import axios from 'axios';

// Types
interface Actor {
    id: number;
    name: string;
    email: string;
    avatar: string | null;
}

interface NotificationData {
    message?: string;
}

interface Notification {
    id: number;
    type: string;
    actor: Actor;
    created_at: string;
    read_at: string | null;
    data?: NotificationData;
    notifiable_id?: number;
}

interface PaginatedNotifications {
    data: Notification[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

interface Props {
    notifications: PaginatedNotifications;
    unreadCount: number;
}

const props = defineProps<Props>();

const activeTab = ref<'all' | 'unread'>('all');

// Computed: Filter notifications based on active tab
const filteredNotifications = computed<Notification[]>(() => {
    if (!props.notifications?.data) return [];

    if (activeTab.value === 'unread') {
        return props.notifications.data.filter(n => !n.read_at);
    }
    return props.notifications.data;
});

// Mark all as read
const markAllAsRead = async (): Promise<void> => {
    try {
        await axios.post('/notifications/read-all');
        router.reload({ only: ['notifications', 'unreadCount'] });
    } catch (error) {
        console.error('Error marking all as read:', error);
    }
};

// Mark single notification as read
const markAsRead = async (id: number): Promise<void> => {
    try {
        await axios.post(`/notifications/${id}/read`);
        router.reload({ only: ['notifications', 'unreadCount'] });
    } catch (error) {
        console.error('Error marking as read:', error);
    }
};

// Delete notification
const deleteNotification = async (id: number): Promise<void> => {
    if (!confirm('Hapus notifikasi ini?')) return;

    try {
        await axios.delete(`/notifications/${id}`);
        router.reload({ only: ['notifications', 'unreadCount'] });
    } catch (error) {
        console.error('Error deleting notification:', error);
    }
};

// Handle notification click
const handleNotificationClick = async (notification: Notification): Promise<void> => {
    // Mark as read if unread
    if (!notification.read_at) {
        markAsRead(notification.id); // Don't await, let it run in background
    }

    // Navigate based on notification type immediately
    if (notification.type === 'follow') {
        router.visit(`/${notification.actor.name}`);
    } else if (notification.type === 'like' || notification.type === 'comment' || notification.type === 'repost') {
        // Redirect ke post jika ada notifiable_id
        if (notification.notifiable_id) {
            router.visit(`/posts/${notification.notifiable_id}`);
        }
    }
};

// Get notification message based on type
const getNotificationMessage = (notification: Notification): string => {
    switch (notification.type) {
        case 'follow':
            return 'start following you';
        case 'like':
            return 'liked your post';
        case 'comment':
            return 'commented on your post';
        case 'repost':
            return 'reposted your post';
        default:
            return notification.data?.message || '';
    }
};

// Get initials from name (same as UserProfile.vue)
const getInitials = (name: string): string => {
    if (!name) return 'U';
    return name
        .split(' ')
        .map((word) => word.charAt(0))
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

// Get avatar color (same as UserProfile.vue)
const getAvatarColor = (name: string): string => {
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

    const hash = name.split('').reduce((acc, char) => {
        return acc + char.charCodeAt(0);
    }, 0);

    return colors[hash % colors.length];
};

// Format time
const formatTime = (date: string): string => {
    const now = new Date();
    const notifDate = new Date(date);
    const diff = Math.floor((now.getTime() - notifDate.getTime()) / 1000); // seconds

    if (diff < 60) return 'Baru saja';
    if (diff < 3600) return `${Math.floor(diff / 60)}m`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}h`;
    if (diff < 604800) return `${Math.floor(diff / 86400)}d`;

    return notifDate.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short'
    });
};
</script>

<template>
    <AppSidebarLayout>
        <main class="h-screen bg-gray-50 w-120">
            <div class="h-full flex flex-col max-w-full">
                <!-- Header -->
                <div class="bg-white border-b border-gray-200 px-8 py-4 w-full">
                    <div class="flex items-center justify-between max-w-6xl">
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
                            <span v-if="unreadCount > 0"
                                class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                                {{ unreadCount }}
                            </span>
                        </div>

                        <button v-if="unreadCount > 0" @click="markAllAsRead"
                            class="text-sm text-blue-600 font-medium hover:text-blue-700 transition-colors">
                            Mark All as Read
                        </button>
                    </div>

                    <!-- Tabs -->
                    <div class="flex gap-6 mt-4 border-b border-gray-200 -mb-px max-w-6xl">
                        <button @click="activeTab = 'all'"
                            class="pb-3 px-1 font-medium text-sm transition-colors relative" :class="activeTab === 'all'
                                ? 'text-gray-900'
                                : 'text-gray-500 hover:text-gray-700'">
                            All
                            <div v-if="activeTab === 'all'"
                                class="absolute bottom-0 left-0 right-0 h-0.5 bg-gray-900"></div>
                        </button>
                        <button @click="activeTab = 'unread'"
                            class="pb-3 px-1 font-medium text-sm transition-colors relative flex items-center gap-2"
                            :class="activeTab === 'unread'
                                ? 'text-gray-900'
                                : 'text-gray-500 hover:text-gray-700'">
                            Unread
                            <span v-if="unreadCount > 0"
                                class="bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full">
                                {{ unreadCount }}
                            </span>
                            <div v-if="activeTab === 'unread'"
                                class="absolute bottom-0 left-0 right-0 h-0.5 bg-gray-900"></div>
                        </button>
                    </div>
                </div>

                <!-- Notification List -->
                <div class="flex-1 overflow-y-auto bg-white w-full">
                    <!-- Empty State -->
                    <div v-if="filteredNotifications.length === 0"
                        class="flex flex-col items-center justify-center h-full px-4 text-center">
                        <div class="w-24 h-24 mb-6 rounded-full bg-gray-100 flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">No notifications</h3>
                        <p class="text-sm text-gray-500 max-w-sm">
                            {{ activeTab === 'unread' ? 'All notifications have been read' : 'New notifications will appear here' }}
                        </p>
                    </div>

                    <!-- Notification Items -->
                    <div v-else class="max-w-6xl">
                        <div v-for="notification in filteredNotifications" :key="notification.id"
                            @click="handleNotificationClick(notification)"
                            class="flex items-start gap-3 px-8 py-4 hover:bg-gray-50 cursor-pointer transition-colors border-b border-gray-100"
                            :class="{ 'bg-blue-50/50': !notification.read_at }">
                            <!-- Avatar (same logic as UserProfile.vue) -->
                            <div class="flex-shrink-0 relative">
                                <Link :href="`/${notification.actor.name}`" class="block">
                                    <!-- Avatar dengan gambar jika ada -->
                                    <div v-if="notification.actor.avatar"
                                        class="w-12 h-12 rounded-full overflow-hidden ring-2 ring-white">
                                        <img 
                                            :src="notification.actor.avatar" 
                                            :alt="notification.actor.name"
                                            class="w-full h-full object-cover"
                                        >
                                    </div>
                                    <!-- Avatar fallback dengan warna dinamis (same as UserProfile) -->
                                    <div v-else
                                        class="w-12 h-12 rounded-full flex items-center justify-center text-white font-semibold text-sm ring-2 ring-white"
                                        :style="{ backgroundColor: getAvatarColor(notification.actor.name) }">
                                        {{ getInitials(notification.actor.name) }}
                                    </div>
                                </Link>
                                <!-- Unread indicator on avatar -->
                                <div v-if="!notification.read_at"
                                    class="absolute -top-0.5 -right-0.5 w-3 h-3 bg-red-500 border-2 border-white rounded-full">
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-sm">
                                        <Link :href="`/${notification.actor.name}`"
                                            class="font-semibold text-gray-900 hover:underline">
                                            {{ notification.actor.name }}
                                        </Link>
                                        <span class="text-gray-600 ml-1">
                                            {{ getNotificationMessage(notification) }}
                                        </span>
                                    </p>
                                    <span class="text-xs text-gray-400 flex-shrink-0">
                                        {{ formatTime(notification.created_at) }}
                                    </span>
                                </div>

                                <!-- Action Buttons (show on hover or for unread) -->
                                <div class="flex gap-2 mt-2 opacity-0 group-hover:opacity-100 transition-opacity"
                                    :class="{ 'opacity-100': !notification.read_at }">
                                    <button v-if="!notification.read_at" @click.stop="markAsRead(notification.id)"
                                        class="text-xs text-blue-600 hover:text-blue-700 font-medium">
                                        Mark as Read
                                    </button>
                                    <button @click.stop="deleteNotification(notification.id)"
                                        class="text-xs text-red-600 hover:text-red-700 font-medium">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </AppSidebarLayout>
</template>