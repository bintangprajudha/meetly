# Meetly

Starter kit Laravel 12 + Inertia + Vue 3 + Vite dengan autentikasi Fortify (termasuk 2FA), halaman Settings, Tailwind CSS v4, dan utilitas pengembangan siap pakai.

## Daftar Isi

- Ringkasan
- Teknologi
- Prasyarat
- Quick Start
- Konfigurasi Lingkungan (.env)
- Database, Migrasi & Seeder
- Menjalankan Proyek (Dev & SSR)
- Testing
- Linting & Formatting
- Struktur Proyek
- Alur Kerja Git
- Lisensi

## Ringkasan

Proyek ini menggunakan:

- Backend: Laravel 12 + Fortify (auth, 2FA), Pail (log viewer), Queue
- Frontend: Inertia.js + Vue 3 + Vite, Tailwind CSS v4
- Build tooling: Vite 7, ESLint, Prettier, TypeScript

Direktori `resources/js/pages` sudah berisi halaman-halaman siap pakai (Dashboard, Auth, Settings), dan test menggunakan Pest sudah disiapkan di `tests/`.

## Teknologi

- PHP: ^8.2
- Laravel: ^12
- Inertia.js (Laravel adapter): ^2
- Vue 3, TypeScript, Vite 7
- Tailwind CSS v4
- Database: MySQL/MariaDB atau SQLite

## Prasyarat

Pastikan sudah terpasang:

- Git
- PHP ≥ 8.2 dengan ekstensi umum (curl, mbstring, zip, pdo, xml)
- Composer 2
- Node.js ≥ 18 dan npm
- MySQL/MariaDB (atau SQLite)

Opsional (Ubuntu) untuk ekstensi PHP:

```bash
sudo apt install php-xml php-mysql php-sqlite3 php-curl php-mbstring php-zip
```

## Quick Start

```bash
# 1) Clone
git clone https://github.com/bintangprajudha/meetly.git
cd meetly

# 2) Install dependencies
composer install
npm install

# 3) Siapkan .env dan kunci aplikasi
cp .env.example .env
php artisan key:generate

# 4) Konfigurasi DB di .env (lihat bagian berikut)

# 5) Migrasi + seeder (opsional, jika butuh data awal)
php artisan migrate --seed

# 6) Jalankan mode development (PHP + Vite + Queue + Logs)
composer run dev
```

Alternatif otomatis (bootstrap cepat):

```bash
composer run setup
# Menjalankan install, key:generate, migrate, npm install & build produksi
```

## Konfigurasi Lingkungan (.env)

Ubah nilai berikut sesuai mesin Anda:

```
APP_NAME="Meetly"
APP_ENV=local
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=meetly_db
DB_USERNAME=root
DB_PASSWORD=
```

Catatan:

- Gunakan SQLite bila perlu dengan `DB_CONNECTION=sqlite` dan set `DB_DATABASE=database/database.sqlite`.
- Pastikan collation untuk MySQL: `utf8mb4_unicode_ci`.

## Database, Migrasi & Seeder

- Buat database terlebih dahulu (MySQL/MariaDB) atau siapkan file `database/database.sqlite`.
- Jalankan migrasi dan seeder:

```bash
php artisan migrate --seed
```

## Menjalankan Proyek (Dev & SSR)

Mode development (menjalankan beberapa proses sekaligus):

```bash
composer run dev
```

Yang berjalan:

- Laravel dev server: http://127.0.0.1:8000
- Vite dev server: http://localhost:5173
- Queue listener: `php artisan queue:listen`
- Pail (log): `php artisan pail`

Server-side rendering (opsional, bila dibutuhkan):

```bash
composer run dev:ssr
```

Build aset untuk produksi:

```bash
npm run build
```

## Testing

Pest sudah terpasang. Jalankan:

```bash
composer test
```

## Linting & Formatting

- PHP (Pint):

```bash
php artisan pint
```

- JavaScript/TypeScript/Vue:

```bash
npm run lint      # ESLint (dengan --fix)
npm run format    # Prettier write
npm run format:check
```

## Struktur Proyek (ringkas)

- `app/` – Kode backend Laravel (Controllers, Models, Providers)
- `resources/js/` – Frontend Inertia + Vue (components, pages, layouts)
- `resources/css/` – Style (Tailwind)
- `routes/` – Rute Laravel (`web.php`, `auth.php`, `settings.php`)
- `tests/` – Pengujian dengan Pest (Feature & Unit)
- `config/` – Konfigurasi Laravel & paket

## Alur Kerja Git (ringkas)

```bash
# Update branch main
git checkout main
git pull origin main

# Buat branch fitur
git checkout -b feature/nama-fitur

# Commit teratur
git add .
git commit -m "feat: deskripsi singkat perubahan"

# Push & buka Pull Request
git push origin feature/nama-fitur
```

Hindari push langsung ke `main`. Gunakan PR agar kode direview terlebih dahulu.

## Lisensi

======================================================

# Meetly - Social Media Platform Documentation

## 📋 Daftar Isi
- [Gambaran Umum](#gambaran-umum)
- [Tech Stack](#tech-stack)
- [Persyaratan Sistem](#persyaratan-sistem)
- [Instalasi & Setup](#instalasi--setup)
- [Struktur Database](#struktur-database)
- [Fitur Utama](#fitur-utama)
- [Arsitektur Aplikasi](#arsitektur-aplikasi)
- [API Routes](#api-routes)
- [Testing](#testing)

---

## 🎯 Gambaran Umum

**Meetly** adalah platform social media modern yang memungkinkan pengguna untuk: 
- Membuat dan berbagi postingan (teks, gambar, video)
- Berinteraksi dengan postingan (like, comment, repost, bookmark)
- Mengikuti pengguna lain (follow/unfollow)
- Mengirim pesan langsung (real-time messaging)
- Menerima notifikasi real-time

---

## 💻 Tech Stack

### Backend
- **Framework**: Laravel 12.0
- **PHP**: ^8.2
- **Database**: MySQL/SQLite
- **Authentication**: Laravel Fortify
- **Real-time**:  Pusher PHP Server

### Frontend
- **Framework**:  Vue.js 3.5.13
- **Routing**: Inertia.js 2.1.0
- **UI Components**: Reka UI 2.4.1
- **Styling**: Tailwind CSS 4.1.1
- **Icons**: Lucide Vue Next
- **Utilities**: @vueuse/core, class-variance-authority

### Development Tools
- **Build Tool**: Vite 7.0.4
- **TypeScript**: 5.2.2
- **Linting**: ESLint 9.17.0
- **Formatting**: Prettier 3.4.2
- **Testing**: Pest 4.1

---

## 📦 Persyaratan Sistem

- PHP >= 8.2
- Composer >= 2.0
- Node. js >= 18.x
- NPM >= 9.x
- MySQL >= 8.0 atau SQLite
- Pusher Account (untuk real-time features)

---

## 🚀 Instalasi & Setup

### 1. Clone Repository
```bash
git clone https://github.com/bintangprajudha/meetly. git
cd meetly
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### 3. Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Configure `.env` File
```env
APP_NAME=Meetly
APP_ENV=local
APP_KEY=base64:... 
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=meetly_db
DB_USERNAME=root
DB_PASSWORD=

PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=your_cluster

BROADCAST_DRIVER=pusher
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
```

### 5. Database Migration
```bash
# Run migrations
php artisan migrate

# (Optional) Seed database
php artisan db:seed
```

### 6. Storage Link
```bash
php artisan storage:link
```

### 7. Build Assets
```bash
# Development mode
npm run dev

# Production build
npm run build
```

### 8. Run Application
```bash
# Using Artisan server
php artisan serve

# Or using Composer scripts
composer run dev
```

---

## 🗄️ Struktur Database

### Tabel:  `users`
| Field | Type | Description |
|-------|------|-------------|
| id | BIGINT | Primary key |
| name | VARCHAR(255) | Nama pengguna |
| email | VARCHAR(255) | Email (unique) |
| email_verified_at | TIMESTAMP | Waktu verifikasi email |
| password | VARCHAR(255) | Password (hashed) |
| remember_token | VARCHAR(100) | Token remember me |
| created_at | TIMESTAMP | Waktu pembuatan |
| updated_at | TIMESTAMP | Waktu update |

### Tabel: `posts`
| Field | Type | Description |
|-------|------|-------------|
| id | BIGINT | Primary key |
| user_id | BIGINT | Foreign key ke users |
| content | TEXT | Konten post (max 280 karakter) |
| image_url | VARCHAR(255) | URL gambar (deprecated) |
| media | JSON | Array media (images/videos) |
| likes_count | INTEGER | Jumlah likes |
| replies_count | INTEGER | Jumlah comments |
| bookmarks_count | INTEGER | Jumlah bookmarks |
| reposts_count | INTEGER | Jumlah reposts |
| created_at | TIMESTAMP | Waktu pembuatan |
| updated_at | TIMESTAMP | Waktu update |

**Relationships:**
- `belongsTo` User
- `hasMany` Comments
- `hasMany` Reposts
- `belongsToMany` Users (likes)
- `belongsToMany` Users (bookmarks)

### Tabel: `comments`
| Field | Type | Description |
|-------|------|-------------|
| id | BIGINT | Primary key |
| post_id | BIGINT | Foreign key ke posts |
| user_id | BIGINT | Foreign key ke users |
| content | TEXT | Konten komentar (max 1000 karakter) |
| created_at | TIMESTAMP | Waktu pembuatan |
| updated_at | TIMESTAMP | Waktu update |

### Tabel: `likes`
| Field | Type | Description |
|-------|------|-------------|
| id | BIGINT | Primary key |
| user_id | BIGINT | Foreign key ke users |
| post_id | BIGINT | Foreign key ke posts |
| created_at | TIMESTAMP | Waktu pembuatan |
| updated_at | TIMESTAMP | Waktu update |

**Unique Constraint:** `[user_id, post_id]`

### Tabel: `bookmarks`
| Field | Type | Description |
|-------|------|-------------|
| id | BIGINT | Primary key |
| user_id | BIGINT | Foreign key ke users |
| post_id | BIGINT | Foreign key ke posts |
| created_at | TIMESTAMP | Waktu pembuatan |
| updated_at | TIMESTAMP | Waktu update |

**Unique Constraint:** `[user_id, post_id]`

### Tabel: `reposts`
| Field | Type | Description |
|-------|------|-------------|
| id | BIGINT | Primary key |
| user_id | BIGINT | Foreign key ke users (reposting) |
| post_id | BIGINT | Foreign key ke posts (original) |
| caption | TEXT | Caption tambahan |
| images | JSON | Array gambar tambahan |
| created_at | TIMESTAMP | Waktu pembuatan |
| updated_at | TIMESTAMP | Waktu update |

### Tabel: `follows`
| Field | Type | Description |
|-------|------|-------------|
| id | BIGINT | Primary key |
| follower_user_id | BIGINT | User yang follow |
| followed_user_id | BIGINT | User yang di-follow |
| created_at | TIMESTAMP | Waktu pembuatan |
| updated_at | TIMESTAMP | Waktu update |

**Unique Constraint:** `[follower_user_id, followed_user_id]`

### Tabel: `messages`
| Field | Type | Description |
|-------|------|-------------|
| id | BIGINT | Primary key |
| sender_id | BIGINT | Foreign key ke users (pengirim) |
| receiver_id | BIGINT | Foreign key ke users (penerima) |
| message | TEXT | Isi pesan |
| shared_post_id | BIGINT | Foreign key ke posts (optional) |
| is_read / status | BOOLEAN/VARCHAR | Status baca pesan |
| created_at | TIMESTAMP | Waktu pembuatan |
| updated_at | TIMESTAMP | Waktu update |

### Tabel:  `notifications`
| Field | Type | Description |
|-------|------|-------------|
| id | BIGINT | Primary key |
| user_id | BIGINT | Penerima notifikasi |
| actor_id | BIGINT | User yang melakukan aksi |
| type | VARCHAR(255) | Jenis notifikasi (like, comment, follow, repost) |
| notifiable_id | BIGINT | ID objek terkait |
| notifiable_type | VARCHAR(255) | Class objek terkait |
| data | TEXT | Data tambahan (JSON) |
| read_at | TIMESTAMP | Waktu dibaca |
| created_at | TIMESTAMP | Waktu pembuatan |
| updated_at | TIMESTAMP | Waktu update |

**Index:** `[user_id, read_at]`

---

## ✨ Fitur Utama

### 1. Autentikasi
- **Register**:  Pendaftaran pengguna baru dengan validasi
- **Login**: Login dengan email & password
- **Logout**:  Logout dengan session invalidation
- **Remember Me**: Fitur ingat saya

**File terkait:**
- `app/Http/Controllers/AuthController. php`
- `app/Http/Requests/AuthController/LoginRequest.php`
- `app/Http/Requests/AuthController/RegisterRequest.php`

### 2. Post Management
- **Create Post**: Membuat post dengan teks, gambar, atau video
- **Delete Post**: Menghapus post milik sendiri
- **View Post Detail**: Melihat detail post dengan comments
- **Media Upload**: Upload multiple images (max 4, 10MB each) atau videos (max 4, 50MB each)

**Validasi:**
```php
'content' => 'required|string|max:280',
'images' => 'nullable|array|max:4',
'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
'videos' => 'nullable|array|max:4',
'videos.*' => 'nullable|mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/x-ms-wmv|max:51200',
```

**File terkait:**
- `app/Http/Controllers/PostController.php`
- `app/Http/Requests/PostController/StoreRequest.php`
- `app/Models/Post.php`

### 3. Interaksi Post

#### Like
- Toggle like/unlike pada post
- Real-time counter update
- Notifikasi ke pemilik post

#### Comment
- Menambahkan komentar pada post
- Validasi max 1000 karakter
- Notifikasi ke pemilik post
- Real-time update

#### Bookmark
- Menyimpan post ke bookmark
- Toggle bookmark/unbookmark
- Akses cepat ke saved posts

#### Repost
- Repost dengan caption tambahan
- Upload gambar tambahan untuk repost
- Notifikasi ke pemilik post original
- Tracking repost count

**File terkait:**
- `app/Http/Controllers/PostController.php` (like, bookmark)
- `app/Http/Controllers/CommentController.php`
- `app/Http/Controllers/RepostController.php`

### 4. Follow System
- Follow/unfollow pengguna
- View followers list
- View following list
- Notifikasi saat ada yang follow

**File terkait:**
- `app/Http/Controllers/FollowController.php`
- `app/Models/User.php` (follow methods)

### 5. Messaging (Real-time)
- Send direct message antar user
- Share post via DM
- Real-time message delivery menggunakan Pusher
- Mark message as read
- Fetch conversation history

**Broadcasting Channel:**
```php
PrivateChannel('chat. {userId}')
```

**File terkait:**
- `app/Http/Controllers/MessageController.php`
- `app/Events/NewMessage.php`

### 6. Notifications
- Notifikasi untuk: 
  - Like pada post
  - Comment pada post
  - Follow oleh user lain
  - Repost
- Mark as read
- Unread count
- Real-time notification delivery

**File terkait:**
- `app/Http/Controllers/NotificationController.php`
- `app/Models/Notification.php`

### 7. User Profile
- View public profile
- View user's posts
- View user's reposts
- View user's replies
- View liked posts
- Edit profile (authenticated user only)

**File terkait:**
- `app/Http/Controllers/UserController.php`

---

## 🏗️ Arsitektur Aplikasi

### Directory Structure
```
meetly/
├── app/
│   ├── Events/              # Broadcasting events
│   ├── Http/
│   │   ├── Controllers/     # Business logic controllers
│   │   ├── Middleware/      # Custom middleware
│   │   └── Requests/        # Form validation requests
│   └── Models/              # Eloquent models
├── database/
│   ├── migrations/          # Database migrations
│   ├── factories/           # Model factories
│   └── seeders/             # Database seeders
├── public/                  # Public assets
├── resources/
│   ├── css/                 # Stylesheets
│   ├── js/                  # Vue.js components & pages
│   └── views/               # Blade views
├── routes/
│   ├── web.php              # Web routes
│   ├── api.php              # API routes
│   └── channels. php         # Broadcasting channels
├── storage/                 # Uploaded files & logs
└── tests/                   # Test files
```

### Design Pattern

#### 1. MVC Pattern
- **Model**: Eloquent ORM untuk database interaction
- **View**: Vue.js components dengan Inertia.js
- **Controller**: Business logic handling

#### 2. Form Request Validation
Validation logic dipisah ke Form Request classes:
- `LoginRequest`
- `RegisterRequest`
- `StoreRequest` (Post)
- `StoreRequest` (Comment)

#### 3. Event Broadcasting
Real-time features menggunakan Laravel Events & Pusher:
- `NewMessage` event untuk chat
- `MessageRead` event untuk read status

#### 4. Eloquent Relationships
Penggunaan Eloquent relationships untuk data modeling:
```php
// User model
public function posts(): HasMany
public function following(): BelongsToMany
public function followers(): BelongsToMany

// Post model
public function user(): BelongsTo
public function comments(): HasMany
public function likes(): BelongsToMany
public function bookmarks(): BelongsToMany
public function reposts(): HasMany
```

---

## 🛣️ API Routes

### Authentication Routes (Guest)
```php
GET  /login              # Show login form
POST /login              # Process login
GET  /register           # Show register form
POST /register           # Process registration
```

### Protected Routes (Authenticated)
```php
# Dashboard
GET  /dashboard          # Main feed

# Posts
POST   /posts            # Create new post
GET    /posts/{post}     # View post detail
DELETE /posts/{post}     # Delete post
POST   /posts/{post}/like      # Toggle like
POST   /posts/{post}/bookmark  # Toggle bookmark

# Comments
POST /posts/{post}/comments        # Add comment
GET  /posts/{post}/comments/latest # Get latest comments

# Reposts
POST   /posts/{post}/repost    # Create repost
DELETE /reposts/{repost}       # Delete repost

# Follow
POST   /users/{user}/follow    # Follow user
DELETE /users/{user}/follow    # Unfollow user

# Profile
GET /users/{username}          # View user profile

# Messages
GET  /messages/{user? }         # Message inbox/chat
GET  /messages/fetch/{user}    # Fetch messages
POST /messages/send            # Send message

# Notifications
GET  /notifications            # Get notifications
POST /notifications/mark-read  # Mark as read
POST /logout                   # Logout
```

---

## 🧪 Testing

### Running Tests
```bash
# Run all tests
php artisan test

# Or using Pest
./vendor/bin/pest

# Run specific test
php artisan test --filter=UserTest
```

### Test Structure
```
tests/
├── Feature/         # Feature/Integration tests
│   ├── Auth/
│   ├── Posts/
│   └── Messages/
└── Unit/           # Unit tests
    └── Models/
```

---

## 🔧 Konfigurasi Tambahan

### File Upload Limits
Custom middleware untuk validasi file size:
- **Images**: Max 10MB per file, max 4 files
- **Videos**: Max 50MB per file, max 4 files
- **Total Request**: Max 70MB

**File**:  `app/Http/Middleware/ValidatePostSizeCustom.php`

### Pusher Configuration
Untuk real-time features, configure Pusher di `.env`:
```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=your_cluster
```

### Queue Configuration
Untuk handling jobs (email, notifications):
```bash
# Using database queue
php artisan queue:table
php artisan migrate

# Run queue worker
php artisan queue: work
```

---

## 📝 Best Practices

### 1. Code Organization
- Gunakan Form Requests untuk validation
- Pisahkan business logic ke Service classes (future improvement)
- Gunakan Resource classes untuk API responses

### 2. Security
- Password di-hash menggunakan bcrypt
- CSRF protection enabled
- SQL injection prevention dengan Eloquent ORM
- File upload validation
- XSS protection

### 3. Performance
- Eager loading untuk menghindari N+1 queries
- Database indexing pada foreign keys
- Caching untuk data yang sering diakses

### 4. Database
- Gunakan migrations untuk version control
- Buat seeders untuk sample data
- Index pada kolom yang sering di-query

---

## 🚀 Deployment

### Production Checklist
```bash
# Set environment
APP_ENV=production
APP_DEBUG=false

# Optimize application
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route: cache
php artisan view:cache

# Build assets
npm run build

# Set permissions
chmod -R 755 storage bootstrap/cache
```

### Server Requirements
- PHP 8.2+
- MySQL 8.0+
- Nginx/Apache
- Composer
- Node.js (untuk build)

---

## 📞 Support & Contributing

Untuk pertanyaan atau kontribusi, silakan hubungi: 
- **Repository**: https://github.com/bintangprajudha/meetly
- **Developer**: @bintangprajudha

---

## 📄 License

This project is licensed under the MIT License. 
```

MIT License.
