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

const navigationItems = [
    {
        name: 'Dashboard',
        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        active: true
    },
    {
        name: 'Analytics',
        icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        active: false
    },
    {
        name: 'Posts',
        icon: 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
        active: false
    },
    {
        name: 'Users',
        icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z',
        active: false
    },
    {
        name: 'Settings',
        icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
        active: false
    }
];

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

    <div class="flex min-h-screen bg-gray-50">
        <!-- Sidebar Kiri dengan Icon -->
        <div class="fixed left-0 top-0 z-50 h-full w-16 bg-white shadow-lg border-r border-gray-200">
            <div class="flex flex-col items-center py-4 space-y-4">
                <!-- Logo/Brand -->
                <Link href="/dashboard" class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold text-lg hover:bg-blue-700 transition-colors">
                    M
                </Link>
                
                <!-- Navigation Items -->
                <nav class="flex flex-col space-y-3">
                    <template v-for="(item, index) in navigationItems" :key="item.name">
                        <button 
                            @click="activeTab = index"
                            :class="[
                                'w-10 h-10 flex items-center justify-center rounded-xl transition-all duration-200',
                                activeTab === index 
                                    ? 'bg-blue-100 text-blue-600 shadow-md' 
                                    : 'text-gray-600 hover:bg-gray-100 hover:text-black'
                            ]"
                            :title="item.name"
                        >
                            <svg class="w-5 h-5 stroke-current" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"></path>
                            </svg>
                        </button>
                    </template>
                </nav>



                <!-- Post Button -->
                <div class="mt-4">
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
                
                <!-- Bottom Actions -->
                <div class="flex-1"></div>
                <div class="flex flex-col space-y-3">
                    <!-- Profile -->
                    <div class="w-10 h-10 bg-gray-200 rounded-xl flex items-center justify-center text-gray-700 font-medium text-sm">
                        {{ props.user?.name?.charAt(0).toUpperCase() || 'G' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 ml-16">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                            
                        </div>
                        <div class="flex items-center space-x-4">
                            <!-- Search -->
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
                            <!-- User Profile Link -->
                            <Link :href="`/${props.user?.name}`" class="p-2 text-gray-600 hover:text-black hover:bg-gray-100 rounded-lg transition-colors">
                                <span class="border-r-2 pr-2">
                                    {{ props.user?.name || 'Guest' }}
                                </span>
                            </Link>
                            <!-- Logout -->
                            <Link
                                href="/logout" 
                                method="post"
                                as="button"
                                class="px-4 py-2 text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors"
                            >
                                Logout
                            </Link>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <main class="p-6">
                <!-- Welcome Message -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-2">Welcome back, {{ props.user.name }}!</h2>
                    <p class="text-gray-600">Here are your recent posts and updates.</p>
                </div>

                <!-- Quick Post Button (Desktop) -->
                <div class="mb-6 md:hidden">
                    <button 
                        @click="openPostModal"
                        class="w-full py-3 px-4 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-medium transition-colors"
                    >
                        What's happening?
                    </button>
                </div>

                <!-- Posts Feed -->
                <div class="max-w-2xl mx-auto">
                    <!-- Posts List -->
                    <div v-if="props.posts.length > 0" class="space-y-4">
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
     




