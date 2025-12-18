<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

interface User {
  id: number;
  name: string;
  email: string;
  bio?: string;
  avatar?: string | null;
  location?: string;
  website?: string;
}

const props = defineProps<{
  user: User;
}>();

const showEditModal = ref(false);
const avatarPreview = ref<string | null>(props.user.avatar ?? null);
const avatarFile = ref<File | null>(null);

const form = useForm({
  name: props.user.name,
  bio: props.user.bio ?? '',
  location: props.user.location ?? '',
  website: props.user.website ?? '',
  avatar: null as File | null,
});

const openEditModal = () => {
  showEditModal.value = true;
  // Reset form with current user data
  form.name = props.user.name;
  form.bio = props.user.bio ?? '';
  form.location = props.user.location ?? '';
  form.website = props.user.website ?? '';
  avatarPreview.value = props.user.avatar ?? null;
  avatarFile.value = null;
};

const closeEditModal = () => {
  showEditModal.value = false;
  form.reset();
  form.clearErrors();
  avatarPreview.value = props.user.avatar ?? null;
  avatarFile.value = null;
};

const handleAvatarChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];
  
  if (file) {
    // Validate file type
    if (!file.type.startsWith('image/')) {
      alert('Please select an image file');
      return;
    }
    
    // Validate file size (max 2MB)
    if (file.size > 2 * 1024 * 1024) {
      alert('Image size must be less than 2MB');
      return;
    }
    
    avatarFile.value = file;
    form.avatar = file;
    
    // Create preview
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
  
  // Reset file input
  const fileInput = document.getElementById('avatar-input') as HTMLInputElement;
  if (fileInput) {
    fileInput.value = '';
  }
};

const submitForm = () => {
  form.post('/profile/update', {
    preserveScroll: true,
    onSuccess: () => {
      closeEditModal();
    },
  });
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
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
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
            <!-- Avatar Section -->
            <div class="space-y-3">
              <label class="block text-sm font-semibold text-gray-900">Profile Picture</label>
              <div class="flex items-center gap-4">
                <!-- Avatar Preview -->
                <div class="relative">
                  <img
                    :src="avatarPreview ?? '/profile.png'"
                    alt="Profile"
                    class="h-24 w-24 rounded-full object-cover border-2 border-gray-200"
                  />
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
                  <p class="mt-2 text-xs text-gray-500">JPG, PNG or GIF. Max 2MB.</p>
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
                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-500 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
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
                  class="w-full rounded-lg border border-gray-300 pl-8 pr-4 py-2.5 text-gray-900 placeholder-gray-500 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
                  placeholder="username"
                />
              </div>
              <div class="flex justify-between text-xs">
                <span v-if="form.errors.username" class="text-red-600">{{ form.errors.username }}</span>
                <span class="text-gray-500 ml-auto">{{ form.username.length }}/30</span>
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
                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-500 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500 resize-none"
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
                  class="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2.5 text-gray-900 placeholder-gray-500 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
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
                  class="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2.5 text-gray-900 placeholder-gray-500 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
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
                class="rounded-full bg-red-500 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-red-600 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
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