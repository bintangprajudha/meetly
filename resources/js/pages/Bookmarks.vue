<script setup lang="ts">
import PostCard from '@/components/PostCard.vue';
// import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

// Define types
interface Post {
    id: number;
    content: string;
    images?: string[];
    videos?: string[];
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

// Define props
const props = defineProps<{
    user: {
        id: number;
        name: string;
        email: string;
    };
    posts: Post[];
}>();

// local reactive copy so we can update likes optimistically
const posts = ref(props.posts.map((p) => ({ ...p })));

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
</script>

<template>
    <Head/>

    <AppSidebarLayout>
        <!-- <AppHeaderLayout> -->
            <main class="min-h-screen bg-white p-4 sm:p-6">
                <div class="mx-auto max-w-2xl">
                    <!-- Header -->
                    <div class="mb-4 border-b border-gray-200 pb-4 sm:mb-6 sm:pb-6">
                        <h1 class="text-xl font-bold text-gray-900 sm:text-2xl">
                            Bookmarked Posts
                        </h1>
                    </div>

                    <!-- Posts List -->
                    <div v-if="posts.length > 0" class="space-y-4">
                        <PostCard
                            v-for="post in posts"
                            :key="post.id"
                            :post="post"
                            :current-user="props.user"
                            @delete="handleDeletePost"
                        />
                    </div>

                    <!-- Empty State -->
                    <div v-else class="py-12 text-center sm:py-16">
                        <div
                            class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-yellow-100"
                        >
                            <svg
                                class="h-8 w-8 text-yellow-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5 5a2 2 0 012-2h6a2 2 0 012 2v16l-7-3.5L5 21V5z"
                                ></path>
                            </svg>
                        </div>
                        <h3 class="mb-2 text-lg font-medium text-gray-900">
                            No bookmarked posts yet
                        </h3>
                        <p class="mb-4 text-sm text-gray-500 sm:text-base">
                            Posts you bookmark will appear here.
                        </p>
                        
                        <a 
                            href="/dashboard"
                            class="inline-block rounded-lg bg-[#D84040] px-6 py-2.5 text-sm font-medium text-white transition-colors hover:bg-[#C73636] sm:px-8 sm:py-3 sm:text-base"
                        >
                            Browse Posts
                        </a>
                    </div>
                </div>
            </main>
        <!-- </AppHeaderLayout> -->
    </AppSidebarLayout>
</template>