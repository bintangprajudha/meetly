<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

const page = usePage();

const props = defineProps<{
    isOpen: boolean;
    post: {
        id: number | string;
        content: string;
        user: {
            name: string;
        };
    };
}>();

const emit = defineEmits<{
    close: [];
    shared: [];
}>();

const searchQuery = ref('');
const selectedUsers = ref<number[]>([]);
const users = ref<{ id: number; name: string; avatar?: string }[]>([]);
const loading = ref(false);
const sharing = ref(false);
const failedAvatars = ref<Set<number>>(new Set());

const filteredUsers = computed(() => {
    if (!searchQuery.value) return users.value;
    return users.value.filter(user =>
        user.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

// Avatar functions
const getInitials = (name: string) => {
    if (!name) return 'U';
    return name
        .split(' ')
        .map((word) => word.charAt(0))
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

const getAvatarColor = (name: string) => {
    if (!name) return '#6B7280';
    
    const colors = [
        '#EF4444', '#F97316', '#F59E0B', '#EAB308',
        '#84CC16', '#22C55E', '#10B981', '#14B8A6',
        '#06B6D4', '#0EA5E9', '#3B82F6', '#6366F1',
        '#8B5CF6', '#A855F7', '#D946EF', '#EC4899',
    ];
    
    const hash = name
        .split('')
        .reduce((acc, char) => acc + char.charCodeAt(0), 0);
    
    return colors[hash % colors.length];
};

const fetchUsers = async () => {
    loading.value = true;
    try {
        const res = await axios.get('/api/users');
        users.value = res.data.filter((user: any) => user.id !== page.props.auth?.user?.id);
    } catch (e) {
        console.error('Failed to fetch users:', e);
    } finally {
        loading.value = false;
    }
};

const toggleUser = (userId: number) => {
    const index = selectedUsers.value.indexOf(userId);
    if (index > -1) {
        selectedUsers.value.splice(index, 1);
    } else {
        selectedUsers.value.push(userId);
    }
};

const handleImageError = (userId: number) => {
    failedAvatars.value.add(userId);
};

const sharePost = async () => {
    if (selectedUsers.value.length === 0) return;

    sharing.value = true;
    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (token) axios.defaults.headers.common['X-CSRF-TOKEN'] = token;

        const response = await axios.post('/api/posts/share', {
            post_id: props.post.id,
            user_ids: selectedUsers.value,
        });

        if (response.data.success) {
            emit('shared');
            emit('close');
            selectedUsers.value = [];
            searchQuery.value = '';
        } else {
            console.error('Share failed:', response.data);
        }

    } catch (e: any) {
        console.error('Failed to share post:', e);
        alert('Failed to share post. Please check your connection and try again.');
    } finally {
        sharing.value = false;
    }
};

const closeModal = () => {
    emit('close');
    selectedUsers.value = [];
    searchQuery.value = '';
    failedAvatars.value.clear();
};

// Watch for modal opening
watch(() => props.isOpen, (isOpen) => {
    if (isOpen) {
        fetchUsers();
    }
});
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4 shadow-2xl border border-gray-200">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Share Post</h3>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-2">Share this post with:</p>
                <div class="bg-gray-50 p-3 rounded-lg mb-4 border border-gray-200">
                    <p class="text-sm font-medium text-gray-900">{{ post.user.name }}</p>
                    <p class="text-sm text-gray-700 truncate">{{ post.content }}</p>
                </div>
            </div>

            <div class="mb-4">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search users..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900 placeholder-gray-500 bg-white"
                />
            </div>

            <div class="max-h-48 overflow-y-auto mb-4 border border-gray-200 rounded-lg bg-white">
                <div v-if="loading" class="text-center py-4">
                    <div class="text-sm text-gray-500">Loading users...</div>
                </div>
                <div v-else-if="filteredUsers.length === 0" class="text-center py-4">
                    <div class="text-sm text-gray-500">No users found</div>
                </div>
                <div v-else>
                    <div
                        v-for="user in filteredUsers"
                        :key="user.id"
                        @click="toggleUser(user.id)"
                        class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 cursor-pointer transition-colors"
                    >
                        <input
                            type="checkbox"
                            :checked="selectedUsers.includes(user.id)"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            readonly
                        />
                        
                        <!-- Avatar with color and fallback -->
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-medium text-white flex-shrink-0"
                             :style="{ backgroundColor: getAvatarColor(user.name) }">
                            <img v-if="user.avatar && !failedAvatars.has(user.id)" 
                                 :src="user.avatar" 
                                 :alt="user.name"
                                 class="w-full h-full object-cover rounded-full" 
                                 @error="handleImageError(user.id)" />
                            <span v-else class="select-none">{{ getInitials(user.name) }}</span>
                        </div>
                        
                        <span class="text-sm font-medium text-gray-900">{{ user.name }}</span>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-2">
                <button
                    @click="closeModal"
                    class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    Cancel
                </button>
                <button
                    @click="sharePost"
                    :disabled="selectedUsers.length === 0 || sharing"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <span v-if="sharing">Sharing...</span>
                    <span v-else>Share ({{ selectedUsers.length }})</span>
                </button>
            </div>
        </div>
    </div>
</template>