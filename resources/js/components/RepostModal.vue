<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

interface PostData {
    id: number;
    content: string;
    user: {
        id: number;
        name: string;
        email: string;
        avatar?: string | null;
    };
    images?: string[];
    created_at: string;
}

const props = defineProps<{
    post: PostData;
    targetPostId: number; // post id to repost (handles reposting a repost)
}>();

const emit = defineEmits<{
    close: [];
    submitted: [];
}>();

const caption = ref('');
const imageFiles = ref<File[]>([]);
const imagePreviews = ref<string[]>([]);
const isSubmitting = ref(false);
const maxImages = 4;

const characterCount = computed(() => caption.value.length);
const isOverLimit = computed(() => characterCount.value > 280);
const canSubmit = computed(
    () => !isOverLimit.value && !isSubmitting.value
);

const handleImageSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const files = Array.from(target.files || []);

    if (imageFiles.value.length + files.length > maxImages) {
        alert(`Maximum ${maxImages} images allowed`);
        return;
    }

    for (const file of files) {
        if (file.size > 2 * 1024 * 1024) {
            alert(`${file.name} size must be less than 2MB`);
            continue;
        }

        if (!file.type.startsWith('image/')) {
            alert(`${file.name} is not an image file`);
            continue;
        }

        imageFiles.value.push(file);

        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreviews.value.push(e.target?.result as string);
        };
        reader.readAsDataURL(file);
    }
};

const removeImage = (index: number) => {
    imageFiles.value.splice(index, 1);
    imagePreviews.value.splice(index, 1);
};

const submitRepost = async () => {
    isSubmitting.value = true;

    try {
        console.log('Submitting repost to target post ID:', props.targetPostId);
        const formData = new FormData();
        formData.append('caption', caption.value.trim());

        imageFiles.value.forEach((file) => {
            formData.append('images[]', file);
        });

        console.log('Posting to URL:', `/posts/${props.targetPostId}/repost`);
        await router.post(`/posts/${props.targetPostId}/repost`, formData, {
            preserveState: false,
            forceFormData: true,
            onSuccess: () => {
                console.log('Repost successful!');
                caption.value = '';
                imageFiles.value = [];
                imagePreviews.value = [];
                emit('submitted');
                emit('close');
            },
            onError: (errors) => {
                console.error('Error reposting:', errors);
                alert('Failed to repost. Please try again.');
            },
        });
    } catch (error) {
        console.error('Repost submission failed:', error);
    } finally {
        isSubmitting.value = false;
    }
};

const closeModal = () => {
    if (!isSubmitting.value) {
        caption.value = '';
        imageFiles.value = [];
        imagePreviews.value = [];
        emit('close');
    }
};

const getUserUsername = (user: any) => {
    if (!user) return '';
    return user.username || user.name.toLowerCase().replace(/\s+/g, '');
};

const handleKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Escape') {
        closeModal();
    }
};


</script>

<template>
    <div class="bg-opacity-30 fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm"
        @click.self="closeModal" @keydown="handleKeydown" tabindex="0">
        <div class="mx-4 max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white shadow-xl">
            <!-- Header -->
            <div class="sticky top-0 z-10 flex items-center justify-between border-b border-gray-200 bg-white p-4">
                <h3 class="text-lg font-semibold text-gray-900">Repost</h3>
                <button @click="closeModal" :disabled="isSubmitting"
                    class="rounded-full p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 disabled:opacity-50">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="p-4">
                <!-- Original Post Preview (Bordered Container) -->
                <div class="border-l-4 border-gray-300 bg-gray-50 p-3 mb-4 rounded">
                    <div class="flex items-start space-x-3 mb-3">
                        <div class="h-8 w-8 overflow-hidden rounded-full bg-blue-500 flex-shrink-0">
                            <img v-if="post.user.avatar" :src="`/storage/${post.user.avatar}`" :alt="post.user.name"
                                class="h-full w-full object-cover" />

                            <div v-else
                                class="flex h-full w-full items-center justify-center text-xs font-medium text-white">
                                {{ post.user.name.charAt(0).toUpperCase() }}
                            </div>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-sm text-gray-900">{{ post.user.name }}</p>
                            <p class="text-xs text-gray-500">@{{ getUserUsername(post.user) }}</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-800 mb-3">{{ post.content }}</p>
                    <div v-if="post.images?.length" class="grid gap-2"
                        :class="{ 'grid-cols-2': post.images.length > 1 }">
                        <img v-for="(img, idx) in post.images" :key="idx" :src="img" :alt="'Post image ' + (idx + 1)"
                            class="w-full h-32 object-cover rounded" />
                    </div>
                </div>

                <!-- Repost Form -->
                <form @submit.prevent="submitRepost" class="space-y-4">
                    <!-- Caption Textarea -->
                    <div>
                        <textarea v-model="caption" placeholder="Add your thoughts... (optional)"
                            class="min-h-[100px] w-full resize-none rounded-lg border border-gray-300 p-3 text-base text-gray-900 placeholder-gray-500 focus:border-transparent focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            :class="{ 'border-red-500 focus:ring-red-500': isOverLimit }"
                            :disabled="isSubmitting"></textarea>

                        <!-- Image Previews Grid -->
                        <div v-if="imagePreviews.length > 0" class="mt-3 grid gap-2"
                            :class="{ 'grid-cols-1': imagePreviews.length === 1, 'grid-cols-2': imagePreviews.length > 1 }">
                            <div v-for="(preview, index) in imagePreviews" :key="index" class="relative">
                                <img :src="preview" :alt="`Preview ${index + 1}`"
                                    class="h-48 w-full rounded-lg border border-gray-200 object-cover" />
                                <button type="button" @click="removeImage(index)"
                                    class="bg-opacity-75 hover:bg-opacity-90 absolute top-2 right-2 rounded-full bg-gray-900 p-1.5 text-white transition-colors"
                                    :disabled="isSubmitting">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Character Count -->
                        <div class="mt-2 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-500" :class="{ 'text-red-500': isOverLimit }">
                                    {{ characterCount }}/280
                                </span>
                                <div v-if="imagePreviews.length > 0" class="text-sm text-gray-500">
                                    • {{ imagePreviews.length }}/{{ maxImages }} images
                                </div>
                            </div>
                            <div class="h-8 w-8">
                                <svg class="h-full w-full -rotate-90 transform">
                                    <circle cx="16" cy="16" r="14" fill="none" stroke="#e5e7eb" stroke-width="4" />
                                    <circle cx="16" cy="16" r="14" fill="none"
                                        :stroke="isOverLimit ? '#ef4444' : '#3b82f6'" stroke-width="4"
                                        stroke-linecap="round" :stroke-dasharray="2 * Math.PI * 14"
                                        :stroke-dashoffset="2 * Math.PI * 14 * (1 - Math.min(characterCount / 280, 1))"
                                        class="transition-all duration-300" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between border-t border-gray-200 pt-4">
                        <label class="cursor-pointer rounded-full p-2 text-blue-500 transition-colors hover:bg-blue-50"
                            :class="{ 'cursor-not-allowed opacity-50': imageFiles.length >= maxImages }">
                            <input id="image-upload" type="file" accept="image/*" multiple class="hidden"
                                @change="handleImageSelect"
                                :disabled="isSubmitting || imageFiles.length >= maxImages" />
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                        </label>

                        <button type="submit" :disabled="!canSubmit"
                            class="rounded-full bg-blue-500 px-6 py-2 font-medium text-white transition-colors hover:bg-blue-600 disabled:cursor-not-allowed disabled:bg-gray-300">
                            <span v-if="isSubmitting" class="flex items-center gap-2">
                                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Reposting...
                            </span>
                            <span v-else>Repost</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
