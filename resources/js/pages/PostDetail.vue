<script setup lang="ts">
import CommentModal from '@/components/CommentModal.vue';
import PostCard from '@/components/PostCard.vue';
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

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

const handleDeletePost = (postId: number | string) => {
    router.delete(`/posts/${postId}`, {
        onSuccess: () => {
            router.visit('/dashboard');
        },
    });
};

const handlePostCardCommented = (postId: number | string, comment: any) => {
    // If we have a comment payload from PostCard, prepend it and update count
    if (comment) {
        comments.value.unshift(comment);
        // if props.post has a comments_count, keep it in sync
        if ((props.post as any).comments_count !== undefined) {
            (props.post as any).comments_count =
                ((props.post as any).comments_count || 0) + 1;
        }
    }
};

const handleCommented = async () => {
    // Fetch the latest comment from the server and prepend it
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
</script>

<template>
    <AppSidebarLayout>
        <AppHeaderLayout>
            <main class="p-6">
                <div class="mx-auto max-w-2xl">
                    <!-- Back button -->
                    <Link
                        href="/dashboard"
                        class="mb-4 text-sm font-medium text-blue-500 hover:text-blue-600"
                    >
                        ← Back to feed
                    </Link>

                    <!-- Post Card -->
                    <PostCard
                        :post="post"
                        :current-user="user"
                        @delete="handleDeletePost"
                        @commented="handlePostCardCommented"
                    />

                    <!-- Comments Section -->
                    <div class="mx-auto mt-6 max-w-2xl">
                        <!-- Comments List -->
                        <div class="space-y-4">
                            <div
                                v-if="comments.length === 0"
                                class="py-8 text-center text-gray-500"
                            >
                                No comments yet. Be the first to comment!
                            </div>

                            <div
                                v-for="comment in comments"
                                :key="comment.id"
                                class="rounded-lg border border-gray-200 bg-white p-4"
                            >
                                <div class="flex items-start space-x-3">
                                    <div
                                        class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-blue-500 text-sm font-medium text-white"
                                    >
                                        {{
                                            comment.user?.name
                                                .charAt(0)
                                                .toUpperCase()
                                        }}
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="flex items-center space-x-2"
                                        >
                                            <p
                                                class="font-semibold text-gray-900"
                                            >
                                                {{ comment.user?.name }}
                                            </p>
                                            <span class="text-sm text-gray-500"
                                                >@{{
                                                    comment.user?.email.split(
                                                        '@',
                                                    )[0]
                                                }}</span
                                            >
                                            <span class="text-gray-400">·</span>
                                            <span
                                                class="text-sm text-gray-500"
                                                >{{
                                                    formatDate(
                                                        comment.created_at,
                                                    )
                                                }}</span
                                            >
                                        </div>
                                        <p
                                            class="mt-2 whitespace-pre-wrap text-gray-900"
                                        >
                                            {{ comment.content }}
                                        </p>
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
        </AppHeaderLayout>
    </AppSidebarLayout>
</template>
