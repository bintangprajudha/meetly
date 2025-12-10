# Contoh Penggunaan API Repost

## 1. Membuat Repost

### Request
```bash
POST /posts/{post}/repost HTTP/1.1
Content-Type: multipart/form-data
X-CSRF-TOKEN: {csrf_token}

form-data:
  caption: "Great post! Everyone should see this"
  images[0]: (binary image file)
  images[1]: (binary image file)
```

### cURL Example
```bash
curl -X POST http://localhost:8000/posts/5/repost \
  -H "X-CSRF-TOKEN: $(curl -s http://localhost:8000 | grep csrf-token | head -1 | sed 's/.*content="//;s/".*//')" \
  -F "caption=Wow, incredible!" \
  -F "images[0]=@/path/to/image1.jpg" \
  -F "images[1]=@/path/to/image2.png" \
  --cookie "XSRF-TOKEN=..." \
  --cookie "laravel_session=..."
```

### Response
```json
{
  "message": "Repost created successfully",
  "repost": {
    "id": 42,
    "user_id": 1,
    "post_id": 5,
    "caption": "Wow, incredible!",
    "images": [
      "http://localhost:8000/storage/reposts/abc123.jpg",
      "http://localhost:8000/storage/reposts/def456.png"
    ],
    "created_at": "2025-12-10T20:30:00.000000Z",
    "updated_at": "2025-12-10T20:30:00.000000Z"
  },
  "reposts_count": 3
}
```

## 2. Delete Repost

### Request
```bash
DELETE /reposts/{repost} HTTP/1.1
X-CSRF-TOKEN: {csrf_token}
```

### cURL Example
```bash
curl -X DELETE http://localhost:8000/reposts/42 \
  -H "X-CSRF-TOKEN: {token}" \
  --cookie "XSRF-TOKEN=..." \
  --cookie "laravel_session=..."
```

### Response
```json
{
  "message": "Repost deleted successfully",
  "reposts_count": 2
}
```

## 3. Check if User Has Reposted

### Request
```bash
GET /posts/{post}/has-reposted HTTP/1.1
```

### cURL Example
```bash
curl -X GET http://localhost:8000/posts/5/has-reposted \
  --cookie "laravel_session=..."
```

### Response
```json
{
  "reposted": true
}
```

## 4. Get All Reposts for a Post

### Request
```bash
GET /posts/{post}/reposts HTTP/1.1
```

### cURL Example
```bash
curl -X GET http://localhost:8000/posts/5/reposts \
  --cookie "laravel_session=..."
```

### Response
```json
[
  {
    "id": "repost_42",
    "type": "repost",
    "repost_id": 42,
    "user_id": 1,
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "content": "Original post content",
    "images": ["http://localhost:8000/storage/posts/xyz.jpg"],
    "repost_caption": "Wow, incredible!",
    "repost_images": ["http://localhost:8000/storage/reposts/abc123.jpg"],
    "created_at": "2025-12-10T20:30:00.000000Z",
    "original_post": {
      "id": 5,
      "user": {
        "id": 2,
        "name": "Jane Smith",
        "email": "jane@example.com"
      }
    },
    "likes_count": 0,
    "bookmarks_count": 0,
    "reposts_count": 0,
    "replies_count": 0
  }
]
```

## JavaScript/Fetch Examples

### Create Repost with Caption
```javascript
async function createRepost(postId, caption) {
  const formData = new FormData();
  if (caption) {
    formData.append('caption', caption);
  }
  
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  
  const response = await fetch(`/posts/${postId}/repost`, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': csrfToken,
      'X-Requested-With': 'XMLHttpRequest',
    },
    body: formData,
  });
  
  return response.json();
}

// Usage
createRepost(5, 'Great post!').then(data => {
  console.log('Reposted successfully:', data);
});
```

### Create Repost with Images
```javascript
async function createRepostWithImages(postId, caption, imageFiles) {
  const formData = new FormData();
  
  if (caption) {
    formData.append('caption', caption);
  }
  
  // Add images
  imageFiles.forEach((file, index) => {
    formData.append(`images[${index}]`, file);
  });
  
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  
  const response = await fetch(`/posts/${postId}/repost`, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': csrfToken,
      'X-Requested-With': 'XMLHttpRequest',
    },
    body: formData,
  });
  
  return response.json();
}

// Usage
const fileInput = document.querySelector('input[type="file"]');
createRepostWithImages(5, 'Check this out!', fileInput.files).then(data => {
  console.log('Reposted with images:', data);
});
```

### Check Repost Status
```javascript
async function checkRepostStatus(postId) {
  const response = await fetch(`/posts/${postId}/has-reposted`);
  const data = await response.json();
  return data.reposted;
}

// Usage
checkRepostStatus(5).then(reposted => {
  if (reposted) {
    console.log('You have reposted this post');
  } else {
    console.log('You have not reposted this post');
  }
});
```

### Delete Repost
```javascript
async function deleteRepost(repostId) {
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  
  const response = await fetch(`/reposts/${repostId}`, {
    method: 'DELETE',
    headers: {
      'X-CSRF-TOKEN': csrfToken,
      'X-Requested-With': 'XMLHttpRequest',
    },
  });
  
  return response.json();
}

// Usage
deleteRepost(42).then(data => {
  console.log('Repost deleted:', data);
});
```

## Vue.js Integration Example

```vue
<template>
  <button 
    @click="handleRepost"
    :class="['repost-btn', { reposted: isReposted }]"
  >
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
            d="M7 16V4m0 0L3 8m0 0l4 4m10-4v12m0 0l4-4m0 0l-4-4" />
    </svg>
    <span>{{ repostCount }}</span>
  </button>
  
  <!-- Repost Modal -->
  <div v-if="showModal" class="modal">
    <input v-model="caption" placeholder="Add a caption..." maxlength="280" />
    <input v-if="imageInput" type="file" multiple @change="handleImageSelect" />
    <button @click="submitRepost">Repost</button>
    <button @click="showModal = false">Cancel</button>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  post: Object,
});

const showModal = ref(false);
const caption = ref('');
const isReposted = ref(false);
const repostCount = ref(0);
const selectedImages = ref([]);

const handleRepost = async () => {
  // Check status first
  const response = await fetch(`/posts/${props.post.id}/has-reposted`);
  const data = await response.json();
  
  if (data.reposted) {
    // Delete repost
    isReposted.value = false;
  } else {
    // Open modal to create
    showModal.value = true;
  }
};

const handleImageSelect = (event) => {
  selectedImages.value = Array.from(event.target.files);
};

const submitRepost = async () => {
  const formData = new FormData();
  
  if (caption.value) {
    formData.append('caption', caption.value);
  }
  
  selectedImages.value.forEach((file, index) => {
    formData.append(`images[${index}]`, file);
  });
  
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  
  try {
    const response = await fetch(`/posts/${props.post.id}/repost`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: formData,
    });
    
    const data = await response.json();
    isReposted.value = true;
    repostCount.value = data.reposts_count;
    showModal.value = false;
    caption.value = '';
    selectedImages.value = [];
  } catch (error) {
    console.error('Error reposting:', error);
  }
};
</script>

<style scoped>
.repost-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  cursor: pointer;
  transition: colors 0.15s;
}

.repost-btn:hover {
  color: #22c55e;
  background-color: #f0fdf4;
}

.repost-btn.reposted {
  color: #22c55e;
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
}
</style>
```

## Error Handling

### 401 Unauthorized
```json
{
  "message": "Unauthorized"
}
```
User tidak authenticated. Redirect ke login.

### 403 Forbidden
```json
{
  "message": "Unauthorized"
}
```
User mencoba delete repost orang lain.

### 404 Not Found
```json
{
  "message": "Post not found"
}
```
Post atau repost tidak ditemukan.

### 422 Validation Error
```json
{
  "message": "...",
  "errors": {
    "caption": ["The caption field must not exceed 280 characters."],
    "images": ["Maximum 4 images allowed."]
  }
}
```
Validasi gagal pada input.
