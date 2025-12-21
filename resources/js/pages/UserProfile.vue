<script setup lang="ts">
import PostCard from '@/components/PostCard.vue';
import PostModal from '@/components/PostModal.vue';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import EditProfile from '@/components/EditProfile.vue';
import { Head, router, usePage, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
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
    };
}

// Define props
const props = defineProps<{
    user?: {
        id: number;
        name: string;
        email: string;
        username?: string;
        avatar?: string | null;
        banner?: string | null;
        bio?: string;
        location?: string;
        website?: string;
        created_at?: string;
        followers_count?: number;
        following_count?: number;
    };
    profileUser?: {
        id: number;
        name: string;
        email: string;
        username?: string;  
        avatar?: string | null;   
        banner?: string | null;    
        bio?: string;       
        location?: string;  
        website?: string;   
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
const profileUser = (props.user ?? (props as any).profileUser) as {
    id: number;
    name: string;
    email: string;
    username?: string;
    avatar?: string | null;
    banner?: string | null;
    bio?: string;
    location?: string;
    website?: string;
    created_at?: string;
    followers_count?: number;
    following_count?: number;
};

// Authenticated user from Inertia page props
const page = usePage();
const pageTyped = page as unknown as {
    props: { auth?: { user?: { id: number; name: string; email?: string } } };
};
const authUser = pageTyped.props.auth?.user ?? null;

const showPostModal = ref(false);

// Active tab state
const activeTab = ref<
    'posts' | 'replies' | 'highlights' | 'articles' | 'media' | 'likes'
>('posts');

// Computed property to filter posts with media (exclude reposts)
const mediaPosts = computed(() => {
    return props.posts.filter((post) => {
        // Exclude reposted posts (posts with type 'repost' or posts that have original_post_user)
        const isRepost = post.type === 'repost' || post.original_post_user;
        if (isRepost) return false;

        // Only include posts with images
        const hasImages =
            (post.images && post.images.length > 0) || post.image_url;

        return hasImages;
    });
});

// Computed property to combine replies and reposts
const repliesAndReposts = computed(() => {
    const replies = props.replies || [];
    const reposts = props.reposts || [];

    // Combine both arrays
    const combined = [...replies, ...reposts];

    // Sort by created_at (newest first)
    return combined.sort((a, b) => {
        const dateA = new Date(a.created_at).getTime();
        const dateB = new Date(b.created_at).getTime();
        return dateB - dateA;
    });
});

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

// Set active tab
const setActiveTab = (
    tab: 'posts' | 'replies' | 'media' | 'likes',
) => {
    activeTab.value = tab;
};

const followUser = async () => {
    await router.post(
        `/users/${profileUser.id}/follow`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                isFollowing.value = true;
            },
        },
    );
};

const unfollowUser = async () => {
    await router.delete(`/users/${profileUser.id}/follow`, {
        preserveScroll: true,
        onSuccess: () => {
            isFollowing.value = false;
        },
    });
};
</script>

<template>

    <Head />

    <AppSidebarLayout @open-post="openPostModal">
        <Navbar />
        <main class="mx-auto min-h-screen max-w-2xl bg-white">
            <!-- Top Bar with Back Button -->
            <div class="sticky top-0 z-10 border-b border-gray-200 bg-white/95 backdrop-blur-sm">
                <div class="flex items-center space-x-8 px-4 py-2">
                    <button @click="router.visit('/dashboard')"
                        class="-ml-2 rounded-full p-2 transition hover:bg-gray-100">
                        <svg class="h-5 w-5 text-black" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">
                            {{ profileUser.name }}
                        </h1>
                        <p class="text-[13px] text-gray-500">
                            {{ props.posts.length }} posts
                        </p>
                    </div>
                </div>
            </div>

            <!-- Banner -->
            <div class="h-[200px] w-full overflow-hidden">
                <img v-if="profileUser.banner" :src="`/storage/${profileUser.banner}`" alt="Banner"
                    class="h-full w-full object-cover" />
                <div v-else class="h-full w-full bg-gradient-to-br from-slate-200 via-slate-300 to-slate-200"></div>
            </div>

            <!-- Profile Section -->
            <div class="px-4 pb-4">
                <div class="mb-3 flex items-start justify-between">
                    <!-- Avatar -->
                    <div
                        class="-mt-[75px] h-[140px] w-[140px] overflow-hidden rounded-full border-4 border-white bg-white shadow-md">
                        <img v-if="profileUser.avatar" :src="`/storage/${profileUser.avatar}`" :alt="profileUser.name"
                            class="h-full w-full object-cover" />
                        <div v-else class="flex h-full w-full items-center justify-center text-4xl font-bold"
                            :style="{ backgroundColor: getAvatarColor(profileUser.name) }">
                            {{ getInitials(profileUser.name) }}
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="mt-3 flex items-center gap-2">
                        <!-- Edit Profile Button (for own profile) -->
                        <EditProfile v-if="authUser && authUser.id === profileUser.id" :user="profileUser" />

                        <!-- Follow Button (for other users when not following) -->
                        <button v-if="
                            authUser &&
                            authUser.id !== profileUser.id &&
                            !isFollowing
                        " @click="followUser"
                            class="rounded-full bg-black px-5 py-[7px] text-[15px] font-semibold text-white transition hover:bg-gray-800">
                            Follow
                        </button>

                        <!-- Following Button (for other users when already following) -->
                        <button v-if="
                            authUser &&
                            authUser.id !== profileUser.id &&
                            isFollowing
                        " @click="unfollowUser"
                            class="group rounded-full border border-gray-300 px-5 py-[7px] text-[15px] font-semibold text-gray-900 transition hover:border-red-600 hover:bg-red-50 hover:text-red-600">
                            <span class="group-hover:hidden">Following</span>
                            <span class="hidden group-hover:inline">Unfollow</span>
                        </button>

                    </div>
                </div>

                <!-- User Information -->
                <div class="mb-2">
                    <div class="mb-0.5 flex items-center gap-1">
                        <h2 class="text-xl font-bold text-gray-900">
                            {{ profileUser.name }}
                        </h2>
                        <svg v-if="
                            authUser && authUser.id === profileUser.id
                        " class="h-[18px] w-[18px] text-blue-400" fill="currentColor" viewBox="0 0 22 22">
                           
                        </svg>
                    </div>
                    <p class="text-[15px] text-gray-500">
                        @{{ profileUser.username || profileUser.name.replace(/\s+/g, '').toLowerCase() }}
                    </p>
                </div>

                <!-- Bio (if exists) -->
                <p v-if="profileUser.bio" class="mb-3 text-[15px] text-gray-900">
                    {{ profileUser.bio }}
                </p>

                <!-- Location & Website -->
                <div v-if="profileUser.location || profileUser.website"
                    class="mb-3 flex flex-wrap items-center gap-3 text-[15px] text-gray-500">
                    <!-- Location -->
                    <div v-if="profileUser.location" class="flex items-center gap-1">
                        <svg class="h-[17px] w-[17px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>{{ profileUser.location }}</span>
                    </div>

                    <!-- Website -->
                    <a v-if="profileUser.website" :href="profileUser.website" target="_blank"
                        class="flex items-center gap-1 transition hover:underline">
                        <svg class="h-[17px] w-[17px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        <span class="text-blue-500">{{ profileUser.website.replace(/^https?:\/\//, '') }}</span>
                    </a>
                </div>

                <!-- Join Date -->
                <div class="mb-3 flex items-center text-[15px] text-gray-500">
                    <svg class="mr-1 h-[17px] w-[17px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Joined December 2025</span>
                </div>

                <!-- Stats dengan Link ke Followers/Following -->
                <div class="flex items-center space-x-5 text-[15px]">
                    <!-- Following Button -->
                    <Link :href="`/${profileUser.name}/following`" class="group transition hover:underline">
                        <span class="font-bold text-gray-900">
                            {{ profileUser.following_count || 0 }}
                        </span>
                        <span class="ml-1 text-gray-500 group-hover:text-gray-700">
                            Following
                        </span>
                    </Link>

                    <!-- Followers Button -->
                    <Link :href="`/${profileUser.name}/followers`" class="group transition hover:underline">
                        <span class="font-bold text-gray-900">
                            {{ profileUser.followers_count || 0 }}
                        </span>
                        <span class="ml-1 text-gray-500 group-hover:text-gray-700">
                            Followers
                        </span>
                    </Link>
                </div>
            </div>

            <!-- Navigation Tabs -->
            <div class="sticky top-[57px] z-10 border-b border-gray-200 bg-white">
                <nav class="flex">
                    <button @click="setActiveTab('posts')" :class="[
                        'relative flex-1 py-4 text-center text-[15px] font-medium transition hover:bg-gray-50/80',
                        activeTab === 'posts'
                            ? 'font-semibold text-gray-900'
                            : 'text-gray-500',
                    ]">
                        Posts
                        <div v-if="activeTab === 'posts'"
                            class="absolute bottom-0 left-1/2 h-1 w-[56px] -translate-x-1/2 transform rounded-full bg-blue-500">
                        </div>
                    </button>

                    <button @click="setActiveTab('replies')" :class="[
                        'relative flex-1 py-4 text-center text-[15px] font-medium transition hover:bg-gray-50/80',
                        activeTab === 'replies'
                            ? 'font-semibold text-gray-900'
                            : 'text-gray-500',
                    ]">
                        Repost
                        <div v-if="activeTab === 'replies'"
                            class="absolute bottom-0 left-1/2 h-1 w-[56px] -translate-x-1/2 transform rounded-full bg-blue-500">
                        </div>
                    </button>

                    <button @click="setActiveTab('media')" :class="[
                        'relative flex-1 py-4 text-center text-[15px] font-medium transition hover:bg-gray-50/80',
                        activeTab === 'media'
                            ? 'font-semibold text-gray-900'
                            : 'text-gray-500',
                    ]">
                        Media
                        <div v-if="activeTab === 'media'"
                            class="absolute bottom-0 left-1/2 h-1 w-[56px] -translate-x-1/2 transform rounded-full bg-blue-500">
                        </div>
                    </button>

                    <button @click="setActiveTab('likes')" :class="[
                        'relative flex-1 py-4 text-center text-[15px] font-medium transition hover:bg-gray-50/80',
                        activeTab === 'likes'
                            ? 'font-semibold text-gray-900'
                            : 'text-gray-500',
                    ]">
                        Likes
                        <div v-if="activeTab === 'likes'"
                            class="absolute bottom-0 left-1/2 h-1 w-[56px] -translate-x-1/2 transform rounded-full bg-blue-500">
                        </div>
                    </button>
                </nav>
            </div>

            <!-- Content Panel -->
            <div class="min-h-[400px]">
                <!-- POSTS -->
                <div v-if="activeTab === 'posts'">
                    <div v-if="props.posts.length === 0" class="px-8 py-24 text-center">
                        <h3 class="mb-2 text-[31px] font-bold text-gray-900">
                            No posts yet
                        </h3>
                    </div>
                    <div v-else>
                        <PostCard v-for="post in props.posts" :key="post.id" :post="post"
                            :current-user="authUser as any" @delete="handleDeletePost"
                            class="cursor-pointer border-b border-gray-200 transition hover:bg-gray-50/60" />
                    </div>
                </div>

                <!-- REPLIES -->
                <div v-if="activeTab === 'replies'">
                    <div v-if="repliesAndReposts.length === 0" class="px-8 py-24 text-center">
                        <h3 class="mb-2 text-[31px] font-bold text-gray-900">
                            No replies yet
                        </h3>
                        <p class="text-[15px] text-gray-500">
                            When {{ profileUser.name }} replies to posts,
                            they'll show up here
                        </p>
                    </div>
                    <div v-else>
                        <PostCard v-for="post in repliesAndReposts" :key="post.id" :post="post"
                            :current-user="authUser as any" @delete="handleDeletePost"
                            class="cursor-pointer border-b border-gray-200 transition hover:bg-gray-50/60" />
                    </div>
                </div>

                <!-- MEDIA -->
                <div v-if="activeTab === 'media'">
                    <div v-if="mediaPosts.length === 0" class="px-8 py-24 text-center">
                        <h3 class="mb-2 text-[31px] font-bold text-gray-900">
                            No media yet
                        </h3>
                        <p class="text-[15px] text-gray-500">
                            Photos and videos will show up here
                        </p>
                    </div>
                    <div v-else class="grid grid-cols-3 gap-1 p-1">
                        <template v-for="post in mediaPosts" :key="post.id">
                            <!-- Handle multiple images in a post -->
                            <template v-if="post.images && post.images.length > 0">
                                <div v-for="(image, index) in post.images" :key="`${post.id}-${index}`" @click="
                                    router.visit(`/posts/${post.id}`)
                                    " class="group relative aspect-square cursor-pointer overflow-hidden bg-gray-200">
                                    <img :src="image" :alt="`Media ${index + 1}`"
                                        class="h-full w-full object-cover transition group-hover:opacity-90" />
                                </div>
                            </template>
                            <!-- Handle single image_url -->
                            <div v-else-if="post.image_url" @click="router.visit(`/posts/${post.id}`)"
                                class="group relative aspect-square cursor-pointer overflow-hidden bg-gray-200">
                                <img :src="post.image_url" alt="Media"
                                    class="h-full w-full object-cover transition group-hover:opacity-90" />
                            </div>
                        </template>
                    </div>
                </div>

                <!-- LIKES -->
                <div v-if="activeTab === 'likes'">
                    <div v-if="
                        !props.likedPosts ||
                        props.likedPosts.length === 0
                    " class="px-8 py-24 text-center">
                        <h3 class="mb-2 text-[31px] font-bold text-gray-900">
                            No likes yet
                        </h3>
                        <p class="text-[15px] text-gray-500">
                            When {{ profileUser.name }} likes a post, it'll
                            show up here
                        </p>
                    </div>
                    <div v-else>
                        <PostCard v-for="post in props.likedPosts" :key="post.id" :post="post"
                            :current-user="authUser as any" @delete="handleDeletePost"
                            class="cursor-pointer border-b border-gray-200 transition hover:bg-gray-50/60" />
                    </div>
                </div>
            </div>
        </main>

        <PostModal :is-open="showPostModal" :user="authUser as any" @close="showPostModal = false"
            @posted="handlePostCreated" />
    </AppSidebarLayout>
</template>
