<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
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

// Define props
const props = defineProps<{
    user: {
        id: number;
        name: string;
        email: string;
    };
    posts: Post[];
}>();

const showPostModal = ref(false);

// local reactive copy so we can update likes optimistically
const posts = ref(props.posts.map(p => ({ ...p })));

const handlePostCreated = () => {
    // Page will reload via Inertia redirect in controller
};

const openPostModal = () => {
    showPostModal.value = true;
};

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
  <Head title="Dashboard" />

  <AppSidebarLayout @open-post="openPostModal">
    <AppHeaderLayout>

      <main class="p-6">
        <div class="max-w-2xl mx-auto">
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
            <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
              <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No posts yet</h3>
            <p class="text-gray-500 mb-4">Be the first to share something with the community!</p>
            <button @click="openPostModal" class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-medium transition-colors">Create your first post</button>
          </div>
        </div>
      </main>

      <PostModal :is-open="showPostModal" :user="props.user" @close="showPostModal = false" @posted="handlePostCreated" />
    </AppHeaderLayout>
  </AppSidebarLayout>
</template>
