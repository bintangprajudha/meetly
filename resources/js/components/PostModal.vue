<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
    isOpen: boolean;
    user: {
        id: number;
        name: string;
        email: string;
    };
}>();

const emit = defineEmits<{
    close: [];
    posted: [];
}>();

const content = ref('');
const imageUrl = ref('');
const isSubmitting = ref(false);

const characterCount = computed(() => content.value.length);
const isOverLimit = computed(() => characterCount.value > 280);
const canPost = computed(() => content.value.trim().length > 0 && !isOverLimit.value && !isSubmitting.value);

const submitPost = async () => {
    if (!canPost.value) return;

    isSubmitting.value = true;

    try {
        await router.post('/posts', {
            content: content.value.trim(),
            image_url: imageUrl.value.trim() || null,
        }, {
            preserveState: false, // Refresh halaman untuk menampilkan post baru
            onSuccess: () => {
                content.value = '';
                imageUrl.value = '';
                emit('posted');
                emit('close');
            },
            onError: (errors) => {
                console.error('Error posting:', errors);
            },
        });
    } catch (error) {
        console.error('Post submission failed:', error);
    } finally {
        isSubmitting.value = false;
    }
};

const closeModal = () => {
    if (!isSubmitting.value) {
        emit('close');
    }
};

// Close modal on Escape key
const handleKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Escape') {
        closeModal();
    }
};
</script>

<template>
    <div 
        v-if="isOpen" 
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        @click.self="closeModal"
        @keydown="handleKeydown"
        tabindex="0"
    >
        <div class="w-full max-w-lg mx-4 bg-white rounded-2xl shadow-xl">
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Create Post</h3>
                <button 
                    @click="closeModal"
                    :disabled="isSubmitting"
                    class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors disabled:opacity-50"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="p-4">
                <form @submit.prevent="submitPost">
                    <!-- User Info -->
                    <div class="flex items-start space-x-3 mb-4">
                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-medium text-sm">
                            {{ user.name.charAt(0).toUpperCase() }}
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">{{ user.name }}</p>
                            <p class="text-sm text-gray-500">@{{ user.email.split('@')[0] }}</p>
                        </div>
                    </div>

                    <!-- Text Area -->
                    <div class="mb-4">
                        <textarea
                            v-model="content"
                            placeholder="What's happening?"
                            class="text-black w-full min-h-[120px] p-3 text-lg border border-gray-300 rounded-lg resize-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{ 'border-red-500 focus:ring-red-500': isOverLimit }"
                            :disabled="isSubmitting"
                        ></textarea>
                        
                        <!-- Character Count -->
                        <div class="flex justify-between items-center mt-2">
                            <div class="text-sm text-gray-500">
                                <span :class="{ 'text-red-500': isOverLimit }">
                                    {{ characterCount }}/280
                                </span>
                            </div>
                            <div class="w-8 h-8">
                                <svg class="w-full h-full transform -rotate-90">
                                    <circle
                                        cx="16" cy="16" r="14"
                                        fill="none"
                                        stroke="#e5e7eb"
                                        stroke-width="4"
                                    />
                                    <circle
                                        cx="16" cy="16" r="14"
                                        fill="none"
                                        :stroke="isOverLimit ? '#ef4444' : '#3b82f6'"
                                        stroke-width="4"
                                        stroke-linecap="round"
                                        :stroke-dasharray="2 * Math.PI * 14"
                                        :stroke-dashoffset="2 * Math.PI * 14 * (1 - Math.min(characterCount / 280, 1))"
                                        class="transition-all duration-300"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                        <div class="flex items-center space-x-4">
                            <!-- Media Icons (for future features) -->
                            <button type="button" class="p-2 text-gray-400 hover:text-blue-500 hover:bg-blue-50 rounded-full transition-colors" disabled>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </button>
                        </div>

                        <button
                            type="submit"
                            :disabled="!canPost"
                            class="px-6 py-2 bg-blue-500 text-white font-medium rounded-full hover:bg-blue-600 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
                        >
                            <span v-if="isSubmitting" class="flex items-center">
                                <svg class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Posting...
                            </span>
                            <span v-else>Post</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>