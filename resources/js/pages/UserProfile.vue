<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import PostModal from '@/components/PostModal.vue';
import PostCard from '@/components/PostCard.vue';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue';

// Define types
interface Post {
    id: number | string;
    type?: string;
    content: string;
    image_url?: string;
    images?: string[];
    likes_count: number;
    bookmarks_count: number;
    reposts_count?: number;
    replies_count: number;
    created_at: string;
    liked?: boolean;
    bookmarked?: boolean;
    reposted?: boolean;
    repost_caption?: string;
    repost_images?: string[];
    original_post_user?: {
        id: number;
        name: string;
        email: string;
    };
    user: {
        id: number;
        name: string;
        email: string;
    };
}

// Define props (accept either `user` or `profileUser` depending on server payload)
const props = defineProps<{
    user?: {
        id: number;
        name: string;
        email: string;
        created_at?: string;
        followers_count?: number;
        following_count?: number;
    };
    profileUser?: {
        id: number;
        name: string;
        email: string;
        created_at?: string;
        followers_count?: number;
        following_count?: number;
    };
    posts: Post[];
    isFollowing: boolean;
    likedPosts?: Post[];
    replies?: Post[];
    reposts?: Post[];
}>();

const isFollowing = ref(props.isFollowing);
// Normalize profile user: some controllers send `profileUser`, others `user`.
const profileUser = (props.user ?? (props as any).profileUser) as {
    id: number;
    name: string;
    email?: string;
    created_at?: string;
    followers_count?: number;
    following_count?: number;
};

// Authenticated user from Inertia page props
const page = usePage();
const pageTyped = page as unknown as { props: { auth?: { user?: { id: number; name: string; email?: string } } } };
const authUser = pageTyped.props.auth?.user ?? null;

const showPostModal = ref(false);

// Active tab state
const activeTab = ref<'posts' | 'replies' | 'likes'>('posts');

const handlePostCreated = () => {
    // Page will reload via Inertia redirect in controller
};

const openPostModal = () => {
    showPostModal.value = true;
};

// Handle post deletion
const handleDeletePost = async (postId: number | string) => {
    try {
        await router.delete(`/posts/${postId}`, {
            preserveState: false,
        });
    } catch (error) {
        console.error('Error deleting post:', error);
        alert('Error deleting post. Please try again.');
    }
};

// Helper function to get user initials
const getInitials = (name: string) => {
    if (!name) return 'U';
    return name.split(' ')
        .map(word => word.charAt(0))
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

// Helper function to get avatar color
const getAvatarColor = (name: string) => {
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

// Set active tab
const setActiveTab = (tab: 'posts' | 'replies' | 'likes') => {
    activeTab.value = tab;
};

const followUser = async () => {
    await router.post(`/users/${profileUser.id}/follow`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            isFollowing.value = true;
        }
    });
};

const unfollowUser = async () => {
    await router.delete(`/users/${profileUser.id}/follow`, {
        preserveScroll: true,
        onSuccess: () => {
            isFollowing.value = false;
        }
    });
};




</script>

<template>

    <Head :title="'/' + (props.user?.name ?? 'Profile')" />

    <AppSidebarLayout @open-post="openPostModal">
        <AppHeaderLayout>

            <main class="max-w-2xl mx-auto py-8 px-8 sm:px-6 lg:px-8 md:px-10">

                <!-- Profile Header -->
                <div class="bg-white rounded-xl shadow border border-[#C9C9C9] overflow-hidden mb-8">

                    <!-- Banner -->
                    <div class="h-44 w-full bg-gradient-to-r from-blue-400 to-blue-600 relative"></div>

                    <!-- Profile Section -->
                    <div class="px-6 py-6 -mt-20 relative">

                        <div class="flex items-center justify-start space-x-6">

                            <!-- Avatar -->
                            <div class="w-32 h-32 bg-white rounded-full border-4 border-white shadow-xl flex items-center justify-center text-3xl font-bold text-gray-700"
                                :style="{ backgroundColor: '#FF3B30' }">
                                {{ getInitials(profileUser.name) }}
                            </div>

                            <!-- User Information -->
                            <div class="pt-20 ml-3">
                                <h1 class="text-3xl font-bold text-gray-900">{{ profileUser.name }}</h1>
                                <p class="text-gray-500 -mt-1">@{{ profileUser.name }}</p>
                                <p class="text-gray-500 mt-1 text-sm">{{ profileUser.email }}</p>

                                <!-- Stats -->
                                <div class="flex items-center space-x-8 mt-4">
                                    <div class="flex items-center gap-2">
                                        <p class="font-semibold text-gray-900 text-md">{{ profileUser.followers_count }}
                                        </p>
                                        <p class="text-gray-500 text-sm">Followers</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <p class="font-semibold text-gray-900 text-md">{{ profileUser.following_count }}
                                        </p>
                                        <p class="text-gray-500 text-sm">Following</p>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Bio -->
                        <div class="mt-5">
                            <p class="text-gray-700 text-sm">
                                Welcome to my profile! I love sharing thoughts and connecting with others.
                            </p>
                        </div>

                        <!-- Follow Button -->
                        <div v-if="authUser && authUser.id !== profileUser.id" class="mt-6 flex gap-3">
                            <button v-if="!isFollowing" @click="followUser"
                                class="px-6 py-2 bg-red-500 hover:bg-red-600 text-white rounded-full font-medium transition">
                                Follow
                            </button>

                            <button v-else @click="unfollowUser"
                                class="px-6 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-full font-medium transition">
                                Unfollow
                            </button>
                        </div>

                    </div>
                </div>

                <!-- Navigation Tabs -->
                <div class="bg-white rounded-xl shadow border border-[#C9C9C9] overflow-hidden">

                    <!-- Tabs -->
                    <div class="border-b border-[#E5E7EB]">
                        <nav class="flex">

                            <!-- Posts -->
                            <button @click="setActiveTab('posts')" :class="[
                                'flex-1 py-3 text-center font-medium text-sm transition',
                                activeTab === 'posts'
                                    ? 'text-red-500 border-b-2 border-red-500'
                                    : 'text-gray-600 hover:text-gray-800'
                            ]">
                                Posts
                            </button>

                            <!-- Reposts -->
                            <button @click="setActiveTab('replies')" :class="[
                                'flex-1 py-3 text-center font-medium text-sm transition',
                                activeTab === 'replies'
                                    ? 'text-red-500 border-b-2 border-red-500'
                                    : 'text-gray-600 hover:text-gray-800'
                            ]">
                                Reposts
                            </button>

                            <!-- Likes -->
                            <button @click="setActiveTab('likes')" :class="[
                                'flex-1 py-3 text-center font-medium text-sm transition',
                                activeTab === 'likes'
                                    ? 'text-red-500 border-b-2 border-red-500'
                                    : 'text-gray-600 hover:text-gray-800'
                            ]">
                                Likes
                            </button>

                        </nav>
                    </div>

                    <!-- Content Panel -->
                    <div class="min-h-[330px] px-6 py-10 flex flex-col items-center justify-center text-center">

                        <!-- POSTS EMPTY -->
                        <div v-if="activeTab === 'posts' && props.posts.length === 0"
                            class="flex flex-col items-center">
                            <div
                                class="w-14 h-14 mb-4 bg-gray-100 rounded-full flex items-center justify-center shadow-sm">
                                <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800">No posts yet</h3>
                            <p class="text-gray-500 text-sm">{{ profileUser.name }} hasn't shared anything yet.</p>
                        </div>

                        <!-- REPLIES EMPTY -->
                        <div v-if="activeTab === 'replies' && (!props.replies || props.replies.length === 0)"
                            class="flex flex-col items-center">
                            <div
                                class="w-14 h-14 mb-4 bg-gray-100 rounded-full flex items-center justify-center shadow-sm">
                                <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800">No reposts yet</h3>
                            <p class="text-gray-500 text-sm">{{ profileUser.name }} hasn't reposted anything.</p>
                        </div>

                        <!-- LIKES EMPTY -->
                        <div v-if="activeTab === 'likes' && (!props.likedPosts || props.likedPosts.length === 0)"
                            class="flex flex-col items-center">
                            <div
                                class="w-14 h-14 mb-4 bg-gray-100 rounded-full flex items-center justify-center shadow-sm">
                                <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800">No likes yet</h3>
                            <p class="text-gray-500 text-sm">{{ profileUser.name }} hasn't liked any posts.</p>
                        </div>

                        <!-- ACTUAL POSTS (KEEP ORIGINAL STRUCTURE) -->
                        <div v-if="activeTab === 'posts' && props.posts.length > 0"
                            class="w-full divide-y divide-[#C9C9C9] mt-4">
                            <PostCard v-for="post in props.posts" :key="post.id" :post="post"
                                :current-user="authUser as any" @delete="handleDeletePost"
                                class="border-none hover:bg-gray-50 transition" />
                        </div>

                        <div v-if="activeTab === 'replies' && props.replies && props.replies.length > 0"
                            class="w-full divide-y divide-[#C9C9C9] mt-4">
                            <PostCard v-for="post in props.replies" :key="post.id" :post="post"
                                :current-user="authUser as any" @delete="handleDeletePost"
                                class="border-none hover:bg-gray-50 transition" />
                        </div>

                        <div v-if="activeTab === 'likes' && props.likedPosts && props.likedPosts.length > 0"
                            class="w-full divide-y divide-[#C9C9C9] mt-4">
                            <PostCard v-for="post in props.likedPosts" :key="post.id" :post="post"
                                :current-user="authUser as any" @delete="handleDeletePost"
                                class="border-none hover:bg-gray-50 transition" />
                        </div>

                    </div>

                </div>

            </main>

            <PostModal :is-open="showPostModal" :user="authUser as any" @close="showPostModal = false"
                @posted="handlePostCreated" />

        </AppHeaderLayout>
    </AppSidebarLayout>
</template>
