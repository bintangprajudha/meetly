<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import RepostModal from './RepostModal.vue';
import CommentModal from './CommentModal.vue';
import ShareModal from './ShareModal.vue';
import ImageViewerModal from './ImageViewerModal.vue';

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
        post_id?: number;
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
const commentsCount = computed(() => {
    const p = props.post as any;
    return p.comments_count ?? p.replies_count ?? (comments.value ? comments.value.length : 0);
});
const showCommentModal = ref(false);

const liked = ref<boolean>(props.post.liked ?? false);
const likes = ref<number>(props.post.likes_count ?? 0);
const bookmarked = ref<boolean>(props.post.bookmarked ?? false);
const bookmarks = ref<number>(props.post.bookmarks_count ?? 0);
const reposted = ref<boolean>(props.post.reposted ?? false);
const reposts = ref<number>(props.post.reposts_count ?? 0);
const showRepostModal = ref(false);
const repostLoading = ref(false);
const showShareModal = ref(false);
const showImageViewer = ref(false);
const imageViewerImages = ref<string[]>([]);
const imageViewerCurrentIndex = ref(0);
const showDropdown = ref(false);

const getInitials = (name: string) => {
    if (!name) return 'U';
    return name.split(' ').map(word => word.charAt(0)).join('').toUpperCase().slice(0, 2);
};

const getAvatarColor = (name: string) => {
    if (!name) return '#6B7280';
    const colors = ['#EF4444', '#F97316', '#F59E0B', '#EAB308', '#84CC16', '#22C55E', '#10B981', '#14B8A6', '#06B6D4', '#0EA5E9', '#3B82F6', '#6366F1', '#8B5CF6', '#A855F7', '#D946EF', '#EC4899'];
    const hash = name.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0);
    return colors[hash % colors.length];
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000);
    
    if (diffInSeconds < 60) return `${diffInSeconds}s`;
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h`;
    if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}d`;
    
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const formatViews = (num: number) => {
    if (num >= 1000000) return `${(num / 1000000).toFixed(1)}M`;
    if (num >= 1000) return `${(num / 1000).toFixed(1)}K`;
    return num.toString();
};

// FIXED: Added event parameter and stopPropagation
const toggleLike = async (event: Event) => {
    event.stopPropagation();
    event.preventDefault();
    
    const prevLiked = liked.value;
    const prevLikes = likes.value;

    // Optimistic update
    liked.value = !prevLiked;
    likes.value += liked.value ? 1 : -1;
    if (likes.value < 0) likes.value = 0;

    try {
        const targetId = props.post.type === 'repost' && props.post.post_id 
            ? props.post.post_id 
            : props.post.id;

        console.log('🔄 Toggling like for post:', targetId);

        const response = await window.axios.post(`/posts/${targetId}/like`);
        
        console.log('✅ Like response:', response.data);

        if (response.data.likes_count !== undefined) {
            likes.value = response.data.likes_count;
        }
        if (response.data.liked !== undefined) {
            liked.value = response.data.liked;
        }
    } catch (err: any) {
        liked.value = prevLiked;
        likes.value = prevLikes;
        console.error('❌ Error toggling like:', err.response?.data || err.message);
        alert('Failed to toggle like. Please try again.');
    }
};

// FIXED: Added event parameter and using axios
const toggleBookmark = async (event: Event) => {
    event.stopPropagation();
    event.preventDefault();
    
    const prevBookmarked = bookmarked.value;
    const prevBookmarks = bookmarks.value;

    bookmarked.value = !prevBookmarked;
    bookmarks.value += bookmarked.value ? 1 : -1;
    if (bookmarks.value < 0) bookmarks.value = 0;

    try {
        const targetId = props.post.type === 'repost' && props.post.post_id 
            ? props.post.post_id 
            : props.post.id;

        console.log('🔄 Toggling bookmark for post:', targetId);

        const response = await window.axios.post(`/posts/${targetId}/bookmark`);
        
        console.log('✅ Bookmark response:', response.data);

        if (response.data.bookmarked !== undefined) {
            bookmarked.value = response.data.bookmarked;
        }
        if (response.data.bookmarks_count !== undefined) {
            bookmarks.value = response.data.bookmarks_count;
        }
    } catch (err: any) {
        bookmarked.value = prevBookmarked;
        bookmarks.value = prevBookmarks;
        console.error('❌ Error toggling bookmark:', err.response?.data || err.message);
        alert('Failed to toggle bookmark. Please try again.');
    }
};

const openRepostModal = (event: Event) => {
    event.stopPropagation();
    event.preventDefault();
    showRepostModal.value = true;
};

const closeRepostModal = () => {
    showRepostModal.value = false;
};

const handleRepostSubmitted = async () => {
    await router.visit(window.location.href, {
        method: 'get',
        preserveState: false,
    });
};

const openShareModal = (event: Event) => {
    event.stopPropagation();
    event.preventDefault();
    showShareModal.value = true;
};

const closeShareModal = () => {
    showShareModal.value = false;
};

const handlePostShared = () => {
    console.log('Post shared successfully');
};

const openImageViewer = (images: string[], startIndex: number = 0) => {
    imageViewerImages.value = images;
    imageViewerCurrentIndex.value = startIndex;
    showImageViewer.value = true;
};

const closeImageViewer = () => {
    showImageViewer.value = false;
    imageViewerImages.value = [];
    imageViewerCurrentIndex.value = 0;
};

const deleteRepost = async () => {
    if (props.post.type !== 'repost' || !props.post.repost_id) {
        console.error('Cannot delete: not a repost or missing repost_id', props.post);
        return;
    }
    try {
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

const isOwnPost = computed(() => props.currentUser.id === props.post.user.id);

const deletePost = (event: Event) => {
    event.stopPropagation();
    event.preventDefault();
    
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

const viewPostDetail = () => {
    const postId = props.post.type === 'repost' && props.post.post_id ? props.post.post_id : props.post.id;
    router.visit(`/posts/${postId}`);
};

const openCommentModal = (event: Event) => {
    event.stopPropagation();
    event.preventDefault();
    showCommentModal.value = true;
};

const closeCommentModal = () => {
    showCommentModal.value = false;
};

const handleCommented = async () => {
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
                (props.post as any).comments_count = ((props.post as any).comments_count || 0) + 1;
                emit('commented', targetId, data.comment);
            }
        }
    } catch (e) {
        console.warn('Failed to fetch latest comment', e);
    }
};
</script>

<template>
    <div class="border-b border-gray-200 p-4 hover:bg-gray-50/50 transition-colors cursor-pointer">
        <!-- Post Header -->
        <div class="flex items-start gap-3">
            <!-- Avatar -->
            <Link :href="`/${post.user.name}`"
                @click.stop
                class="w-12 h-12 rounded-full flex items-center justify-center text-white font-medium text-sm flex-shrink-0 hover:opacity-90 transition-opacity -mt-0.5"
                :style="{ backgroundColor: getAvatarColor(post.user.name) }">
                {{ getInitials(post.user.name) }}
            </Link>

            <!-- Post Content -->
            <div class="flex-1 min-w-0">
                <!-- User Info & Actions -->
                <div class="flex items-start justify-between mb-1">
                    <div class="flex items-center gap-1 min-w-0 flex-1 -mt-0.2">
                        <Link :href="`/${post.user.name}`"
                            @click.stop
                            class="font-bold text-[15px] text-gray-900 hover:underline truncate">
                            {{ post.user.name }}
                        </Link>
                        <Link :href="`/${post.user.name}`"
                            @click.stop
                            class="text-gray-500 text-[15px] hover:underline truncate">
                            @{{ post.user.name.toLowerCase() }}
                        </Link>
                        <span class="text-gray-500 text-[15px]">·</span>
                        <span class="text-gray-500 text-[15px] whitespace-nowrap">{{ formatDate(post.created_at) }}</span>
                    </div>

                    <!-- More Options Button -->
                    <div v-if="isOwnPost" class="relative">
                        <button 
                            @click.stop="showDropdown = !showDropdown"
                            class="ml-2 p-1.5 text-gray-500 hover:text-blue-500 hover:bg-blue-50 rounded-full transition-colors flex-shrink-0">
                            <svg class="w-[18px] h-[18px]" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                            </svg>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div v-if="showDropdown" 
                            @click.stop
                            class="absolute right-0 mt-1 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-2 z-10">
                            <button 
                                @click="deletePost($event); showDropdown = false"
                                class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-gray-100 transition-colors flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Post Text Content -->
                <div @click="viewPostDetail" class="mt-1 mb-3">
                    <!-- Repost Indicator -->
                    <div v-if="post.type === 'repost'" class="mb-2 flex items-center gap-2 text-gray-500 text-[13px]">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M4.5 3.88l4.432 4.14-1.364 1.46L5.5 7.55V16c0 1.1.896 2 2 2H13v2H7.5c-2.209 0-4-1.79-4-4V7.55L1.432 9.48.068 8.02 4.5 3.88zM16.5 6H11V4h5.5c2.209 0 4 1.79 4 4v8.45l2.068-1.93 1.364 1.46-4.432 4.14-4.432-4.14 1.364-1.46 2.068 1.93V8c0-1.1-.896-2-2-2z"/>
                        </svg>
                        <span>{{ post.user.name }} reposted</span>
                    </div>

                    <!-- Original Post (if repost) -->
                    <div v-if="post.type === 'repost'" class="mb-3 border border-gray-200 rounded-2xl p-3">
                        <!-- Original User Info -->
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-5 h-5 rounded-full flex items-center justify-center text-white text-[11px] font-medium"
                                :style="{ backgroundColor: getAvatarColor(post.original_post_user?.name || '') }">
                                {{ getInitials(post.original_post_user?.name || '') }}
                            </div>
                            <span class="font-bold text-[15px] text-gray-900">{{ post.original_post_user?.name }}</span>
                            <span class="text-gray-500 text-[15px]">@{{ post.original_post_user?.name.toLowerCase() }}</span>
                        </div>

                        <!-- Original Content -->
                        <p class="text-[15px] text-gray-900 whitespace-pre-wrap">{{ post.content }}</p>

                        <!-- Original Images -->
                        <div v-if="post.images && post.images.length > 0" 
                            :class="[
                                'gap-0.5 rounded-2xl overflow-hidden',
                                post.images.length === 1 ? 'grid grid-cols-1' : 'grid grid-cols-2'
                            ]">
                            <img v-for="(image, index) in post.images.slice(0, 4)" 
                                :key="index" 
                                :src="image" 
                                :alt="`Image ${index + 1}`"
                                :class="[
                                    'w-full object-cover',
                                    post.images.length === 1 ? 'max-h-[500px]' : 'h-[280px]'
                                ]" />
                        </div>
                    </div>

                    <!-- Repost Caption -->
                    <p v-if="post.type === 'repost' && post.repost_caption" 
                        class="text-[15px] text-gray-900 whitespace-pre-wrap mb-3">
                        {{ post.repost_caption }}
                    </p>

                    <!-- Repost Images -->
                    <div v-if="post.type === 'repost' && post.repost_images && post.repost_images.length > 0"
                        :class="[
                            'gap-0.5 rounded-2xl overflow-hidden mb-3',
                            post.repost_images.length === 1 ? 'grid grid-cols-1' : 'grid grid-cols-2'
                        ]">
                        <img v-for="(image, index) in post.repost_images.slice(0, 4)" 
                            :key="index" 
                            :src="image"
                            :alt="`Repost Image ${index + 1}`"
                            :class="[
                                'w-full object-cover',
                                post.repost_images.length === 1 ? 'max-h-[500px]' : 'h-[280px]'
                            ]" />
                    </div>

                    <!-- Regular Post Content -->
                    <div v-if="post.type !== 'repost'">
                        <p v-if="post.content" class="text-[15px] text-gray-900 whitespace-pre-wrap mb-3 -mt-1.5">{{ post.content }}</p>

                        <!-- Images -->
                        <div v-if="post.images && post.images.length > 0"
                            :class="[
                                'gap-0.5 rounded-2xl overflow-hidden border border-gray-200',
                                post.images.length === 1 ? 'grid grid-cols-1' : 'grid grid-cols-2'
                            ]">
                            <img v-for="(image, index) in post.images.slice(0, 4)" 
                                :key="index" 
                                :src="image"
                                :alt="`Image ${index + 1}`"
                                :class="[
                                    'w-full object-cover cursor-pointer hover:opacity-95 transition-opacity',
                                    post.images.length === 1 ? 'max-h-[500px]' : 'h-[280px]'
                                ]"
                                @click.stop="openImageViewer(post.images, index)" />
                        </div>

                        <!-- Single Image URL Fallback -->
                        <div v-if="post.image_url && !imageError">
                            <img :src="post.image_url" 
                                :alt="`Post image`"
                                class="w-full max-h-[500px] object-cover rounded-2xl border border-gray-200 cursor-pointer hover:opacity-95 transition-opacity"
                                @error="handleImageError"
                                @click.stop="openImageViewer([post.image_url], 0)" />
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between max-w-[425px] mt-3">
                    <!-- Reply -->
                    <button @click="openCommentModal"
                        class="flex items-center gap-2 group text-gray-500 hover:text-blue-500 transition-colors">
                        <div class="p-2 rounded-full group-hover:bg-blue-50 transition-colors">
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4.92-1.314L3 20l1.314-4.08A8 8 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <span v-if="commentsCount > 0" class="text-[13px]">{{ commentsCount }}</span>
                    </button>

                    <!-- Repost -->
                    <button @click="openRepostModal"
                        :class="[
                            'flex items-center gap-2 group transition-colors',
                            reposted ? 'text-green-600' : 'text-gray-500 hover:text-green-600'
                        ]"
                        :disabled="repostLoading">
                        <div class="p-2 rounded-full group-hover:bg-green-50 transition-colors">
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                        <span v-if="reposts > 0" class="text-[13px]">{{ reposts }}</span>
                    </button>

                    <!-- Like -->
                    <button @click="toggleLike"
                        :class="[
                            'flex items-center gap-2 group transition-colors',
                            liked ? 'text-pink-600' : 'text-gray-500 hover:text-pink-600'
                        ]">
                        <div class="p-2 rounded-full group-hover:bg-pink-50 transition-colors">
                            <svg class="w-[18px] h-[18px]" :fill="liked ? 'currentColor' : 'none'" 
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </div>
                        <span v-if="likes > 0" class="text-[13px]">{{ likes }}</span>
                    </button>

                    <!-- Views/Analytics -->
                    <button @click.stop class="flex items-center gap-2 group text-gray-500 hover:text-blue-500 transition-colors">
                        <div class="p-2 rounded-full group-hover:bg-blue-50 transition-colors">
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <span class="text-[13px]">{{ formatViews(0) }}</span>
                    </button>

                    <!-- Bookmark & Share -->
                    <div class="flex items-center gap-0">
                        <!-- Bookmark -->
                        <button @click="toggleBookmark"
                            :class="[
                                'p-2 rounded-full group transition-colors',
                                bookmarked ? 'text-blue-500' : 'text-gray-500 hover:text-blue-500'
                            ]">
                            <div class="group-hover:bg-blue-50 rounded-full p-0 transition-colors">
                                <svg class="w-[18px] h-[18px]" :fill="bookmarked ? 'currentColor' : 'none'" 
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                </svg>
                            </div>
                        </button>

                        <!-- Share -->
                        <button @click="openShareModal"
                            class="p-2 rounded-full group text-gray-500 hover:text-blue-500 transition-colors">
                            <div class="group-hover:bg-blue-50 rounded-full p-0 transition-colors">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <RepostModal v-if="showRepostModal" :post="post"
        :target-post-id="post.type === 'repost' && post.post_id ? post.post_id : post.id" 
        @close="closeRepostModal"
        @submitted="handleRepostSubmitted" />

    <ShareModal :is-open="showShareModal" :post="post" @close="closeShareModal" @shared="handlePostShared" />

    <ImageViewerModal :is-open="showImageViewer" :images="imageViewerImages" :current-index="imageViewerCurrentIndex"
        @close="closeImageViewer" />

    <CommentModal :isOpen="showCommentModal"
        :postId="props.post.type === 'repost' && props.post.post_id ? props.post.post_id : props.post.id"
        :user="props.currentUser" @close="closeCommentModal" @commented="handleCommented" />
</template>