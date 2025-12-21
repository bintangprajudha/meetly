# Meetly

Meetly adalah platform media sosial modern yang dibangun menggunakan Laravel 12, Vue.js 3, dan Inertia.js. Aplikasi ini menyediakan fitur-fitur lengkap untuk berbagi konten, berinteraksi dengan pengguna lain, dan membangun komunitas online. 

## Fitur Utama

- **Autentikasi Pengguna**
  - Registrasi dan Login
  - Manajemen Sesi

- **Manajemen Post**
  - Membuat, melihat, dan menghapus postingan
  - Upload media (gambar/video)
  - Like dan Bookmark post
  - Komentar pada post
  - Repost dengan caption tambahan

- **Interaksi Sosial**
  - Follow/Unfollow pengguna lain
  - Real-time messaging antar pengguna
  - Notifikasi aktivitas
  - Explore untuk menemukan pengguna lain

- **Sistem Moderasi**
  - Laporan post yang tidak sesuai
  - Dashboard admin untuk moderasi
  - Status laporan (pending, reviewed, resolved)

- **Profil Pengguna**
  - Lihat profil pengguna
  - Daftar followers dan following
  - Riwayat post dan aktivitas

## Tech Stack

### Backend
- **Laravel 12** - PHP Framework
- **Laravel Fortify** - Authentication
- **Laravel Wayfinder** - Routing utilities
- **Inertia.js** - Server-side rendering adapter
- **SQLite/MySQL** - Database

### Frontend
- **Vue.js 3** - JavaScript Framework
- **TypeScript** - Type safety
- **Tailwind CSS 4** - Utility-first CSS
- **Reka UI** - UI Components
- **Lucide Icons** - Icon library
- **VueUse** - Vue composition utilities

### Development Tools
- **Vite** - Build tool
- **ESLint** - Linting
- **Prettier** - Code formatting
- **Pest** - Testing framework

## Persyaratan Sistem

- PHP >= 8.2
- Composer
- Node.js >= 18.x
- NPM atau Yarn
- SQLite atau MySQL

## Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/bintangprajudha/meetly.git
cd meetly
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### 3. Konfigurasi Environment

```bash
# Copy file .env.example
cp . env.example .env

# Generate application key
php artisan key: generate
```

### 4. Konfigurasi Database

Edit file `.env` dan sesuaikan konfigurasi database: 

```env
DB_CONNECTION=sqlite
# Atau untuk MySQL: 
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=meetly
# DB_USERNAME=root
# DB_PASSWORD=
```

Untuk SQLite, buat file database: 

```bash
touch database/database.sqlite
```

### 5. Migrasi Database

```bash
php artisan migrate
```

### 6. (Opsional) Seed Database

```bash
php artisan db:seed
```

### 7. Build Assets

```bash
# Development
npm run dev

# Production
npm run build
```

### 8. Jalankan Aplikasi

```bash
# Menggunakan Composer script
composer run dev

# Atau manual
php artisan serve
```

Aplikasi akan berjalan di `http://localhost:8000`

## Quick Start dengan Composer

```bash
# Setup otomatis (install, migrate, build)
composer run setup

# Development mode (server + queue + logs + vite)
composer run dev

# SSR mode
composer run dev: ssr

# Testing
composer run test
```

## Struktur Project

```
meetly/
├── app/
│   ├── Events/          # Event classes
│   ├── Http/
│   │   └── Controllers/ # Controller files
│   ├── Models/          # Eloquent models
│   ├── Policies/        # Authorization policies
│   └── Providers/       # Service providers
├── database/
│   ├── migrations/      # Database migrations
│   ├── factories/       # Model factories
│   └── seeders/         # Database seeders
├── resources/
│   ├── css/            # Stylesheets
│   ├── js/             # Vue.js components & pages
│   └── views/          # Blade templates
├── routes/
│   └── web. php         # Web routes
└── public/             # Public assets
```

## Database Schema

### Tabel Utama

- **users** - Data pengguna
- **posts** - Postingan pengguna
- **comments** - Komentar pada post
- **likes** - Like pada post
- **bookmarks** - Bookmark post
- **follows** - Relasi follow antar pengguna
- **reposts** - Repost dengan caption
- **messages** - Pesan antar pengguna
- **notifications** - Notifikasi pengguna
- **reports** - Laporan post

### ERD Diagram

```
users
  ├── posts (one-to-many)
  ├── comments (one-to-many)
  ├── likes (one-to-many)
  ├── bookmarks (one-to-many)
  ├── followers (many-to-many)
  ├── following (many-to-many)
  ├── messages_sent (one-to-many)
  ├── messages_received (one-to-many)
  └── notifications (one-to-many)

posts
  ├── user (belongs-to)
  ├── comments (one-to-many)
  ├── likes (one-to-many)
  ├── bookmarks (one-to-many)
  ├── reposts (one-to-many)
  └── reports (one-to-many)
```

## API Routes

### Authentication
- `POST /login` - Login
- `POST /register` - Register
- `POST /logout` - Logout

### Posts
- `GET /posts/{post}` - Lihat detail post
- `POST /posts` - Buat post baru
- `DELETE /posts/{post}` - Hapus post
- `POST /posts/{post}/like` - Toggle like
- `POST /posts/{post}/bookmark` - Toggle bookmark

### Comments
- `POST /posts/{post}/comments` - Tambah komentar
- `GET /posts/{post}/comments/latest` - Komentar terbaru

### Follow System
- `POST /users/{user}/follow` - Follow user
- `DELETE /users/{user}/follow` - Unfollow user

### Messages
- `GET /messages` - Daftar pesan
- `POST /messages` - Kirim pesan

### Notifications
- `GET /notifications` - Daftar notifikasi
- `POST /notifications/{id}/read` - Tandai dibaca

### Reports
- `POST /reports` - Laporkan post
- `GET /admin/reports` - Admin: Lihat laporan

## Testing

```bash
# Run semua test
composer test

# Atau menggunakan Pest langsung
./vendor/bin/pest

# Test dengan coverage
./vendor/bin/pest --coverage
```

## Development

### Code Formatting

```bash
# Format dengan Prettier
npm run format

# Check formatting
npm run format:check

# Lint dengan ESLint
npm run lint
```

### Development Server

```bash
# Jalankan semua services (server, queue, logs, vite)
composer run dev
```

Services yang berjalan:
- PHP Development Server:  `http://localhost:8000`
- Vite Dev Server: Hot module replacement

## Security

- CSRF Protection
- Password Hashing (bcrypt)

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Environment Variables

Variabel penting di `.env`:

```env
APP_NAME=Meetly
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=sqlite

BROADCAST_CONNECTION=log
QUEUE_CONNECTION=database
CACHE_STORE=database

SESSION_DRIVER=database
SESSION_LIFETIME=120
```

## Contributing

1. Fork repository
2. Buat branch fitur (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## License

Project ini adalah open source dan tersedia di bawah [MIT License](LICENSE).

## Acknowledgments

- Laravel Team
- Vue.js Team
- Inertia.js Team
- Tailwind CSS Team
- All contributors
