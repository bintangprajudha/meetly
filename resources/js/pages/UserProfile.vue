<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

import PostModal from '@/components/PostModal.vue';
import PostCard from '@/components/PostCard.vue';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue';

// ========= TYPES =========
interface Post {
    id: number;
    content: string;
    image_url?: string;
    likes_count: number;
    bookmarks_count: number;
    replies_count: number;
    created_at: string;
    liked?: boolean;
    bookmarked?: boolean;
    user: {
        id: number;
        name: string;
        email: string;
    };
}

// ========= PROPS =========
const props = defineProps<{
    user?: {
        id: number;
        name: string;
        email: string;
        created_at?: string;
    };
    posts: Post[];
    isFollowing: boolean;
    likedPosts?: Post[];
    replies?: Post[];
}>();

const isFollowing = ref(props.isFollowing);

// Normalize profile user
const profileUser = (props.user ?? (props as any).profileUser) as {
    id: number;
    name: string;
    email?: string;
    created_at?: string;
    followers_count?: number;
    following_count?: number;
};

// Auth user
const page = usePage();
const pageTyped = page as unknown as {
    props: {
        auth?: { user?: { id: number; name: string; email?: string } };
    };
};
const authUser = pageTyped.props.auth?.user ?? null;

// UI States
const showPostModal = ref(false);
const activeTab = ref<'posts' | 'replies' | 'likes'>('posts');

// Modal
const openPostModal = () => {
    showPostModal.value = true;
};

const handlePostCreated = () => {
    // Inertia handles reload
};

// Delete Post
const handleDeletePost = async (postId: number) => {
    try {
        await router.delete(`/posts/${postId}`, {
            preserveState: false,
        });
    } catch (error) {
        console.error('Error deleting post:', error);
        alert('Error deleting post. Please try again.');
    }
};

// Avatar Helpers
const getInitials = (name: string) => {
    if (!name) return 'U';
    return name
        .split(' ')
        .map(word => word.charAt(0))
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

const getAvatarColor = (name: string) => {
    if (!name) return '#6B7280';

    const colors = [
        '#EF4444', '#F97316', '#F59E0B', '#EAB308', '#84CC16', '#22C55E',
        '#10B981', '#14B8A6', '#06B6D4', '#0EA5E9', '#3B82F6', '#6366F1',
        '#8B5CF6', '#A855F7', '#D946EF', '#EC4899',
    ];

    const hash = name.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0);
    return colors[hash % colors.length];
};

// Tabs
const setActiveTab = (tab: 'posts' | 'replies' | 'likes') => {
    activeTab.value = tab;
};

// Follow / Unfollow
const followUser = async () => {
    await router.post(`/users/${profileUser.id}/follow`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            isFollowing.value = true;
        },
    });
};

const unfollowUser = async () => {
    await router.delete(`/users/${profileUser.id}/follow`, {
        preserveScroll: true,
        onSuccess: () => {
            isFollowing.value = false;
        },
    });
};
</script>

<template>
    <Head :title="'' + (props.user?.name ?? 'Profile')" />

    <AppSidebarLayout @open-post="openPostModal">
        <AppHeaderLayout>
            <main class="max-w-2xl mx-auto py-8 px-8 sm:px-6 lg:px-8 md:px-10">

                <!-- PROFILE HEADER -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-8">

                    <!-- Banner -->
                    <div class="w-full h-48 bg-gray-300"></div>

                    <!-- Profile Info -->
                    <div class="relative px-6 pb-6">

                        <!-- Avatar -->
                        <div class="absolute -top-20 left-6">
                            <div
                                class="w-36 h-36 bg-white rounded-full border-4 border-white shadow-lg flex items-center justify-center text-5xl font-bold text-gray-800"
                                :style="{ backgroundColor: getAvatarColor(profileUser.name) }"
                            >
                                {{ getInitials(profileUser.name) }}
                            </div>

                        </div>

                        <!-- Edit / Follow -->
                        <div class="flex justify-end pt-4">
                            <button class="px-4 py-2 rounded-full border hover:bg-gray-100 transition text-black">
                                Edit profile
                            </button>
                        </div>

                        <!-- User Info -->
                        <div class="mt-4 pl-1">
                            <h1 class="text-xl font-bold text-gray-900">{{ profileUser.name }}</h1>
                            <p class="text-gray-600">@{{ profileUser.name }}</p>

                            <!-- Join Date -->
                            <div class="flex items-center text-gray-600 text-sm mt-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-1" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 7V3m8 4V3m-9 14h10M5 21h14a2 2 0 002-2V7H3v12a2 2 0 002 2z" />
                                </svg>
                                Joined December 2025
                            </div>

                            <!-- Stats -->
                            <div class="flex items-center space-x-6 mt-3">
                                <div class="text-center flex items-center gap-1">
                                    <p class="font-bold text-gray-900">{{ profileUser.following_count }}</p>
                                    <p class="text-gray-600 text-sm">Following</p>
                                </div>
                                <div class="text-center flex items-center gap-1">
                                    <p class="font-bold text-gray-900">{{ profileUser.followers_count }}</p>
                                    <p class="text-gray-600 text-sm">Followers</p>

                                </div>
                            </div>

                            <!-- Bio -->
                            <div class="mt-4">
                                <p class="text-gray-700">
                                    Welcome to my profile! I love sharing thoughts and connecting with others.
                                </p>
                            </div>

                            <!-- Follow Button -->
                            <div
                                v-if="authUser && authUser.id !== profileUser.id"
                                class="mt-6"
                            >
                                <button
                                    v-if="!isFollowing"
                                    @click="followUser"
                                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors"
                                >
                                    Follow
                                </button>

                                <button
                                    v-else
                                    @click="unfollowUser"
                                    class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium transition-colors"
                                >
                                    Unfollow
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABS -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">

                    <div class="border-b border-gray-200">
                        <nav class="flex -mb-px">

                            <!-- Posts -->
                            <button
                                @click="setActiveTab('posts')"
                                :class="[
                                    'flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors',
                                    activeTab === 'posts'
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                ]"
                            >
                                Posts
                            </button>

                            <!-- Replies -->
                            <button
                                @click="setActiveTab('replies')"
                                :class="[
                                    'flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors',
                                    activeTab === 'replies'
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                ]"
                            >
                                Replies
                            </button>

                            <!-- Likes -->
                            <button
                                @click="setActiveTab('likes')"
                                :class="[
                                    'flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors',
                                    activeTab === 'likes'
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                ]"
                            >
                                Likes
                            </button>

                        </nav>
                    </div>

                    <!-- TAB CONTENT -->
                    <div class="min-h-[400px]">

                        <!-- POSTS -->
                        <div v-show="activeTab === 'posts'">
                            <div v-if="props.posts.length > 0" class="divide-y divide-gray-200">
                                <PostCard
                                    v-for="post in props.posts"
                                    :key="post.id"
                                    :post="post"
                                    :current-user="authUser as any"
                                    @delete="handleDeletePost"
                                />
                            </div>

                            <div v-else class="px-6 py-12 text-center">
                                <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M11 5H6a2 2 0 00-2 2v11a2 
                                                  2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414
                                                  a2 2 0 112.828 2.828L11.828 15H9v-2.828
                                                  l8.586-8.586z" />
                                    </svg>
                                </div>

                                <h3 class="text-lg font-medium text-gray-900 mb-2">No posts yet</h3>
                                <p class="text-gray-500">{{ profileUser.name }} hasn't shared anything yet.</p>
                            </div>
                        </div>

                        <!-- REPLIES -->
                        <div v-show="activeTab === 'replies'">
                            <div v-if="props.replies && props.replies.length > 0" class="divide-y divide-gray-200">
                                <PostCard
                                    v-for="post in props.replies"
                                    :key="post.id"
                                    :post="post"
                                    :current-user="authUser as any"
                                    @delete="handleDeletePost"
                                />
                            </div>

                            <div v-else class="px-6 py-12 text-center">
                                <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                    </svg>
                                </div>

                                <h3 class="text-lg font-medium text-gray-900 mb-2">No replies yet</h3>
                                <p class="text-gray-500">{{ profileUser.name }} hasn't replied to any posts.</p>
                            </div>
                        </div>

                        <!-- LIKES -->
                        <div v-show="activeTab === 'likes'">

                            <div v-if="props.likedPosts && props.likedPosts.length > 0" class="divide-y divide-gray-200">
                                <PostCard
                                    v-for="post in props.likedPosts"
                                    :key="post.id"
                                    :post="post"
                                    :current-user="authUser as any"
                                    @delete="handleDeletePost"
                                />
                            </div>

                            <div v-else class="px-6 py-12 text-center">
                                <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M4.318 6.318a4.5 4.5 0 000 6.364L12
                                                 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12
                                                 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                </div>

                                <h3 class="text-lg font-medium text-gray-900 mb-2">No likes yet</h3>
                                <p class="text-gray-500">{{ profileUser.name }} hasn't liked any posts.</p>
                            </div>
                        </div>

                    </div>
                </div>
            </main>

            <PostModal
                :is-open="showPostModal"
                :user="authUser as any"
                @close="showPostModal = false"
                @posted="handlePostCreated"
            />
        </AppHeaderLayout>
    </AppSidebarLayout>
</template>
