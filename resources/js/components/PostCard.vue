<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import CommentModal from './CommentModal.vue';
import MediaPreviewModal from './MediaPreviewModal.vue';
import RepostModal from './RepostModal.vue';
import ShareModal from './ShareModal.vue';

const props = defineProps<{
    post: {
        id: number | string;
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
    // `type` kept for compatibility with existing call sites.
    void type;
    previewMedia.value = getPreviewMedia();
    currentMediaIndex.value = index;
    showMediaPreview.value = true;
};

// Navigate media
const nextMedia = () => {
    if (currentMediaIndex.value < previewMedia.value.length - 1) {
        currentMediaIndex.value++;
    }
};

const prevMedia = () => {
    if (currentMediaIndex.value > 0) {
        currentMediaIndex.value--;
    }
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
        const targetId =
            props.post.type === 'repost' && props.post.post_id
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
        console.error(
            '❌ Error toggling like:',
            err.response?.data || err.message,
        );
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
        const targetId =
            props.post.type === 'repost' && props.post.post_id
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

const handlePostShared = () => {
    console.log('Post shared successfully');
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
</script>

<template>
    <div
        class="cursor-pointer border-b border-gray-200 p-4 transition-colors hover:bg-gray-50/50"
    >
        <!-- Post Header -->
        <div class="flex items-start gap-3">
            <!-- Avatar -->
            <Link
                :href="`/${post.user.name}`"
                @click.stop
                class="-mt-0.5 flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full text-sm font-medium text-white transition-opacity hover:opacity-90"
                :style="{ backgroundColor: getAvatarColor(post.user.name) }"
            >
                {{ getInitials(post.user.name) }}
            </Link>

            <!-- Post Content -->
            <div class="min-w-0 flex-1">
                <!-- User Info & Actions -->
                <div class="mb-1 flex items-start justify-between">
                    <div class="-mt-0.2 flex min-w-0 flex-1 items-center gap-1">
                        <Link
                            :href="`/${post.user.name}`"
                            @click.stop
                            class="truncate text-[15px] font-bold text-gray-900 hover:underline"
                        >
                            {{ post.user.name }}
                        </Link>
                        <Link
                            :href="`/${post.user.name}`"
                            @click.stop
                            class="truncate text-[15px] text-gray-500 hover:underline"
                        >
                            @{{ post.user.name.toLowerCase() }}
                        </Link>
                        <span class="text-[15px] text-gray-500">·</span>
                        <span
                            class="text-[15px] whitespace-nowrap text-gray-500"
                            >{{ formatDate(post.created_at) }}</span
                        >
                    </div>
                    <!-- More Options Button -->
                    <div v-if="isOwnPost" class="relative">
                        <button
                            @click.stop="showDropdown = !showDropdown"
                            class="ml-2 flex-shrink-0 rounded-full p-1.5 text-gray-500 transition-colors hover:bg-blue-50 hover:text-blue-500"
                        >
                            <svg
                                class="h-[18px] w-[18px]"
                                fill="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"
                                />
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div
                            v-if="showDropdown"
                            @click.stop
                            class="absolute right-0 z-10 mt-1 w-48 rounded-xl border border-gray-200 bg-white py-2 shadow-lg"
                        >
                            <button
                                @click="
                                    deletePost($event);
                                    showDropdown = false;
                                "
                                class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-red-600 transition-colors hover:bg-gray-100"
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
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                    />
                                </svg>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Post Text Content -->
                <div @click="viewPostDetail" class="mt-1 mb-3">
                    <!-- Repost Indicator -->
                    <div
                        v-if="post.type === 'repost'"
                        class="mb-2 flex items-center gap-2 text-[13px] text-gray-500"
                    >
                        <svg
                            class="h-4 w-4"
                            fill="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                d="M4.5 3.88l4.432 4.14-1.364 1.46L5.5 7.55V16c0 1.1.896 2 2 2H13v2H7.5c-2.209 0-4-1.79-4-4V7.55L1.432 9.48.068 8.02 4.5 3.88zM16.5 6H11V4h5.5c2.209 0 4 1.79 4 4v8.45l2.068-1.93 1.364 1.46-4.432 4.14-4.432-4.14 1.364-1.46 2.068 1.93V8c0-1.1-.896-2-2-2z"
                            />
                        </svg>
                        <span>{{ post.user.name }} reposted</span>
                    </div>

                    <!-- Original Post (if repost) -->
                    <div
                        v-if="post.type === 'repost'"
                        class="mb-3 rounded-2xl border border-gray-200 p-3"
                    >
                        <!-- Original User Info -->
                        <div class="mb-2 flex items-center gap-2">
                            <div
                                class="flex h-5 w-5 items-center justify-center rounded-full text-[11px] font-medium text-white"
                                :style="{
                                    backgroundColor: getAvatarColor(
                                        post.original_post_user?.name || '',
                                    ),
                                }"
                            >
                                {{
                                    getInitials(
                                        post.original_post_user?.name || '',
                                    )
                                }}
                            </div>
                            <span class="text-[15px] font-bold text-gray-900">{{
                                post.original_post_user?.name
                            }}</span>
                            <span class="text-[15px] text-gray-500"
                                >@{{
                                    post.original_post_user?.name.toLowerCase()
                                }}</span
                            >
                        </div>

                        <!-- Original Content -->
                        <!-- Post Content -->
                        <div class="mb-3">
                            <p
                                class="leading-relaxed whitespace-pre-wrap text-gray-900"
                            >
                                {{ post.content }}
                            </p>

                            <!-- Combined Media (Images + Videos) -->
                            <div
                                v-if="previewMediaList.length > 0"
                                class="mt-3 grid gap-2"
                                :class="{
                                    'grid-cols-1':
                                        previewMediaList.length === 1,
                                    'grid-cols-2': previewMediaList.length > 1,
                                }"
                            >
                                <template
                                    v-for="(
                                        item, index
                                    ) in previewMediaList.slice(0, 4)"
                                    :key="
                                        item.type + '-' + item.src + '-' + index
                                    "
                                >
                                    <img
                                        v-if="item.type === 'image'"
                                        :src="item.src"
                                        :alt="`Post media ${index + 1}`"
                                        :class="[
                                            'cursor-pointer rounded-lg object-cover transition-opacity hover:opacity-90',
                                            previewMediaList.length === 1
                                                ? 'max-h-96 max-w-96 object-contain'
                                                : 'h-64 w-full object-cover',
                                        ]"
                                        @click.stop="
                                            openMediaPreview(index, 'image')
                                        "
                                        @error="
                                            (e) =>
                                                ((
                                                    e.target as HTMLImageElement
                                                ).style.display = 'none')
                                        "
                                    />

                                    <div
                                        v-else
                                        :class="[
                                            'relative cursor-pointer overflow-hidden rounded-lg bg-black',
                                            previewMediaList.length === 1
                                                ? 'max-h-96'
                                                : 'h-64',
                                        ]"
                                        @click.stop="
                                            openMediaPreview(index, 'video')
                                        "
                                    >
                                        <video
                                            :src="item.src"
                                            preload="metadata"
                                            muted
                                            playsinline
                                            class="h-full w-full object-cover"
                                        ></video>
                                        <div
                                            class="pointer-events-none absolute inset-0 flex items-center justify-center"
                                        >
                                            <div
                                                class="bg-opacity-50 rounded-full bg-black p-3"
                                            >
                                                <svg
                                                    class="h-8 w-8 text-white"
                                                    fill="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
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
                    <p
                        v-if="post.type === 'repost' && post.repost_caption"
                        class="mb-3 text-[15px] whitespace-pre-wrap text-gray-900"
                    >
                        {{ post.repost_caption }}
                    </p>

                    <!-- Repost Images -->
                    <div
                        v-if="
                            post.type === 'repost' &&
                            post.repost_images &&
                            post.repost_images.length > 0
                        "
                        :class="[
                            'mb-3 gap-0.5 overflow-hidden rounded-2xl',
                            post.repost_images.length === 1
                                ? 'grid grid-cols-1'
                                : 'grid grid-cols-2',
                        ]"
                    >
                        <img
                            v-for="(image, index) in post.repost_images.slice(
                                0,
                                4,
                            )"
                            :key="index"
                            :src="image"
                            :alt="`Repost Image ${index + 1}`"
                            :class="[
                                'w-full object-cover',
                                post.repost_images.length === 1
                                    ? 'max-h-[500px]'
                                    : 'h-[280px]',
                            ]"
                        />
                    </div>

                    <!-- Regular Post Content -->
                    <div v-if="post.type !== 'repost'">
                        <p
                            v-if="post.content"
                            class="-mt-1.5 mb-3 text-[15px] whitespace-pre-wrap text-gray-900"
                        >
                            {{ post.content }}
                        </p>

                        <!-- Media (ordered: image + video) -->
                        <div
                            v-if="previewMediaList.length > 0"
                            :class="[
                                'gap-0.5 overflow-hidden rounded-2xl border border-gray-200',
                                previewMediaList.length === 1
                                    ? 'grid grid-cols-1'
                                    : 'grid grid-cols-2',
                            ]"
                        >
                            <template
                                v-for="(item, index) in previewMediaList.slice(
                                    0,
                                    4,
                                )"
                                :key="item.type + '-' + item.src + '-' + index"
                            >
                                <img
                                    v-if="item.type === 'image'"
                                    :src="item.src"
                                    :alt="`Media ${index + 1}`"
                                    :class="[
                                        'w-full cursor-pointer object-cover transition-opacity hover:opacity-95',
                                        previewMediaList.length === 1
                                            ? 'max-h-[500px]'
                                            : 'h-[280px]',
                                    ]"
                                    @click.stop="
                                        openMediaPreview(index, 'image')
                                    "
                                />

                                <div
                                    v-else
                                    :class="[
                                        'relative cursor-pointer overflow-hidden bg-black',
                                        previewMediaList.length === 1
                                            ? 'max-h-[500px]'
                                            : 'h-[280px]',
                                    ]"
                                    @click.stop="
                                        openMediaPreview(index, 'video')
                                    "
                                >
                                    <video
                                        :src="item.src"
                                        preload="metadata"
                                        muted
                                        playsinline
                                        class="h-full w-full object-cover"
                                    ></video>
                                    <div
                                        class="pointer-events-none absolute inset-0 flex items-center justify-center"
                                    >
                                        <div
                                            class="bg-opacity-50 rounded-full bg-black p-3"
                                        >
                                            <svg
                                                class="h-8 w-8 text-white"
                                                fill="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path d="M8 5v14l11-7z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Single Image URL Fallback -->
                        <div
                            v-if="
                                post.image_url &&
                                !imageError &&
                                previewMediaList.length === 0 &&
                                (!post.images || post.images.length === 0)
                            "
                        >
                            <img
                                :src="post.image_url"
                                :alt="`Post image`"
                                class="max-h-[500px] w-full cursor-pointer rounded-2xl border border-gray-200 object-cover transition-opacity hover:opacity-95"
                                @error="handleImageError"
                                @click.stop="openMediaPreview(0, 'image')"
                            />
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div
                    class="mt-3 flex max-w-[425px] items-center justify-between"
                >
                    <!-- Reply -->
                    <button
                        @click="openCommentModal"
                        class="group flex items-center gap-2 text-gray-500 transition-colors hover:text-blue-500"
                    >
                        <div
                            class="rounded-full p-2 transition-colors group-hover:bg-blue-50"
                        >
                            <svg
                                class="h-[18px] w-[18px]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4.92-1.314L3 20l1.314-4.08A8 8 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                                />
                            </svg>
                        </div>
                        <span v-if="commentsCount > 0" class="text-[13px]">{{
                            commentsCount
                        }}</span>
                    </button>

                    <!-- Repost -->
                    <button
                        @click="openRepostModal"
                        :class="[
                            'group flex items-center gap-2 transition-colors',
                            reposted
                                ? 'text-green-600'
                                : 'text-gray-500 hover:text-green-600',
                        ]"
                        :disabled="repostLoading"
                    >
                        <div
                            class="rounded-full p-2 transition-colors group-hover:bg-green-50"
                        >
                            <svg
                                class="h-[18px] w-[18px]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                />
                            </svg>
                        </div>
                        <span v-if="reposts > 0" class="text-[13px]">{{
                            reposts
                        }}</span>
                    </button>

                    <!-- Like -->
                    <button
                        @click="toggleLike"
                        :class="[
                            'group flex items-center gap-2 transition-colors',
                            liked
                                ? 'text-pink-600'
                                : 'text-gray-500 hover:text-pink-600',
                        ]"
                    >
                        <div
                            class="rounded-full p-2 transition-colors group-hover:bg-pink-50"
                        >
                            <svg
                                class="h-[18px] w-[18px]"
                                :fill="liked ? 'currentColor' : 'none'"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                                />
                            </svg>
                        </div>
                        <span v-if="likes > 0" class="text-[13px]">{{
                            likes
                        }}</span>
                    </button>

                    <!-- Views/Analytics -->
                    <button
                        @click.stop
                        class="group flex items-center gap-2 text-gray-500 transition-colors hover:text-blue-500"
                    >
                        <div
                            class="rounded-full p-2 transition-colors group-hover:bg-blue-50"
                        >
                            <svg
                                class="h-[18px] w-[18px]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                />
                            </svg>
                        </div>
                        <span class="text-[13px]">{{ formatViews(0) }}</span>
                    </button>

                    <!-- Bookmark & Share -->

                    <!-- Bookmark -->
                    <button
                        @click="toggleBookmark"
                        :class="[
                            'group rounded-full p-2 transition-colors',
                            bookmarked
                                ? 'text-blue-500'
                                : 'text-gray-500 hover:text-blue-500',
                        ]"
                    >
                        <div
                            class="rounded-full p-0 transition-colors group-hover:bg-blue-50"
                        >
                            <svg
                                class="h-[18px] w-[18px]"
                                :fill="bookmarked ? 'currentColor' : 'none'"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"
                                />
                            </svg>
                        </div>
                    </button>

                    <!-- Share -->
                    <button
                        @click="openShareModal"
                        class="group rounded-full p-2 text-gray-500 transition-colors hover:text-blue-500"
                    >
                        <div
                            class="rounded-full p-0 transition-colors group-hover:bg-blue-50"
                        >
                            <svg
                                class="h-[18px] w-[18px]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"
                                />
                            </svg>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Media Preview Modal -->
    <MediaPreviewModal
        :is-open="showMediaPreview"
        :media="previewMedia"
        :current-index="currentMediaIndex"
        @close="closeMediaPreview"
        @update-index="updateMediaIndex"
    />

    <!-- Modals -->
    <RepostModal
        v-if="showRepostModal"
        :post="post"
        :target-post-id="
            post.type === 'repost' && post.post_id ? post.post_id : post.id
        "
        @close="closeRepostModal"
        @submitted="handleRepostSubmitted"
    />

    <ShareModal
        :is-open="showShareModal"
        :post="post"
        @close="closeShareModal"
        @shared="handlePostShared"
    />

    <CommentModal
        :isOpen="showCommentModal"
        :postId="
            props.post.type === 'repost' && props.post.post_id
                ? props.post.post_id
                : props.post.id
        "
        :user="props.currentUser"
        @close="closeCommentModal"
        @commented="handleCommented"
    />
</template>
