<script setup lang="ts">
import CommentModal from '@/components/CommentModal.vue';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps<{
    post: {
        id: number | string;
        type?: string;
        content: string;
        image_url?: string;
        images?: string[];
        videos?: string[];
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
        comments?: Array<{
            id: number;
            content: string;
            created_at: string;
            user: {
                id: number;
                name: string;
                email: string;
            };
        }>;
    };
    user: {
        id: number;
        name: string;
        email: string;
    };
}>();

const comments = ref(props.post.comments || []);
const showCommentModal = ref(false);
const showDropdown = ref(false);

const isOwnPost = computed(() => {
    return props.post.user.id === props.user.id;
});

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

    const hash = name.split('').reduce((acc, char) => {
        return acc + char.charCodeAt(0);
    }, 0);

    return colors[hash % colors.length];
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

const formatFullDate = (dateString: string) => {
    const date = new Date(dateString);
    const time = date.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    });
    const dateStr = date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
    return `${time} · ${dateStr}`;
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
            }
        }
    } catch (e) {
        console.warn('Failed to fetch latest comment', e);
    }
};

const closeCommentModal = () => {
    showCommentModal.value = false;
};

const showDeleteModal = ref(false);

const openDeleteModal = () => {
    showDeleteModal.value = true;
    showDropdown.value = false;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
};

const confirmDelete = () => {
    router.delete(`/posts/${props.post.id}`, {
        onSuccess: () => {
            router.visit('/dashboard');
        },
    });
    closeDeleteModal();
};

const handleDeletePost = () => {
    openDeleteModal();
};

const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value;
};
</script>

<template>
    <AppSidebarLayout>
        <main class="min-h-screen bg-gray-50">
            <div class="mx-auto max-w-2xl bg-white border-x border-gray-200">
                <!-- Header with back button -->
                <div
                    class="sticky top-0 z-10 flex items-center space-x-8 border-b border-gray-200 bg-white/80 px-4 py-3 backdrop-blur-md"
                >
                    <Link
                        href="/dashboard"
                        class="flex h-8 w-8 items-center justify-center rounded-full transition-colors hover:bg-gray-100"
                    >
                        <svg
                            class="h-5 w-5 text-black"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"
                            />
                        </svg>
                    </Link>
                    <h1 class="text-xl font-bold text-black">Post</h1>
                </div>

                <!-- Post Content -->
                <div class="border-b border-gray-200 px-4 py-3">
                    <!-- User Info -->
                    <div class="flex items-start justify-between">
                        <div class="flex items-center space-x-3">
                            <div
                                class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full text-sm font-medium text-white"
                                :style="{ backgroundColor: getAvatarColor(post.user.name) }"
                            >
                                {{ getInitials(post.user.name) }}
                            </div>
                            <div>
                                <p class="font-bold text-gray-900">
                                    {{ post.user.name }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    @{{ post.user.name.toLowerCase().replace(/\s+/g, '') }}
                                </p>
                            </div>
                        </div>
                        <div class="relative" v-if="isOwnPost">
                            <button
                                @click="toggleDropdown"
                                class="flex h-8 w-8 items-center justify-center rounded-full transition-colors hover:bg-blue-50"
                            >
                                <svg
                                    class="h-5 w-5 text-gray-500"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"
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
                                        handleDeletePost();
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

                    <!-- Content -->
                    <div class="mt-3">
                        <p
                            class="whitespace-pre-wrap text-[15px] leading-5 text-gray-900"
                        >
                            {{ post.content }}
                        </p>
                    </div>

                    <!-- Images -->
                    <div
                        v-if="post.images && post.images.length > 0"
                        class="mt-3"
                    >
                        <div
                            v-if="post.images.length === 1"
                            class="overflow-hidden rounded-2xl border border-gray-200"
                        >
                            <img
                                :src="post.images[0]"
                                :alt="`Image 1`"
                                class="w-full object-cover"
                            />
                        </div>
                        <div
                            v-else-if="post.images.length === 2"
                            class="grid grid-cols-2 gap-0.5 overflow-hidden rounded-2xl border border-gray-200"
                        >
                            <img
                                v-for="(img, idx) in post.images"
                                :key="idx"
                                :src="img"
                                :alt="`Image ${idx + 1}`"
                                class="h-full w-full object-cover"
                            />
                        </div>
                        <div
                            v-else-if="post.images.length === 3"
                            class="grid grid-cols-2 gap-0.5 overflow-hidden rounded-2xl border border-gray-200"
                        >
                            <img
                                :src="post.images[0]"
                                alt="Image 1"
                                class="row-span-2 h-full w-full object-cover"
                            />
                            <img
                                :src="post.images[1]"
                                alt="Image 2"
                                class="h-full w-full object-cover"
                            />
                            <img
                                :src="post.images[2]"
                                alt="Image 3"
                                class="h-full w-full object-cover"
                            />
                        </div>
                        <div
                            v-else
                            class="grid grid-cols-2 gap-0.5 overflow-hidden rounded-2xl border border-gray-200"
                        >
                            <img
                                v-for="(img, idx) in post.images.slice(0, 4)"
                                :key="idx"
                                :src="img"
                                :alt="`Image ${idx + 1}`"
                                class="h-full w-full object-cover"
                            />
                        </div>
                    </div>

                    <!-- Timestamp -->
                    <div class="mt-4 text-[15px] text-gray-500">
                        {{ formatFullDate(post.created_at) }}
                    </div>

                    <!-- Stats -->
                    <div
                        class="mt-4 flex items-center space-x-5 border-y border-gray-200 py-3 text-sm"
                    >
                        <div class="flex items-center space-x-1">
                            <span class="font-bold text-gray-900">{{
                                post.reposts_count || 0
                            }}</span>
                            <span class="text-gray-500">Reposts</span>
                        </div>
                        <div class="flex items-center space-x-1">
                            <span class="font-bold text-gray-900">{{
                                post.likes_count || 0
                            }}</span>
                            <span class="text-gray-500">Likes</span>
                        </div>
                        <div class="flex items-center space-x-1">
                            <span class="font-bold text-gray-900">{{
                                post.bookmarks_count || 0
                            }}</span>
                            <span class="text-gray-500">Bookmarks</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div
                        class="flex items-center justify-around border-b border-gray-200 py-2"
                    >
                        <button
                            @click="showCommentModal = true"
                            class="group flex items-center space-x-2 rounded-full p-2 transition-colors hover:bg-blue-50"
                        >
                            <svg
                                class="h-5 w-5 text-gray-500 group-hover:text-blue-500"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                                />
                            </svg>
                        </button>
                        <button
                            class="group flex items-center space-x-2 rounded-full p-2 transition-colors hover:bg-green-50"
                        >
                            <svg
                                class="h-5 w-5 text-gray-500 group-hover:text-green-500"
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
                        </button>
                        <button
                            :class="[
                                'group flex items-center space-x-2 rounded-full p-2 transition-colors',
                                post.liked
                                    ? 'text-red-500'
                                    : 'hover:bg-red-50',
                            ]"
                        >
                            <svg
                                class="h-5 w-5 group-hover:text-red-500"
                                :class="
                                    post.liked
                                        ? 'fill-current text-red-500'
                                        : 'text-gray-500'
                                "
                                :fill="post.liked ? 'currentColor' : 'none'"
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
                        </button>
                        <button
                            :class="[
                                'group flex items-center space-x-2 rounded-full p-2 transition-colors',
                                post.bookmarked
                                    ? 'text-blue-500'
                                    : 'hover:bg-blue-50',
                            ]"
                        >
                            <svg
                                class="h-5 w-5 group-hover:text-blue-500"
                                :class="
                                    post.bookmarked
                                        ? 'fill-current text-blue-500'
                                        : 'text-gray-500'
                                "
                                :fill="post.bookmarked ? 'currentColor' : 'none'"
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
                        </button>
                        <button
                            class="group flex items-center space-x-2 rounded-full p-2 transition-colors hover:bg-blue-50"
                        >
                            <svg
                                class="h-5 w-5 text-gray-500 group-hover:text-blue-500"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Comments Section -->
                <div class="divide-y divide-gray-200">
                    <div
                        v-if="comments.length === 0"
                        class="px-4 py-8 text-center text-gray-500"
                    >
                        No comments yet. Be the first to comment!
                    </div>

                    <div
                        v-for="comment in comments"
                        :key="comment.id"
                        class="px-4 py-3 transition-colors hover:bg-gray-50"
                    >
                        <div class="flex space-x-3">
                            <div
                                class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full text-sm font-medium text-white"
                                :style="{ backgroundColor: getAvatarColor(comment.user?.name || '') }"
                            >
                                {{ getInitials(comment.user?.name || '') }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-1">
                                    <p class="font-bold text-gray-900">
                                        {{ comment.user?.name }}
                                    </p>
                                    <span class="text-sm text-gray-500"
                                        >@{{ (comment.user?.name || '').toLowerCase().replace(/\s+/g, '') }}</span
                                    >
                                    <span class="text-gray-400">·</span>
                                    <span class="text-sm text-gray-500">{{
                                        formatDate(comment.created_at)
                                    }}</span>
                                </div>
                                <p
                                    class="mt-1 whitespace-pre-wrap text-[15px] leading-5 text-gray-900"
                                >
                                    {{ comment.content }}
                                </p>
                                <!-- Comment Actions -->
                                <div class="mt-2 flex items-center space-x-16">
                                    <button
                                        class="group flex items-center space-x-2 text-gray-500 transition-colors hover:text-blue-500"
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
                                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                                            />
                                        </svg>
                                    </button>
                                    <button
                                        class="group flex items-center space-x-2 text-gray-500 transition-colors hover:text-red-500"
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
                                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comment Modal -->
                <CommentModal
                    :isOpen="showCommentModal"
                    :postId="
                        props.post.type === 'repost' && props.post.post_id
                            ? props.post.post_id
                            : props.post.id
                    "
                    :user="props.user"
                    @close="closeCommentModal"
                    @commented="handleCommented"
                />
            </div>
        </main>
    </AppSidebarLayout>

    <!-- Delete Confirmation Modal -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showDeleteModal"
                class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
                @click.self="closeDeleteModal"
            >
                <Transition
                    enter-active-class="transition-all duration-200"
                    enter-from-class="opacity-0 scale-95"
                    enter-to-class="opacity-100 scale-100"
                    leave-active-class="transition-all duration-200"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95"
                >
                    <div
                        v-if="showDeleteModal"
                        class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden"
                        @click.stop
                    >
                        <!-- Header -->
                        <div class="bg-gradient-to-r from-red-500 to-red-600 p-6">
                            <div class="flex items-center justify-center mb-4">
                                <div class="bg-white/20 backdrop-blur-sm rounded-full p-3">
                                    <svg 
                                        class="w-8 h-8 text-white" 
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
                                </div>
                            </div>
                            <h3 class="text-2xl font-bold text-white text-center">
                                Delete Post?
                            </h3>
                        </div>

                        <!-- Body -->
                        <div class="p-6">
                            <p class="text-gray-600 text-center mb-6">
                                This action cannot be undone. Your post will be permanently deleted.
                            </p>

                            <!-- Actions -->
                            <div class="flex gap-3">
                                <button
                                    @click="closeDeleteModal"
                                    class="flex-1 px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition-colors duration-200"
                                >
                                    Cancel
                                </button>
                                <button
                                    @click="confirmDelete"
                                    class="flex-1 px-4 py-3 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>