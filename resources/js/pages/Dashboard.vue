<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import PostModal from '@/components/PostModal.vue';
import PostCard from '@/components/PostCard.vue';
import { dashboard } from '@/routes';

// Define types
interface Post {
    id: number;
    content: string;
    image_url?: string;
    likes_count: number;
    replies_count: number;
    created_at: string;
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

const activeTab = ref(0);
const showPostModal = ref(false);


const handlePostCreated = () => {
    // Post akan otomatis ter-refresh karena Inertia akan reload halaman
};

const handleDeletePost = async (postId: number) => {
    try {
        await router.delete(`/posts/${postId}`, {
            preserveState: false, // Refresh halaman setelah delete
        });
    } catch (error) {
        console.error('Error deleting post:', error);
    }
};

const openPostModal = () => {
    showPostModal.value = true;
};
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex min-h-screen w-screen bg-gray-50">
        <!-- Sidebar Kiri dengan Icon -->
    <div class="fixed left-0 top-0 z-50 h-screen w-16 bg-white shadow-lg border-r border-gray-200">
            <div class="flex flex-col h-full py-4">
                <!-- Logo/Brand (top) -->
                <div class="flex items-center justify-center">
                    <Link href="/dashboard" class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold text-lg hover:bg-blue-700 transition-colors">
                        M
                    </Link>
                </div>

                <!-- Centered nav area -->
                <div class="flex-1 flex items-center justify-center">
                    <nav class="flex flex-col space-y-3 items-center">
                        <!-- Profile (authenticated user) -->
                        <div class="mt-2">
                            <Link :href="`/${$page.props.auth.user.name}`" class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-medium text-sm flex-shrink-0 hover:bg-blue-600 transition-colors">
                                {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                            </Link>
                        </div>

                        <!-- Create Post -->
                        <div>
                            <button 
                                @click="openPostModal"
                                class="w-10 h-10 bg-blue-500 hover:bg-blue-600 text-white rounded-xl flex items-center justify-center transition-colors shadow-lg"
                                title="Create Post"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </button>
                        </div>
                    </nav>
                </div>

            </div>
    </div>

        <!-- Main Content Area -->
        
    <div class="flex flex-col flex-1 ml-16">
            <!-- Header Navigation -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center space-x-4">
                        <h1 class="text-xl font-semibold text-gray-900">Dashboard</h1>
                    </div>
                    
                    <!-- User Actions -->
                    <div>
                        <div class="relative">
                                <input 
                                    type="text" 
                                    placeholder="Search..."
                                    class="w-64 px-4 py-2 pl-10 pr-4 text-gray-700 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                                <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>                            
                    </div>
                </div>
            </div>
        </header>

            <!-- Dashboard Content -->
            <main class="p-6 flex-1 overflow-auto">
                <div class="max-w-2xl mx-auto">
                    <!-- Posts List -->
                    <div v-if="props.posts.length > 0" class="space-y-4 max-w-3xl">
                        <PostCard
                            v-for="post in props.posts"
                            :key="post.id"
                            :post="post"
                            :current-user="props.user"
                            @delete="handleDeletePost"
                        />
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-12">
                        <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No posts yet</h3>
                        <p class="text-gray-500 mb-4">Be the first to share something with the community!</p>
                        <button 
                            @click="openPostModal"
                            class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-medium transition-colors"
                        >
                            Create your first post
                        </button>
                    </div>
                </div>
            </main>

            <!-- Post Modal -->
            <PostModal
                :is-open="showPostModal"
                :user="props.user"
                @close="showPostModal = false"
                @posted="handlePostCreated"
            />
        </div>
    </div>
</template>
     




