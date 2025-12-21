<script setup lang="ts">
import PostCard from '@/components/PostCard.vue';
import PostModal from '@/components/PostModal.vue';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Navbar from '@/layouts/app/Navbar.vue';

// Define types
interface Post {
    id: number;
    type?: string;
    content: string;
    image_url?: string;
    images?: string[];
    videos?: string[];
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
        avatar?: string | null;
    };
}

// Define props
const props = defineProps<{
    user: {
        id: number;
        name: string;
        email: string;
        avatar?: string | null;
    };
    posts: Post[];
}>();

const showPostModal = ref(false);

// local reactive copy so we can update likes optimistically
const posts = ref(props.posts.map((p) => ({ ...p })));

const handlePostCreated = () => {
    // Page will reload via Inertia redirect in controller
};

const openPostModal = () => {
    showPostModal.value = true;
};

const handleDeletePost = async (postId: string | number) => {
    try {
        await router.delete(`/posts/${postId}`, {
            preserveState: false,
        });
    } catch (error) {
        console.error('Error deleting post:', error);
        alert('Error deleting post. Please try again.');
    }
};

const handlePostCommented = (postId: number | string) => {
    // Find the post in local list and increment its comment count
    const idx = posts.value.findIndex((p) => String(p.id) === String(postId));
    if (idx !== -1) {
        // prefer replies_count if available, otherwise try comments_count
        if ((posts.value[idx] as any).replies_count !== undefined) {
            (posts.value[idx] as any).replies_count =
                ((posts.value[idx] as any).replies_count || 0) + 1;
        } else if ((posts.value[idx] as any).comments_count !== undefined) {
            (posts.value[idx] as any).comments_count =
                ((posts.value[idx] as any).comments_count || 0) + 1;
        }
    }
};

// Helper function to get user initials
const getInitials = (name: string) => {
    if (!name) return 'U';
    return name
        .split(' ')
        .map((word) => word.charAt(0))
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

// Helper function to get avatar color
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
</script>

<template>


    <Navbar />
    <AppSidebarLayout @open-post="openPostModal">
            <main class="min-h-screen bg-gray-50">
                <div class="mx-auto max-w-2xl">
                    <!-- Create Post Box -->
                    <div class="bg-white border-b border-gray-200">
                        <div class="p-4">
                            <div class="flex gap-3">
                                <!-- User Avatar - FIXED -->
                                <div class="flex-shrink-0">
                                    <!-- Show user avatar if exists -->
                                    <img 
                                        v-if="props.user.avatar" 
                                        :src="`/storage/${props.user.avatar}`"
                                        :alt="props.user.name" 
                                        class="w-12 h-12 rounded-full object-cover border border-gray-200" 
                                    />
                                    <!-- Show initials if no avatar -->
                                    <div 
                                        v-else
                                        class="w-12 h-12 rounded-full flex items-center justify-center text-white font-semibold text-sm border border-gray-200"
                                        :style="{ backgroundColor: getAvatarColor(props.user.name) }">
                                        {{ getInitials(props.user.name) }}
                                    </div>
                                </div>

                                <!-- Input Area -->
                                <div class="flex-1">
                                    <button @click="openPostModal"
                                        class="w-full text-left text-gray-500 text-lg py-3 px-0 hover:text-gray-700 transition-colors focus:outline-none">
                                        What's happening?
                                    </button>

                                    <!-- Action Buttons -->
                                    <div class="flex items-center justify-between pt-3 border-t border-gray-100 mt-3">
                                        <div class="flex items-center gap-1">
                                            <!-- Media Button -->
                                            <!-- <button @click="openPostModal"
                                                class="p-2 rounded-full hover:bg-blue-50 text-blue-500 transition-colors"
                                                title="Media">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </button> -->
                                        </div>
                                        <!-- Post Button -->
                                        <button @click="openPostModal"
                                            class="bg-blue-500 text-white px-4 py-1.5 rounded-full font-bold text-sm hover:bg-blue-600 transition-colors">
                                            Post
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Posts Feed -->
                    <div v-if="posts.length > 0" class="bg-white">
                        <div v-for="post in posts" :key="post.id" class="border-b border-gray-200 last:border-b-0">
                            <PostCard :post="post" :current-user="props.user" @delete="handleDeletePost"
                                @commented="handlePostCommented" />
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="bg-white border-b border-gray-200 py-12">
                        <div class="text-center px-4">
                            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
                                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="mb-2 text-lg font-medium text-gray-900">
                                No posts yet
                            </h3>
                            <p class="mb-4 text-gray-500">
                                Be the first to share something with the community!
                            </p>
                            <button @click="openPostModal"
                                class="rounded-full bg-blue-500 px-6 py-2 font-bold text-white transition-colors hover:bg-blue-600">
                                Create your first post
                            </button>
                        </div>
                    </div>
                </div>
            </main>
        </AppSidebarLayout>

        <PostModal :is-open="showPostModal" :user="props.user" @close="showPostModal = false"
            @posted="handlePostCreated" />
    
    
</template>