# Meetly API Documentation

Dokumentasi lengkap untuk semua endpoint API yang tersedia di aplikasi Meetly. 

## Base URL

```
http://localhost:8000
```

## Authentication

Meetly menggunakan session-based authentication dengan Laravel Fortify.  Semua request yang memerlukan autentikasi harus menyertakan CSRF token dan cookie session yang valid.

## Authentication Endpoints

### 1. Show Login Form

```http
GET /login
```

**Middleware:** guest

**Response:** Halaman login (Inertia render)

---

### 2. Show Register Form

```http
GET /register
```

**Middleware:** guest

**Response:** Halaman register (Inertia render)

---

### 3. Login

```http
POST /login
```

**Middleware:** guest

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123",
  "remember": true
}
```

**Validation Rules:**
- `email`: required, string, email
- `password`: required, string
- `remember`: optional, boolean

**Success Response (200):**
```json
{
  "message": "Login successful",
  "user": {
    "id":  1,
    "name": "John Doe",
    "email": "user@example.com",
    "email_verified_at": "2025-12-21T10:00:00.000000Z",
    "created_at": "2025-12-01T10:00:00.000000Z",
    "updated_at": "2025-12-21T10:00:00.000000Z"
  }
}
```

**Error Response (401):**
```json
{
  "message": "Invalid credentials",
  "errors": {
    "email": ["These credentials do not match our records."]
  }
}
```

---

### 4. Register

```http
POST /register
```

**Middleware:** guest

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "user@example.com",
  "password":  "password123",
  "password_confirmation": "password123"
}
```

**Validation Rules:**
- `name`: required, string, max:255
- `email`: required, string, email, max:255, unique:users
- `password`: required, string, min:8, confirmed

**Success Response (201):**
```json
{
  "message": "Registration successful",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "created_at": "2025-12-21T10:00:00.000000Z",
    "updated_at": "2025-12-21T10:00:00.000000Z"
  }
}
```

**Error Response (422):**
```json
{
  "message": "The given data was invalid",
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password confirmation does not match."]
  }
}
```

---

### 5. Logout

```http
POST /logout
```

**Middleware:** auth

**Success Response (200):**
```json
{
  "message": "Logged out successfully"
}
```

---

## Post Endpoints

### 1. Get Dashboard (All Posts)

```http
GET /dashboard
```

**Middleware:** auth

**Response (200):**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com"
  },
  "posts":  [
    {
      "id": 1,
      "user_id": 2,
      "content": "Hello world!",
      "image_url": null,
      "media":  null,
      "likes_count": 5,
      "replies_count": 3,
      "created_at":  "2025-12-21T10:00:00.000000Z",
      "updated_at": "2025-12-21T10:00:00.000000Z",
      "user": {
        "id": 2,
        "name": "Jane Doe",
        "email": "jane@example.com"
      },
      "liked":  false,
      "bookmarked": true,
      "comments": []
    }
  ]
}
```

---

### 2. Create Post

```http
POST /posts
```

**Middleware:** auth

**Request Body (multipart/form-data):**
```json
{
  "content": "This is my new post! ",
  "images": ["file1.jpg", "file2.png"],
  "videos": ["video1.mp4"]
}
```

**Validation Rules:**
- `content`: required, string, max:5000
- `images`: optional, array
- `images.*`: image, max:10240 (10MB)
- `videos`: optional, array
- `videos.*`: mimetypes: video/mp4,video/mpeg, max:51200 (50MB)

**Success Response (201):**
```json
{
  "message":  "Post created successfully",
  "post": {
    "id":  10,
    "user_id":  1,
    "content":  "This is my new post!",
    "image_url": null,
    "media": [
      {
        "type": "image",
        "url": "/storage/posts/image1.jpg"
      },
      {
        "type": "video",
        "url": "/storage/posts/video1.mp4"
      }
    ],
    "likes_count": 0,
    "replies_count": 0,
    "created_at": "2025-12-21T10:00:00.000000Z",
    "updated_at": "2025-12-21T10:00:00.000000Z"
  }
}
```

**Error Response (422):**
```json
{
  "message": "The given data was invalid",
  "errors": {
    "content": ["The content field is required."],
    "images. 0": ["The file must be an image."]
  }
}
```

---

### 3. Get Single Post

```http
GET /posts/{post}
```

**Middleware:** auth

**URL Parameters:**
- `post` (integer, required): Post ID

**Success Response (200):**
```json
{
  "post": {
    "id": 1,
    "user_id": 2,
    "content": "Hello world!",
    "image_url": "/storage/posts/image. jpg",
    "media": null,
    "likes_count":  5,
    "replies_count":  3,
    "created_at": "2025-12-21T10:00:00.000000Z",
    "updated_at": "2025-12-21T10:00:00.000000Z",
    "user": {
      "id":  2,
      "name":  "Jane Doe",
      "email": "jane@example.com"
    },
    "comments": [
      {
        "id": 1,
        "post_id": 1,
        "user_id": 3,
        "content": "Nice post!",
        "created_at": "2025-12-21T10:05:00.000000Z",
        "user": {
          "id": 3,
          "name": "Bob Smith"
        }
      }
    ],
    "liked": false,
    "bookmarked": true
  }
}
```

**Error Response (404):**
```json
{
  "message": "Post not found"
}
```

---

### 4. Delete Post

```http
DELETE /posts/{post}
```

**Middleware:** auth

**Authorization:** User must own the post

**URL Parameters:**
- `post` (integer, required): Post ID

**Success Response (200):**
```json
{
  "message":  "Post deleted successfully"
}
```

**Error Response (403):**
```json
{
  "message": "Unauthorized to delete this post"
}
```

**Error Response (404):**
```json
{
  "message":  "Post not found"
}
```

---

### 5. Toggle Like on Post

```http
POST /posts/{post}/like
```

**Middleware:** auth

**URL Parameters:**
- `post` (integer, required): Post ID

**Success Response (200):**
```json
{
  "message": "Post liked",
  "liked": true,
  "likes_count": 6
}
```

Or if unliking: 
```json
{
  "message": "Post unliked",
  "liked": false,
  "likes_count": 5
}
```

---

### 6. Toggle Bookmark on Post

```http
POST /posts/{post}/bookmark
```

**Middleware:** auth

**URL Parameters:**
- `post` (integer, required): Post ID

**Success Response (200):**
```json
{
  "message":  "Post bookmarked",
  "bookmarked": true
}
```

Or if unbookmarking:
```json
{
  "message": "Bookmark removed",
  "bookmarked": false
}
```

---

## Comment Endpoints

### 1. Create Comment

```http
POST /posts/{post}/comments
```

**Middleware:** auth

**URL Parameters:**
- `post` (integer, required): Post ID

**Request Body:**
```json
{
  "content": "Great post!  Thanks for sharing."
}
```

**Validation Rules:**
- `content`: required, string, max:1000

**Success Response (201):**
```json
{
  "message": "Comment created successfully",
  "comment": {
    "id": 15,
    "post_id": 1,
    "user_id":  1,
    "content": "Great post! Thanks for sharing.",
    "created_at": "2025-12-21T10:00:00.000000Z",
    "updated_at": "2025-12-21T10:00:00.000000Z",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com"
    }
  }
}
```

**Error Response (422):**
```json
{
  "message":  "The given data was invalid",
  "errors": {
    "content": ["The content field is required."]
  }
}
```

---

### 2. Get Latest Comments

```http
GET /posts/{post}/comments/latest
```

**Middleware:** auth

**URL Parameters:**
- `post` (integer, required): Post ID

**Success Response (200):**
```json
{
  "comments": [
    {
      "id": 1,
      "post_id": 1,
      "user_id": 3,
      "content": "Nice post!",
      "created_at": "2025-12-21T10:05:00.000000Z",
      "updated_at": "2025-12-21T10:05:00.000000Z",
      "user": {
        "id":  3,
        "name":  "Bob Smith",
        "email": "bob@example.com"
      }
    },
    {
      "id":  2,
      "post_id": 1,
      "user_id": 4,
      "content": "Awesome! ",
      "created_at":  "2025-12-21T10:10:00.000000Z",
      "updated_at": "2025-12-21T10:10:00.000000Z",
      "user":  {
        "id": 4,
        "name": "Alice Johnson",
        "email": "alice@example.com"
      }
    }
  ]
}
```

---

## Follow Endpoints

### 1. Follow User

```http
POST /users/{user}/follow
```

**Middleware:** auth

**URL Parameters:**
- `user` (integer, required): User ID to follow

**Success Response (201):**
```json
{
  "message": "User followed successfully",
  "following": true,
  "followers_count": 25
}
```

**Error Response (400):**
```json
{
  "message": "You cannot follow yourself"
}
```

**Error Response (409):**
```json
{
  "message": "Already following this user"
}
```

---

### 2. Unfollow User

```http
DELETE /users/{user}/follow
```

**Middleware:** auth

**URL Parameters:**
- `user` (integer, required): User ID to unfollow

**Success Response (200):**
```json
{
  "message": "User unfollowed successfully",
  "following":  false,
  "followers_count": 24
}
```

**Error Response (404):**
```json
{
  "message": "You are not following this user"
}
```

---

## User Profile Endpoints

### 1. Get User Profile

```http
GET /@{username}
```

**Middleware:** auth

**URL Parameters:**
- `user` (integer, required): User ID

**Success Response (200):**
```json
{
  "user": {
    "id": 2,
    "name": "Jane Doe",
    "email":  "jane@example.com",
    "bio": "Software developer and tech enthusiast",
    "avatar_url": "/storage/avatars/jane.jpg",
    "created_at": "2025-11-01T10:00:00.000000Z",
    "followers_count": 150,
    "following_count":  75,
    "posts_count": 42,
    "is_following": true,
    "is_followed_by": false
  },
  "posts": [
    {
      "id": 5,
      "user_id":  2,
      "content":  "My latest project.. .",
      "likes_count": 10,
      "replies_count": 3,
      "created_at":  "2025-12-20T15:30:00.000000Z"
    }
  ]
}
```

---

### 2. Update Profile

```http
POST /profile/update
```

**Middleware:** auth

**Request Body:**
```json
{
  "name": "John Updated",
  "email": "newemail@example.com",
  "bio": "Updated bio",
  "avatar":  "base64_image_string"
}
```

**Validation Rules:**
- `name`: optional, string, max:255
- `email`: optional, email, unique:users,email,{user_id}
- `bio`: optional, string, max:500
- `avatar`: optional, image, max:2048

**Success Response (200):**
```json
{
  "message": "Profile updated successfully",
  "user": {
    "id": 1,
    "name": "John Updated",
    "email": "newemail@example.com",
    "bio": "Updated bio",
    "avatar_url":  "/storage/avatars/new_avatar.jpg",
    "updated_at": "2025-12-21T10:00:00.000000Z"
  }
}
```

---

## Repost Endpoints

### 1. Create Repost

```http
POST /posts/{post}/repost
```

**Middleware:** auth

**Request Body:**
```json
{
  "post_id": 5,
  "caption": "This is amazing!  Check it out.",
  "images": ["file1.jpg"]
}
```

**Validation Rules:**
- `post_id`: required, exists: posts,id
- `caption`: optional, string, max:500
- `images`: optional, array
- `images.*`: image, max:10240

**Success Response (201):**
```json
{
  "message": "Post reposted successfully",
  "repost": {
    "id":  20,
    "user_id": 1,
    "post_id": 5,
    "caption": "This is amazing! Check it out.",
    "images": ["/storage/reposts/image1.jpg"],
    "created_at": "2025-12-21T10:00:00.000000Z",
    "original_post": {
      "id": 5,
      "user_id": 3,
      "content": "Original content.. .",
      "user": {
        "id": 3,
        "name": "Bob Smith"
      }
    }
  }
}
```

---

### 2. Delete Repost

```http
DELETE /reposts/{repost}
```

**Middleware:** auth

**Authorization:** User must own the repost

**URL Parameters:**
- `repost` (integer, required): Repost ID

**Success Response (200):**
```json
{
  "message": "Repost deleted successfully"
}
```

---

## Message Endpoints

### 1. Get All Messages/Conversations

```http
GET /api/messages
```

**Middleware:** auth

**Success Response (200):**
```json
{
  "conversations": [
    {
      "user":  {
        "id": 2,
        "name": "Jane Doe",
        "avatar_url": "/storage/avatars/jane.jpg"
      },
      "last_message": {
        "id": 50,
        "sender_id":  2,
        "receiver_id": 1,
        "message": "Hey, how are you?",
        "is_read": false,
        "created_at": "2025-12-21T09:30:00.000000Z"
      },
      "unread_count":  3
    }
  ]
}
```

---

### 2. Get Messages with Specific User

```http
GET /api/messages/{user}
```

**Middleware:** auth

**URL Parameters:**
- `user` (integer, required): User ID

**Success Response (200):**
```json
{
  "user": {
    "id": 2,
    "name": "Jane Doe",
    "avatar_url": "/storage/avatars/jane.jpg"
  },
  "messages":  [
    {
      "id": 48,
      "sender_id":  1,
      "receiver_id":  2,
      "message":  "Hi there!",
      "is_read": true,
      "created_at":  "2025-12-21T09:00:00.000000Z"
    },
    {
      "id":  49,
      "sender_id": 2,
      "receiver_id": 1,
      "message": "Hello!",
      "is_read": true,
      "created_at":  "2025-12-21T09:15:00.000000Z"
    },
    {
      "id":  50,
      "sender_id": 2,
      "receiver_id":  1,
      "message":  "Hey, how are you?",
      "is_read": false,
      "created_at": "2025-12-21T09:30:00.000000Z"
    }
  ]
}
```

---

## Notification Endpoints

### 1. Get All Notifications

```http
GET /notifications
```

**Middleware:** auth

**Query Parameters:**
- `unread` (boolean, optional): Filter unread notifications only

**Success Response (200):**
```json
{
  "notifications": [
    {
      "id": 1,
      "user_id": 1,
      "actor_id": 2,
      "type": "like",
      "notifiable_id": 5,
      "notifiable_type": "post",
      "data": {
        "message": "Jane Doe liked your post"
      },
      "read_at": null,
      "created_at": "2025-12-21T09:00:00.000000Z",
      "actor": {
        "id": 2,
        "name": "Jane Doe",
        "avatar_url": "/storage/avatars/jane.jpg"
      }
    },
    {
      "id": 2,
      "user_id": 1,
      "actor_id": 3,
      "type": "follow",
      "notifiable_id": null,
      "notifiable_type": null,
      "data": {
        "message": "Bob Smith started following you"
      },
      "read_at": "2025-12-21T09:30:00.000000Z",
      "created_at": "2025-12-21T08:45:00.000000Z",
      "actor": {
        "id": 3,
        "name": "Bob Smith",
        "avatar_url": "/storage/avatars/bob.jpg"
      }
    }
  ],
  "unread_count": 5
}
```

---

### 2. Mark Notification as Read

```http
POST /notifications/{id}/read
```

**Middleware:** auth

**Authorization:** User must own the notification

**URL Parameters:**
- `notification` (integer, required): Notification ID

**Success Response (200):**
```json
{
  "message": "Notification marked as read",
  "notification": {
    "id": 1,
    "read_at": "2025-12-21T10:00:00.000000Z"
  }
}
```

---

### 3. Mark All Notifications as Read

```http
POST /notifications/read-all
```

**Middleware:** auth

**Success Response (200):**
```json
{
  "message": "All notifications marked as read",
  "count": 5
}
```

---

### 4. Delete Notification

```http
DELETE /notifications/{id}
```

**Middleware:** auth

**Authorization:** User must own the notification

**URL Parameters:**
- `notification` (integer, required): Notification ID

**Success Response (200):**
```json
{
  "message": "Notification deleted successfully"
}
```

---

## Report Endpoints

### 1. Create Report

```http
POST /reports
```

**Middleware:** auth

**Request Body:**
```json
{
  "post_id": 10,
  "reason": "spam",
  "description": "This post contains spam content and promotional links."
}
```

**Validation Rules:**
- `post_id`: required, exists:posts,id
- `reason`: required, in:spam,harassment,violence,hate_speech,misinformation,other
- `description`: optional, string, max:1000

**Success Response (201):**
```json
{
  "message": "Report submitted successfully",
  "report": {
    "id": 5,
    "user_id": 1,
    "post_id": 10,
    "reason": "spam",
    "description": "This post contains spam content and promotional links.",
    "status": "pending",
    "created_at": "2025-12-21T10:00:00.000000Z"
  }
}
```

**Error Response (409):**
```json
{
  "message": "You have already reported this post"
}
```

---

### 2. Get All Reports (Admin)

```http
GET /admin/dashboard
```

**Middleware:** auth, admin

**Query Parameters:**
- `status` (string, optional): Filter by status (pending, reviewed, resolved)
- `page` (integer, optional): Page number for pagination

**Success Response (200):**
```json
{
  "reports": [
    {
      "id": 5,
      "user_id":  1,
      "post_id": 10,
      "reason": "spam",
      "description": "This post contains spam content.. .",
      "status": "pending",
      "created_at":  "2025-12-21T10:00:00.000000Z",
      "reporter": {
        "id": 1,
        "name": "John Doe"
      },
      "post": {
        "id": 10,
        "content": "Reported content...",
        "user": {
          "id": 5,
          "name": "Spammer User"
        }
      }
    }
  ],
  "pagination": {
    "current_page": 1,
    "total_pages": 5,
    "total_reports": 47,
    "per_page": 10
  }
}
```

---

### 3. Update Report Status (Admin)

```http
PATCH /reports/{report}/status
```

**Middleware:** auth, admin

**URL Parameters:**
- `report` (integer, required): Report ID

**Request Body:**
```json
{
  "status": "resolved"
}
```

**Validation Rules:**
- `status`: required, in:pending,reviewed,resolved

**Success Response (200):**
```json
{
  "message": "Report status updated successfully",
  "report":  {
    "id": 5,
    "status": "resolved",
    "updated_at": "2025-12-21T10:00:00.000000Z"
  }
}
```

---

## Explore Endpoints

### 1. Explore Posts

```http
GET /explore
```

**Middleware:** auth

**Query Parameters:**
- `filter` (string, optional): trending, latest, popular
- `page` (integer, optional): Page number

**Success Response (200):**
```json
{
  "posts": [
    {
      "id": 15,
      "user_id":  5,
      "content": "Trending post content...",
      "likes_count": 150,
      "replies_count":  45,
      "created_at":  "2025-12-20T15:00:00.000000Z",
      "user": {
        "id": 5,
        "name": "Popular User",
        "avatar_url": "/storage/avatars/popular.jpg"
      },
      "liked":  false,
      "bookmarked": false
    }
  ],
  "pagination":  {
    "current_page":  1,
    "total_pages": 10,
    "per_page": 20
  }
}
```
---

## Bookmark Endpoints

### 1. Get All Bookmarks

```http
GET /bookmarks
```

**Middleware:** auth

**Success Response (200):**
```json
{
  "bookmarks": [
    {
      "id": 1,
      "user_id": 1,
      "post_id": 5,
      "created_at": "2025-12-20T10:00:00.000000Z",
      "post": {
        "id": 5,
        "user_id": 3,
        "content": "Bookmarked post content...",
        "likes_count": 20,
        "replies_count": 5,
        "user": {
          "id": 3,
          "name": "Bob Smith",
          "avatar_url": "/storage/avatars/bob.jpg"
        }
      }
    }
  ]
}
```

---

## Error Responses

### Common Error Codes

**400 Bad Request**
```json
{
  "message": "Bad request",
  "errors": {
    "field":  ["Error message"]
  }
}
```

**401 Unauthorized**
```json
{
  "message": "Unauthenticated"
}
```

**403 Forbidden**
```json
{
  "message": "This action is unauthorized"
}
```

**404 Not Found**
```json
{
  "message": "Resource not found"
}
```

**422 Unprocessable Entity**
```json
{
  "message": "The given data was invalid",
  "errors": {
    "field_name": [
      "Validation error message"
    ]
  }
}
```

**429 Too Many Requests**
```json
{
  "message":  "Too many requests.  Please try again later."
}
```

**500 Internal Server Error**
```json
{
  "message":  "Server error",
  "error": "Detailed error message (only in development)"
}
```

---

## Notification Types

Available notification types:

- `like` - Someone liked your post
- `comment` - Someone commented on your post
- `follow` - Someone followed you
- `repost` - Someone reposted your post
- `mention` - Someone mentioned you in a post

---

## Report Reasons

Available report reasons:

- `spam` - Spam or misleading
- `harassment` - Harassment or bullying
- `violence` - Violence or dangerous organizations
- `hate_speech` - Hate speech or symbols
- `misinformation` - False information
- `other` - Other reasons

## Support

Untuk pertanyaan atau masalah terkait API, silakan buat issue di [GitHub Issues](https://github.com/bintangprajudha/meetly/issues).
