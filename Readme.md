Proyek Meetly (Nama Proyek Anda)
README ini berisi panduan lengkap untuk melakukan instalasi, konfigurasi awal, hingga alur kerja kolaborasi untuk proyek Meetly. Ikuti setiap langkah dengan saksama untuk memastikan lingkungan pengembangan Anda berjalan dengan benar.

Daftar Isi
Teknologi yang Digunakan

Kebutuhan Sistem (Prerequisites)

Instalasi & Konfigurasi Awal

Menjalankan Proyek

Alur Kerja Kolaborasi (Git Workflow)

Aturan Penulisan Kode (Coding Style)

Teknologi yang Digunakan
PHP: 8.3+

Framework: Laravel 12+

Database: MySQL / MariaDB

Frontend: Vue.js & Vite

Package Manager: Composer (untuk PHP), NPM (untuk Node.js)

Server Lokal: XAMPP (disarankan) atau sejenisnya.

Kebutuhan Sistem (Prerequisites)
Pastikan perangkat lunak berikut sudah terinstal di komputer Anda sebelum memulai:

Git: https://git-scm.com/

PHP 8.3+: Pastikan ekstensi PHP berikut sudah aktif.

Untuk pengguna Ubuntu, Anda bisa menginstalnya dengan perintah:

sudo apt install php8.3-xml php8.3-mysql php8.3-sqlite3 php8.3-curl php8.3-mbstring php8.3-zip

Composer 2: https://getcomposer.org/

Node.js & NPM: https://nodejs.org/

Server Lokal (XAMPP): https://www.apachefriends.org/

Instalasi & Konfigurasi Awal
Ini adalah panduan langkah demi langkah untuk menginisialisasi proyek dari awal.

1. Clone Repositori
Clone proyek ini dari repositori Git ke folder lokal Anda.

git clone [URL_REPOSITORY_GIT_ANDA]
cd nama-folder-proyek

2. Instal Dependensi PHP
Gunakan Composer untuk menginstal semua pustaka PHP yang dibutuhkan oleh Laravel.

composer install

3. Instal Dependensi JavaScript
Gunakan NPM untuk menginstal semua pustaka JavaScript yang dibutuhkan oleh Vite dan Vue.

npm install

4. Buat File Environment
Salin file .env.example menjadi .env. File ini akan berisi semua konfigurasi lokal Anda.

cp .env.example .env

5. Generate Application Key
Buat kunci enkripsi unik untuk aplikasi Anda.

php artisan key:generate

6. Konfigurasi Database
Langkah ini menghubungkan proyek Laravel ke database Anda.
a. Buat Database Baru: Buka XAMPP, jalankan Apache & MySQL. Buka http://localhost/phpmyadmin dan buat sebuah database baru (misalnya: meetly_db). Pastikan collation-nya adalah utf8mb4_unicode_ci.

b. Update File .env: Buka file .env dan sesuaikan konfigurasi database berikut:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=meetly_db     # <== Ganti dengan nama database yang Anda buat
DB_USERNAME=root          # <== Username default XAMPP adalah 'root'
DB_PASSWORD=              # <== Password default XAMPP adalah KOSONG

7. Jalankan Migrasi & Seeder
Perintah ini akan membuat semua tabel yang diperlukan di database Anda (migrate) dan mengisinya dengan data awal (seed).

php artisan migrate --seed

Jika berhasil, Anda akan melihat tabel-tabel baru muncul di meetly_db melalui phpMyAdmin.

Menjalankan Proyek
Proyek ini menggunakan concurrently untuk menjalankan semua layanan yang dibutuhkan secara bersamaan.

Buka Terminal di direktori proyek.

Jalankan perintah berikut:

composer run dev

Perintah ini akan menjalankan:

Server PHP Laravel (php artisan serve) di http://12-7.0.0.1:8000

Server Vite untuk aset frontend (npm run dev) di http://localhost:5173

Proses antrian (queue:listen)

Logging (pail)

Buka http://127.0.0.1:8000 di browser Anda untuk melihat aplikasi berjalan.

Alur Kerja Kolaborasi (Git Workflow)
Untuk menjaga agar riwayat Git tetap bersih dan teratur, ikuti alur kerja berikut:

Selalu Update Branch main: Sebelum mulai mengerjakan fitur baru, pastikan branch main lokal Anda sudah yang terbaru.

git checkout main
git pull origin main

Buat Branch Baru: Buat branch baru dari main untuk setiap fitur atau perbaikan yang akan Anda kerjakan. Gunakan format penamaan feature/nama-fitur atau bugfix/deskripsi-bug.

# Contoh untuk fitur login
git checkout -b feature/login-page

Kerjakan & Commit: Lakukan perubahan pada kode Anda. Lakukan commit secara berkala dengan pesan yang jelas dan deskriptif.

git add .
git commit -m "feat: Menambahkan form login dan validasi dasar"

Push Branch Anda: Setelah selesai, push branch Anda ke repositori remote.

git push origin feature/login-page

Buat Pull Request (PR): Buka repositori di GitHub/GitLab, dan buat Pull Request dari branch Anda ke branch main. Tugaskan anggota tim lain untuk me-review kode Anda.

Jangan Push Langsung ke main: Semua perubahan harus melalui Pull Request yang sudah disetujui.

Aturan Penulisan Kode (Coding Style)
Proyek ini menggunakan Laravel Pint untuk menjaga konsistensi format kode PHP. Sebelum melakukan commit, pastikan untuk menjalankan perintah berikut untuk merapikan kode Anda secara otomatis:

php artisan pint
