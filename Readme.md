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

MIT License.
