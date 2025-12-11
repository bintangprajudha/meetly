# Fitur Repost - Dokumentasi Implementasi

## Ringkasan Fitur
Fitur repost memungkinkan user untuk melakukan repost pada post milik user lain atau milik diri sendiri, mirip seperti retweet pada Twitter.

## Requirement yang Telah Diimplementasikan

✅ **1. User dapat repost post pengguna lain atau post diri sendiri**
- User dapat membuka modal repost dengan mengklik tombol panah (repost button)
- Tidak ada batasan untuk repost post sendiri atau milik user lain

✅ **2. Repost dapat dilakukan lebih dari satu kali untuk post yang sama**
- Constraint `unique(['user_id', 'post_id'])` telah dihapus dari migration
- User dapat repost post yang sama berkali-kali

✅ **3. Sertakan repost count pada post utama**
- Kolom `reposts_count` telah ditambahkan ke `posts` table
- Count diupdate otomatis ketika repost dibuat atau dihapus
- Ditampilkan pada UI dengan angka di sebelah tombol repost

✅ **4. User dapat repost dengan atau tanpa caption/gambar**
- RepostModal memiliki form untuk optional caption (max 280 karakter)
- User dapat upload hingga 4 gambar tambahan untuk repost
- Semua field bersifat optional

✅ **5. Repost muncul sebagai post baru dengan konten yang direpost**
- Repost ditampilkan di feed dengan layout khusus
- Original post ditampilkan dalam container terbatas
- Repost caption (jika ada) ditampilkan di bawah original post
- Repost images (jika ada) ditampilkan di bawah caption
- Repost muncul sebagai post baru di feed dengan waktu pembuatan repost

## Struktur Database

### Table: reposts
```sql
- id (primary key)
- user_id (foreign key ke users table)
- post_id (foreign key ke posts table)
- caption (nullable, max 280 chars)
- images (nullable JSON array untuk gambar tambahan)
- created_at, updated_at (timestamps)
```

### Table: posts
```sql
Kolom baru:
- reposts_count (default 0)
```

## File-File yang Dibuat/Dimodifikasi

### Backend

#### 1. Models
- **app/Models/Repost.php** (BARU)
  - Model untuk table reposts
  - Relationships: belongsTo User dan Post

- **app/Models/Post.php** (MODIFIED)
  - Tambahan: `reposts_count` di fillable
  - Tambahan: relationship `reposts()` yang mengembalikan HasMany

- **app/Models/User.php** (MODIFIED)
  - Tambahan: relationship `reposts()` yang mengembalikan HasMany

#### 2. Controllers
- **app/Http/Controllers/RepostController.php** (BARU)
  - `store()` - Membuat repost baru
  - `destroy()` - Menghapus repost
  - `getReposts()` - Mendapatkan list repost untuk post tertentu
  - `hasUserReposted()` - Check apakah user sudah repost post
  - `getUserReposts()` - Mendapatkan semua repost dari user tertentu

- **app/Http/Controllers/PostController.php** (MODIFIED)
  - `index()` - Updated untuk merge posts dan reposts dalam feed
  - Reposts ditransformasi menjadi post-like objects dengan type='repost'
  - Feed diurutkan berdasarkan created_at (posts dan reposts bercampur)

#### 3. Routes
- **routes/web.php** (MODIFIED)
  - POST `/posts/{post}/repost` - Buat repost
  - DELETE `/reposts/{repost}` - Hapus repost
  - GET `/posts/{post}/reposts` - List reposts
  - GET `/posts/{post}/has-reposted` - Check user repost status

#### 4. Migrations
- **database/migrations/2025_12_09_create_reposts_table.php** (MODIFIED)
  - Membuat table reposts dengan struktur lengkap

- **database/migrations/2025_12_10_add_reposts_count_to_posts_table.php** (BARU)
  - Menambahkan kolom reposts_count ke posts table

### Frontend

#### 1. Vue Components
- **resources/js/components/PostCard.vue** (MODIFIED)
  - Repost button dengan icon panah (disebelah kiri comment)
  - Menampilkan repost count
  - Logic untuk membuka RepostModal
  - Menangani display mode untuk repost posts
  - Menampilkan original post dalam bordered container
  - Menampilkan repost caption dan repost images jika ada

- **resources/js/components/RepostModal.vue** (BARU)
  - Modal form untuk membuat repost
  - Caption input field (optional, max 280 chars)
  - Image upload field (up to 4 images)
  - Preview original post
  - Submit dan cancel buttons

## Cara Penggunaan

### Membuat Repost
1. Klik icon panah pada post
2. Modal repost akan terbuka
3. (Optional) Tambahkan caption
4. (Optional) Upload gambar (max 4)
5. Klik "Repost" button

### Menampilkan Repost
- Reposts muncul di feed sebagai post baru
- Ditampilkan dengan indicator "User X reposted"
- Original post ditampilkan dalam container
- Repost caption dan images ditampilkan di bawah original post

### Menghapus Repost
- User yang membuat repost dapat menghapus repostnya
- Repost count otomatis berkurang

## API Endpoints

### POST /posts/{post}/repost
Membuat repost baru
- Request body: `{ caption?: string, images?: File[] }`
- Response: `{ message, repost, reposts_count }`

### DELETE /reposts/{repost}
Menghapus repost
- Response: `{ message, reposts_count }`

### GET /posts/{post}/has-reposted
Check apakah user sudah repost
- Response: `{ reposted: boolean }`

### GET /posts/{post}/reposts
List semua repost untuk post
- Response: Array of reposts dengan user info

## Styling dan UI

### Repost Button
- Icon: Panah double (↔) warna hijau saat dihover
- Position: Paling kiri dari action buttons
- Menampilkan count di sebelahnya

### Repost Card Display
- Indicator text: "User X reposted" (green color)
- Original post: Border gray, background gray-50
- Caption: Full width text di bawah original post
- Images: Grid 2 column untuk repost images

## Notes

- Repost dapat dilakukan berkali-kali oleh user yang sama untuk post yang sama
- Repost title/type ditampilkan dengan visual indication
- Original post user credited dengan menampilkan nama/avatar mereka
- Repost akan sorted chronologically dengan regular posts dalam feed
- File penyimpanan untuk repost images menggunakan folder 'reposts'
