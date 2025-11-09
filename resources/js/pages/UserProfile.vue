<template>
    <Head :title="`@${profileUser.name} - Profile`" />

    <div class="min-h-screen bg-gray-50">
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
                        <div class="mt-2" v-if="$page.props.auth && $page.props.auth.user">
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
        <!-- Header Navigation -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center space-x-4">                       
                        <h1 class="text-xl font-semibold text-gray-900">Profile</h1>
                    </div>
                    
                    <!-- User Actions -->
                    <div v-if="$page.props.auth.user" class="flex items-center space-x-4">
                        <!-- Use a guarded, strict ID comparison to determine if the current user
                             is viewing their own profile. This is safer than comparing names. -->
                        <template v-if="$page.props.auth.user && profileUser && $page.props.auth.user.id === profileUser.id">
                            <span class="text-gray-600">{{ $page.props.auth.user.name }}</span>
                            <Link
                                href="/logout" 
                                method="post"
                                as="button"
                                class="px-4 py-2 text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors text-sm"
                            >
                                Logout
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </header>

    <!-- Main Content -->
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
                    <!-- Follow Button (placeholder for future) -->
                    <!-- <div v-if="$page.props.auth.user && $page.props.auth.user.id !== profileUser.id" class="pt-2">
                        <button class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                            Follow
                        </button>
                    </div> -->

                    

                    <!-- Bio (placeholder) -->
                    <div class="mt-6">
                        <p class="text-gray-700">
                            Welcome to my profile! I love sharing thoughts and connecting with others.
                        </p>
                        <div class="flex items-center space-x-4 mt-2 text-gray-500 text-sm">
                            <span>📍 Location</span>
                            <span>🗓️ Joined {{ formatJoinDate(profileUser.created_at) }}</span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">Follow</button>
                    </div>

                </div>
            </div>

            <!-- Posts Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Posts</h2>
                </div>

                <!-- Posts List -->
                <div v-if="posts.length > 0" class="divide-y divide-gray-200">
                    <PostCard
                        v-for="post in posts"
                        :key="post.id"
                        :post="post"
                        :current-user="$page.props.auth.user"
                        @delete="handleDeletePost"
                        class="border-none"
                    />
                </div>

                <!-- Empty State -->
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
        </main>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import PostCard from '../components/PostCard.vue';

// Define props
const props = defineProps({
    profileUser: {
        type: Object,
        required: true
    },
    posts: {
        type: Array,
        default: () => []
    },
    postsCount: {
        type: Number,
        default: 0
    }
});

// Helper function to get user initials
const getInitials = (name) => {
    if (!name) return 'U';
    return name.split(' ')
        .map(word => word.charAt(0))
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

// Helper function to get avatar color
const getAvatarColor = (name) => {
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

// Helper function to format join date
const formatJoinDate = (dateString) => {
    const date = new Date(dateString);
    const options = { 
        year: 'numeric', 
        month: 'long' 
    };
    return date.toLocaleDateString('en-US', options);
};

// Handle post deletion
const handleDeletePost = (postId) => {
    if (confirm('Are you sure you want to delete this post?')) {
        router.delete(`/posts/${postId}`, {
            preserveScroll: true,
            onSuccess: () => {
                // Reload page to update posts
                router.reload({ only: ['posts', 'postsCount'] });
            },
            onError: (error) => {
                console.error('Error deleting post:', error);
                alert('Error deleting post. Please try again.');
            }
        });
    }
};
</script>