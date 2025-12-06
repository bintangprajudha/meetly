<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const props = defineProps<{
    post: {
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
    };
    currentUser: {
        id: number;
        name: string;
        email: string;
    };
}>();

const emit = defineEmits<{
    delete: [postId: number];
}>();

const imageError = ref(false);

// local reactive UI state for likes - initialized once, managed locally
const liked = ref<boolean>(props.post.liked ?? false);
const likes = ref<number>(props.post.likes_count ?? 0);

// local reactive UI state for bookmarks - initialized once, managed locally
const bookmarked = ref<boolean>(props.post.bookmarked ?? false);
const bookmarks = ref<number>(props.post.bookmarks_count ?? 0);

const toggleLike = async () => {
    const prevLiked = liked.value;
    const prevLikes = likes.value;

    // optimistic update
    liked.value = !prevLiked;
    likes.value += liked.value ? 1 : -1;
    if (likes.value < 0) likes.value = 0;

    try {
        const csrfToken = (page.props as any).csrf_token || '';

        const res = await fetch(`/posts/${props.post.id}/like`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({}),
        });

        if (!res.ok) throw res;

        const data = await res.json();
        // Use server response as source of truth
        if (data.likes_count !== undefined) likes.value = data.likes_count;
        if (data.liked !== undefined) liked.value = data.liked;
    } catch (err) {
        // revert optimistic
        liked.value = prevLiked;
        likes.value = prevLikes;
        console.error('Error toggling like', err);
    }
};

const toggleBookmark = async () => {
    const prevBookmarked = bookmarked.value;
    const prevBookmarks = bookmarks.value;

    // optimistic update
    bookmarked.value = !prevBookmarked;
    bookmarks.value += bookmarked.value ? 1 : -1;
    if (bookmarks.value < 0) bookmarks.value = 0;

    try {
        const csrfToken = (page.props as any).csrf_token || '';

        const res = await fetch(`/posts/${props.post.id}/bookmark`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({}),
        });

        if (!res.ok) throw res;

        const data = await res.json();
        console.log('Bookmark response:', data);
        // Use server response as source of truth
        if (data.bookmarked !== undefined) bookmarked.value = data.bookmarked;
        if (data.bookmarks_count !== undefined) bookmarks.value = data.bookmarks_count;
    } catch (err) {
        // revert optimistic
        bookmarked.value = prevBookmarked;
        bookmarks.value = prevBookmarks;
        console.error('Error toggling bookmark', err);
    }
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffInHours = (now.getTime() - date.getTime()) / (1000 * 60 * 60);
    
    if (diffInHours < 1) {
        const diffInMinutes = Math.floor(diffInHours * 60);
        return `${diffInMinutes}m`;
    } else if (diffInHours < 24) {
        return `${Math.floor(diffInHours)}h`;
    } else {
        const diffInDays = Math.floor(diffInHours / 24);
        return `${diffInDays}d`;
    }
};

const isOwnPost = computed(() => props.currentUser.id === props.post.user.id);

const deletePost = () => {
    if (confirm('Are you sure you want to delete this post?')) {
        emit('delete', props.post.id);
    }
};

const handleImageError = () => {
    imageError.value = true;
};
</script>

<template>
    <div class="bg-white border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
        <!-- User Header -->
        <div class="flex items-start justify-between mb-3">
            <div class="flex items-start space-x-3">
                <Link :href="`/${post.user.name}`" class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-medium text-sm flex-shrink-0 hover:bg-blue-600 transition-colors">
                    {{ post.user.name.charAt(0).toUpperCase() }}
                </Link>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center space-x-2">
                        <Link :href="`/${post.user.name}`" class="font-semibold text-gray-900 truncate hover:underline">{{ post.user.name }}</Link>
                        <Link :href="`/${post.user.name}`" class="text-gray-500 text-sm hover:underline">@{{ post.user.name }}</Link>
                        <span class="text-gray-400">·</span>
                        <span class="text-gray-500 text-sm">{{ formatDate(post.created_at) }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Delete Button (only for own posts) -->
            <button 
                v-if="isOwnPost"
                @click="deletePost"
                class="p-1 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-full transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
        </div>

        <!-- Post Content -->
        <div class="mb-3">
            <p class="text-gray-900 whitespace-pre-wrap leading-relaxed">{{ post.content }}</p>
            
            <!-- Image (if exists) -->
            <div v-if="post.image_url && !imageError" class="mt-3">
                <img 
                    :src="post.image_url" 
                    :alt="`Image from ${post.user.name}'s post`"
                    class="max-w-full h-auto rounded-lg border border-gray-200"
                    @error="handleImageError"
                />
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center space-x-6 text-gray-500">
            <!-- Reply -->
            <button class="flex items-center space-x-2 hover:text-blue-500 transition-colors group">
                <div class="p-2 rounded-full group-hover:bg-blue-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                    </svg>
                </div>
                <span class="text-sm">{{ post.replies_count || '' }}</span>
            </button>

            <!-- Like -->
            <button
                @click.prevent="toggleLike"
                :class="['flex items-center space-x-2 px-2 py-1 rounded transition-colors', liked ? 'text-red-500 bg-red-50' : 'text-gray-500 hover:bg-gray-100']"
                aria-label="Like"
            >
                <svg class="w-4 h-4" :fill="liked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
                <span class="text-sm">{{ likes }}</span>
            </button>

            <!-- Bookmark -->
            <button
                @click.prevent="toggleBookmark"
                :class="['flex items-center space-x-2 px-2 py-1 rounded transition-colors', bookmarked ? 'text-yellow-500 bg-yellow-50' : 'text-gray-500 hover:bg-gray-100']"
                aria-label="Bookmark"
            >
                <svg class="w-4 h-4" :fill="bookmarked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h6a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                </svg>
                <span class="text-sm">{{ bookmarks }}</span>
            </button>

            <!-- Share -->
            <button class="flex items-center space-x-2 hover:text-green-500 transition-colors group">
                <div class="p-2 rounded-full group-hover:bg-green-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                    </svg>
                </div>
            </button>
        </div>
    </div>
</template>