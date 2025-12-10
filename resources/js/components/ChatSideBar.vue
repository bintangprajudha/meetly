<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { Link } from '@inertiajs/vue3';

const chats = ref([]);
const loading = ref(true);

const fetchChats = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/api/messages');
    chats.value = res.data;
  } catch (e) {
    console.error('Gagal memuat chats', e);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchChats();
});
</script>

<template>
    <div class="w-80 border-r bg-white h-screen overflow-y-auto p-4 space-y-3">
    <div class="font-semibold text-lg mb-3">Chat</div>

    <div v-if="loading" class="text-sm text-black">Memuat...</div>
    <div v-else>
      <div v-if="chats.length === 0" class="text-sm text-black">Belum ada percakapan</div>

      <div v-for="chat in chats" :key="chat.user.id" class="mb-1 text-gray-600">
        <Link
          :href="`/chat/${chat.user.id}`"
          class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-100 transition"
        >
          <img
            :src="chat.user.avatar ?? '/profile.png'"
            class="w-10 h-10 rounded-full object-cover"
            alt="avatar"
          />
          <div class="min-w-0">
            <div class="font-semibold truncate">{{ chat.user.name }}</div>
            <div class="text-sm text-gray-900 truncate">
              {{ chat.last_message ?? "Belum ada pesan" }}
            </div>
          </div>
        </Link>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* tambahan style bila perlu */
</style>
