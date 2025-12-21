<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import PostModal from '@/components/PostModal.vue';
import Navbar from '@/layouts/app/Navbar.vue';

// Define types
interface User {
    id: number;
    name: string;
    username: string;
    avatar: string | null;
    bio: string;
    followers_count: number;
    isFollowing: boolean;
}

interface PaginatedUsers {
    data: User[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

// Props
const props = defineProps<{
    users: PaginatedUsers;
    search: string;
}>();

// Page
const page = usePage();
const pageTyped = page as unknown as {
    props: { auth?: { user?: { id: number; name: string; email?: string } } };
};
const authUser = pageTyped.props.auth?.user ?? null;

// State
const searchQuery = ref(props.search || '');
const showPostModal = ref(false);
const isSearching = ref(false);

// Watch search query and debounce
let searchTimeout: ReturnType<typeof setTimeout>;
watch(searchQuery, (newValue) => {
    clearTimeout(searchTimeout);
    isSearching.value = true;

    searchTimeout = setTimeout(() => {
        router.get(
            '/explore',
            { search: newValue },
            {
                preserveState: true,
                preserveScroll: true,
                onFinish: () => {
                    isSearching.value = false;
                },
            }
        );
    }, 500);
});

// Methods
const openPostModal = () => {
    showPostModal.value = true;
};

const handlePostCreated = () => {
    showPostModal.value = false;
    router.reload();
};

const followUser = async (userId: number, event: Event) => {
    event.stopPropagation(); // Prevent navigation to profile
    await router.post(
        `/users/${userId}/follow`,
        {},
        {
            preserveScroll: true,
            preserveState: true,
        }
    );
};

const unfollowUser = async (userId: number, event: Event) => {
    event.stopPropagation(); // Prevent navigation to profile
    await router.delete(`/users/${userId}/follow`, {
        preserveScroll: true,
        preserveState: true,
    });
};

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

    const hash = name.split('').reduce((acc, char) => {
        return acc + char.charCodeAt(0);
    }, 0);

    return colors[hash % colors.length];
};

const goToProfile = (user: User) => {
    // Route format: /@{username}
    router.visit(`/@${user.username}`);
};

const formatCount = (count: number): string => {
    if (count >= 1000000) {
        return (count / 1000000).toFixed(1) + 'M';
    } else if (count >= 1000) {
        return (count / 1000).toFixed(1) + 'K';
    }
    return count.toString();
};
</script>

<template>
    <Head />
    <Navbar />
    <AppSidebarLayout @open-post="openPostModal">
            <main class="mx-auto min-h-screen max-w-2xl bg-white">
                <!-- Search Bar -->
                <div
                    class="sticky top-[57px] z-10 border-b border-gray-200 bg-white p-4">
                    <div class="relative">
                        <svg
                            class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            stroke-width="2">
                            <circle
                                cx="10.5"
                                cy="10.5"
                                r="7.5"
                                stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path
                                d="M21 21l-5.2-5.2"
                                stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search people by name, username, or email..."
                            class="w-full rounded-full border-2 border-gray-200 py-3 pl-12 pr-4 text-[15px] text-gray-900 transition focus:border-[#D84040] focus:outline-none" />

                        <!-- Loading indicator -->
                        <div
                            v-if="isSearching"
                            class="absolute right-4 top-1/2 -translate-y-1/2">
                            <svg
                                class="h-5 w-5 animate-spin text-[#D84040]"
                                fill="none"
                                viewBox="0 0 24 24">
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Users List -->
                <div class="min-h-[400px]">
                    <!-- Empty State -->
                    <div
                        v-if="users.data.length === 0"
                        class="px-8 py-24 text-center">
                        <div
                            class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-gray-100">
                            <svg
                                class="h-10 w-10 text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                stroke-width="2">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-[31px] font-bold text-gray-900">
                            No users found
                        </h3>
                        <p class="text-[15px] text-gray-500">
                            {{
                                searchQuery
                                    ? 'Try searching with different keywords'
                                    : 'No users available at the moment'
                            }}
                        </p>
                    </div>

                    <!-- Users Grid -->
                    <div v-else class="divide-y divide-gray-200">
                        <div
                            v-for="user in users.data"
                            :key="user.id"
                            @click="goToProfile(user)"
                            class="cursor-pointer p-4 transition hover:bg-gray-50">
                            <div
                                class="flex items-start justify-between gap-4">
                                <!-- User Info - Clickable Area -->
                                <div class="flex flex-1 items-start gap-3">
                                    <!-- Avatar -->
                                    <div
                                        class="h-12 w-12 flex-shrink-0 overflow-hidden rounded-full">
                                        <img
                                            v-if="user.avatar"
                                            :src="`/storage/${user.avatar}`"
                                            :alt="user.name"
                                            class="h-full w-full object-cover" />
                                        <div
                                            v-else
                                            class="flex h-full w-full items-center justify-center text-sm font-bold text-white"
                                            :style="{
                                                backgroundColor:
                                                    getAvatarColor(user.name),
                                            }">
                                            {{ getInitials(user.name) }}
                                        </div>
                                    </div>

                                    <!-- Details -->
                                    <div class="min-w-0 flex-1">
                                        <div
                                            class="flex items-center gap-2">
                                            <h3
                                                class="truncate font-semibold text-gray-900 hover:underline">
                                                {{ user.name }}
                                            </h3>
                                        </div>
                                        <p
                                            class="text-[15px] text-gray-500 hover:underline">
                                            @{{ user.username }}
                                        </p>
                                        <p
                                            v-if="user.bio"
                                            class="line-clamp-2 mt-1 text-[15px] text-gray-700">
                                            {{ user.bio }}
                                        </p>
                                        <p class="mt-1 text-sm text-gray-500">
                                            {{
                                                formatCount(
                                                    user.followers_count,
                                                )
                                            }}
                                            followers
                                        </p>
                                    </div>
                                </div>

                                <!-- Follow Button -->
                                <div class="flex-shrink-0">
                                    <button
                                        v-if="!user.isFollowing"
                                        @click="followUser(user.id, $event)"
                                        class="rounded-full bg-black px-5 py-[7px] text-[15px] font-semibold text-white transition hover:bg-gray-800">
                                        Follow
                                    </button>
                                    <button
                                        v-else
                                        @click="unfollowUser(user.id, $event)"
                                        class="group rounded-full border border-gray-300 px-5 py-[7px] text-[15px] font-semibold text-gray-900 transition hover:border-red-600 hover:bg-red-50 hover:text-red-600">
                                        <span class="group-hover:hidden"
                                            >Following</span
                                        >
                                        <span
                                            class="hidden group-hover:inline"
                                            >Unfollow</span
                                        >
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="users.last_page > 1"
                        class="border-t border-gray-200 px-4 py-6">
                        <div class="flex items-center justify-between">
                            <!-- Previous Button -->
                            <button
                                @click="
                                    router.get('/explore', {
                                        search: searchQuery,
                                        page: users.current_page - 1,
                                    })
                                "
                                :disabled="users.current_page === 1"
                                :class="[
                                    'rounded-full px-5 py-2 text-[15px] font-semibold transition',
                                    users.current_page === 1
                                        ? 'cursor-not-allowed bg-gray-100 text-gray-400'
                                        : 'bg-gray-900 text-white hover:bg-gray-800',
                                ]">
                                Previous
                            </button>

                            <!-- Page Info -->
                            <span class="text-[15px] text-gray-600">
                                Page {{ users.current_page }} of
                                {{ users.last_page }}
                            </span>

                            <!-- Next Button -->
                            <button
                                @click="
                                    router.get('/explore', {
                                        search: searchQuery,
                                        page: users.current_page + 1,
                                    })
                                "
                                :disabled="
                                    users.current_page === users.last_page
                                "
                                :class="[
                                    'rounded-full px-5 py-2 text-[15px] font-semibold transition',
                                    users.current_page === users.last_page
                                        ? 'cursor-not-allowed bg-gray-100 text-gray-400'
                                        : 'bg-gray-900 text-white hover:bg-gray-800',
                                ]">
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </main>

            <PostModal
                :is-open="showPostModal"
                :user="authUser as any"
                @close="showPostModal = false"
                @posted="handlePostCreated" />
    </AppSidebarLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>