F<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';

// Types
interface User {
    id: number;
    name: string;
    username?: string | null;
    email: string;
    avatar: string | null;
    bio?: string;
    followers_count?: number;
    following_count?: number;
    is_following?: boolean;
}

interface Props {
    user: User;
    followers?: User[];
    following?: User[];
    initialTab?: 'followers' | 'following';
}

const props = defineProps<Props>();

const activeTab = ref<'followers' | 'following'>(props.initialTab || 'followers');

// Computed: Get current list based on active tab
const currentList = computed<User[]>(() => {
    if (activeTab.value === 'followers') {
        return props.followers || [];
    }
    return props.following || [];
});

// Toggle follow/unfollow
const toggleFollow = async (targetUser: User) => {
    if (targetUser.is_following) {
        await router.delete(`/users/${targetUser.id}/follow`, {
            preserveScroll: true,
            onSuccess: () => {
                targetUser.is_following = false;
            },
        });
    } else {
        await router.post(`/users/${targetUser.id}/follow`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                targetUser.is_following = true;
            },
        });
    }
};

// Get initials from name
const getInitials = (name: string): string => {
    if (!name) return 'U';
    return name
        .split(' ')
        .map((word) => word.charAt(0))
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

// Get avatar color
const getAvatarColor = (name: string): string => {
    if (!name) return '#6B7280';

    const colors = [
        '#EF4444', '#F97316', '#F59E0B', '#EAB308',
        '#84CC16', '#22C55E', '#10B981', '#14B8A6',
        '#06B6D4', '#0EA5E9', '#3B82F6', '#6366F1',
        '#8B5CF6', '#A855F7', '#D946EF', '#EC4899',
    ];

    const hash = name.split('').reduce((acc, char) => {
        return acc + char.charCodeAt(0);
    }, 0);

    return colors[hash % colors.length];
};

// Get username (prefer stored username, fall back to name-derived identifier)
const getUserUsername = (user: User | null | undefined): string => {
    if (!user) return '';
    return (user.username && String(user.username)) || user.name.toLowerCase().replace(/\s+/g, '');
};

// Build public profile URL for a given user (always uses /@username)
const getUserProfileUrl = (user: User | null | undefined): string => {
    const username = getUserUsername(user as User);
    return `/@${username}`;
};

// Resolve avatar URL (supports absolute URLs and storage paths). Adds cache-busting query so updated avatars show immediately
const getAvatarUrl = (avatar: string | null) => {
    if (!avatar) return null;
    const base = avatar.startsWith('http') ? avatar : `/storage/${avatar}`;
    return `${base}${base.includes('?') ? '&' : '?'}t=${Date.now()}`;
};

// On avatar load error, clear avatar so initials fallback displays
const handleAvatarError = (user: User) => {
    user.avatar = null;
};
</script>

<template>
    <AppSidebarLayout>
        <main class="min-h-screen bg-white lg:ml-16">
            <div class="mx-auto max-w-2xl">
                <!-- Header -->
                <div class="sticky top-0 z-10 bg-white/80 backdrop-blur-md border-b border-gray-200">
                    <div class="px-4 py-3">
                        <div class="flex items-center gap-4">
                            <!-- Back Button -->
                            <Link :href="getUserProfileUrl(user)" class="p-2 -ml-2 rounded-full hover:bg-gray-100 transition-colors">
                                 <svg
                                class="h-5 w-5 text-black"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            </Link>

                            <!-- User Info -->
                            <div>
                                <h1 class="text-xl font-bold text-gray-900">{{ user.name }}</h1>
                                <p class="text-sm text-gray-500">@{{ getUserUsername(user) }}</p>
                            </div> 
                        </div>
                    </div>

                    <!-- Tabs -->
                    <div class="flex border-b border-gray-200">
                        <button
                            @click="activeTab = 'followers'"
                            class="flex-1 py-4 font-semibold text-sm relative hover:bg-gray-50 transition-colors"
                            :class="activeTab === 'followers' ? 'text-gray-900' : 'text-gray-500'"
                        >
                            Followers
                            <div v-if="activeTab === 'followers'" class="absolute bottom-0 left-0 right-0 h-1 bg-blue-500 rounded-full"></div>
                        </button>
                        <button
                            @click="activeTab = 'following'"
                            class="flex-1 py-4 font-semibold text-sm relative hover:bg-gray-50 transition-colors"
                            :class="activeTab === 'following' ? 'text-gray-900' : 'text-gray-500'"
                        >
                            Following
                            <div v-if="activeTab === 'following'" class="absolute bottom-0 left-0 right-0 h-1 bg-blue-500 rounded-full"></div>
                        </button>
                    </div>
                </div>

                <!-- User List -->
                <div v-if="currentList.length > 0">
                    <div
                        v-for="targetUser in currentList"
                        :key="targetUser.id"
                        class="px-4 py-3 hover:bg-gray-50 transition-colors border-b border-gray-100"
                    >
                        <div class="flex items-start gap-3">
                            <!-- Avatar -->
                            <Link :href="`/${targetUser.name}`" class="flex-shrink-0">
                                <div v-if="getAvatarUrl(targetUser.avatar)" class="w-12 h-12 rounded-full overflow-hidden">
                                    <img :src="getAvatarUrl(targetUser.avatar)!" :alt="targetUser.name" @error="handleAvatarError(targetUser)" class="w-full h-full object-cover">
                                </div>
                                <div v-else
                                    class="w-12 h-12 rounded-full flex items-center justify-center text-white font-semibold text-sm"
                                    :style="{ backgroundColor: getAvatarColor(targetUser.name) }">
                                    {{ getInitials(targetUser.name) }}
                                </div>
                            </Link>

                            <!-- User Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex-1 min-w-0">
                                        <Link :href="getUserProfileUrl(targetUser)" class="block">
                                            <h3 class="font-bold text-gray-900 hover:underline truncate">
                                                {{ targetUser.name }}
                                            </h3>
                                            <p class="text-gray-500 text-sm truncate">@{{ getUserUsername(targetUser) }}</p>
                                        </Link>

                                        <!-- Bio -->
                                        <p v-if="targetUser.bio" class="text-gray-700 text-sm mt-2 line-clamp-2">
                                            {{ targetUser.bio }}
                                        </p>
                                    </div>

                                    <!-- Follow Button -->
                                    <button
                                        v-if="targetUser.id !== user.id"
                                        @click="toggleFollow(targetUser)"
                                        class="px-4 py-1.5 rounded-full font-bold text-sm transition-colors flex-shrink-0"
                                        :class="targetUser.is_following
                                            ? 'bg-transparent border border-gray-300 text-gray-900 hover:bg-red-50 hover:text-red-600 hover:border-red-200'
                                            : 'bg-gray-900 text-white hover:bg-gray-800'"
                                    >
                                        <span v-if="targetUser.is_following" class="following-text">Following</span>
                                        <span v-if="targetUser.is_following" class="unfollow-text hidden">Unfollow</span>
                                        <span v-if="!targetUser.is_following">Follow</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="py-16 px-4 text-center">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">
                        {{ activeTab === 'followers' ? 'No followers yet' : 'Not following anyone yet' }}
                    </h3>
                    <p class="text-gray-500 max-w-sm mx-auto">
                        {{ activeTab === 'followers' 
                            ? 'When someone follows this account, they\'ll show up here.' 
                            : 'When this account follows someone, they\'ll show up here.' }}
                    </p>
                </div>
            </div>
        </main>
    </AppSidebarLayout>
</template>

<style scoped>
/* Hover effect for Following button to show Unfollow */
button:hover .following-text {
    display: none;
}

button:hover .unfollow-text {
    display: inline !important;
}
</style>