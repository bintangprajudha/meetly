<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { AlertCircle, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

interface Report {
    id: number;
    reason: string;
    description: string | null;
    status: 'pending' | 'reviewed' | 'resolved';
    created_at: string;
    user: {
        id: number;
        name: string;
        email: string;
    };
    post: {
        id: number;
        content: string;
        user: {
            id: number;
            name: string;
            email: string;
        };
    };
}

defineProps<{
    reports: {
        data: Report[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}>();

const processing = ref(false);

const updateStatus = (
    reportId: number,
    status: 'pending' | 'reviewed' | 'resolved',
) => {
    processing.value = true;
    router.patch(
        `/admin/reports/${reportId}/status`,
        { status },
        {
            preserveScroll: true,
            onFinish: () => (processing.value = false),
        },
    );
};

const deletePost = (postId: number) => {
    if (!confirm('Are you sure you want to delete this post?')) return;

    processing.value = true;
    router.delete(`/posts/${postId}`, {
        preserveScroll: true,
        onFinish: () => (processing.value = false),
    });
};

const deleteUser = (userId: number) => {
    if (
        !confirm(
            'Are you sure you want to delete this user? This will also delete all their posts and data.',
        )
    )
        return;

    processing.value = true;
    router.delete(`/admin/users/${userId}`, {
        preserveScroll: true,
        onSuccess: () => {
            alert('User deleted successfully!');
        },
        onError: (errors) => {
            console.error('Delete user error:', errors);
            alert(
                'Failed to delete user. You may not have permission or the user cannot be deleted.',
            );
        },
        onFinish: () => (processing.value = false),
    });
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        case 'reviewed':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        case 'resolved':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        default:
            return '';
    }
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <AppSidebarLayout>
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">
                    Admin Dashboard
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Manage reported posts and take action
                </p>
            </div>

            <div v-if="reports.data.length === 0" class="py-12 text-center">
                <AlertCircle class="mx-auto h-12 w-12 text-gray-400" />
                <p class="mt-4 text-gray-600 dark:text-gray-400">
                    No reports found
                </p>
            </div>

            <div v-else class="space-y-4">
                <Card v-for="report in reports.data" :key="report.id">
                    <CardHeader>
                        <div class="flex items-start justify-between">
                            <div>
                                <CardTitle class="text-lg"
                                    >Report #{{ report.id }}</CardTitle
                                >
                                <CardDescription class="mt-1">
                                    Reported by
                                    <span class="font-medium">{{
                                        report.user.name
                                    }}</span>
                                    on
                                    {{
                                        new Date(
                                            report.created_at,
                                        ).toLocaleDateString()
                                    }}
                                </CardDescription>
                            </div>
                            <Badge :class="getStatusColor(report.status)">
                                {{ report.status }}
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <!-- Report Details -->
                            <div>
                                <p
                                    class="text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Reason:
                                </p>
                                <p
                                    class="mt-1 text-sm text-gray-600 dark:text-gray-400"
                                >
                                    {{ report.reason }}
                                </p>
                            </div>

                            <div v-if="report.description">
                                <p
                                    class="text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Description:
                                </p>
                                <p
                                    class="mt-1 text-sm text-gray-600 dark:text-gray-400"
                                >
                                    {{ report.description }}
                                </p>
                            </div>

                            <!-- Reported Post -->
                            <div
                                class="rounded border-l-4 border-red-500 bg-gray-50 p-4 pl-4 dark:bg-gray-800"
                            >
                                <p
                                    class="text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Reported Post:
                                </p>
                                <p
                                    class="mt-2 text-sm text-gray-600 dark:text-gray-400"
                                >
                                    {{ report.post.content }}
                                </p>
                                <p
                                    class="mt-2 text-xs text-gray-500 dark:text-gray-500"
                                >
                                    Posted by
                                    <span class="font-medium">{{
                                        report.post.user.name
                                    }}</span>
                                </p>
                            </div>

                            <!-- Actions -->
                            <div class="flex flex-wrap gap-2 pt-2">
                                <Button
                                    v-if="report.status === 'pending'"
                                    size="sm"
                                    variant="outline"
                                    @click="updateStatus(report.id, 'reviewed')"
                                    :disabled="processing"
                                >
                                    Mark as Reviewed
                                </Button>
                                <Button
                                    v-if="report.status !== 'resolved'"
                                    size="sm"
                                    variant="outline"
                                    @click="updateStatus(report.id, 'resolved')"
                                    :disabled="processing"
                                >
                                    Mark as Resolved
                                </Button>
                                <Button
                                    size="sm"
                                    variant="destructive"
                                    @click="deletePost(report.post.id)"
                                    :disabled="processing"
                                >
                                    <Trash2 class="mr-2 h-4 w-4" />
                                    Delete Post
                                </Button>
                                <Button
                                    size="sm"
                                    variant="destructive"
                                    @click="deleteUser(report.post.user.id)"
                                    :disabled="processing"
                                >
                                    <Trash2 class="mr-2 h-4 w-4" />
                                    Delete User
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Pagination -->
            <div
                v-if="reports.last_page > 1"
                class="mt-6 flex justify-center gap-2"
            >
                <Button
                    v-for="page in reports.last_page"
                    :key="page"
                    size="sm"
                    :variant="
                        page === reports.current_page ? 'default' : 'outline'
                    "
                    @click="router.get(`/admin/dashboard?page=${page}`)"
                >
                    {{ page }}
                </Button>
            </div>
        </div>
    </AppSidebarLayout>
</template>
