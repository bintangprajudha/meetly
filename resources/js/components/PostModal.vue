<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    isOpen: boolean;
    user: {
        id: number;
        name: string;
        email: string;
        avatar?: string | null;
        username?: string;
    };
}>();

const emit = defineEmits<{
    close: [];
    posted: [];
}>();

const content = ref('');

type MediaItem = {
    id: string;
    type: 'image' | 'video';
    file: File;
    preview: string;
};

const mediaItems = ref<MediaItem[]>([]);
const isSubmitting = ref(false);
const maxTotalFiles = 4; // Total max untuk gambar + video
const maxVideoSize = 50 * 1024 * 1024; // 50MB
const maxImageSize = 10 * 1024 * 1024; // 10MB

const characterCount = computed(() => content.value.length);
const isOverLimit = computed(() => characterCount.value > 280);
const totalFiles = computed(() => mediaItems.value.length);
const canPost = computed(
    () =>
        content.value.trim().length > 0 &&
        !isOverLimit.value &&
        !isSubmitting.value &&
        totalFiles.value <= maxTotalFiles,
);

const imagePreviews = computed(() =>
    mediaItems.value.filter((m) => m.type === 'image'),
);

const videoPreviews = computed(() =>
    mediaItems.value.filter((m) => m.type === 'video'),
);

const createMediaId = () =>
    `${Date.now()}_${Math.random().toString(16).slice(2)}`;

// Handle multiple image selection
const handleImageSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const files = Array.from(target.files || []);

    // Check total files limit
    if (totalFiles.value + files.length > maxTotalFiles) {
        alert(`Maximum ${maxTotalFiles} files (images + videos) allowed`);
        target.value = ''; // Reset input
        return;
    }

    for (const file of files) {
        if (file.size > maxImageSize) {
            alert(`${file.name} size must be less than 10MB`);
            continue;
        }

        if (!file.type.startsWith('image/')) {
            alert(`${file.name} is not an image file`);
            continue;
        }

        const id = createMediaId();
        mediaItems.value.push({ id, type: 'image', file, preview: '' });
        const reader = new FileReader();
        reader.onload = (e) => {
            const item = mediaItems.value.find((m) => m.id === id);
            if (item) item.preview = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

// Handle video selection
const handleVideoSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const files = Array.from(target.files || []);

    // Check total files limit
    if (totalFiles.value + files.length > maxTotalFiles) {
        alert(`Maximum ${maxTotalFiles} files (images + videos) allowed`);
        target.value = ''; // Reset input
        return;
    }

    for (const file of files) {
        if (file.size > maxVideoSize) {
            alert(`${file.name} size must be less than 10MB`);
            continue;
        }

        if (!file.type.startsWith('video/')) {
            alert(`${file.name} is not a video file`);
            continue;
        }

        const id = createMediaId();
        mediaItems.value.push({ id, type: 'video', file, preview: '' });
        const reader = new FileReader();
        reader.onload = (e) => {
            const item = mediaItems.value.find((m) => m.id === id);
            if (item) item.preview = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

// Remove image
const removeImage = (index: number) => {
    const item = imagePreviews.value[index];
    if (!item) return;
    mediaItems.value = mediaItems.value.filter((m) => m.id !== item.id);

    const fileInput = document.getElementById(
        'image-upload',
    ) as HTMLInputElement;
    if (fileInput) fileInput.value = '';
};

// Remove video
const removeVideo = (index: number) => {
    const item = videoPreviews.value[index];
    if (!item) return;
    mediaItems.value = mediaItems.value.filter((m) => m.id !== item.id);

    const fileInput = document.getElementById(
        'video-upload',
    ) as HTMLInputElement;
    if (fileInput) fileInput.value = '';
};

const submitPost = async () => {
    if (!canPost.value) return;

    // Double check total files
    if (totalFiles.value > maxTotalFiles) {
        alert(`Maximum ${maxTotalFiles} files allowed`);
        return;
    }

    isSubmitting.value = true;

    try {
        const formData = new FormData();
        formData.append('content', content.value.trim());

        const mediaOrder: Array<{ type: 'image' | 'video'; index: number }> =
            [];
        let imageIndex = 0;
        let videoIndex = 0;

        // Append files in the exact order user selected them.
        mediaItems.value.forEach((item) => {
            if (item.type === 'image') {
                formData.append('images[]', item.file);
                mediaOrder.push({ type: 'image', index: imageIndex });
                imageIndex++;
                return;
            }

            formData.append('videos[]', item.file);
            mediaOrder.push({ type: 'video', index: videoIndex });
            videoIndex++;
        });

        formData.append('media_order', JSON.stringify(mediaOrder));

        await router.post('/posts', formData, {
            preserveState: false,
            forceFormData: true,
            onSuccess: () => {
                content.value = '';
                mediaItems.value = [];
                emit('posted');
                emit('close');
            },
            onError: (errors) => {
                console.error('Post submit validation errors:', errors);
                const firstError =
                    (errors as any).content ||
                    (errors as any).media ||
                    (errors as any).videos ||
                    (errors as any)['videos.0'] ||
                    (errors as any).images ||
                    (errors as any)['images.0'] ||
                    Object.values(errors as Record<string, string>)[0];

                alert(
                    firstError ||
                        'An error occurred while submitting your post.',
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
        mediaItems.value = [];
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
    <div
        v-if="props.isOpen"
        class="bg-opacity-30 fixed inset-0 z-50 flex items-center justify-center backdrop-blur"
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
                <h3 class="text-lg font-semibold text-gray-900">Create Post</h3>
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
                <form @submit.prevent="submitPost">
                    <!-- User Info -->
                    <div class="mb-4 flex items-start space-x-3">
                       <div class="h-10 w-10 overflow-hidden rounded-full bg-blue-500 flex-shrink-0">
                            <img v-if="user.avatar" 
                                 :src="`/storage/${user.avatar}`" 
                                 :alt="user.name"
                                 class="h-full w-full object-cover" />
                            
                            <div v-else
                                 class="flex h-full w-full items-center justify-center text-sm font-medium text-white">
                                {{ user.name.charAt(0).toUpperCase() }}
                            </div>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">
                                {{ user.name }}
                            </p>
                            <p class="text-sm text-gray-500">
                                @{{getUserUsername(user)}}
                            </p>
                        </div>
                    </div>

                    <!-- Text Area -->
                    <div class="mb-4">
                        <textarea
                            v-model="content"
                            placeholder="What's happening?"
                            class="min-h-[120px] w-full resize-none rounded-lg border border-gray-300 p-3 text-lg text-black focus:border-transparent focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            :class="{
                                'border-red-500 focus:ring-red-500':
                                    isOverLimit,
                            }"
                            :disabled="isSubmitting"
                        ></textarea>

                        <!-- Image Previews Grid -->
                        <div
                            v-if="imagePreviews.length > 0"
                            class="mt-3 grid gap-2"
                            :class="{
                                'grid-cols-1': totalFiles === 1,
                                'grid-cols-2': totalFiles > 1,
                            }"
                        >
                            <div
                                v-for="(item, index) in imagePreviews"
                                :key="item.id"
                                class="relative"
                            >
                                <img
                                    :src="item.preview"
                                    :alt="`Preview ${index + 1}`"
                                    class="h-48 w-full rounded-lg border border-gray-200 object-cover"
                                />
                                <button
                                    type="button"
                                    @click="removeImage(index)"
                                    class="bg-opacity-75 hover:bg-opacity-90 absolute top-2 right-2 rounded-full bg-gray-900 p-1.5 text-white transition-colors"
                                    :disabled="isSubmitting"
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
                                            d="M6 18L18 6M6 6l12 12"
                                        ></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Video Previews -->
                        <div
                            v-if="videoPreviews.length > 0"
                            class="mt-3 grid gap-2"
                            :class="{
                                'grid-cols-1': totalFiles === 1,
                                'grid-cols-2': totalFiles > 1,
                            }"
                        >
                            <div
                                v-for="(item, index) in videoPreviews"
                                :key="item.id"
                                class="relative"
                            >
                                <video
                                    :src="item.preview"
                                    controls
                                    class="h-48 w-full rounded-lg border border-gray-200 object-cover"
                                ></video>
                                <button
                                    type="button"
                                    @click="removeVideo(index)"
                                    class="bg-opacity-75 hover:bg-opacity-90 absolute top-2 right-2 rounded-full bg-gray-900 p-1.5 text-white transition-colors"
                                    :disabled="isSubmitting"
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
                                            d="M6 18L18 6M6 6l12 12"
                                        ></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Character Count -->
                        <div class="mt-2 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="text-sm text-gray-500">
                                    <span
                                        :class="{ 'text-red-500': isOverLimit }"
                                    >
                                        {{ characterCount }}/280
                                    </span>
                                </div>
                                <div
                                    v-if="totalFiles > 0"
                                    class="text-sm text-gray-500"
                                >
                                    • {{ totalFiles }}/{{ maxTotalFiles }} files
                                </div>
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
                                                    characterCount / 280,
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
                        class="flex items-center justify-between border-t border-gray-200 pt-4"
                    >
                        <div class="flex items-center space-x-2">
                            <!-- Image Upload Button -->
                            <label
                                class="cursor-pointer rounded-full p-2 text-blue-500 transition-colors hover:bg-blue-50"
                                :class="{
                                    'cursor-not-allowed opacity-50':
                                        totalFiles >= maxTotalFiles,
                                }"
                            >
                                <input
                                    id="image-upload"
                                    type="file"
                                    accept="image/*"
                                    multiple
                                    class="hidden"
                                    @change="handleImageSelect"
                                    :disabled="
                                        isSubmitting ||
                                        totalFiles >= maxTotalFiles
                                    "
                                />
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
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    ></path>
                                </svg>
                            </label>

                            <!-- Video Upload Button -->
                            <label
                                class="cursor-pointer rounded-full p-2 text-green-500 transition-colors hover:bg-green-50"
                                :class="{
                                    'cursor-not-allowed opacity-50':
                                        totalFiles >= maxTotalFiles,
                                }"
                            >
                                <input
                                    id="video-upload"
                                    type="file"
                                    accept="video/*"
                                    multiple
                                    class="hidden"
                                    @change="handleVideoSelect"
                                    :disabled="
                                        isSubmitting ||
                                        totalFiles >= maxTotalFiles
                                    "
                                />
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
                                        d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
                                    ></path>
                                </svg>
                            </label>
                        </div>

                        <button
                            type="submit"
                            :disabled="!canPost"
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
