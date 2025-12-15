# Manual Instalasi Meetly

Panduan ini akan membantu Anda menginstal dan menjalankan aplikasi Meetly di lingkungan pengembangan lokal Anda.

## Prasyarat

Sebelum memulai, pastikan Anda telah menginstal perangkat lunak berikut di sistem Anda:

*   PHP >= 8.2
*   Composer
*   Node.js >= 18.x
*   NPM
*   Database (misalnya MySQL, PostgreSQL, atau SQLite)

## Langkah-langkah Instalasi

1.  **Clone Repository**

    Clone repositori ini ke mesin lokal Anda menggunakan Git:

    ```bash
    git clone https://github.com/your-username/meetly.git
    cd meetly
    ```

2.  **Instal Dependensi PHP**

    Instal semua dependensi PHP yang diperlukan menggunakan Composer:

    ```bash
    composer install
    ```

3.  **Instal Dependensi JavaScript**

    Instal semua dependensi JavaScript yang diperlukan menggunakan NPM:

    ```bash
    npm install
    ```

4.  **Konfigurasi Lingkungan**

    Salin file `.env.example` menjadi `.env`:

    ```bash
    cp .env.example .env
    ```

    Buka file `.env` dan konfigurasikan variabel lingkungan Anda, terutama koneksi database (`DB_*` variables):

    ```
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=meetly
    DB_USERNAME=root
    DB_PASSWORD=
    ```

5.  **Generate Kunci Aplikasi**

    Generate kunci aplikasi Laravel yang unik:

    ```bash
    php artisan key:generate
    ```

6.  **Migrasi Database**

    Jalankan migrasi database untuk membuat semua tabel yang diperlukan. Pastikan database `meetly` sudah dibuat terlebih dahulu.

    ```bash
    php artisan migrate
    ```

7.  **Seed Database (Opsional)**

    Jika Anda ingin mengisi database dengan data awal (contohnya, beberapa user), jalankan seeder:

    ```bash
    php artisan db:seed
    ```

8.  **Build Aset Frontend**

    Kompilasi dan bundle aset frontend (CSS dan JavaScript) menggunakan Vite:

    ```bash
    npm run build
    ```

## Menjalankan Aplikasi

1.  **Jalankan Development Server**

    Untuk menjalankan server pengembangan lokal, gunakan perintah `serve` dari Artisan:

    ```bash
    php artisan serve
    ```

    Aplikasi akan berjalan di `http://127.0.0.1:8000`.

2.  **Jalankan Vite Dev Server**

    Untuk pengembangan frontend dengan Hot Module Replacement (HMR), jalankan server Vite di terminal terpisah:

    ```bash
    npm run dev
    ```

    Ini akan memonitor perubahan pada file frontend Anda dan memperbarui browser secara otomatis.

Selamat! Aplikasi Meetly sekarang seharusnya sudah berjalan di lingkungan lokal Anda.
