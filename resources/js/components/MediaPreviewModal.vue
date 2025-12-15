<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps<{
    isOpen: boolean;
    media: Array<{ type: 'image' | 'video'; src: string }>;
    currentIndex: number;
}>();

const emit = defineEmits<{
    close: [];
    updateIndex: [index: number];
}>();

const localIndex = ref(props.currentIndex);

watch(
    () => props.currentIndex,
    (newIndex) => {
        localIndex.value = newIndex;
    },
);

watch(
    () => props.isOpen,
    (isOpen) => {
        if (isOpen) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    },
);

const nextMedia = () => {
    if (localIndex.value < props.media.length - 1) {
        localIndex.value++;
        emit('updateIndex', localIndex.value);
    }
};

const prevMedia = () => {
    if (localIndex.value > 0) {
        localIndex.value--;
        emit('updateIndex', localIndex.value);
    }
};

const closeModal = () => {
    emit('close');
};

const handleKeydown = (e: KeyboardEvent) => {
    if (!props.isOpen) return;

    if (e.key === 'ArrowRight') nextMedia();
    if (e.key === 'ArrowLeft') prevMedia();
    if (e.key === 'Escape') closeModal();
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <Teleport to="body">
        <div
            v-if="isOpen"
            class="fixed inset-0 z-50 flex h-screen w-screen items-center justify-center bg-black/90"
            @click="closeModal"
        >
            <!-- Close Button -->
            <button
                @click.stop="closeModal"
                class="absolute top-4 right-4 z-20 rounded-full bg-black/50 p-2 text-white shadow-lg transition-all hover:bg-black/70"
            >
                <svg
                    class="h-6 w-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="3"
                        d="M6 18L18 6M6 6l12 12"
                    ></path>
                </svg>
            </button>

            <!-- Previous Button -->
            <button
                v-if="localIndex > 0"
                @click.stop="prevMedia"
                class="absolute top-1/2 left-4 z-20 -translate-y-1/2 rounded-full bg-black/50 p-3 text-white shadow-lg transition-all hover:bg-black/70"
            >
                <svg
                    class="h-6 w-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="3"
                        d="M15 19l-7-7 7-7"
                    ></path>
                </svg>
            </button>

            <!-- Media Content (Image or Video) -->
            <div
                class="flex h-screen w-screen items-center justify-center p-4"
                @click.stop
            >
                <!-- Image -->
                <img
                    v-if="media[localIndex]?.type === 'image'"
                    :src="media[localIndex].src"
                    :alt="`Media ${localIndex + 1}`"
                    class="max-h-full max-w-full object-contain"
                />

                <!-- Video -->
                <video
                    v-else-if="media[localIndex]?.type === 'video'"
                    :src="media[localIndex].src"
                    controls
                    autoplay
                    playsinline
                    class="max-h-full max-w-full"
                ></video>
            </div>

            <!-- Next Button -->
            <button
                v-if="localIndex < media.length - 1"
                @click.stop="nextMedia"
                class="absolute top-1/2 right-4 z-20 -translate-y-1/2 rounded-full bg-black/50 p-3 text-white shadow-lg transition-all hover:bg-black/70"
            >
                <svg
                    class="h-6 w-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="3"
                        d="M9 5l7 7-7 7"
                    ></path>
                </svg>
            </button>

            <!-- Media Counter with Type -->
            <div
                class="absolute bottom-4 left-1/2 z-20 -translate-x-1/2 transform rounded-full bg-black/50 px-4 py-2 text-white"
            >
                <span class="capitalize">{{ media[localIndex]?.type }}</span>
                {{ localIndex + 1 }} / {{ media.length }}
            </div>
        </div>
    </Teleport>
</template>
