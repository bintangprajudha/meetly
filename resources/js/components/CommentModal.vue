<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    isOpen: boolean;
    postId: number | string;
    user: {
        id: number;
        name: string;
        email: string;
    };
}>();

const emit = defineEmits<{
    close: [];
    commented: [];
}>();

const content = ref('');
const isSubmitting = ref(false);
const maxChars = 500;

const characterCount = computed(() => content.value.length);
const isOverLimit = computed(() => characterCount.value > maxChars);
const canComment = computed(
    () =>
        content.value.trim().length > 0 &&
        !isOverLimit.value &&
        !isSubmitting.value,
);

const submitComment = async () => {
    if (!canComment.value) return;

    isSubmitting.value = true;

    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        await router.post(`/posts/${props.postId}/comments`, 
            { content: content.value.trim(), _token: token }, 
            {
                preserveState: true,
                onSuccess: () => {
                    content.value = '';
                    emit('commented');
                    emit('close');
                },
                onError: (errors) => {
                    console.error('Error posting comment:', errors);
                    alert('Failed to post comment. Please try again.');
                },
            });
    } catch (error) {
        console.error('Comment submission failed:', error);
    } finally {
        isSubmitting.value = false;
    }
};

const closeModal = () => {
    if (!isSubmitting.value) {
        content.value = '';
        emit('close');
    }
};

const handleKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Escape') {
        closeModal();
    }
};
</script>

<template>
    <div
        v-if="props.isOpen"
        class="bg-opacity-30 fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm"
        @click.self="closeModal"
        @keydown="handleKeydown"
        tabindex="0"
    >
        <div
            class="mx-4 max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white shadow-xl"
        >
            <!-- Header -->
            <div
                class="sticky top-0 z-10 flex items-center justify-between border-b border-gray-200 bg-white p-4"
            >
                <h3 class="text-lg font-semibold text-gray-900">Reply to Post</h3>
                <button
                    @click="closeModal"
                    :disabled="isSubmitting"
                    class="rounded-full p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 disabled:opacity-50"
                >
                    <svg
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        ></path>
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="p-4">
                <form @submit.prevent="submitComment">
                    <!-- User Info -->
                    <div class="mb-4 flex items-start space-x-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-500 text-sm font-medium text-white"
                        >
                            {{ user.name.charAt(0).toUpperCase() }}
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">
                                {{ user.name }}
                            </p>
                            <p class="text-sm text-gray-500">
                                @{{ user.email.split('@')[0] }}
                            </p>
                        </div>
                    </div>

                    <!-- Text Area -->
                    <div class="mb-4">
                        <textarea
                            v-model="content"
                            placeholder="Write your comment..."
                            class="min-h-[100px] w-full resize-none rounded-lg border border-gray-300 p-3 text-base text-black focus:border-transparent focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            :class="{
                                'border-red-500 focus:ring-red-500':
                                    isOverLimit,
                            }"
                            :disabled="isSubmitting"
                        ></textarea>

                        <!-- Character Count -->
                        <div class="mt-2 flex items-center justify-between">
                            <div class="text-sm text-gray-500">
                                <span
                                    :class="{ 'text-red-500': isOverLimit }"
                                >
                                    {{ characterCount }}/{{ maxChars }}
                                </span>
                            </div>
                            <div class="h-8 w-8">
                                <svg class="h-full w-full -rotate-90 transform">
                                    <circle
                                        cx="16"
                                        cy="16"
                                        r="14"
                                        fill="none"
                                        stroke="#e5e7eb"
                                        stroke-width="4"
                                    />
                                    <circle
                                        cx="16"
                                        cy="16"
                                        r="14"
                                        fill="none"
                                        :stroke="
                                            isOverLimit ? '#ef4444' : '#3b82f6'
                                        "
                                        stroke-width="4"
                                        stroke-linecap="round"
                                        :stroke-dasharray="2 * Math.PI * 14"
                                        :stroke-dashoffset="
                                            2 *
                                            Math.PI *
                                            14 *
                                            (1 -
                                                Math.min(
                                                    characterCount / maxChars,
                                                    1,
                                                ))
                                        "
                                        class="transition-all duration-300"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div
                        class="flex items-center justify-end border-t border-gray-200 pt-4"
                    >
                        <button
                            type="submit"
                            :disabled="!canComment"
                            class="rounded-full bg-blue-500 px-6 py-2 font-medium text-white transition-colors hover:bg-blue-600 disabled:cursor-not-allowed disabled:bg-gray-300"
                        >
                            <span v-if="isSubmitting" class="flex items-center">
                                <svg
                                    class="mr-2 h-4 w-4 animate-spin"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                Commenting...
                            </span>
                            <span v-else>Reply</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
