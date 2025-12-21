<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import CommentModal from './CommentModal.vue';
import MediaPreviewModal from './MediaPreviewModal.vue';
import RepostModal from './RepostModal.vue';
import ShareModal from './ShareModal.vue';

const props = defineProps<{
    post: {
        id: number;
        type?: string;
        content: string;
        image_url?: string;
        images?: string[];
        videos?: string[];
        media?: Array<{ type: 'image' | 'video'; src: string }>;
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
        original_post?: {
            id: number;
            content: string;
            image_url?: string;
            images?: string[];
            user: {
                id: number;
                name: string;
                email: string;
                username?: string;
                avatar?: string | null;
                profile?: {
                    avatar?: string | null;
                };
            };
        };
        original_post_user?: {
            id: number;
            name: string;
            email: string;
            username?: string;
            avatar?: string | null;
            profile?: {
                avatar?: string | null;
            };
        };
        user: {
            id: number;
            name: string;
            email: string;
            username?: string;
            avatar?: string | null;
            profile?: {
                avatar?: string | null;
            };
        };
    };
    currentUser: {
        id: number;
        name: string;
        email: string;
        username?: string;
        avatar?: string | null;
        is_admin?: boolean;
        profile?: {
            avatar?: string | null;
        };
    };
}>();

const emit = defineEmits<{
    delete: [postId: number | string];
    commented: [postId: number | string, comment: any];
}>();

const imageError = ref(false);
const comments = ref(
    (props.post as any).comments ? [...(props.post as any).comments] : [],
);
const commentsCount = computed(() => {
    const p = props.post as any;
    return (
        p.comments_count ??
        p.replies_count ??
        (comments.value ? comments.value.length : 0)
    );
});
const showCommentModal = ref(false);

// Media preview modal (images + videos)
const showMediaPreview = ref(false);
const currentMediaIndex = ref(0);
const previewMedia = ref<Array<{ type: 'image' | 'video'; src: string }>>([]);

// local reactive UI state for likes - initialized once, managed locally
const liked = ref<boolean>(props.post.liked ?? false);
const likes = ref<number>(props.post.likes_count ?? 0);
const bookmarked = ref<boolean>(props.post.bookmarked ?? false);
const bookmarks = ref<number>(props.post.bookmarks_count ?? 0);
const reposted = ref<boolean>(props.post.reposted ?? false);
const reposts = ref<number>(props.post.reposts_count ?? 0);
const showRepostModal = ref(false);
const repostLoading = ref(false);
const showShareModal = ref(false);
const showDropdown = ref(false);

// Helper functions for user profile
const getUserAvatar = (user: any) => {
    const avatar = user?.profile?.avatar || user?.avatar;
    if (!avatar) return null;

    return avatar.startsWith('http')
        ? avatar
        : `/storage/${avatar}`;
};

const getUserUsername = (user: any) => {
    if (!user) return '';
    return user.username || user.name.toLowerCase().replace(/\s+/g, '');
};

const getUserProfileUrl = (user: any) => {
    if (!user) return '#';
    const username = getUserUsername(user);
    return `/@${username}`;
};

const getInitials = (name: string) => {
    if (!name) return 'U';
    return name
        .split(' ')
        .map((word) => word.charAt(0))
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

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
    const hash = name
        .split('')
        .reduce((acc, char) => acc + char.charCodeAt(0), 0);
    return colors[hash % colors.length];
};

const getPreviewImages = () => {
    if (props.post.images && props.post.images.length > 0)
        return props.post.images;
    if (props.post.image_url) return [props.post.image_url];
    return [];
};

const getPreviewMedia = () => {
    const p: any = props.post as any;
    if (Array.isArray(p.media) && p.media.length > 0) {
        return p.media
            .filter(
                (m: any) =>
                    m &&
                    (m.type === 'image' || m.type === 'video') &&
                    typeof m.src === 'string' &&
                    m.src.length > 0,
            )
            .map((m: any) => ({ type: m.type, src: m.src }));
    }

    const media: Array<{ type: 'image' | 'video'; src: string }> = [];
    getPreviewImages().forEach((src) => media.push({ type: 'image', src }));
    (props.post.videos || []).forEach((src) =>
        media.push({ type: 'video', src }),
    );
    return media;
};

const previewMediaList = computed(() => getPreviewMedia());

// Open media preview
const openMediaPreview = (index: number, type: 'image' | 'video') => {
    void type;
    previewMedia.value = getPreviewMedia();
    currentMediaIndex.value = index;
    showMediaPreview.value = true;
};

// Close preview
const closeMediaPreview = () => {
    showMediaPreview.value = false;
};

// Update media index
const updateMediaIndex = (index: number) => {
    currentMediaIndex.value = index;
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

const toggleLike = async (event: Event) => {
    event.stopPropagation();
    event.preventDefault();

    const prevLiked = liked.value;
    const prevLikes = likes.value;

    liked.value = !prevLiked;
    likes.value += liked.value ? 1 : -1;
    if (likes.value < 0) likes.value = 0;

    try {
        const targetId =
            props.post.type === 'repost' && props.post.post_id
                ? props.post.post_id
                : props.post.id;

        const response = await window.axios.post(`/posts/${targetId}/like`);

        if (response.data.likes_count !== undefined) {
            likes.value = response.data.likes_count;
        }
        if (response.data.liked !== undefined) {
            liked.value = response.data.liked;
        }
    } catch (err: any) {
        liked.value = prevLiked;
        likes.value = prevLikes;
        console.error(
            '❌ Error toggling like:',
            err.response?.data || err.message,
        );
        alert('Failed to toggle like. Please try again.');
    }
};

const toggleBookmark = async (event: Event) => {
    event.stopPropagation();
    event.preventDefault();

    const prevBookmarked = bookmarked.value;
    const prevBookmarks = bookmarks.value;

    bookmarked.value = !prevBookmarked;
    bookmarks.value += bookmarked.value ? 1 : -1;
    if (bookmarks.value < 0) bookmarks.value = 0;

    try {
        const targetId =
            props.post.type === 'repost' && props.post.post_id
                ? props.post.post_id
                : props.post.id;

        const response = await window.axios.post(`/posts/${targetId}/bookmark`);

        if (response.data.bookmarked !== undefined) {
            bookmarked.value = response.data.bookmarked;
        }
        if (response.data.bookmarks_count !== undefined) {
            bookmarks.value = response.data.bookmarks_count;
        }
    } catch (err: any) {
        bookmarked.value = prevBookmarked;
        bookmarks.value = prevBookmarks;
        console.error(
            '❌ Error toggling bookmark:',
            err.response?.data || err.message,
        );
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

const deleteRepost = async () => {
    if (props.post.type !== 'repost' || !props.post.repost_id) {
        console.error(
            'Cannot delete: not a repost or missing repost_id',
            props.post,
        );
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
            },
        });
    } catch (err) {
        console.error('Error deleting repost:', err);
        alert('Failed to delete repost. Please try again.');
    }
};

const isOwnPost = computed(() => props.currentUser.id === props.post.user.id);
const isAdmin = computed(() => props.currentUser.is_admin === true);
const canDelete = computed(() => isOwnPost.value || isAdmin.value);

const showReportModal = ref(false);
const reportReason = ref('');
const reportDescription = ref('');
const reportLoading = ref(false);

const submitReport = () => {
    if (!reportReason.value.trim()) {
        alert('Please provide a reason for reporting this post.');
        return;
    }

    reportLoading.value = true;
    router.post(
        '/reports',
        {
            post_id: props.post.id,
            reason: reportReason.value,
            description: reportDescription.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showReportModal.value = false;
                reportReason.value = '';
                reportDescription.value = '';
            },
            onFinish: () => {
                reportLoading.value = false;
            },
        },
    );
};

const handleImageError = () => {
    imageError.value = true;
};

const viewPostDetail = () => {
    const postId =
        props.post.type === 'repost' && props.post.post_id
            ? props.post.post_id
            : props.post.id;
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
        const targetId =
            props.post.type === 'repost' && props.post.post_id
                ? props.post.post_id
                : props.post.id;
        const res = await fetch(`/posts/${targetId}/comments/latest`, {
            method: 'GET',
            credentials: 'include',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (res.ok) {
            const data = await res.json();
            if (data?.comment) {
                comments.value.unshift(data.comment);
                (props.post as any).comments_count =
                    ((props.post as any).comments_count || 0) + 1;
                emit('commented', targetId, data.comment);
            }
        }
    } catch (e) {
        console.warn('Failed to fetch latest comment', e);
    }
};

const showDeleteModal = ref(false);
const deleteType = ref<'post' | 'repost'>('post');

const openDeleteModal = (event: Event, type: 'post' | 'repost') => {
    event.stopPropagation();
    event.preventDefault();
    deleteType.value = type;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
};

const confirmDelete = () => {
    if (deleteType.value === 'repost') {
        deleteRepost();
    } else {
        emit('delete', props.post.id);
    }
    closeDeleteModal();
};

const deletePost = (event: Event) => {
    event.stopPropagation();
    event.preventDefault();

    if (props.post.type === 'repost') {
        openDeleteModal(event, 'repost');
    } else {
        openDeleteModal(event, 'post');
    }
};

const showShareSuccess = ref(false);

const handlePostShared = () => {
    console.log('Post shared successfully');
    closeShareModal();

    showShareSuccess.value = true;

    setTimeout(() => {
        showShareSuccess.value = false;
    }, 3000);
};

const originalPostMediaList = computed(() => {
    if (props.post.type !== 'repost') return [];
    
    const originalPost = props.post.original_post;
    if (!originalPost) return [];
    
    const media: Array<{ type: 'image' | 'video'; src: string }> = [];
    
    // Cast ke any untuk mengakses property yang mungkin ada
    const op = originalPost as any;
    
    // Cek media array terlebih dahulu
    if (Array.isArray(op.media) && op.media.length > 0) {
        op.media.forEach((m: any) => {
            if (m && (m.type === 'image' || m.type === 'video') && m.src) {
                media.push({ type: m.type, src: m.src });
            }
        });
        return media;
    }
    
    // Fallback ke images array
    if (Array.isArray(op.images) && op.images.length > 0) {
        op.images.forEach((src: string) => {
            if (src) media.push({ type: 'image', src });
        });
    } 
    // Fallback ke image_url single
    else if (op.image_url) {
        media.push({ type: 'image', src: op.image_url });
    }
    
    // Tambahkan videos jika ada
    if (Array.isArray(op.videos) && op.videos.length > 0) {
        op.videos.forEach((src: string) => {
            if (src) media.push({ type: 'video', src });
        });
    }
    
    return media;
});

// Fungsi untuk membuka preview media dari original post
const openOriginalMediaPreview = (index: number) => {
    previewMedia.value = originalPostMediaList.value;
    currentMediaIndex.value = index;
    showMediaPreview.value = true;
};


</script>

<template>
    <div class="cursor-pointer border-b border-gray-200 p-4 transition-colors hover:bg-gray-50/50">
        <!-- Post Header -->
        <div class="flex items-start gap-3">
            <!-- Avatar -->
            <Link :href="getUserProfileUrl(post.user)" @click.stop
                class="-mt-0.5 flex h-12 w-12 flex-shrink-0 items-center justify-center overflow-hidden rounded-full text-sm font-medium text-white transition-opacity hover:opacity-90"
                :style="{ backgroundColor: getUserAvatar(post.user) ? 'transparent' : getAvatarColor(post.user.name) }">
                <img v-if="getUserAvatar(post.user)" :src="getUserAvatar(post.user)" :alt="post.user.name"
                    class="h-full w-full object-cover" />
                <span v-else>{{ getInitials(post.user.name) }}</span>
            </Link>

            <!-- Post Content -->
            <div class="min-w-0 flex-1">
                <!-- User Info & Actions -->
                <div class="mb-1 flex items-start justify-between">
                    <div class="-mt-0.2 flex min-w-0 flex-1 items-center gap-1">
                        <Link :href="getUserProfileUrl(post.user)" @click.stop
                            class="truncate text-[15px] font-bold text-gray-900 hover:underline">
                            {{ post.user.name }}
                        </Link>
                        <Link :href="getUserProfileUrl(post.user)" @click.stop
                            class="truncate text-[15px] text-gray-500 hover:underline">
                            @{{ getUserUsername(post.user) }}
                        </Link>
                        <span class="text-[15px] text-gray-500">·</span>
                        <span class="text-[15px] whitespace-nowrap text-gray-500">{{ formatDate(post.created_at)
                            }}</span>
                    </div>
                    <!-- More Options Button -->
                    <div class="relative">
                        <button @click.stop="showDropdown = !showDropdown"
                            class="ml-2 flex-shrink-0 rounded-full p-1.5 text-gray-500 transition-colors hover:bg-blue-50 hover:text-blue-500">
                            <svg class="h-[18px] w-[18px]" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div v-if="showDropdown" @click.stop
                            class="absolute right-0 z-10 mt-1 w-48 rounded-xl border border-gray-200 bg-white py-2 shadow-lg">
                            <!-- Delete button (for owner or admin) -->
                            <button v-if="canDelete" @click="
                                deletePost($event);
                            showDropdown = false;
                            "
                                class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-red-600 transition-colors hover:bg-gray-100">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete{{
                                    isAdmin && !isOwnPost ? ' (Admin)' : ''
                                }}
                            </button>
                            <!-- Report button (for non-owner) -->
                            <button v-if="!isOwnPost" @click="
                                showReportModal = true;
                            showDropdown = false;
                            "
                                class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-gray-700 transition-colors hover:bg-gray-100">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                                </svg>
                                Report Post
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Post Text Content -->
                <div @click="viewPostDetail" class="mt-1 mb-3">
                    <!-- Repost Indicator -->
                    <div v-if="post.type === 'repost'" class="mb-2 flex items-center gap-2 text-[13px] text-gray-500">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M4.5 3.88l4.432 4.14-1.364 1.46L5.5 7.55V16c0 1.1.896 2 2 2H13v2H7.5c-2.209 0-4-1.79-4-4V7.55L1.432 9.48.068 8.02 4.5 3.88zM16.5 6H11V4h5.5c2.209 0 4 1.79 4 4v8.45l2.068-1.93 1.364 1.46-4.432 4.14-4.432-4.14 1.364-1.46 2.068 1.93V8c0-1.1-.896-2-2-2z" />
                        </svg>
                        <span>{{ post.user.name }} reposted</span>
                    </div>

                    <!-- Original Post (if repost) -->
                    <div v-if="post.type === 'repost'" class="mb-3 rounded-2xl border border-gray-200 p-3">
                        <!-- Original User Info -->
                        <div class="mb-2 flex items-center gap-2">
                            <Link :href="getUserProfileUrl(post.original_post_user || post.user)" @click.stop
                                class="-mt-0.5 flex h-12 w-12 flex-shrink-0 items-center justify-center overflow-hidden rounded-full text-sm font-medium text-white transition-opacity hover:opacity-90"
                                :style="{ backgroundColor: getUserAvatar(post.original_post_user || post.user) ? 'transparent' : getAvatarColor((post.original_post_user || post.user).name) }">
                                <img v-if="getUserAvatar(post.original_post_user || post.user)"
                                    :src="getUserAvatar(post.original_post_user || post.user)"
                                    :alt="(post.original_post_user || post.user).name"
                                    class="h-full w-full object-cover" />
                                <span v-else>{{ getInitials((post.original_post_user || post.user).name) }}</span>
                            </Link>
                            <span class="text-[15px] font-bold text-gray-900">
                                {{ post.original_post_user?.name || post.user?.name }}
                            </span>
                            <span class="text-[15px] text-gray-500">
                                @{{ getUserUsername(post.original_post_user || post.user) }}
                            </span>
                        </div>

                        <!-- Original Content -->
                        <div class="mb-3">
                            <p class="leading-relaxed whitespace-pre-wrap text-gray-900">
                                {{ post.original_post?.content || post.content }}
                            </p>

                            <!-- Original Post Media -->
                            <div v-if="originalPostMediaList.length > 0" class="mt-3 grid gap-2" :class="{
                                'grid-cols-1': originalPostMediaList.length === 1,
                                'grid-cols-2': originalPostMediaList.length > 1,
                            }">
                                <template v-for="(item, index) in originalPostMediaList.slice(0, 4)"
                                    :key="`original-${item.type}-${item.src}-${index}`">
                                    <!-- Image -->
                                    <img v-if="item.type === 'image'" :src="item.src" :alt="`Post media ${index + 1}`"
                                        :class="[
                                            'cursor-pointer rounded-lg object-cover transition-opacity hover:opacity-90',
                                            originalPostMediaList.length === 1
                                                ? 'max-h-96 max-w-96 object-contain'
                                                : 'h-64 w-full object-cover',
                                        ]" @click.stop="openOriginalMediaPreview(index)"
                                        @error="(e: Event) => ((e.target as HTMLImageElement).style.display = 'none')" />

                                    <!-- Video -->
                                    <div v-else :class="[
                                        'relative cursor-pointer overflow-hidden rounded-lg bg-black',
                                        originalPostMediaList.length === 1 ? 'max-h-96' : 'h-64',
                                    ]" @click.stop="openOriginalMediaPreview(index)">
                                        <video :src="item.src" preload="metadata" muted playsinline
                                            class="h-full w-full object-cover"></video>
                                        <div
                                            class="pointer-events-none absolute inset-0 flex items-center justify-center">
                                            <div class="bg-opacity-50 rounded-full bg-black p-3">
                                                <svg class="h-8 w-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M8 5v14l11-7z" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Repost Caption -->
                    <p v-if="post.type === 'repost' && post.repost_caption"
                        class="mb-3 text-[15px] whitespace-pre-wrap text-gray-900">
                        {{ post.repost_caption }}
                    </p>

                    <!-- Repost Images -->
                    <div v-if="
                        post.type === 'repost' &&
                        post.repost_images &&
                        post.repost_images.length > 0
                    " :class="[
                        'mb-3 gap-0.5 overflow-hidden rounded-2xl',
                        post.repost_images.length === 1
                            ? 'grid grid-cols-1'
                            : 'grid grid-cols-2',
                    ]">
                        <img v-for="(image, index) in post.repost_images.slice(
                            0,
                            4,
                        )" :key="index" :src="image" :alt="`Repost Image ${index + 1}`" :class="[
                            'w-full object-cover',
                            post.repost_images.length === 1
                                ? 'max-h-[500px]'
                                : 'h-[280px]',
                        ]" />
                    </div>

                    <!-- Regular Post Content -->
                    <div v-if="post.type !== 'repost'">
                        <p v-if="post.content" class="-mt-1.5 mb-3 text-[15px] whitespace-pre-wrap text-gray-900">
                            {{ post.content }}
                        </p>

                        <!-- Media (ordered: image + video) -->
                        <div v-if="previewMediaList.length > 0" :class="[
                            'gap-0.5 overflow-hidden rounded-2xl border border-gray-200',
                            previewMediaList.length === 1
                                ? 'grid grid-cols-1'
                                : 'grid grid-cols-2',
                        ]">
                            <template v-for="(item, index) in previewMediaList.slice(
                                0,
                                4,
                            )" :key="item.type + '-' + item.src + '-' + index">
                                <img v-if="item.type === 'image'" :src="item.src" :alt="`Media ${index + 1}`" :class="[
                                    'w-full cursor-pointer object-cover transition-opacity hover:opacity-95',
                                    previewMediaList.length === 1
                                        ? 'max-h-[500px]'
                                        : 'h-[280px]',
                                ]" @click.stop="
                                    openMediaPreview(index, 'image')
                                    " />

                                <div v-else :class="[
                                    'relative cursor-pointer overflow-hidden bg-black',
                                    previewMediaList.length === 1
                                        ? 'max-h-[500px]'
                                        : 'h-[280px]',
                                ]" @click.stop="
                                    openMediaPreview(index, 'video')
                                    ">
                                    <video :src="item.src" preload="metadata" muted playsinline
                                        class="h-full w-full object-cover"></video>
                                    <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                                        <div class="bg-opacity-50 rounded-full bg-black p-3">
                                            <svg class="h-8 w-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8 5v14l11-7z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Single Image URL Fallback -->
                        <div v-if="
                            post.image_url &&
                            !imageError &&
                            previewMediaList.length === 0 &&
                            (!post.images || post.images.length === 0)
                        ">
                            <img :src="post.image_url" :alt="`Post image`"
                                class="max-h-[500px] w-full cursor-pointer rounded-2xl border border-gray-200 object-cover transition-opacity hover:opacity-95"
                                @error="handleImageError" @click.stop="openMediaPreview(0, 'image')" />
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-3 flex max-w-[425px] items-center justify-between">
                    <!-- Reply -->
                    <button @click="openCommentModal"
                        class="group flex items-center gap-2 text-gray-500 transition-colors hover:text-blue-500">
                        <div class="rounded-full p-2 transition-colors group-hover:bg-blue-50">
                            <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4.92-1.314L3 20l1.314-4.08A8 8 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <span v-if="commentsCount > 0" class="text-[13px]">{{
                            commentsCount
                            }}</span>
                    </button>

                    <!-- Repost -->
                    <button @click="openRepostModal" :class="[
                        'group flex items-center gap-2 transition-colors',
                        reposted
                            ? 'text-green-600'
                            : 'text-gray-500 hover:text-green-600',
                    ]" :disabled="repostLoading">
                        <div class="rounded-full p-2 transition-colors group-hover:bg-green-50">
                            <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                        <span v-if="reposts > 0" class="text-[13px]">{{
                            reposts
                            }}</span>
                    </button>

                    <!-- Like -->
                    <button @click="toggleLike" :class="[
                        'group flex items-center gap-2 transition-colors',
                        liked
                            ? 'text-pink-600'
                            : 'text-gray-500 hover:text-pink-600',
                    ]">
                        <div class="rounded-full p-2 transition-colors group-hover:bg-pink-50">
                            <svg class="h-[18px] w-[18px]" :fill="liked ? 'currentColor' : 'none'" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </div>
                        <span v-if="likes > 0" class="text-[13px]">{{
                            likes
                            }}</span>
                    </button>

                    <!-- Bookmark -->
                    <button @click="toggleBookmark" :class="[
                        'group flex items-center gap-2 transition-colors',
                        bookmarked
                            ? 'text-yellow-500'
                            : 'text-gray-500 hover:text-yellow-500',
                    ]">
                        <div class="rounded-full p-2 transition-colors group-hover:bg-yellow-50">
                            <svg class="h-[18px] w-[18px]" :fill="bookmarked ? 'currentColor' : 'none'"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                            </svg>
                        </div>
                        <span v-if="bookmarks > 0" class="text-[13px]">{{
                            bookmarks
                            }}</span>
                    </button>

                    <!-- Share -->
                    <button @click="openShareModal"
                        class="group rounded-full p-2 text-gray-500 transition-colors hover:text-blue-500">
                        <div class="rounded-full p-0 transition-colors group-hover:bg-blue-50">
                            <svg class="h-5 w-5 text-gray-500 group-hover:text-blue-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                            </svg>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Media Preview Modal -->
    <MediaPreviewModal :is-open="showMediaPreview" :media="previewMedia" :current-index="currentMediaIndex"
        @close="closeMediaPreview" @update-index="updateMediaIndex" />

    <!-- Modals -->
    <RepostModal v-if="showRepostModal" :post="post" :target-post-id="post.type === 'repost' && post.post_id ? post.post_id : post.id
        " @close="closeRepostModal" @submitted="handleRepostSubmitted" />

    <ShareModal :is-open="showShareModal" :post="post" @close="closeShareModal" @shared="handlePostShared" />

    <CommentModal :isOpen="showCommentModal" :postId="props.post.type === 'repost' && props.post.post_id
        ? props.post.post_id
        : props.post.id
        " :user="props.currentUser" @close="closeCommentModal" @commented="handleCommented" />

    <!-- Delete Confirmation Modal -->
    <Teleport to="body">
        <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showDeleteModal"
                class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
                @click.self="closeDeleteModal">
                <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0 scale-95"
                    enter-to-class="opacity-100 scale-100" leave-active-class="transition-all duration-200"
                    leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                    <div v-if="showDeleteModal" class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl"
                        @click.stop>
                        <!-- Header -->
                        <div class="bg-gradient-to-r from-red-500 to-red-600 p-6">
                            <div class="mb-4 flex items-center justify-center">
                                <div class="rounded-full bg-white/20 p-3 backdrop-blur-sm">
                                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </div>
                            </div>
                            <h3 class="text-center text-2xl font-bold text-white">
                                Delete
                                {{
                                    deleteType === 'repost' ? 'Repost' : 'Post'
                                }}?
                            </h3>
                        </div>

                        <!-- Body -->
                        <div class="p-6">
                            <p class="mb-6 text-center text-gray-600">
                                <template v-if="deleteType === 'repost'">
                                    This will remove your repost from your
                                    profile. The original post will remain
                                    visible.
                                </template>
                                <template v-else>
                                    This action cannot be undone. Your post will
                                    be permanently deleted.
                                </template>
                            </p>

                            <!-- Actions -->
                            <div class="flex gap-3">
                                <button @click="closeDeleteModal"
                                    class="flex-1 rounded-xl bg-gray-100 px-4 py-3 font-medium text-gray-700 transition-colors duration-200 hover:bg-gray-200">
                                    Cancel
                                </button>
                                <button @click="confirmDelete"
                                    class="flex-1 rounded-xl bg-gradient-to-r from-red-500 to-red-600 px-4 py-3 font-medium text-white shadow-lg transition-all duration-200 hover:from-red-600 hover:to-red-700 hover:shadow-xl">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>

    <!-- Share Success Toast -->
    <Teleport to="body">
        <Transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0 translate-y-2"
            enter-to-class="opacity-100 translate-y-0" leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-2">
            <div v-if="showShareSuccess" class="pointer-events-none fixed top-20 left-1/2 z-[110] -translate-x-1/2">
                <div class="pointer-events-auto overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-2xl">
                    <div class="flex items-center gap-4 px-6 py-4">
                        <!-- Success Icon -->
                        <div class="flex-shrink-0">
                            <div class="rounded-full bg-gradient-to-br from-green-400 to-green-600 p-2">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>

                        <!-- Message -->
                        <div class="flex-1">
                            <p class="text-base font-semibold text-gray-900">
                                Post Shared Successfully!
                            </p>
                            <p class="mt-0.5 text-sm text-gray-600">
                                The post link has been copied to your clipboard
                            </p>
                        </div>

                        <!-- Close Button -->
                        <button @click="showShareSuccess = false"
                            class="flex-shrink-0 text-gray-400 transition-colors hover:text-gray-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Progress Bar -->
                    <div class="h-1 overflow-hidden bg-gray-100">
                        <div class="animate-progress h-full bg-gradient-to-r from-green-400 to-green-600"></div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>

    <!-- Report Modal -->
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="showReportModal" @click="showReportModal = false"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
                <div @click.stop class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                    <h3 class="mb-4 text-xl font-bold text-gray-900">
                        Report Post
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">
                                Reason for reporting
                                <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="reportReason"
                                class="w-full text-black rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            >
                                <option value="">Select a reason</option>
                                <option value="spam">Spam</option>
                                <option value="harassment">
                                    Harassment or bullying
                                </option>
                                <option value="hate_speech">Hate speech</option>
                                <option value="violence">
                                    Violence or dangerous content
                                </option>
                                <option value="misinformation">
                                    Misinformation
                                </option>
                                <option value="inappropriate">
                                    Inappropriate content
                                </option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">
                                Additional details (optional)
                            </label>
                            <textarea
                                v-model="reportDescription"
                                rows="3"
                                class="w-full rounded-lg border text-black border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                placeholder="Provide more information about why you're reporting this post..."
                            ></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button @click="showReportModal = false"
                            class="rounded-full px-5 py-2 font-semibold text-gray-700 transition-colors hover:bg-gray-100">
                            Cancel
                        </button>
                        <button @click="submitReport" :disabled="reportLoading || !reportReason"
                            class="rounded-full bg-red-500 px-5 py-2 font-semibold text-white transition-colors hover:bg-red-600 disabled:cursor-not-allowed disabled:opacity-50">
                            {{
                                reportLoading
                                    ? 'Submitting...'
                                    : 'Submit Report'
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
@keyframes progress {
    from {
        width: 100%;
    }

    to {
        width: 0%;
    }
}

.animate-progress {
    animation: progress 3s linear forwards;
}
</style>