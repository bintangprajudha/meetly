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
  profile?: {
    avatar?: string | null;
  };
}

interface SharedPost {
  id: number;
  user_name: string;
  user_avatar?: string | null;
  content: string;
  images?: string[];
  created_at: string;
  likes_count?: number;
  comments_count?: number;
}

interface ChatMessage {
  id: number;
  sender_id: number;
  receiver_id: number;
  message: string;
  images?: string[] | null;
  created_at: string;
  status?: string;
  shared_post?: SharedPost | null;
}

interface Chat {
  user: User;
  last_message: string | null;
  last_message_at: string | null;
  is_read: boolean;
  unread_count?: number;
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
const showDeleteModal = ref(false);
const messageToDelete = ref<number | null>(null);

// Helper functions untuk avatar - konsisten dengan profile
const getUserAvatar = (user: User | null) => {
  if (!user) return null;

  // Cek avatar dari profile dulu, lalu dari user langsung
  const avatar = user.profile?.avatar || user.avatar;
  if (!avatar) return null;

  // Jika sudah full URL, return langsung
  if (avatar.startsWith('http')) {
    return avatar;
  }

  // Jika relatif path, tambahkan /storage/
  return `/storage/${avatar}`;
};

// Computed - dengan sorting berdasarkan waktu terbaru
const filteredChats = computed(() => {
  let filtered = chats.value;

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(chat =>
      chat.user.name.toLowerCase().includes(query) ||
      chat.last_message?.toLowerCase().includes(query)
    );
  }

  return filtered.sort((a, b) => {
    if (!a.last_message_at) return 1;
    if (!b.last_message_at) return -1;
    return new Date(b.last_message_at).getTime() - new Date(a.last_message_at).getTime();
  });
});

const filteredUsers = computed(() => {
  if (!userSearchQuery.value) return allUsers.value;
  const query = userSearchQuery.value.toLowerCase();
  return allUsers.value.filter(user =>
    user.name.toLowerCase().includes(query) ||
    user.email?.toLowerCase().includes(query)
  );
});

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

// Enhanced date formatting
const formatMessageDate = (ts: string) => {
  if (!ts) return '';

  try {
    const date = new Date(ts);
    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);
    const messageDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());

    if (messageDate.getTime() === today.getTime()) {
      return 'Today';
    } else if (messageDate.getTime() === yesterday.getTime()) {
      return 'Yesterday';
    } else {
      return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
      });
    }
  } catch (error) {
    console.error('Error formatting date:', error);
    return '';
  }
};

const formatMessageTime = (ts: string) => {
  if (!ts) return '';

  try {
    const date = new Date(ts);
    return date.toLocaleTimeString('en-US', {
      hour: 'numeric',
      minute: '2-digit',
      hour12: true
    });
  } catch (error) {
    console.error('Error formatting time:', error);
    return '';
  }
};

const formatTime = (ts: string) => {
  if (!ts) return '';

  try {
    const date = new Date(ts);
    const now = new Date();
    const diff = now.getTime() - date.getTime();
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));

    if (days === 0) {
      return date.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
      });
    } else if (days === 1) {
      return 'Yesterday';
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

// Group messages by date
const groupedMessages = computed(() => {
  const groups: { [key: string]: ChatMessage[] } = {};

  messages.value.forEach(msg => {
    const dateKey = formatMessageDate(msg.created_at);
    if (!groups[dateKey]) {
      groups[dateKey] = [];
    }
    groups[dateKey].push(msg);
  });

  return groups;
});

const scrollToBottom = () => {
  nextTick(() => {
    if (chatContainer.value) {
      chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
  });
};

const fetchMessages = async () => {
  if (!chatUser.value?.id) return;

  if (chatUser.value.id === authUser.id) {
    console.error('Cannot fetch messages with yourself');
    return;
  }

  try {
    const res = await axios.get(`/api/messages/${chatUser.value.id}`);
    messages.value = res.data;

    await markMessagesAsRead(chatUser.value.id);

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

const markMessagesAsRead = async (userId: number) => {
  try {
    await axios.post(`/api/messages/${userId}/mark-read`);
    await fetchChats();
  } catch (error) {
    console.error('Failed to mark messages as read:', error);
  }
};

const sendMessage = async () => {
  if (!chatUser.value?.id || !newMessage.value.trim()) return;

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
    chats.value = res.data.filter((chat: Chat) => chat.last_message !== null);
    console.log('Chats loaded:', chats.value.length);
  } catch (e) {
    console.error("Gagal fetch chats:", e);
  } finally {
    loading.value = false;
  }
};

const fetchAllUsers = async () => {
  loadingUsers.value = true;
  try {
    const res = await axios.get('/api/chat/users');
    const users = Array.isArray(res.data) ? res.data : [];
    allUsers.value = users.filter((user: User) => user.id !== authUser.id);
    console.log('Users loaded:', allUsers.value.length, 'users');
  } catch (e: any) {
    console.error("Gagal fetch users:", e);
    console.error("Error detail:", e.response?.data);
  } finally {
    loadingUsers.value = false;
  }
};

const startNewChat = async (user: User) => {
  if (user.id === authUser.id) {
    alert('Tidak bisa chat dengan diri sendiri');
    return;
  }

  showUserModal.value = false;
  userSearchQuery.value = '';

  router.visit(`/chat/${user.id}`);
};

const toggleDropdown = (messageId: number) => {
  activeDropdown.value = activeDropdown.value === messageId ? null : messageId;
};

const openDeleteModal = (messageId: number) => {
  messageToDelete.value = messageId;
  showDeleteModal.value = true;
  activeDropdown.value = null;
};

const closeDeleteModal = () => {
  showDeleteModal.value = false;
  messageToDelete.value = null;
};

const confirmDeleteMessage = async () => {
  if (!messageToDelete.value) return;

  try {
    await axios.delete(`/api/messages/${messageToDelete.value}`);
    messages.value = messages.value.filter(msg => msg.id !== messageToDelete.value);

    await fetchChats();

    closeDeleteModal();
  } catch (e) {
    console.error("Gagal menghapus pesan:", e);
    alert('Gagal menghapus pesan');
    closeDeleteModal();
  }
};

const openImageViewer = (images: string[], index: number) => {
  imageViewerImages.value = images;
  imageViewerCurrentIndex.value = index;
  showImageViewer.value = true;
};

onMounted(() => {
  if (chatUser.value?.id === authUser.id) {
    console.error('Cannot chat with yourself, redirecting...');
    router.visit('/chat');
    return;
  }

  if (chatUser.value?.id) {
    fetchMessages();
  }

  fetchChats();
  fetchAllUsers();

  document.addEventListener('click', (e) => {
    const target = e.target as HTMLElement;
    if (!target.closest('.dropdown-container')) {
      activeDropdown.value = null;
    }
  });

  if (window.Echo && authUser?.id) {
    window.Echo.private(`chat.${authUser.id}`)
      .listen('NewMessage', (e: { message: ChatMessage }) => {
        if (chatUser.value && e.message.sender_id === chatUser.value.id) {
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
        fetchChats();
      });
  }
});

const navigateToPost = (postId: number) => {
  router.visit(`/posts/${postId}`);
};
</script>

<template>
  <AppSidebarLayout>
    <div class="h-screen flex overflow-hidden justify-center bg-white md:bg-gray-50">
      <div class="flex w-full max-w-[1000px] md:border md:border-[#C9C9C9] bg-white min-h-0">

        <!-- Sidebar Chat - Hidden on mobile when chat is open -->
        <div class="w-full md:w-72 border-r border-[#C9C9C9] bg-white h-screen overflow-y-auto"
          :class="{ 'hidden md:block': chatUser }">
          <div class="p-4 space-y-3 sticky top-0 bg-white z-10 border-b border-[#C9C9C9]">
            <div class="flex items-center justify-between">
              <div class="font-semibold text-lg md:text-xl text-black flex items-center gap-2">
                Chat
              </div>

              <button @click="showUserModal = true"
                class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-full transition" title="Start new chat">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 md:w-5 md:h-5" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
              </button>
            </div>

            <!-- Search -->
            <div class="relative">
              <input v-model="searchQuery" type="text"
                class="w-full px-3 py-2 border border-[#C9C9C9] rounded-lg bg-white text-black text-sm focus:outline-none focus:border-red-500"
                placeholder="Search..." />
            </div>
          </div>

          <div class="p-4 pt-0">
            <div v-if="loading" class="text-sm text-gray-500 text-center py-4">Loading...</div>

            <div v-else>
              <!-- Empty state -->
              <div v-if="filteredChats.length === 0" class="flex flex-col items-center justify-center py-8 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 md:w-16 md:h-16 text-gray-300 mb-3" fill="none"
                  viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <p class="text-sm text-gray-500 mb-1">No conversations yet</p>
              </div>

              <!-- Chat list -->
              <div class="space-y-1">
                <div v-for="chat in filteredChats" :key="chat.user.id" class="text-gray-600">
                  <Link :href="`/chat/${chat.user.id}`"
                    class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-100 active:bg-gray-200 transition relative"
                    :class="{ 'bg-red-50': chatUser?.id === chat.user.id }">

                    <div class="relative">
                      <!-- Avatar -->
                      <div v-if="getUserAvatar(chat.user)"
                        class="w-10 h-10 md:w-12 md:h-12 rounded-full overflow-hidden flex-shrink-0">
                        <img :src="getUserAvatar(chat.user)!" :alt="chat.user.name"
                          class="w-full h-full object-cover" />
                      </div>
                      <div v-else
                        class="w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center text-sm font-medium text-white flex-shrink-0"
                        :style="{ backgroundColor: getAvatarColor(chat.user.name) }">
                        {{ getInitials(chat.user.name) }}
                      </div>
                      <span v-if="chat.unread_count && chat.unread_count > 0"
                        class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[18px] text-center">
                        {{ chat.unread_count > 9 ? '9+' : chat.unread_count }}
                      </span>
                    </div>

                    <div class="flex-1 min-w-0">
                      <div class="flex items-center justify-between gap-2 mb-1">
                        <div class="font-semibold truncate text-black text-sm md:text-base">{{ chat.user.name }}</div>
                        <div class="text-xs text-gray-500 whitespace-nowrap">
                          {{ chat.last_message_at ? formatTime(chat.last_message_at) : '' }}
                        </div>
                      </div>
                      <div class="flex items-center gap-1.5 text-xs">
                        <template v-if="chat.last_message && chat.unread_count === 0">
                          <svg v-if="chat.is_read" class="w-3.5 h-3.5 md:w-4 md:h-4 text-blue-500 flex-shrink-0"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 6L9 17l-5-5" />
                            <path d="M9 17l11-11" />
                          </svg>
                          <svg v-else class="w-3 h-3 md:w-3.5 md:h-3.5 text-gray-400 flex-shrink-0" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 6L9 17l-5-5" />
                          </svg>
                        </template>

                        <span class="truncate" :class="{
                          'font-semibold text-gray-900': chat.unread_count && chat.unread_count > 0,
                          'text-gray-500': !chat.unread_count || chat.unread_count === 0
                        }">
                          {{ chat.last_message ?? "No messages yet" }}
                        </span>
                      </div>
                    </div>
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Chat Panel - Full width on mobile -->
        <div class="flex flex-col flex-1 bg-white" :class="{ 'hidden md:flex': !chatUser }">

          <!-- Header Chat -->
          <div v-if="chatUser"
            class="p-3 md:p-4 border-b border-[#C9C9C9] bg-white text-black flex items-center gap-2 font-semibold sticky top-0 z-10">
            <!-- Back button for mobile -->
            <Link :href="'/chat'" class="md:hidden mr-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-700" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </Link>

            <!-- Avatar -->
            <div v-if="getUserAvatar(chatUser)" class="w-8 h-8 md:w-10 md:h-10 rounded-full overflow-hidden">
              <img :src="getUserAvatar(chatUser)!" :alt="chatUser.name" class="w-full h-full object-cover" />
            </div>
            <div v-else
              class="w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center text-xs font-medium text-white"
              :style="{ backgroundColor: getAvatarColor(chatUser.name) }">
              {{ getInitials(chatUser.name) }}
            </div>
            <span class="text-sm md:text-base">{{ chatUser.name }}</span>
          </div>

          <div v-else
            class="p-3 md:p-4 border-b border-[#C9C9C9] bg-white text-black flex items-center gap-2 font-semibold">
            <span class="text-gray-400 text-sm md:text-base">Select or start a chat</span>
          </div>

          <!-- Messages Container -->
          <div ref="chatContainer" class="flex-1 overflow-y-auto p-3 md:p-5 space-y-3 md:space-y-4 bg-white min-h-0">
            <!-- Empty state -->
            <div v-if="!chatUser" class="flex flex-col items-center justify-center h-full text-center px-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 md:w-24 md:h-24 text-gray-300 mb-4" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
              </svg>
              <h3 class="text-base md:text-lg font-semibold text-gray-700 mb-2">Start a Conversation</h3>
              <p class="text-xs md:text-sm text-gray-500 mb-4">Select a chat from the list or start a new chat</p>
              <button @click="showUserModal = true"
                class="bg-red-500 hover:bg-red-600 text-white px-5 py-2 md:px-6 md:py-2.5 rounded-lg font-medium transition text-sm md:text-base">
                Start New Chat
              </button>
            </div>

            <!-- Messages list -->
            <template v-else>
              <div v-for="(msgs, date) in groupedMessages" :key="date" class="space-y-3 md:space-y-4">
                <!-- Date separator -->
                <div class="flex items-center justify-center my-3 md:my-4">
                  <div class="bg-gray-200 text-gray-600 text-xs px-3 py-1 rounded-full font-medium">
                    {{ date }}
                  </div>
                </div>

                <!-- Messages -->
                <div v-for="msg in msgs" :key="msg.id" class="flex w-full group"
                  :class="msg.sender_id === authUser.id ? 'justify-end' : 'justify-start'">

                  <!-- Dropdown Button (Left) - Hidden on mobile -->
                  <div v-if="msg.sender_id !== authUser.id"
                    class="hidden md:flex relative items-start mt-2 dropdown-container flex-shrink-0">
                    <button @click.stop="toggleDropdown(msg.id)"
                      class="opacity-0 group-hover:opacity-100 transition-opacity p-1 hover:bg-gray-200 rounded-full mr-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                      </svg>
                    </button>

                    <div v-if="activeDropdown === msg.id"
                      class="absolute left-0 top-8 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-10 min-w-[120px]">
                      <button @click="openDeleteModal(msg.id)"
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
                  <div class="px-3 py-2 md:px-4 md:py-2 rounded-2xl text-sm md:text-[15px] leading-relaxed shadow-sm
                    max-w-[85%] sm:max-w-[80%] md:max-w-[70%] lg:max-w-[60%] break-words relative group/bubble"
                    :class="msg.sender_id === authUser.id ? 'bg-red-500 text-white' : 'bg-gray-100 text-black'">

                    <!-- Mobile delete button (always visible on mobile) -->
                    <button v-if="msg.sender_id === authUser.id" @click.stop="openDeleteModal(msg.id)"
                      class="md:hidden absolute -top-2 -right-2 bg-white rounded-full p-1.5 shadow-md border border-gray-200 hover:bg-red-50 active:bg-red-100 z-10">
                      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-500" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>

                    <button v-if="msg.sender_id !== authUser.id" @click.stop="openDeleteModal(msg.id)"
                      class="md:hidden absolute -top-2 -left-2 bg-white rounded-full p-1.5 shadow-md border border-gray-200 hover:bg-red-50 active:bg-red-100 z-10">
                      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-500" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>

                    <!-- Shared Post -->
                    <div v-if="msg.shared_post" @click="navigateToPost(msg.shared_post.id)"
                      class="mb-2 border rounded-lg overflow-hidden cursor-pointer hover:opacity-90 transition-opacity"
                      :class="msg.sender_id === authUser.id ? 'border-white/20' : 'border-gray-300'">
                      <div class="p-2 md:p-3" :class="msg.sender_id === authUser.id ? 'bg-white/10' : 'bg-white'">
                        <!-- Post Header -->
                        <div class="flex items-center gap-2 mb-2">
                          <div v-if="msg.shared_post.user_avatar"
                            class="w-5 h-5 md:w-6 md:h-6 rounded-full overflow-hidden flex-shrink-0">
                            <img :src="msg.shared_post.user_avatar" class="w-full h-full object-cover" />
                          </div>
                          <div v-else
                            class="w-5 h-5 md:w-6 md:h-6 rounded-full flex items-center justify-center text-xs font-medium text-white flex-shrink-0"
                            :style="{ backgroundColor: getAvatarColor(msg.shared_post.user_name) }">
                            {{ getInitials(msg.shared_post.user_name) }}
                          </div>
                          <div class="min-w-0">
                            <div class="text-xs font-semibold truncate"
                              :class="msg.sender_id === authUser.id ? 'text-white' : 'text-black'">
                              {{ msg.shared_post.user_name }}
                            </div>
                            <div class="text-[10px]"
                              :class="msg.sender_id === authUser.id ? 'text-white/70' : 'text-gray-500'">
                              {{ formatTime(msg.shared_post.created_at) }}
                            </div>
                          </div>
                        </div>

                        <!-- Post Content -->
                        <div class="text-xs mb-2"
                          :class="msg.sender_id === authUser.id ? 'text-white/90' : 'text-gray-700'">
                          {{ msg.shared_post.content }}
                        </div>

                        <!-- Post Images -->
                        <div v-if="msg.shared_post.images?.length" class="grid grid-cols-2 gap-1 mb-2">
                          <img v-for="(image, index) in msg.shared_post.images" :key="index" :src="image"
                            class="w-full h-12 md:h-16 object-cover rounded" @click.stop />
                        </div>

                        <!-- Post Stats -->
                        <div class="flex items-center gap-3 text-[10px]"
                          :class="msg.sender_id === authUser.id ? 'text-white/70' : 'text-gray-500'">
                          <span v-if="msg.shared_post.likes_count">{{ msg.shared_post.likes_count }} likes</span>
                          <span v-if="msg.shared_post.comments_count">{{ msg.shared_post.comments_count }}
                            comments</span>
                        </div>

                        <!-- Click to view indicator -->
                        <div class="flex items-center gap-1 mt-2 text-[10px]"
                          :class="msg.sender_id === authUser.id ? 'text-white/50' : 'text-gray-400'">
                          <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                          </svg>
                          <span>Click to view post</span>
                        </div>
                      </div>
                    </div>

                    <!-- Message Text -->
                    <div v-if="msg.message" class="break-words whitespace-pre-wrap">{{ msg.message }}</div>

                    <!-- Images -->
                    <div v-if="msg.images?.length" class="mt-2 grid grid-cols-2 gap-1">
                      <img v-for="(image, index) in msg.images" :key="index" :src="image"
                        class="w-full h-16 md:h-20 object-cover rounded border border-gray-300 cursor-pointer hover:opacity-90 transition"
                        @click="openImageViewer(msg.images!, index)" />
                    </div>

                    <!-- Timestamp + Status -->
                    <div class="flex items-center justify-end gap-1 mt-1 text-[10px] opacity-70">
                      <div>{{ formatMessageTime(msg.created_at) }}</div>

                      <template v-if="msg.sender_id === authUser.id">
                        <svg v-if="msg.status === 'sent'" xmlns="http://www.w3.org/2000/svg"
                          class="w-4 h-4 md:w-5 md:h-5 text-gray-400" fill="none" viewBox="0 0 24 24"
                          stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>

                        <svg v-if="msg.status === 'delivered'" xmlns="http://www.w3.org/2000/svg"
                          class="w-4 h-4 md:w-5 md:h-5 text-gray-500" fill="none" viewBox="0 0 24 24"
                          stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 12l6 6 10-10" />
                        </svg>

                        <svg v-if="msg.status === 'read'" xmlns="http://www.w3.org/2000/svg"
                          class="w-4 h-4 md:w-5 md:h-5 text-blue-500" fill="none" viewBox="0 0 24 24"
                          stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2 12l6 6 10-10 M8 12l6 6 10-10" />
                        </svg>
                      </template>
                    </div>
                  </div>

                  <!-- Dropdown Button (Right) - Hidden on mobile -->
                  <div v-if="msg.sender_id === authUser.id"
                    class="hidden md:flex relative items-start mt-2 dropdown-container flex-shrink-0">
                    <button @click.stop="toggleDropdown(msg.id)"
                      class="opacity-0 group-hover:opacity-100 transition-opacity p-1 hover:bg-gray-200 rounded-full ml-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                      </svg>
                    </button>

                    <div v-if="activeDropdown === msg.id"
                      class="absolute right-0 top-8 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-10 min-w-[120px]">
                      <button @click="openDeleteModal(msg.id)"
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
              </div>

              <div class="h-4"></div>
            </template>
          </div>

          <!-- Input Box -->
          <div class="border-t border-[#C9C9C9] px-3 py-2 md:px-4 md:py-3 bg-white">
            <div class="flex items-center gap-2 md:gap-3">
              <input v-model="newMessage" @keyup.enter="sendMessage" :disabled="!chatUser"
                class="flex-1 border border-[#C9C9C9] rounded-full px-3 py-2 md:px-4 text-sm md:text-base focus:outline-none focus:border-red-500 text-black disabled:bg-gray-100 disabled:cursor-not-allowed"
                :placeholder="chatUser ? 'Type a message...' : 'Select a chat first...'" />

              <button @click="sendMessage" :disabled="!chatUser"
                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 md:px-6 rounded-full font-medium transition disabled:opacity-50 disabled:cursor-not-allowed text-sm md:text-base">
                Send
              </button>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- User Selection Modal -->
    <div v-if="showUserModal"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
      @click.self="showUserModal = false">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[85vh] md:max-h-[80vh] flex flex-col">
        <div class="px-4 py-3 md:px-6 md:py-4 border-b border-gray-200 flex items-center justify-between">
          <h3 class="text-base md:text-lg font-semibold text-black">Select User</h3>
          <button @click="showUserModal = false" class="text-gray-400 hover:text-gray-600 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 md:w-6 md:h-6" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="px-4 py-2 md:px-6 md:py-3 border-b border-gray-200">
          <input v-model="userSearchQuery" type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-red-500 text-gray-800"
            placeholder="Search users..." />
        </div>

        <div class="flex-1 overflow-y-auto p-3 md:p-4 min-h-0">
          <div v-if="loadingUsers" class="text-center py-8 text-gray-500 text-sm">
            Loading users...
          </div>

          <div v-else-if="filteredUsers.length === 0" class="text-center py-8 text-gray-500 text-sm">
            No users found
          </div>

          <div v-else class="space-y-2">
            <button v-for="user in filteredUsers" :key="user.id" @click="startNewChat(user)"
              class="w-full flex items-center gap-3 p-2 md:p-3 rounded-lg hover:bg-gray-100 active:bg-gray-200 transition text-left">
              <div v-if="getUserAvatar(user)"
                class="w-10 h-10 md:w-12 md:h-12 rounded-full overflow-hidden flex-shrink-0">
                <img :src="getUserAvatar(user)!" :alt="user.name" class="w-full h-full object-cover" />
              </div>
              <div v-else
                class="w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center text-sm font-medium text-white flex-shrink-0"
                :style="{ backgroundColor: getAvatarColor(user.name) }">
                {{ getInitials(user.name) }}
              </div>
              <div class="flex-1 min-w-0">
                <div class="font-semibold text-black text-sm md:text-base truncate">{{ user.name }}</div>
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Image Viewer Modal -->
    <ImageViewerModal :is-open="showImageViewer" :images="imageViewerImages" :current-index="imageViewerCurrentIndex"
      @close="showImageViewer = false" />

    <!-- Delete Confirmation Modal -->
    <Teleport to="body">
      <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0"
        enter-to-class="opacity-100" leave-active-class="transition-opacity duration-200" leave-from-class="opacity-100"
        leave-to-class="opacity-0">
        <div v-if="showDeleteModal"
          class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
          @click.self="closeDeleteModal">
          <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition-all duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showDeleteModal"
              class="bg-white rounded-xl md:rounded-2xl shadow-2xl max-w-sm md:max-w-md w-full overflow-hidden"
              @click.stop>
              <div class="bg-gradient-to-r from-red-500 to-red-600 p-4 md:p-6">
                <div class="flex items-center justify-center mb-3 md:mb-4">
                  <div class="bg-white/20 backdrop-blur-sm rounded-full p-2 md:p-3">
                    <svg class="w-6 h-6 md:w-8 md:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </div>
                </div>
                <h3 class="text-xl md:text-2xl font-bold text-white text-center">
                  Delete Message?
                </h3>
              </div>

              <div class="p-4 md:p-6">
                <p class="text-sm md:text-base text-gray-600 text-center mb-4 md:mb-6">
                  This action cannot be undone. This message will be permanently deleted from the conversation.
                </p>

                <div class="flex flex-col sm:flex-row gap-2 md:gap-3">
                  <button @click="closeDeleteModal"
                    class="flex-1 px-4 py-2.5 md:py-3 bg-gray-100 hover:bg-gray-200 active:bg-gray-300 text-gray-700 font-medium rounded-lg md:rounded-xl transition-colors duration-200 text-sm md:text-base">
                    Cancel
                  </button>
                  <button @click="confirmDeleteMessage"
                    class="flex-1 px-4 py-2.5 md:py-3 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 active:from-red-700 active:to-red-800 text-white font-medium rounded-lg md:rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl text-sm md:text-base">
                    Delete
                  </button>
                </div>
              </div>
            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>
  </AppSidebarLayout>
</template>