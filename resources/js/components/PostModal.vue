<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

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
const imageFiles = ref<File[]>([]); // Array of files
const imagePreviews = ref<string[]>([]); // Array of previews
const isSubmitting = ref(false);
const maxImages = 4;

const characterCount = computed(() => content.value.length);
const isOverLimit = computed(() => characterCount.value > 280);
const canPost = computed(
    () =>
        content.value.trim().length > 0 &&
        !isOverLimit.value &&
        !isSubmitting.value,
);

// Handle multiple image selection
const handleImageSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const files = Array.from(target.files || []);

    // Check if adding these files exceeds max limit
    if (imageFiles.value.length + files.length > maxImages) {
        alert(`Maximum ${maxImages} images allowed`);
        return;
    }

    for (const file of files) {
        // Validate file size (10MB max)
        if (file.size > 10 * 1024 * 1024) {
            alert(`${file.name} size must be less than 10MB`);
            continue;
        }

        // Validate file type
        if (!file.type.startsWith('image/')) {
            alert(`${file.name} is not an image file`);
            continue;
        }

        imageFiles.value.push(file);

        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreviews.value.push(e.target?.result as string);
        };
        reader.readAsDataURL(file);
    }
};

// Remove specific image by index
const removeImage = (index: number) => {
    imageFiles.value.splice(index, 1);
    imagePreviews.value.splice(index, 1);

    // Reset file input
    const fileInput = document.getElementById(
        'image-upload',
    ) as HTMLInputElement;
    if (fileInput) fileInput.value = '';
};

const submitPost = async () => {
    if (!canPost.value) return;

    isSubmitting.value = true;

    try {
        const formData = new FormData();
        formData.append('content', content.value.trim());

        // Append all images
        imageFiles.value.forEach((file) => {
            formData.append('images[]', file);
        });

        await router.post('/posts', formData, {
            preserveState: false,
            forceFormData: true,
            onSuccess: () => {
                content.value = '';
                imageFiles.value = [];
                imagePreviews.value = [];
                emit('posted');
                emit('close');
            },
            onError: (errors) => {
                console.error('Error posting:', errors.content);
                alert(
                    errors.content ||
                    'An error occurred while submitting your post.'
                );
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
        content.value = '';
        imageFiles.value = [];
        imagePreviews.value = [];
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
    <div v-if="props.isOpen"
        class="bg-opacity-30 fixed inset-0 z-50 flex items-center justify-center bg-gray-400 backdrop-blur"
        @click.self="closeModal" @keydown="handleKeydown" tabindex="0">
        <div class="mx-4 max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white shadow-xl">
            <!-- Header -->
            <div class="sticky top-0 z-10 flex items-center justify-between border-b border-gray-200 bg-white p-4">
                <h3 class="text-lg font-semibold text-gray-900">Create Post</h3>
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
                <form @submit.prevent="submitPost">
                    <!-- User Info -->
                    <div class="mb-4 flex items-start space-x-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-500 text-sm font-medium text-white">
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
                        <textarea v-model="content" placeholder="What's happening?"
                            class="min-h-[120px] w-full resize-none rounded-lg border border-gray-300 p-3 text-lg text-black focus:border-transparent focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            :class="{
                                'border-red-500 focus:ring-red-500':
                                    isOverLimit,
                            }" :disabled="isSubmitting"></textarea>

                        <!-- Image Previews Grid -->
                        <div v-if="imagePreviews.length > 0" class="mt-3 grid gap-2" :class="{
                            'grid-cols-1': imagePreviews.length === 1,
                            'grid-cols-2': imagePreviews.length > 1,
                        }">
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
                                <div class="text-sm text-gray-500">
                                    <span :class="{ 'text-red-500': isOverLimit }">
                                        {{ characterCount }}/280
                                    </span>
                                </div>
                                <div v-if="imagePreviews.length > 0" class="text-sm text-gray-500">
                                    • {{ imagePreviews.length }}/{{
                                        maxImages
                                    }}
                                    images
                                </div>
                            </div>
                            <div class="h-8 w-8">
                                <svg class="h-full w-full -rotate-90 transform">
                                    <circle cx="16" cy="16" r="14" fill="none" stroke="#e5e7eb" stroke-width="4" />
                                    <circle cx="16" cy="16" r="14" fill="none" :stroke="isOverLimit ? '#ef4444' : '#3b82f6'
                                        " stroke-width="4" stroke-linecap="round" :stroke-dasharray="2 * Math.PI * 14"
                                        :stroke-dashoffset="2 *
                                            Math.PI *
                                            14 *
                                            (1 -
                                                Math.min(
                                                    characterCount / 280,
                                                    1,
                                                ))
                                            " class="transition-all duration-300" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between border-t border-gray-200 pt-4">
                        <div class="flex items-center space-x-4">
                            <!-- Image Upload Button -->
                            <label
                                class="cursor-pointer rounded-full p-2 text-blue-500 transition-colors hover:bg-blue-50"
                                :class="{
                                    'cursor-not-allowed opacity-50':
                                        imageFiles.length >= maxImages,
                                }">
                                <input id="image-upload" type="file" accept="image/*" multiple class="hidden"
                                    @change="handleImageSelect" :disabled="isSubmitting ||
                                        imageFiles.length >= maxImages
                                        " />
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </label>
                        </div>

                        <button type="submit" :disabled="!canPost"
                            class="rounded-full bg-blue-500 px-6 py-2 font-medium text-white transition-colors hover:bg-blue-600 disabled:cursor-not-allowed disabled:bg-gray-300">
                            <span v-if="isSubmitting" class="flex items-center">
                                <svg class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
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
