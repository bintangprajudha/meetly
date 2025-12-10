<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue';
import axios from 'axios';
import { usePage, Link } from '@inertiajs/vue3';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue';

interface User { id: number; name: string; avatar?: string | null }
interface ChatMessage { id: number; sender_id: number; receiver_id: number; message: string; created_at: string }

// Page props
const page = usePage();
const chatUser = page.props.chatUser as User | null;
const authUser = page.props.auth!.user as User;

// Chat state
const messages = ref<ChatMessage[]>([]);
const newMessage = ref('');
const chatContainer = ref<HTMLElement | null>(null);

// Sidebar chat
const chats = ref<{ user: User; last_message: string | null }[]>([]);
const loading = ref(true);

// Helpers
const formatTime = (ts: string) => new Date(ts).toLocaleTimeString();
const scrollToBottom = () => {
  if (chatContainer.value) chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
};

// Fetch chat messages
const fetchMessages = async () => {
  if (!chatUser?.id) return;

  try {
    const res = await axios.get(`/api/messages/${chatUser.id}`);
    messages.value = res.data;
    await nextTick();
    scrollToBottom();
  } catch (e) {
    console.error('Gagal fetch messages:', e);
  }
};

// Send message
const sendMessage = async () => {
  if (!chatUser?.id) return console.error('Tidak ada pengguna untuk dikirimi pesan');
  if (!newMessage.value.trim()) return;

  try {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (token) axios.defaults.headers.common['X-CSRF-TOKEN'] = token;

    await axios.post('/api/messages', {
      receiver_id: chatUser.id,
      message: newMessage.value,
    });

    newMessage.value = '';
    await fetchMessages();
  } catch (e: any) {
    console.error('Gagal mengirim pesan:', e.response?.data || e);
  }
};

// Fetch sidebar chat list
const fetchChats = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/api/messages');
    chats.value = res.data;
  } catch (e) {
    console.error('Gagal fetch chats:', e);
  } finally {
    loading.value = false;
  }
};

// Lifecycle & Realtime
onMounted(() => {
  fetchMessages();
  fetchChats();

  if (window.Echo && authUser?.id) {
    window.Echo.private(`chat.${authUser.id}`)
      .listen('NewMessage', (e: { message: ChatMessage }) => {
        if (chatUser && (e.message.sender_id === chatUser.id || e.message.receiver_id === chatUser.id)) {
          fetchMessages();
          fetchChats();
        }
      });
  }
});
</script>

<template>
  <AppSidebarLayout>
    <AppHeaderLayout>
      <div class="h-screen flex justify-center overflow-hidden bg-gray-100">
        <div class="flex w-full max-w-[1200px]">

          <!-- Sidebar Chat -->
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
                  <img :src="chat.user.avatar ?? '/profile.png'" class="w-10 h-10 rounded-full object-cover" />
                  <div class="min-w-0">
                    <div class="font-semibold truncate">{{ chat.user.name }}</div>
                    <div class="text-sm text-gray-900 truncate">{{ chat.last_message ?? "Belum ada pesan" }}</div>
                  </div>
                </Link>
              </div>
            </div>
          </div>

          <!-- Chat Panel -->
          <div class="flex flex-col flex-1 max-w-[900px] bg-white border-l">

            <!-- Header Chat -->
            <div class="p-4 border-b font-semibold bg-white text-black flex items-center gap-2">
              <img :src="chatUser?.avatar ?? '/profile.png'" class="w-8 h-8 rounded-full object-cover" />
              <span>{{ chatUser?.name ?? "Pilih pengguna" }}</span>
            </div>

            <!-- Messages -->
            <div ref="chatContainer" class="flex-1 overflow-y-auto p-5 space-y-3 bg-gray-50">
              <div v-for="msg in messages" :key="msg.id"
                class="max-w-[70%] px-4 py-2 rounded-2xl text-[15px] leading-relaxed shadow-sm"
                :class="msg.sender_id === authUser.id
                  ? 'bg-blue-100 text-black ml-auto'
                  : 'bg-gray-200 text-black'">
                <div>{{ msg.message }}</div>
                <div class="text-[10px] opacity-60 mt-1 text-right">{{ formatTime(msg.created_at) }}</div>
              </div>
            </div>

            <!-- Input -->
            <div class="p-4 border-t bg-white flex gap-3">
              <input
                v-model="newMessage"
                @keyup.enter="sendMessage"
                class="flex-1 border border-gray-300 rounded-full px-4 py-2 focus:outline-none focus:border-blue-500 text-black"
                placeholder="Ketik pesan..."
              />
              <button
                @click="sendMessage"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-full font-medium transition"
              >
                Kirim
              </button>
            </div>

          </div>
        </div>
      </div>
    </AppHeaderLayout>
  </AppSidebarLayout>
</template>