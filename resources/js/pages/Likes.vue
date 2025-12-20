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
</script>

<template>
    <Head title="Liked Posts" />

    <AppSidebarLayout>
        <!-- <AppHeaderLayout> -->
            <main class="p-6">
                <div class="mx-auto max-w-2xl">
                    <h1 class="mb-6 text-2xl font-bold text-gray-900">
                        Liked Posts
                    </h1>

                    <div v-if="posts.length > 0" class="space-y-4">
                        <PostCard
                            v-for="post in posts"
                            :key="post.id"
                            :post="post"
                            :current-user="props.user"
                            @delete="handleDeletePost"
                        />
                    </div>

                    <div v-else class="py-12 text-center">
                        <div
                            class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-100"
                        >
                            <svg
                                class="h-8 w-8 text-red-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                                ></path>
                            </svg>
                        </div>
                        <h3 class="mb-2 text-lg font-medium text-gray-900">
                            No liked posts yet
                        </h3>
                        <p class="mb-4 text-gray-500">
                            Posts you like will appear here.
                        </p>
                        <a
                            href="/dashboard"
                            class="rounded-lg bg-blue-500 px-6 py-3 font-medium text-white transition-colors hover:bg-blue-600"
                            >Browse Posts</a
                        >
                    </div>
                </div>
            </main>
        <!-- </AppHeaderLayout> -->
    </AppSidebarLayout>
</template>
