<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import PostCard from '@/components/PostCard.vue';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue';

// Define types
interface Post {
    id: number;
    content: string;
    images?: string[];
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
const posts = ref(props.posts.map(p => ({ ...p })));

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

</script>

<template>
  <Head title="Liked Posts" />

  <AppSidebarLayout>
    <AppHeaderLayout>

      <main class="p-6">
        <div class="max-w-2xl mx-auto">
          <h1 class="text-2xl font-bold text-gray-900 mb-6">Liked Posts</h1>

          <div v-if="posts.length > 0" class="space-y-4">
            <PostCard
              v-for="post in posts"
              :key="post.id"
              :post="post"
              :current-user="props.user"
              @delete="handleDeletePost"
            />
          </div>

          <div v-else class="text-center py-12">
            <div class="w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
              <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
              </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No liked posts yet</h3>
            <p class="text-gray-500 mb-4">Posts you like will appear here.</p>
            <a href="/dashboard" class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-medium transition-colors">Browse Posts</a>
          </div>
        </div>
      </main>

    </AppHeaderLayout>
  </AppSidebarLayout>
</template>
