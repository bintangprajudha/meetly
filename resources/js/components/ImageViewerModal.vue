<script setup lang="ts">
import { ref, computed, watch, onUnmounted } from 'vue';

const props = defineProps<{
    isOpen: boolean;
    images: string[];
    currentIndex: number;
}>();

const emit = defineEmits<{
    close: [];
}>();

const currentImageIndex = ref(props.currentIndex || 0);

const currentImage = computed(() => {
    return props.images[currentImageIndex.value] || '';
});

const nextImage = () => {
    if (currentImageIndex.value < props.images.length - 1) {
        currentImageIndex.value++;
    }
};

const prevImage = () => {
    if (currentImageIndex.value > 0) {
        currentImageIndex.value--;
    }
};

const goToImage = (index: number) => {
    currentImageIndex.value = index;
};

const closeModal = () => {
    emit('close');
};

// Handle keyboard navigation
const handleKeydown = (event: KeyboardEvent) => {
    if (!props.isOpen) return;

    switch (event.key) {
        case 'ArrowRight':
            nextImage();
            break;
        case 'ArrowLeft':
            prevImage();
            break;
        case 'Escape':
            closeModal();
            break;
    }
};

// Watch for modal opening to reset current index
watch(() => props.isOpen, (isOpen) => {
    if (isOpen) {
        currentImageIndex.value = props.currentIndex || 0;
        document.addEventListener('keydown', handleKeydown);
    } else {
        document.removeEventListener('keydown', handleKeydown);
    }
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <div
        v-if="isOpen"
        class="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50"
        @click="closeModal"
    >
        <div class="relative max-w-4xl max-h-screen p-4" @click.stop>
            <!-- Close button -->
            <button
                @click="closeModal"
                class="absolute top-2 right-2 z-10 text-white hover:text-gray-300 transition-colors"
            >
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <!-- Main image -->
            <div class="relative">
                <img
                    :src="currentImage"
                    :alt="`Image ${currentImageIndex + 1} of ${images.length}`"
                    class="max-w-full max-h-[80vh] object-contain mx-auto"
                />

                <!-- Navigation arrows (only show if multiple images) -->
                <button
                    v-if="images.length > 1"
                    @click="prevImage"
                    :disabled="currentImageIndex === 0"
                    class="absolute left-2 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>

                <button
                    v-if="images.length > 1"
                    @click="nextImage"
                    :disabled="currentImageIndex === images.length - 1"
                    class="absolute right-2 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>

            <!-- Thumbnail navigation (only show if multiple images) -->
            <div v-if="images.length > 1" class="flex justify-center mt-4 space-x-2 overflow-x-auto">
                <button
                    v-for="(image, index) in images"
                    :key="index"
                    @click="goToImage(index)"
                    :class="[
                        'flex-shrink-0 w-16 h-16 rounded border-2 overflow-hidden',
                        index === currentImageIndex ? 'border-blue-500' : 'border-gray-400 hover:border-gray-300'
                    ]"
                >
                    <img
                        :src="image"
                        :alt="`Thumbnail ${index + 1}`"
                        class="w-full h-full object-cover"
                    />
                </button>
            </div>

            <!-- Image counter -->
            <div v-if="images.length > 1" class="text-center mt-2 text-white text-sm">
                {{ currentImageIndex + 1 }} / {{ images.length }}
            </div>
        </div>
    </div>
</template>
