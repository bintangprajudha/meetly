<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();

const props = defineProps<{
    post: {
        id: number;
        content: string;
        images?: string[];
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
    commented: [postId: number, comment: any];
}>();

const imageError = ref(false);
const repliesCount = ref(props.post.replies_count || 0);
const comments = ref(
    (props.post as any).comments ? [...(props.post as any).comments] : [],
);
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
                Accept: 'application/json',
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
                Accept: 'application/json',
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
        if (data.bookmarks_count !== undefined)
            bookmarks.value = data.bookmarks_count;
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

const toggleCommentBox = () => {
    showCommentBox.value = !showCommentBox.value;
    commentError.value = null;
};

const postComment = async () => {
    commentError.value = null;
    const content = (commentInput.value || '').trim();
    if (!content) {
        commentError.value = 'Comment cannot be empty';
        return;
    }

    posting.value = true;

    const token =
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') || '';

    router.post(
        `/posts/${props.post.id}/comments`,
        { content, _token: token },
        {
            preserveState: true,
            onSuccess: async () => {
                // Clear input
                commentInput.value = '';
                showCommentBox.value = false;

                // Fetch the latest comment from the server and prepend it so it appears immediately
                try {
                    const res = await fetch(
                        `/posts/${props.post.id}/comments/latest`,
                        {
                            method: 'GET',
                            credentials: 'include',
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        },
                    );

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
                commentError.value =
                    errors?.message || 'Failed to post comment';
                posting.value = false;
            },
        },
    );
};

const cancelComment = () => {
    // simply close the box and clear errors (Inertia post is not abortable here)
    showCommentBox.value = false;
    commentError.value = null;
    posting.value = false;
};
</script>

<template>
    <div
        class="rounded-lg border border-gray-200 bg-white p-4 transition-colors hover:bg-gray-50"
    >
        <!-- User Header -->
        <div class="mb-3 flex items-start justify-between">
            <div class="flex items-start space-x-3">
                <Link
                    :href="`/${post.user.name}`"
                    class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-blue-500 text-sm font-medium text-white transition-colors hover:bg-blue-600"
                >
                    {{ post.user.name.charAt(0).toUpperCase() }}
                </Link>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center space-x-2">
                        <Link
                            :href="`/${post.user.name}`"
                            class="truncate font-semibold text-gray-900 hover:underline"
                            >{{ post.user.name }}</Link
                        >
                        <Link
                            :href="`/${post.user.name}`"
                            class="text-sm text-gray-500 hover:underline"
                            >@{{ post.user.name }}</Link
                        >
                        <span class="text-gray-400">·</span>
                        <span class="text-sm text-gray-500">{{
                            formatDate(post.created_at)
                        }}</span>
                    </div>
                </div>
            </div>

            <!-- Delete Button (only for own posts) -->
            <button
                v-if="isOwnPost"
                @click="deletePost"
                class="rounded-full p-1 text-gray-400 transition-colors hover:bg-red-50 hover:text-red-500"
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
                    ></path>
                </svg>
            </button>
        </div>

        <!-- Comment Box -->
        <div v-if="showCommentBox" class="mt-3">
            <textarea
                v-model="commentInput"
                rows="3"
                class="w-full rounded-md border border-gray-200 bg-white p-2 text-black focus:ring-2 focus:ring-blue-200 focus:outline-none"
                placeholder="Write a comment..."
            ></textarea>

            <div class="mt-2 flex items-center space-x-2">
                <button
                    @click="postComment"
                    :disabled="posting"
                    class="rounded-md bg-blue-600 px-3 py-1 text-white hover:bg-blue-700 disabled:opacity-50"
                >
                    <span v-if="!posting">Post</span>
                    <span v-else>Posting...</span>
                </button>

                <button
                    @click="cancelComment"
                    class="rounded-md border border-gray-200 px-3 py-1 text-gray-700 hover:bg-gray-50"
                >
                    Cancel
                </button>
            </div>

            <p v-if="commentError" class="mt-2 text-sm text-red-500">
                {{ commentError }}
            </p>
        </div>

        <!-- Post Content -->
        <div class="mb-3">
            <p class="leading-relaxed whitespace-pre-wrap text-gray-900">
                {{ post.content }}
            </p>

            <!-- Image (if exists) -->
            <div
                v-if="post.images && post.images.length > 0"
                class="mt-3 grid gap-2"
                :class="{
                    'grid-cols-1': post.images.length === 1,
                    'grid-cols-2': post.images.length > 1,
                }"
            >
                <img
                    v-for="(image, index) in post.images"
                    :key="index"
                    :src="image"
                    :alt="`Post image ${index + 1}`"
                    class="h-64 w-full rounded-lg object-cover"
                    @error="
                        (e) =>
                            ((e.target as HTMLImageElement).style.display =
                                'none')
                    "
                />
            </div>

            <!-- Comments preview -->
            <div v-if="comments && comments.length" class="mt-3 space-y-3">
                <div
                    v-for="comment in comments"
                    :key="comment.id"
                    class="flex items-start space-x-3"
                >
                    <div
                        class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-200 text-xs font-medium text-gray-700"
                    >
                        {{
                            (comment.user?.name || 'U').charAt(0).toUpperCase()
                        }}
                    </div>
                    <div class="flex-1">
                        <div class="text-sm">
                            <span class="mr-2 font-semibold text-gray-800">{{
                                comment.user?.name || 'Unknown'
                            }}</span>
                            <span class="text-xs text-gray-500"
                                >· {{ formatDate(comment.created_at) }}</span
                            >
                        </div>
                        <div class="text-sm text-gray-800">
                            {{ comment.content }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center space-x-6 text-gray-500">
            <!-- Reply (view only) -->
            <button
                class="group flex items-center space-x-2 transition-colors hover:text-blue-500"
            >
                <div
                    class="rounded-full p-2 transition-colors group-hover:bg-blue-50"
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
                            d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"
                        ></path>
                    </svg>
                </div>
                <span class="text-sm">{{ repliesCount || '' }}</span>
            </button>

            <!-- Comment (opens comment box) -->
            <button
                @click="toggleCommentBox"
                class="group flex items-center space-x-2 transition-colors hover:text-blue-500"
            >
                <div
                    class="rounded-full p-2 transition-colors group-hover:bg-blue-50"
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
                            d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.8L3 20l1.2-3.8A7.966 7.966 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                        ></path>
                    </svg>
                </div>
                <span class="text-sm">{{ comments.length || '' }}</span>
            </button>

            <!-- Like -->
            <button
                @click.prevent="toggleLike"
                :class="[
                    'flex items-center space-x-2 rounded px-2 py-1 transition-colors',
                    liked
                        ? 'bg-red-50 text-red-500'
                        : 'text-gray-500 hover:bg-gray-100',
                ]"
                aria-label="Like"
            >
                <svg
                    class="h-4 w-4"
                    :fill="liked ? 'currentColor' : 'none'"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                    ></path>
                </svg>
                <span class="text-sm">{{ likes }}</span>
            </button>

            <!-- Bookmark -->
            <button
                @click.prevent="toggleBookmark"
                :class="[
                    'flex items-center space-x-2 rounded px-2 py-1 transition-colors',
                    bookmarked
                        ? 'bg-yellow-50 text-yellow-500'
                        : 'text-gray-500 hover:bg-gray-100',
                ]"
                aria-label="Bookmark"
            >
                <svg
                    class="h-4 w-4"
                    :fill="bookmarked ? 'currentColor' : 'none'"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M5 5a2 2 0 012-2h6a2 2 0 012 2v16l-7-3.5L5 21V5z"
                    ></path>
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
</template>
