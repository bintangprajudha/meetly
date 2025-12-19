# 🚀 Quick Start: Setup Admin Feature

## Langkah Cepat (5 Menit)

### 1. Jalankan Migration

```bash
php artisan migrate
```

### 2. Buat Admin User

```bash
php artisan db:seed --class=AdminUserSeeder
```

**Kredensial default:**

- Email: `admin@meetly.com`
- Password: `password`

### 3. Login & Test

1. Login dengan kredensial admin di atas
2. Sidebar akan muncul link **Admin Dashboard**
3. Coba report post dari user biasa (buat user baru jika perlu)
4. Lihat laporan di Admin Dashboard
5. Test hapus post & user

---

## Buat Admin dari User yang Sudah Ada

Via Tinker:

```bash
php artisan tinker
```

```php
$user = \App\Models\User::where('email', 'your@email.com')->first();
$user->is_admin = true;
$user->save();
exit
```

Atau manual di database:

```sql
UPDATE users SET is_admin = 1 WHERE email = 'your@email.com';
```

---

## Test Checklist ✅

**Sebagai User Biasa:**

- [ ] Bisa report post orang lain
- [ ] Tidak bisa report post sendiri
- [ ] Tidak ada link Admin Dashboard di sidebar
- [ ] Tidak bisa akses `/admin/dashboard` (403)

**Sebagai Admin:**

- [ ] Link Admin Dashboard muncul di sidebar
- [ ] Bisa lihat daftar reports
- [ ] Bisa update status report
- [ ] Bisa delete post di dashboard
- [ ] Bisa delete user di dashboard
- [ ] Tombol delete muncul di semua post (label "Delete (Admin)")
- [ ] Bisa delete post langsung dari feed
- [ ] Tidak bisa delete diri sendiri

---

## Troubleshooting Cepat

**Link Admin Dashboard tidak muncul?**

```bash
php artisan config:clear
php artisan cache:clear
```

**Masih tidak muncul?**
Check di browser console:

```javascript
console.log($page.props.auth.user.is_admin);
```

Harusnya `true` untuk admin.

**403 saat akses /admin/dashboard?**

- Pastikan sudah login
- Pastikan `is_admin = 1` di database

---

## Next Steps

Lihat dokumentasi lengkap di [ADMIN_FEATURE.md](./ADMIN_FEATURE.md) untuk:

- Detail implementasi backend/frontend
- Customization options
- Security considerations
- Architecture decisions (Policies vs Middleware)

---

**Happy Admin-ing! 🎉**
