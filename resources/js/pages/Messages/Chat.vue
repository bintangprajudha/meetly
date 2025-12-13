<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from 'vue';
import axios from 'axios';
import { usePage, Link, router } from '@inertiajs/vue3';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';

interface User {
  id: number;
  name: string;
  email?: string;
  avatar?: string | null;
}

interface ChatMessage {
  id: number;
  sender_id: number;
  receiver_id: number;
  message: string;
  images?: string[] | null;
  created_at: string;
  status?: string;
}

interface Chat {
  user: User;
  last_message: string | null;
  last_message_at: string | null;
  is_read: boolean;
}

const page = usePage();
const chatUser = ref(page.props.chatUser as User | null);
const authUser = page.props.auth!.user as User;

const messages = ref<ChatMessage[]>([]);
const newMessage = ref('');
const chatContainer = ref<HTMLElement | null>(null);
const chats = ref<Chat[]>([]);
const loading = ref(false);

// State untuk dropdown dan image viewer
const activeDropdown = ref<number | null>(null);
const showImageViewer = ref(false);
const imageViewerImages = ref<string[]>([]);
const imageViewerCurrentIndex = ref(0);

// State untuk modal dan pencarian
const showUserModal = ref(false);
const allUsers = ref<User[]>([]);
const loadingUsers = ref(false);
const searchQuery = ref('');
const userSearchQuery = ref('');

// Computed
const filteredChats = computed(() => {
  if (!searchQuery.value) return chats.value;
  const query = searchQuery.value.toLowerCase();
  return chats.value.filter(chat => 
    chat.user.name.toLowerCase().includes(query) ||
    chat.last_message?.toLowerCase().includes(query)
  );
});

const filteredUsers = computed(() => {
  if (!userSearchQuery.value) return allUsers.value;
  const query = userSearchQuery.value.toLowerCase();
  return allUsers.value.filter(user => 
    user.name.toLowerCase().includes(query) ||
    user.email?.toLowerCase().includes(query)
  );
});

const formatTime = (ts: string) => {
  if (!ts) return '';

  try {
    const date = new Date(ts);
    const now = new Date();
    const diff = now.getTime() - date.getTime();
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    
    if (days === 0) {
      return date.toLocaleTimeString('id-ID', { 
        hour: '2-digit', 
        minute: '2-digit' 
      });
    } else if (days === 1) {
      return 'Kemarin';
    } else if (days < 7) {
      return date.toLocaleDateString('id-ID', { weekday: 'short' });
    } else {
      return date.toLocaleDateString('id-ID', { 
        day: '2-digit', 
        month: '2-digit' 
      });
    }
  } catch (error) {
    console.error('Error formatting time:', error);
    return '';
  }
}

const scrollToBottom = () => {
  nextTick(() => {
    if (chatContainer.value) {
      chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
  });
};

const fetchMessages = async () => {
  if (!chatUser.value?.id) return;
  
  // Cegah fetch messages dengan diri sendiri
  if (chatUser.value.id === authUser.id) {
    console.error('Cannot fetch messages with yourself');
    return;
  }
  
  try {
    const res = await axios.get(`/api/messages/${chatUser.value.id}`);
    messages.value = res.data;
    await nextTick();
    scrollToBottom();
  } catch (err: any) {
    if (err.response?.status === 403) {
      console.error('Forbidden: Cannot chat with yourself');
      router.visit('/chat');
    } else {
      console.error("Gagal fetch messages:", err);
    }
  }
};

const sendMessage = async () => {
  if (!chatUser.value?.id || !newMessage.value.trim()) return;
  
  // Cegah kirim pesan ke diri sendiri
  if (chatUser.value.id === authUser.id) {
    alert('Tidak bisa mengirim pesan ke diri sendiri');
    return;
  }
  
  try {
    await axios.post('/api/messages', {
      receiver_id: chatUser.value.id,
      message: newMessage.value,
    });
    
    newMessage.value = '';
    await fetchMessages();
    await fetchChats();
    scrollToBottom();
  } catch (e: any) {
    if (e.response?.status === 403) {
      alert('Tidak bisa mengirim pesan ke diri sendiri');
    } else {
      console.error("Gagal mengirim pesan:", e);
    }
  }
};

const fetchChats = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/api/messages');
    // Hanya tampilkan chats yang benar-benar punya pesan
    chats.value = res.data.filter((chat: Chat) => chat.last_message !== null);
    console.log('Chats loaded:', chats.value.length); // Debug
  } catch (e) {
    console.error("Gagal fetch chats:", e);
  } finally {
    loading.value = false;
  }
};

const fetchAllUsers = async () => {
  loadingUsers.value = true;
  try {
    // Gunakan route /api/chat/users yang sudah dibuat di MessageController
    const res = await axios.get('/api/chat/users');
    // Response langsung array dengan id, name, email, avatar
    const users = Array.isArray(res.data) ? res.data : [];
    // Filter sudah dilakukan di backend, tapi double check
    allUsers.value = users.filter((user: User) => user.id !== authUser.id);
    console.log('Users loaded:', allUsers.value.length, 'users'); // Debug
  } catch (e: any) {
    console.error("Gagal fetch users:", e);
    console.error("Error detail:", e.response?.data); // Debug error
  } finally {
    loadingUsers.value = false;
  }
};

const startNewChat = async (user: User) => {
  // Cegah chat dengan diri sendiri
  if (user.id === authUser.id) {
    alert('Tidak bisa chat dengan diri sendiri');
    return;
  }
  
  // Close modal first
  showUserModal.value = false;
  userSearchQuery.value = '';
  
  // Navigate to chat
  router.visit(`/chat/${user.id}`);
};

// Fungsi untuk toggle dropdown
const toggleDropdown = (messageId: number) => {
  activeDropdown.value = activeDropdown.value === messageId ? null : messageId;
};

// Fungsi untuk delete message
const deleteMessage = async (messageId: number) => {
  if (!confirm('Hapus pesan ini?')) return;
  
  try {
    await axios.delete(`/api/messages/${messageId}`);
    messages.value = messages.value.filter(msg => msg.id !== messageId);
    activeDropdown.value = null;
    
    // Refresh chat list
    await fetchChats();
  } catch (e) {
    console.error("Gagal menghapus pesan:", e);
    alert('Gagal menghapus pesan');
  }
};

// Fungsi untuk open image viewer
const openImageViewer = (images: string[], index: number) => {
  imageViewerImages.value = images;
  imageViewerCurrentIndex.value = index;
  showImageViewer.value = true;
};

onMounted(() => {
  // Cek apakah chatUser adalah diri sendiri
  if (chatUser.value?.id === authUser.id) {
    console.error('Cannot chat with yourself, redirecting...');
    router.visit('/chat');
    return;
  }
  
  // Fetch messages if chatUser exists and not self
  if (chatUser.value?.id) {
    fetchMessages();
  }
  
  // Fetch chats list (will be empty initially if no chats exist)
  fetchChats();
  
  // Fetch all users for modal
  fetchAllUsers();

  // Close dropdown ketika klik di luar
  document.addEventListener('click', (e) => {
    const target = e.target as HTMLElement;
    if (!target.closest('.dropdown-container')) {
      activeDropdown.value = null;
    }
  });

  // Echo listeners
  if (window.Echo && authUser?.id) {
    window.Echo.private(`chat.${authUser.id}`)
      .listen('NewMessage', (e: { message: ChatMessage }) => {
        if (chatUser.value && (e.message.sender_id === chatUser.value.id || e.message.receiver_id === chatUser.value.id)) {
          fetchMessages();
        }
        fetchChats();
      })
      .listen('MessageDelivered', (e: { message_id: number }) => {
        const msg = messages.value.find(m => m.id === e.message_id);
        if (msg) msg.status = 'delivered';
      })
      .listen('MessageRead', (e: { message_id: number }) => {
        const msg = messages.value.find(m => m.id === e.message_id);
        if (msg) msg.status = 'read';
      });
  }
});
</script>

<template>
  <AppSidebarLayout>
    <div class="h-screen flex overflow-hidden">
      <div class="flex w-full max-w-[1000px] border border-[#C9C9C9] bg-white">

        <!-- Sidebar Chat -->
        <div class="hidden md:block w-72 border-r border-[#C9C9C9] bg-white h-screen overflow-y-auto p-4 space-y-3">
          <div class="flex items-center justify-between mb-3">
            <div class="font-semibold text-lg text-black">Obrolan</div>
            
            <!-- Button to open user selection modal -->
            <button @click="showUserModal = true" 
              class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-full transition"
              title="Mulai chat baru">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
            </button>
          </div>

          <!-- Search -->
          <div class="relative">
            <input v-model="searchQuery" type="text" 
              class="w-full px-3 py-2 border border-[#C9C9C9] rounded-lg bg-white text-black text-sm"
              placeholder="Cari..." />
          </div>

          <div v-if="loading" class="text-sm text-gray-500">Memuat...</div>

          <div v-else>
            <!-- Empty state -->
            <div v-if="filteredChats.length === 0" class="flex flex-col items-center justify-center py-8 text-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                  d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
              </svg>
              <p class="text-sm text-gray-500 mb-1">Belum ada percakapan</p>
            </div>

            <!-- Chat list -->
            <div v-for="chat in filteredChats" :key="chat.user.id" class="mb-1 text-gray-600">
              <Link :href="`/chat/${chat.user.id}`"
                class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-100 transition"
                :class="{ 'bg-red-50': chatUser?.id === chat.user.id }">
                <img :src="chat.user.avatar ?? '/profile.png'" class="w-10 h-10 rounded-full object-cover" />
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between gap-2 mb-1">
                    <div class="font-semibold truncate text-black">{{ chat.user.name }}</div>
                    <div class="text-xs text-gray-500 whitespace-nowrap">
                      {{ chat.last_message_at ? formatTime(chat.last_message_at) : '' }}
                    </div>
                  </div>
                  <div class="flex items-center gap-1.5 text-xs text-gray-500">
                    <!-- Status icon hanya tampil jika ada last_message -->
                    <template v-if="chat.last_message">
                      <svg v-if="chat.is_read" class="w-4 h-4 text-blue-500 flex-shrink-0" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 6L9 17l-5-5" />
                        <path d="M9 17l11-11" />
                      </svg>
                      <svg v-else class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M20 6L9 17l-5-5" />
                      </svg>
                    </template>
                    <span class="truncate">{{ chat.last_message ?? "Belum ada pesan" }}</span>
                  </div>
                </div>
              </Link>
            </div>
          </div>
        </div>

        <!-- Chat Panel -->
        <div class="flex flex-col flex-1 bg-white">

          <!-- Header Chat - Only show if chatUser exists -->
          <div v-if="chatUser" class="p-4 border-b border-[#C9C9C9] bg-white text-black flex items-center gap-2 font-semibold">
            <img :src="chatUser?.avatar ?? '/profile.png'" class="w-8 h-8 rounded-full object-cover" />
            <span>{{ chatUser?.name }}</span>
          </div>

          <!-- Empty header if no chat selected -->
          <div v-else class="p-4 border-b border-[#C9C9C9] bg-white text-black flex items-center gap-2 font-semibold">
            <span class="text-gray-400">Pilih atau mulai chat</span>
          </div>

          <!-- Messages -->
          <div ref="chatContainer" class="flex-1 overflow-y-auto p-5 space-y-4 bg-white">
            <!-- Empty state when no chat selected -->
            <div v-if="!chatUser" class="flex flex-col items-center justify-center h-full text-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-24 h-24 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                  d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
              </svg>
              <h3 class="text-lg font-semibold text-gray-700 mb-2">Mulai Percakapan</h3>
              <p class="text-sm text-gray-500 mb-4">Pilih chat dari daftar atau mulai chat baru</p>
              <button @click="showUserModal = true"
                class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg font-medium transition">
                Mulai Chat Baru
              </button>
            </div>

            <!-- Messages list -->
            <template v-else>
              <div v-for="msg in messages" :key="msg.id" class="flex w-full group"
                :class="msg.sender_id === authUser.id ? 'justify-end' : 'justify-start'">

                <!-- Dropdown Button (Left side for received messages) -->
                <div v-if="msg.sender_id !== authUser.id" class="relative flex items-start mt-2 dropdown-container">
                  <button @click.stop="toggleDropdown(msg.id)"
                    class="opacity-0 group-hover:opacity-100 transition-opacity p-1 hover:bg-gray-200 rounded-full mr-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                    </svg>
                  </button>

                  <!-- Dropdown Menu -->
                  <div v-if="activeDropdown === msg.id"
                    class="absolute left-0 top-8 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-10 min-w-[120px]">
                    <button @click="deleteMessage(msg.id)"
                      class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 flex items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                      Delete
                    </button>
                  </div>
                </div>

                <!-- Message Bubble -->
                <div class="px-4 py-2 rounded-2xl text-[15px] leading-relaxed shadow-sm
                  max-w-[90%] sm:max-w-[80%] md:max-w-[70%] lg:max-w-[60%] break-words" :class="msg.sender_id === authUser.id
                    ? 'bg-red-500 text-white'
                    : 'bg-gray-100 text-black'">
                  <div>{{ msg.message }}</div>

                  <!-- Images -->
                  <div v-if="msg.images?.length" class="mt-2 grid grid-cols-2 gap-1">
                    <img v-for="(image, index) in msg.images" :key="index" :src="image"
                      class="w-full h-20 object-cover rounded border border-gray-300 cursor-pointer hover:opacity-90 transition"
                      @click="openImageViewer(msg.images!, index)" />
                  </div>

                  <!-- Timestamp + Status -->
                  <div class="flex items-center justify-end gap-1 mt-1 text-[10px] opacity-70">
                    <div>{{ formatTime(msg.created_at) }}</div>

                    <!-- Status (only sender) -->
                    <template v-if="msg.sender_id === authUser.id">
                      <!-- sent -->
                      <svg v-if="msg.status === 'sent'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>

                      <!-- delivered -->
                      <svg v-if="msg.status === 'delivered'" xmlns="http://www.w3.org/2000/svg"
                        class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M2 12l6 6 10-10 M8 12l6 6 10-10" />
                      </svg>

                      <!-- read -->
                      <svg v-if="msg.status === 'read'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-500"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M2 12l6 6 10-10 M8 12l6 6 10-10" />
                      </svg>
                    </template>
                  </div>
                </div>

                <!-- Dropdown Button (Right side for sent messages) -->
                <div v-if="msg.sender_id === authUser.id" class="relative flex items-start mt-2 dropdown-container">
                  <button @click.stop="toggleDropdown(msg.id)"
                    class="opacity-0 group-hover:opacity-100 transition-opacity p-1 hover:bg-gray-200 rounded-full ml-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                    </svg>
                  </button>

                  <!-- Dropdown Menu -->
                  <div v-if="activeDropdown === msg.id"
                    class="absolute right-0 top-8 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-10 min-w-[120px]">
                    <button @click="deleteMessage(msg.id)"
                      class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 flex items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                      Delete
                    </button>
                  </div>
                </div>
              </div>
            </template>
          </div>

          <!-- Input Box -->
          <div class="border-t border-[#C9C9C9] px-4 py-3 bg-white">
            <div class="flex items-center gap-3">
              <input v-model="newMessage" @keyup.enter="sendMessage"
                :disabled="!chatUser"
                class="flex-1 border border-[#C9C9C9] rounded-full px-4 py-2 focus:outline-none focus:border-purple-500 text-black disabled:bg-gray-100 disabled:cursor-not-allowed"
                :placeholder="chatUser ? 'Ketik pesan...' : 'Pilih chat terlebih dahulu...'" />

              <button @click="sendMessage"
                :disabled="!chatUser"
                class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-full font-medium transition disabled:opacity-50 disabled:cursor-not-allowed">
                Kirim
              </button>
            </div>
          </div>

        </div>
        <!-- End Chat Panel -->

      </div>
    </div>

    <!-- User Selection Modal -->
    <div v-if="showUserModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="showUserModal = false">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[80vh] flex flex-col">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
          <h3 class="text-lg font-semibold text-black">Pilih Pengguna</h3>
          <button @click="showUserModal = false" class="text-gray-400 hover:text-gray-600 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Search in modal -->
        <div class="px-6 py-3 border-b border-gray-200">
          <input v-model="userSearchQuery" type="text" 
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-red-500 text-gray-800"
            placeholder="Cari pengguna..." />
        </div>

        <!-- User List -->
        <div class="flex-1 overflow-y-auto p-4">
          <div v-if="loadingUsers" class="text-center py-8 text-gray-500">
            Memuat pengguna...
          </div>

          <div v-else-if="filteredUsers.length === 0" class="text-center py-8 text-gray-500">
            Tidak ada pengguna ditemukan
          </div>

          <div v-else class="space-y-2">
            <button v-for="user in filteredUsers" :key="user.id"
              @click="startNewChat(user)"
              class="w-full flex items-center gap-3 p-3 rounded-lg hover:bg-gray-100 transition text-left">
              <img :src="user.avatar ?? '/profile.png'" class="w-10 h-10 rounded-full object-cover" />
              <div class="flex-1 min-w-0">
                <div class="font-semibold text-black">{{ user.name }}</div>
                <div v-if="user.email" class="text-sm text-gray-500 truncate">{{ user.email }}</div>
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Image Viewer Modal -->
    <ImageViewerModal :is-open="showImageViewer" :images="imageViewerImages" :current-index="imageViewerCurrentIndex"
      @close="showImageViewer = false" />
  </AppSidebarLayout>
</template>