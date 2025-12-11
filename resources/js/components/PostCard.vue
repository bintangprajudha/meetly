<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import RepostModal from './RepostModal.vue';
import CommentModal from './CommentModal.vue';

const page = usePage();

const props = defineProps<{
    post: {
        id: number | string;
        type?: string;
        content: string;
        image_url?: string;
        images?: string[];
        likes_count: number;
        bookmarks_count: number;
        reposts_count?: number;
        created_at: string;
        liked?: boolean;
        bookmarked?: boolean;
        reposted?: boolean;
        repost_id?: number;
        post_id?: number; // original post id when this item is a repost
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
    };
    currentUser: {
        id: number;
        name: string;
        email: string;
    };
}>();

const emit = defineEmits<{
    delete: [postId: number | string];
    commented: [postId: number | string, comment: any];
}>();

const imageError = ref(false);
const comments = ref((props.post as any).comments ? [...(props.post as any).comments] : []);
const showCommentModal = ref(false);
const showCommentBox = ref(false);
const commentInput = ref('');
const posting = ref(false);
const commentError = ref<string | null>(null);

// local reactive UI state for likes - initialized once, managed locally
const liked = ref<boolean>(props.post.liked ?? false);
const likes = ref<number>(props.post.likes_count ?? 0);

// local reactive UI state for bookmarks - initialized once, managed locally
const bookmarked = ref<boolean>(props.post.bookmarked ?? false);
const bookmarks = ref<number>(props.post.bookmarks_count ?? 0);

// local reactive UI state for reposts
const reposted = ref<boolean>(props.post.reposted ?? false);
const reposts = ref<number>(props.post.reposts_count ?? 0);
const showRepostModal = ref(false);
const repostLoading = ref(false);

const toggleLike = async () => {
    const prevLiked = liked.value;
    const prevLikes = likes.value;

    // optimistic update
    liked.value = !prevLiked;
    likes.value += liked.value ? 1 : -1;
    if (likes.value < 0) likes.value = 0;

    try {
        const csrfToken = (page.props as any).csrf_token || '';
        // Use post_id for reposts (original post), otherwise use post.id
        const targetId = props.post.type === 'repost' && props.post.post_id ? props.post.post_id : props.post.id;

        const res = await fetch(`/posts/${targetId}/like`, {
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
        // Use post_id for reposts (original post), otherwise use post.id
        const targetId = props.post.type === 'repost' && props.post.post_id ? props.post.post_id : props.post.id;

        const res = await fetch(`/posts/${targetId}/bookmark`, {
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

const openRepostModal = () => {
    console.log('Opening repost modal for post:', props.post);
    console.log('Target post ID will be:', props.post.type === 'repost' && props.post.post_id ? props.post.post_id : props.post.id);
    showRepostModal.value = true;
};

const closeRepostModal = () => {
    showRepostModal.value = false;
};

const handleRepostSubmitted = async () => {
    // Reload the page to get updated feed with the repost
    await router.visit(window.location.href, {
        method: 'get',
        preserveState: false,
    });
};

const deleteRepost = async () => {
    if (props.post.type !== 'repost' || !props.post.repost_id) {
        console.error('Cannot delete: not a repost or missing repost_id', props.post);
        return;
    }
    try {
        console.log('Deleting repost with ID:', props.post.repost_id);
        
        await router.delete(`/reposts/${props.post.repost_id}`, {
            preserveState: false,
            onSuccess: () => {
                console.log('Repost deleted successfully');
            },
            onError: (errors) => {
                console.error('Delete failed:', errors);
                alert('Failed to delete repost. Please try again.');
            }
        });
    } catch (err) {
        console.error('Error deleting repost:', err);
        alert('Failed to delete repost. Please try again.');
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
    if (props.post.type === 'repost') {
        if (confirm('Are you sure you want to delete this repost?')) {
            deleteRepost();
        }
    } else {
        if (confirm('Are you sure you want to delete this post?')) {
            emit('delete', props.post.id);
        }
    }
};

const handleImageError = () => {
    imageError.value = true;
};

const openCommentModal = () => {
    showCommentModal.value = true;
};

const closeCommentModal = () => {
    showCommentModal.value = false;
};

const handleCommented = async () => {
    // Fetch the latest comment from the server and prepend it
    try {
        const targetId = props.post.type === 'repost' && props.post.post_id ? props.post.post_id : props.post.id;
        const res = await fetch(`/posts/${targetId}/comments/latest`, {
            method: 'GET',
            credentials: 'include',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (res.ok) {
            const data = await res.json();
            if (data?.comment) {
                comments.value.unshift(data.comment);
            }
        }
    } catch (e) {
        console.warn('Failed to fetch latest comment', e);
    }
};

const postComment = async () => {
    commentError.value = null;
    const content = (commentInput.value || '').trim();
    if (!content) {
        commentError.value = 'Comment cannot be empty';
        return;
    }

    posting.value = true;

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    // Use post_id for reposts (original post), otherwise use post.id
    const targetId = props.post.type === 'repost' && props.post.post_id ? props.post.post_id : props.post.id;

    router.post(`/posts/${targetId}/comments`, { content, _token: token }, {
        preserveState: true,
        onSuccess: async () => {
            // Clear input
            commentInput.value = '';
            showCommentBox.value = false;

            // Fetch the latest comment from the server and prepend it so it appears immediately
            try {
                const res = await fetch(`/posts/${targetId}/comments/latest`, {
                    method: 'GET',
                    credentials: 'include',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (res.ok) {
                    const data = await res.json();
                    if (data?.comment) {
                        comments.value.unshift(data.comment);
                    }
                }
            } catch (e) {
                // ignore fetch error but leave comment UI consistent
                console.warn('Failed to fetch latest comment', e);
            } finally {
                posting.value = false;
            }
        },
        onError: (errors) => {
            commentError.value = errors?.message || 'Failed to post comment';
            posting.value = false;
        }
    });
};

const cancelComment = () => {
    // simply close the box and clear errors (Inertia post is not abortable here)
    showCommentBox.value = false;
    commentError.value = null;
    posting.value = false;
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
            <!-- Repost indicator - tampilkan jika ini adalah repost -->
            <div v-if="post.type === 'repost'" class="mb-3 flex items-center space-x-2 text-green-600 text-sm">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M7 16V4m0 0L3 8m0 0l4 4m10-4v12m0 0l4-4m0 0l-4-4"></path>
                </svg>
                <span>{{ post.user.name }} reposted</span>
            </div>

            <!-- Original post container jika repost -->
            <div v-if="post.type === 'repost'" class="mb-3 border border-gray-300 rounded-lg p-3 bg-gray-50">
                <!-- Original post user info -->
                <div class="flex items-start space-x-2 mb-2">
                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white font-medium text-xs flex-shrink-0">
                        {{ post.original_post_user?.name.charAt(0).toUpperCase() }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-900 text-sm">{{ post.original_post_user?.name }}</p>
                    </div>
                </div>
                
                <!-- Original post content -->
                <p class="text-gray-900 text-sm whitespace-pre-wrap leading-relaxed mb-2">{{ post.content }}</p>

                <!-- Original post images -->
                <div v-if="post.images && post.images.length > 0" class="grid grid-cols-2 gap-2 mb-2">
                    <img 
                        v-for="(image, index) in post.images"
                        :key="index"
                        :src="image" 
                        :alt="`Image ${index + 1}`"
                        class="w-full h-24 object-cover rounded border border-gray-300"
                    />
                </div>
            </div>

            <!-- Repost caption (jika ada) -->
            <div v-if="post.type === 'repost' && post.repost_caption" class="mb-3">
                <p class="text-gray-900 whitespace-pre-wrap leading-relaxed">{{ post.repost_caption }}</p>
            </div>

            <!-- Repost images (jika ada) -->
            <div v-if="post.type === 'repost' && post.repost_images && post.repost_images.length > 0" class="grid grid-cols-2 gap-2 mb-3">
                <img 
                    v-for="(image, index) in post.repost_images"
                    :key="index"
                    :src="image" 
                    :alt="`Repost Image ${index + 1}`"
                    class="w-full h-24 object-cover rounded border border-gray-200"
                />
            </div>

            <!-- Regular post content (non-repost) -->
            <div v-if="post.type !== 'repost'">
                <p class="text-gray-900 whitespace-pre-wrap leading-relaxed mb-3">{{ post.content }}</p>

                <!-- Images for regular post -->
                <div v-if="post.images && post.images.length > 0" class="mt-3 grid grid-cols-2 gap-2">
                    <img 
                        v-for="(image, index) in post.images"
                        :key="index"
                        :src="image" 
                        :alt="`Image ${index + 1}`"
                        class="max-w-full h-auto rounded-lg border border-gray-200"
                    />
                </div>

                <!-- Single image_url fallback -->
                <div v-if="post.image_url && !imageError" class="mt-3">
                    <img 
                        :src="post.image_url" 
                        :alt="`Image from ${post.user.name}'s post`"
                        class="max-w-full h-auto rounded-lg border border-gray-200"
                        @error="handleImageError"
                    />
                </div>
            </div>
            
            <!-- Comments preview -->
            <div v-if="comments && comments.length" class="mt-3 space-y-3">
                <div v-for="comment in comments" :key="comment.id" class="flex items-start space-x-3">
                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-medium text-gray-700">{{ (comment.user?.name || 'U').charAt(0).toUpperCase() }}</div>
                    <div class="flex-1">
                        <div class="text-sm">
                            <span class="font-semibold text-gray-800 mr-2">{{ comment.user?.name || 'Unknown' }}</span>
                            <span class="text-gray-500 text-xs">· {{ formatDate(comment.created_at) }}</span>
                        </div>
                        <div class="text-gray-800 text-sm">{{ comment.content }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center space-x-4 text-gray-500">
            <!-- Repost (left arrow) - Trigger repost modal -->
            <button 
                @click="openRepostModal"
                :class="['flex items-center space-x-2 hover:text-green-500 transition-colors group', reposted ? 'text-green-500' : 'text-gray-500']"
                :disabled="repostLoading"
            >
                <div class="p-2 rounded-full group-hover:bg-green-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                    </svg>
                </div>
                <span class="text-sm">{{ reposts || '' }}</span>
            </button>

            <!-- Comment (opens comment modal) -->
            <button @click="openCommentModal" class="flex items-center space-x-2 hover:text-blue-500 transition-colors group">
                <div class="p-2 rounded-full group-hover:bg-blue-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.8L3 20l1.2-3.8A7.966 7.966 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </div>
                <span class="text-sm">{{ comments.length || '' }}</span>
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
            <button
                class="group flex items-center space-x-2 transition-colors hover:text-green-500"
            >
                <div
                    class="rounded-full p-2 transition-colors group-hover:bg-green-50"
                >
                    <svg
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"
                        ></path>
                    </svg>
                </div>
            </button>
        </div>
    </div>

    <!-- Repost Modal -->
    <RepostModal 
        v-if="showRepostModal"
        :post="post"
        :target-post-id="post.type === 'repost' && post.post_id ? post.post_id : post.id"
        @close="closeRepostModal"
        @submitted="handleRepostSubmitted"
    />

    <!-- Comment Modal -->
    <CommentModal 
        :isOpen="showCommentModal" 
        :postId="props.post.type === 'repost' && props.post.post_id ? props.post.post_id : props.post.id"
        :user="props.currentUser"
        @close="closeCommentModal"
        @commented="handleCommented"
    />
</template>
