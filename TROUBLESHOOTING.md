# 🔧 Troubleshooting Error 502 Bad Gateway

## Penyebab Error 502

Error 502 Bad Gateway di Laravel Herd biasanya terjadi karena:
1. PHP-FPM tidak running
2. File permissions bermasalah
3. Laravel cache corrupt
4. Herd service perlu restart

---

## ✅ Solusi Step by Step

### Solusi 1: Restart Herd (SUDAH DILAKUKAN)
```bash
herd restart
```
**Status**: ✅ Sudah dijalankan. Coba refresh browser!

---

### Solusi 2: Clear Laravel Cache

Jalankan command berikut satu per satu:

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

### Solusi 3: Fix Storage Permissions

```bash
# Windows (PowerShell)
icacls storage /grant Users:F /T
icacls bootstrap/cache /grant Users:F /T
```

---

### Solusi 4: Regenerate Autoload

```bash
composer dump-autoload
php artisan optimize:clear
```

---

### Solusi 5: Restart dari Herd UI

1. Buka **Laravel Herd** dari system tray
2. Klik **Stop Services**
3. Tunggu beberapa detik
4. Klik **Start Services**
5. Refresh browser

---

### Solusi 6: Check PHP Version

```bash
php -v
```

Pastikan PHP >= 8.2. Jika beda, switch PHP di Herd:
1. Buka Herd UI
2. Settings → PHP Version
3. Pilih PHP 8.2 atau 8.3
4. Apply

---

### Solusi 7: Recreate .env

Jika masih error, mungkin .env bermasalah:

```bash
# Backup dulu
copy .env .env.backup

# Copy dari example
copy .env.example .env

# Generate key baru
php artisan key:generate
```

Lalu edit `.env` sesuaikan database config:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absenindotek
DB_USERNAME=root
DB_PASSWORD=
```

---

### Solusi 8: Alternative - Gunakan PHP Built-in Server

Jika Herd masih bermasalah, gunakan built-in server Laravel:

```bash
php artisan serve
```

Lalu buka: `http://127.0.0.1:8000`

---

## 🧪 Test Apakah Sudah Fix

Setelah mencoba solusi di atas, test dengan:

### 1. Cek Laravel bisa jalan:
```bash
php artisan --version
```

### 2. Cek route list:
```bash
php artisan route:list
```

### 3. Test response:
```bash
curl http://absenindotek.test
```

---

## 📊 Checklist Debugging

- [x] Herd restart (sudah dilakukan)
- [ ] Clear Laravel cache
- [ ] Fix permissions
- [ ] Composer dump-autoload
- [ ] Restart dari Herd UI
- [ ] Check PHP version
- [ ] Recreate .env
- [ ] Try php artisan serve

---

## 💡 Quick Fix (Paling Sering Berhasil)

Jalankan command ini berturut-turut:

```bash
herd restart
php artisan optimize:clear
composer dump-autoload
php artisan config:cache
```

Lalu refresh browser!

---

## 🔄 Alternative Access

Jika Herd masih bermasalah, gunakan cara ini:

```bash
# Start Laravel development server
php artisan serve --host=0.0.0.0 --port=8000
```

Akses di: **http://localhost:8000**

Ini cara termudah dan pasti jalan! ✅

---

## ❓ Masih Error?

Coba langkah ini:

1. **Restart komputer** (serius, ini sering membantu)
2. **Reinstall Herd** (jika memang Herd bermasalah)
3. **Gunakan php artisan serve** (alternative yang reliable)

---

## 📝 Recommended: Gunakan php artisan serve

Untuk development, lebih mudah gunakan:

```bash
php artisan serve
```

**Keuntungan**:
- ✅ Lebih stabil
- ✅ Tidak perlu setup Herd
- ✅ Error messages lebih jelas
- ✅ Mudah debug

**Kekurangan**:
- ❌ Harus jalan manual tiap development
- ❌ Stop saat close terminal

---

**Quick Action**: Jalankan `php artisan serve` sekarang, lalu buka http://localhost:8000
