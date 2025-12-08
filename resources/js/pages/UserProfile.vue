<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import PostModal from '@/components/PostModal.vue';
import PostCard from '@/components/PostCard.vue';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue';

// Define types
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

// Define props (accept either `user` or `profileUser` depending on server payload)
const props = defineProps<{
    user?: {
        id: number;
        name: string;
        email: string;
        created_at?: string;
    };
    profileUser?: {
        id: number;
        name: string;
        email: string;
        created_at?: string;
    };
    posts: Post[];
    likedPosts?: Post[];  // Tambahkan ini
    replies?: Post[];
}>();

// Normalize profile user: some controllers send `profileUser`, others `user`.
const profileUser = (props.user ?? (props as any).profileUser) as { id: number; name: string; email?: string; created_at?: string };

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

// Helper function to get user initials
const getInitials = (name : string) => {
    if (!name) return 'U';
    return name.split(' ')
        .map(word => word.charAt(0))
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

// Helper function to get avatar color
const getAvatarColor = (name : string) => {
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
</script>

<template>
  <Head :title="'/' + (props.user?.name ?? 'Profile')" />

  <AppSidebarLayout @open-post="openPostModal">
    <AppHeaderLayout>

      <main class="max-w-2xl mx-auto py-8 px-8 sm:px-6 lg:px-8 md:px-10">
            <!-- Profile Header -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-8">
                <!-- Profile Info -->
                <div class="px-6 py-6">
                    <div class="flex items-center justify-start space-x-10">
                        
                            <!-- Profile Avatar -->
                            <div class="w-40 h-40 mt-5 bg-white rounded-full border-4 border-white shadow-lg flex items-center justify-center text-2xl font-bold text-gray-600" 
                                 :style="{ backgroundColor: getAvatarColor(profileUser.name) }">
                                {{ getInitials(profileUser.name) }}
                            </div>
                            
                            <!-- User Info -->
                            <div class="pt-5">
                                <h1 class="text-2xl font-bold text-gray-900">{{ profileUser.name }}</h1>
                                <p class="text-gray-600">@{{ profileUser.name }}</p>
                                <p class="text-gray-500 mt-1">{{ profileUser.email }}</p>
                                <!-- Stats -->
                                <div class="flex items-center space-x-8 mt-0 pt-6">                                    
                                    <div class="text-center flex justify-evenly items-center gap-2">
                                        <p class="text-md font-bold text-gray-900">0</p>
                                        <p class="text-gray-600 text-sm">Followers</p>
                                    </div>
                                    <div class="text-center flex justify-evenly items-center gap-2">
                                        <p class="text-md font-bold text-gray-900">0</p>
                                        <p class="text-gray-600 text-sm">Following</p>
                                    </div>
                                </div>
                            </div>
                        
                    </div>

                    <!-- Bio (placeholder) -->
                    <div class="mt-6">
                        <p class="text-gray-700">
                            Welcome to my profile! I love sharing thoughts and connecting with others.
                        </p>
                    </div>

                    <!-- Follow Button (only show for other users) -->
                    <div v-if="authUser && authUser.id !== profileUser.id" class="mt-6">
                        <button class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">Follow</button>
                    </div>

                </div>
            </div>

            <!-- Navigation Tabs -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px">
                        <!-- Posts Tab -->
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

                        <!-- Replies Tab -->
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

                        <!-- Likes Tab -->
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

                <!-- Tab Content -->
                <div class="min-h-[400px]">
                    <!-- Posts Tab Content -->
                    <div v-show="activeTab === 'posts'">
                        <div v-if="props.posts.length > 0" class="divide-y divide-gray-200">
                            <PostCard
                                v-for="post in props.posts"
                                :key="post.id"
                                :post="post"
                                :current-user="authUser as any"
                                @delete="handleDeletePost"
                                class="border-none"
                            />
                        </div>

                        <!-- Empty State for Posts -->
                        <div v-else class="px-6 py-12 text-center">
                            <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No posts yet</h3>
                            <p class="text-gray-500">{{ profileUser.name }} hasn't shared anything yet.</p>
                        </div>
                    </div>

                    <!-- Replies Tab Content -->
                    <div v-show="activeTab === 'replies'">
                        <div v-if="props.replies && props.replies.length > 0" class="divide-y divide-gray-200">
                            <PostCard
                                v-for="post in props.replies"
                                :key="post.id"
                                :post="post"
                                :current-user="authUser as any"
                                @delete="handleDeletePost"
                                class="border-none"
                            />
                        </div>
                        
                        <!-- Empty State for Replies -->
                        <div v-else class="px-6 py-12 text-center">
                            <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No replies yet</h3>
                            <p class="text-gray-500">{{ profileUser.name }} hasn't replied to any posts.</p>
                        </div>
                    </div>

                    <!-- Likes Tab Content -->
                    <div v-show="activeTab === 'likes'">
                        <!-- Menampilkan liked posts -->
                        <div v-if="props.likedPosts && props.likedPosts.length > 0" class="divide-y divide-gray-200">
                            <PostCard
                                v-for="post in props.likedPosts"
                                :key="post.id"
                                :post="post"
                                :current-user="authUser as any"
                                @delete="handleDeletePost"
                                class="border-none"
                            />
                        </div>
                        
                        <!-- Empty State for Likes -->
                        <div v-else class="px-6 py-12 text-center">
                            <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
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
