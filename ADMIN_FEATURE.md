# Fitur Admin untuk Meetly

Dokumentasi lengkap implementasi fitur admin yang memungkinkan:

1. **Admin bisa menghapus post** (milik siapa saja)
2. **Admin bisa menghapus user** (kecuali diri sendiri)
3. **User biasa bisa melaporkan post**
4. **Admin bisa melihat laporan di dashboard** dan mengambil tindakan

---

## 📋 Struktur Implementasi

### Backend (Laravel)

#### 1. **Migrations**

- `2025_12_19_090726_add_is_admin_to_users_table.php`
    - Menambah kolom `is_admin` (boolean, default false) ke tabel `users`
- `2025_12_19_090741_create_reports_table.php`
    - Tabel untuk menyimpan laporan post dari user
    - Kolom: `user_id`, `post_id`, `reason`, `description`, `status` (pending/reviewed/resolved)

#### 2. **Models**

- `app/Models/Report.php`
    - Relasi: `belongsTo(User::class)`, `belongsTo(Post::class)`

#### 3. **Policies** (Authorization)

- `app/Policies/PostPolicy.php`
    - Method `delete()`: Admin **atau** owner bisa hapus post
- `app/Policies/UserPolicy.php`
    - Method `delete()`: Hanya admin, tidak boleh hapus diri sendiri

- `app/Providers/AuthServiceProvider.php`
    - Register policies ke Laravel Gate

#### 4. **Middleware**

- `app/Http/Middleware/IsAdmin.php`
    - Proteksi route admin (hanya `is_admin = true`)
    - Alias: `admin` (terdaftar di `bootstrap/app.php`)

#### 5. **Controllers**

- `app/Http/Controllers/PostController.php`
    - `destroy()` diubah pakai `$this->authorize('delete', $post)` (policy-based)
- `app/Http/Controllers/AdminUserController.php`
    - `dashboard()`: Tampilkan halaman admin dengan daftar reports
    - `destroy(User $user)`: Hapus user (pakai policy)

- `app/Http/Controllers/ReportController.php`
    - `store()`: User melaporkan post
    - `updateStatus()`: Admin ubah status laporan (pending → reviewed → resolved)

#### 6. **Routes** (`routes/web.php`)

```php
// User routes (auth required)
Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');

// Admin routes (auth + admin middleware)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminUserController::class, 'dashboard'])->name('dashboard');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::patch('/reports/{report}/status', [ReportController::class, 'updateStatus'])->name('reports.update-status');
});
```

---

### Frontend (Vue + Inertia)

#### 1. **Inertia Shared Data** (`app/Http/Middleware/HandleInertiaRequests.php`)

- `auth.user.is_admin` sekarang di-share ke semua halaman Vue
- Frontend bisa cek: `$page.props.auth.user.is_admin`

#### 2. **Components**

- `resources/js/components/PostCard.vue`
    - **Tombol Delete**: Muncul untuk owner **atau** admin
        - Jika admin menghapus post orang lain, label jadi "Delete (Admin)"
    - **Tombol Report**: Muncul untuk non-owner (user biasa bisa report)
    - **Modal Report**: Form untuk memilih reason + description

#### 3. **Pages**

- `resources/js/pages/Admin/Dashboard.vue`
    - Tampilan dashboard admin dengan daftar reports
    - Actions per report:
        - Mark as Reviewed / Resolved
        - Delete Post
        - Delete User (yang membuat post)

#### 4. **Layouts**

- `resources/js/layouts/app/AppSidebarLayout.vue`
    - Link **Admin Dashboard** muncul di sidebar (hanya untuk `is_admin = true`)

---

## 🚀 Setup & Usage

### 1. **Jalankan Migration**

```bash
php artisan migrate
```

### 2. **Buat User Admin Pertama**

Run seeder:

```bash
php artisan db:seed --class=AdminUserSeeder
```

**Kredensial admin default:**

- Email: `admin@meetly.com`
- Password: `password`

**Atau buat admin manual via Tinker:**

```bash
php artisan tinker
```

```php
$user = \App\Models\User::where('email', 'your@email.com')->first();
$user->is_admin = true;
$user->save();
```

### 3. **Build Frontend (jika perlu)**

```bash
npm run build
# atau
npm run dev
```

---

## 🎯 Flow Penggunaan

### **Sebagai User Biasa**

1. Lihat post yang tidak pantas
2. Klik **... (More Options)** → **Report Post**
3. Pilih reason (Spam, Harassment, dll) dan opsional tambah deskripsi
4. Submit report
5. Laporan akan dikirim ke admin

### **Sebagai Admin**

1. Login sebagai user dengan `is_admin = true`
2. Sidebar akan menampilkan link **Admin Dashboard**
3. Di dashboard, lihat daftar laporan dari user:
    - Status: Pending / Reviewed / Resolved
    - Detail post yang dilaporkan + alasan
4. Actions yang bisa dilakukan:
    - **Mark as Reviewed / Resolved**: Update status laporan
    - **Delete Post**: Hapus post yang dilaporkan
    - **Delete User**: Hapus user yang membuat post (beserta semua datanya)
5. Admin juga bisa langsung menghapus post di feed (tanpa harus ada laporan):
    - Tombol delete muncul dengan label "Delete (Admin)"

---

## 🔒 Security & Authorization

### **Policy-Based Authorization**

- **PostPolicy**: Admin atau owner bisa hapus post
- **UserPolicy**: Hanya admin, tidak boleh hapus diri sendiri

### **Middleware Protection**

- Route `/admin/*` dilindungi middleware `admin`
- User biasa akan mendapat **403 Forbidden** jika coba akses

### **Cascade Delete**

Ketika admin menghapus user, Laravel akan otomatis hapus (cascade):

- Posts
- Comments
- Likes
- Bookmarks
- Reposts
- Follows
- Notifications

**Note:** Tabel `messages` tidak punya FK, jadi butuh cleanup manual jika perlu.

---

## 📊 Struktur Database

### **users table**

```
id, name, email, password, is_admin (boolean), timestamps
```

### **reports table**

```
id, user_id (FK), post_id (FK), reason, description (nullable),
status (enum: pending/reviewed/resolved), timestamps
```

---

## 🧪 Testing Checklist

- [ ] Admin bisa melihat tombol delete di semua post
- [ ] Admin bisa menghapus post orang lain
- [ ] Admin bisa menghapus user di dashboard
- [ ] Admin **tidak bisa** menghapus diri sendiri
- [ ] User biasa bisa report post
- [ ] User biasa **tidak bisa** report post sendiri (tombol report tidak muncul)
- [ ] Dashboard admin menampilkan daftar reports
- [ ] Admin bisa update status report
- [ ] Link Admin Dashboard hanya muncul untuk admin
- [ ] Route `/admin/*` block non-admin (403)

---

## 🎨 UI/UX Details

### **PostCard Dropdown**

- **Owner**: Hanya tombol "Delete"
- **Admin (bukan owner)**: "Delete (Admin)" + "Report Post"
- **User biasa**: Hanya "Report Post"

### **Admin Dashboard**

- Card per report dengan badge status (Pending = kuning, Reviewed = biru, Resolved = hijau)
- Inline actions tanpa confirmasi untuk status update
- Confirmation dialog untuk delete post/user

### **Sidebar Link**

- Link "Admin Dashboard" dengan ikon lock, muncul di antara Bookmarks dan Profile

---

## 🔧 Customization

### **Ubah Reason Report**

Edit dropdown di `PostCard.vue`:

```vue
<option value="spam">Spam</option>
<option value="harassment">Harassment or bullying</option>
<!-- tambah/ubah sesuai kebutuhan -->
```

### **Ubah Email/Password Admin Default**

Edit `database/seeders/AdminUserSeeder.php`:

```php
'email' => 'your_admin@example.com',
'password' => Hash::make('your_secure_password'),
```

### **Tambah Column ke Reports**

Misalnya `resolved_at`:

1. Buat migration: `php artisan make:migration add_resolved_at_to_reports`
2. Update model `Report` fillable & casts
3. Update form di frontend

---

## 🐛 Troubleshooting

### **Link Admin Dashboard tidak muncul**

- Pastikan kolom `is_admin` sudah ada di DB (`php artisan migrate`)
- Cek `auth.user.is_admin` di HandleInertiaRequests sudah di-share
- Clear cache: `php artisan config:clear && php artisan cache:clear`

### **403 Forbidden saat akses /admin/dashboard**

- User belum login, atau `is_admin = false`
- Periksa middleware alias di `bootstrap/app.php`

### **Tombol Delete tidak muncul untuk admin**

- Pastikan `currentUser.is_admin` dipass ke `<PostCard>` component
- Check di browser devtools: `$page.props.auth.user.is_admin`

### **Report tidak tersimpan**

- Periksa validasi di `ReportController::store()`
- Check console browser untuk error response
- Pastikan CSRF token aktif

---

## 📝 Files Modified/Created

### Backend

- ✅ `database/migrations/*_add_is_admin_to_users_table.php`
- ✅ `database/migrations/*_create_reports_table.php`
- ✅ `database/seeders/AdminUserSeeder.php`
- ✅ `app/Models/Report.php`
- ✅ `app/Providers/AuthServiceProvider.php`
- ✅ `app/Policies/PostPolicy.php`
- ✅ `app/Policies/UserPolicy.php`
- ✅ `app/Http/Middleware/IsAdmin.php`
- ✅ `app/Http/Controllers/AdminUserController.php`
- ✅ `app/Http/Controllers/ReportController.php`
- ✅ `app/Http/Controllers/PostController.php` (updated)
- ✅ `app/Http/Middleware/HandleInertiaRequests.php` (updated)
- ✅ `routes/web.php` (updated)
- ✅ `bootstrap/app.php` (updated)

### Frontend

- ✅ `resources/js/pages/Admin/Dashboard.vue`
- ✅ `resources/js/components/PostCard.vue` (updated)
- ✅ `resources/js/layouts/app/AppSidebarLayout.vue` (updated)

---

## 🎉 Summary

Implementasi lengkap fitur admin dengan pola kombinasi **Policies** (untuk resource-level authorization) dan **Middleware** (untuk route-level protection). User biasa bisa melaporkan konten bermasalah, admin bisa review laporan dan mengambil tindakan di dashboard khusus. Semua dilakukan dengan aman menggunakan authorization Laravel dan protected routes.

**Pola yang digunakan:**

- ✅ **Policies** untuk hapus post (ada logika owner vs admin)
- ✅ **Middleware** untuk route admin-only (tidak ada ownership check)
- ✅ **Inertia shared data** untuk expose `is_admin` ke frontend
- ✅ **Component props** untuk conditional rendering (delete/report buttons)
- ✅ **Modal pattern** untuk report form (tanpa navigate)

Implementasi bersih, maintainable, dan scalable! 🚀
