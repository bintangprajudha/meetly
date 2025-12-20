<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

interface User {
  id: number;
  name: string;
  email: string;
  username?: string;
  bio?: string;
  avatar?: string | null;
  banner?: string | null;
  location?: string;
  website?: string;
}

const props = defineProps<{
  user: User;
}>();

const showEditModal = ref(false);
const avatarPreview = ref<string | null>(
  props.user.avatar ? `/storage/${props.user.avatar}` : null
);
const bannerPreview = ref<string | null>(
  props.user.banner ? `/storage/${props.user.banner}` : null
);

const avatarFile = ref<File | null>(null);
const bannerFile = ref<File | null>(null);

// Track if user wants to remove banner/avatar
const shouldRemoveBanner = ref(false);
const shouldRemoveAvatar = ref(false);

const form = useForm({
  name: props.user.name,
  username: props.user.username ?? '',
  bio: props.user.bio ?? '',
  location: props.user.location ?? '',
  website: props.user.website ?? '',
  avatar: null as File | null,
  banner: null as File | null,
  remove_banner: false,
  remove_avatar: false,
});

const openEditModal = () => {
  showEditModal.value = true;
  form.name = props.user.name;
  form.username = props.user.username ?? '';
  form.bio = props.user.bio ?? '';
  form.location = props.user.location ?? '';
  form.website = props.user.website ?? '';
  avatarPreview.value = props.user.avatar ? `/storage/${props.user.avatar}` : null;
  bannerPreview.value = props.user.banner ? `/storage/${props.user.banner}` : null;
  avatarFile.value = null;
  bannerFile.value = null;
  form.avatar = null;
  form.banner = null;
  form.remove_banner = false;
  form.remove_avatar = false;
  shouldRemoveBanner.value = false;
  shouldRemoveAvatar.value = false;
};

const closeEditModal = () => {
  showEditModal.value = false;
  form.reset();
  form.clearErrors();
  avatarPreview.value = props.user.avatar ? `/storage/${props.user.avatar}` : null;
  bannerPreview.value = props.user.banner ? `/storage/${props.user.banner}` : null;
  avatarFile.value = null;
  bannerFile.value = null;
  shouldRemoveBanner.value = false;
  shouldRemoveAvatar.value = false;
};

const handleAvatarChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];
  
  if (file) {
    if (!file.type.startsWith('image/')) {
      alert('Please select an image file');
      return;
    }
    
    if (file.size > 10 * 1024 * 1024) {
      alert('Image size must be less than 10MB');
      return;
    }
    
    avatarFile.value = file;
    form.avatar = file;
    shouldRemoveAvatar.value = false;
    form.remove_avatar = false;
    
    const reader = new FileReader();
    reader.onload = (e) => {
      avatarPreview.value = e.target?.result as string;
    };
    reader.readAsDataURL(file);
  }
};

const removeAvatar = () => {
  avatarPreview.value = null;
  avatarFile.value = null;
  form.avatar = null;
  shouldRemoveAvatar.value = true;
  form.remove_avatar = true;

  const fileInput = document.getElementById('avatar-input') as HTMLInputElement;
  if (fileInput) {
    fileInput.value = '';
  }
};

const handleBannerChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];
  
  if (file) {
    if (!file.type.startsWith('image/')) {
      alert('Please select an image file');
      return;
    }
    
    if (file.size > 10 * 1024 * 1024) {
      alert('Image size must be less than 10MB');
      return;
    }
    
    bannerFile.value = file;
    form.banner = file;
    shouldRemoveBanner.value = false;
    form.remove_banner = false;
    
    const reader = new FileReader();
    reader.onload = (e) => {
      bannerPreview.value = e.target?.result as string;
    };
    reader.readAsDataURL(file);
  }
};

const removeBanner = () => {
  bannerPreview.value = null;
  bannerFile.value = null;
  form.banner = null;
  shouldRemoveBanner.value = true;
  form.remove_banner = true;
  
  const fileInput = document.getElementById('banner-input') as HTMLInputElement;
  if (fileInput) {
    fileInput.value = '';
  }
};

const submitForm = () => {
  console.log('Form data before submit:', {
    avatar: form.avatar instanceof File,
    banner: form.banner instanceof File,
    remove_banner: form.remove_banner,
    remove_avatar: form.remove_avatar,
    name: form.name,
    username: form.username,
  });

  form.transform((data) => {
    const formData = new FormData();
    
    // Add text fields
    formData.append('name', data.name);
    if (data.username) formData.append('username', data.username);
    if (data.bio) formData.append('bio', data.bio);
    if (data.location) formData.append('location', data.location);
    if (data.website) formData.append('website', data.website);
    
    // Add files
    if (data.avatar instanceof File) {
      formData.append('avatar', data.avatar);
    }
    if (data.banner instanceof File) {
      formData.append('banner', data.banner);
    }
    
    // Add remove flags
    if (data.remove_banner) {
      formData.append('remove_banner', '1');
    }
    if (data.remove_avatar) {
      formData.append('remove_avatar', '1');
    }
    
    return formData;
  }).post('/profile/update', {
    preserveScroll: true,
    onSuccess: () => {
      closeEditModal();
      window.location.reload();
    },
    onError: (errors) => {
      console.error('Upload errors:', errors);
    },
  });
};

// Helper functions
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

  const hash = name.split('').reduce((acc, char) => {
    return acc + char.charCodeAt(0);
  }, 0);

  return colors[hash % colors.length];
};
</script>

<template>
  <div>
    <!-- Edit Profile Button -->
    <button
      @click="openEditModal"
      class="rounded-full border border-gray-300 px-4 py-[7px] text-[15px] font-semibold text-gray-900 transition hover:bg-gray-100"
    >
      Edit profile
    </button>

    <!-- Edit Profile Modal -->
    <Teleport to="body">
      <div
        v-if="showEditModal"
        class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur bg-opacity-50 p-4"
        @click.self="closeEditModal"
      >
        <div class="w-full max-w-2xl max-h-[90vh] overflow-y-auto bg-white rounded-2xl shadow-xl">
          <!-- Modal Header -->
          <div class="sticky top-0 z-10 flex items-center justify-between border-b border-gray-200 bg-white px-6 py-4">
            <h2 class="text-xl font-bold text-gray-900">Edit Profile</h2>
            <button
              @click="closeEditModal"
              class="rounded-full p-2 text-gray-500 transition hover:bg-gray-100"
            >
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Modal Body -->
          <form @submit.prevent="submitForm" class="p-6 space-y-6">
            <!-- Banner Section -->
            <div class="space-y-3">
              <label class="block text-sm font-semibold text-gray-900">Cover Photo</label>
              <div class="relative">
                <!-- Banner Preview -->
                <div class="relative h-48 w-full rounded-lg overflow-hidden bg-gradient-to-br from-slate-200 via-slate-300 to-slate-200">
                  <img
                    v-if="bannerPreview"
                    :src="bannerPreview"
                    alt="Banner"
                    class="h-full w-full object-cover"
                  />
                  
                  <!-- Upload/Remove Buttons Overlay -->
                  <div class="absolute inset-0 flex items-center justify-center gap-3">
                    <!-- Upload Button -->
                    <input
                      id="banner-input"
                      type="file"
                      accept="image/*"
                      @change="handleBannerChange"
                      class="hidden"
                    />
                    <label
                      for="banner-input"
                      class="cursor-pointer rounded-full bg-black/60 p-3 text-white backdrop-blur-sm transition hover:bg-black/80"
                    >
                      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                    </label>
                    
                    <!-- Remove Button -->
                    <button
                      v-if="bannerPreview"
                      @click="removeBanner"
                      type="button"
                      class="rounded-full bg-black/60 p-3 text-white backdrop-blur-sm transition hover:bg-black/80"
                    >
                      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
              <div v-if="form.errors.banner" class="text-sm text-red-600">
                {{ form.errors.banner }}
              </div>
            </div>

            <!-- Avatar Section -->
            <div class="space-y-3">
              <label class="block text-sm font-semibold text-gray-900">Profile Picture</label>
              <div class="flex items-center gap-4">
                <!-- Avatar Preview -->
                <div class="relative">
                  <!-- Show avatar image if exists -->
                  <img
                    v-if="avatarPreview"
                    :src="avatarPreview"
                    alt="Profile"
                    class="h-24 w-24 rounded-full object-cover border-2 border-gray-200"
                  />
                  <!-- Show initials if no avatar -->
                  <div
                    v-else
                    class="h-24 w-24 rounded-full border-2 border-gray-200 flex items-center justify-center text-2xl font-bold text-white"
                    :style="{ backgroundColor: getAvatarColor(user.name) }"
                  >
                    {{ getInitials(user.name) }}
                  </div>
                  
                  <button
                    v-if="avatarPreview"
                    @click="removeAvatar"
                    type="button"
                    class="absolute -top-1 -right-1 rounded-full bg-red-500 p-1.5 text-white shadow-md hover:bg-red-600 transition"
                  >
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>

                <!-- Upload Button -->
                <div class="flex-1">
                  <input
                    id="avatar-input"
                    type="file"
                    accept="image/*"
                    @change="handleAvatarChange"
                    class="hidden"
                  />
                  <label
                    for="avatar-input"
                    class="inline-flex cursor-pointer items-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-900 transition hover:bg-gray-50"
                  >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Choose Photo
                  </label>
                </div>
              </div>
              <div v-if="form.errors.avatar" class="text-sm text-red-600">
                {{ form.errors.avatar }}
              </div>
            </div>

            <!-- Name Input -->
            <div class="space-y-2">
              <label for="name" class="block text-sm font-semibold text-gray-900">
                Name <span class="text-red-500">*</span>
              </label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                required
                maxlength="50"
                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                placeholder="Your name"
              />
              <div class="flex justify-between text-xs">
                <span v-if="form.errors.name" class="text-red-600">{{ form.errors.name }}</span>
                <span class="text-gray-500 ml-auto">{{ form.name.length }}/50</span>
              </div>
            </div>

            <!-- Username Input -->
            <div class="space-y-2">
              <label for="username" class="block text-sm font-semibold text-gray-900">Username</label>
              <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">@</span>
                <input
                  id="username"
                  v-model="form.username"
                  type="text"
                  maxlength="30"
                  class="w-full rounded-lg border border-gray-300 pl-8 pr-4 py-2.5 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                  placeholder="username"
                />
              </div>
              <div class="flex justify-between text-xs">
                <span v-if="form.errors.username" class="text-red-600">{{ form.errors.username }}</span>
                <span class="text-gray-500 ml-auto">{{ (form.username?.length || 0) }}/30</span>
              </div>
            </div>

            <!-- Bio Input -->
            <div class="space-y-2">
              <label for="bio" class="block text-sm font-semibold text-gray-900">Bio</label>
              <textarea
                id="bio"
                v-model="form.bio"
                rows="4"
                maxlength="160"
                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 resize-none"
                placeholder="Write a short bio about yourself..."
              ></textarea>
              <div class="flex justify-between text-xs">
                <span v-if="form.errors.bio" class="text-red-600">{{ form.errors.bio }}</span>
                <span class="text-gray-500 ml-auto">{{ form.bio.length }}/160</span>
              </div>
            </div>

            <!-- Location Input -->
            <div class="space-y-2">
              <label for="location" class="block text-sm font-semibold text-gray-900">Location</label>
              <div class="relative">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <input
                  id="location"
                  v-model="form.location"
                  type="text"
                  maxlength="50"
                  class="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2.5 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                  placeholder="City, Country"
                />
              </div>
              <div v-if="form.errors.location" class="text-sm text-red-600">
                {{ form.errors.location }}
              </div>
            </div>

            <!-- Website Input -->
            <div class="space-y-2">
              <label for="website" class="block text-sm font-semibold text-gray-900">Website</label>
              <div class="relative">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
                <input
                  id="website"
                  v-model="form.website"
                  type="url"
                  maxlength="100"
                  class="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2.5 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                  placeholder="https://yourwebsite.com"
                />
              </div>
              <div v-if="form.errors.website" class="text-sm text-red-600">
                {{ form.errors.website }}
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
              <button
                type="button"
                @click="closeEditModal"
                :disabled="form.processing"
                class="rounded-full border border-gray-300 px-6 py-2.5 text-sm font-semibold text-gray-900 transition hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="form.processing"
                class="rounded-full bg-black px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
              >
                <svg v-if="form.processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ form.processing ? 'Saving...' : 'Save Changes' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>