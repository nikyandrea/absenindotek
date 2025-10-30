# Setup Database - Langkah Lengkap

## 1. Persiapan Database

### Opsi A: Menggunakan MySQL

1. **Buat database baru:**
```sql
CREATE DATABASE absenindotek CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. **Konfigurasi .env:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absenindotek
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Opsi B: Menggunakan PostgreSQL

1. **Buat database baru:**
```sql
CREATE DATABASE absenindotek;
```

2. **Konfigurasi .env:**
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=absenindotek
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

## 2. Jalankan Migrations

Migrations akan membuat semua tabel yang diperlukan:

```bash
php artisan migrate
```

### Tabel yang Akan Dibuat:

1. **users** - Data karyawan, admin, supervisor
2. **offices** - Data kantor/cabang dengan geofence
3. **holidays** - Daftar hari libur nasional & perusahaan
4. **schedules** - Jadwal kerja (untuk jam kerja tetap)
5. **face_profiles** - Profile wajah untuk face recognition
6. **attendance_sessions** - Sesi check-in/check-out
7. **attendance_daily** - Konsolidasi kehadiran harian
8. **late_events** - Record keterlambatan dengan alasan
9. **leave_requests** - Pengajuan cuti/izin/sakit
10. **incentive_adjustments** - Penyesuaian insentif
11. **deduction_adjustments** - Penyesuaian potongan
12. **audit_logs** - Log aktivitas sistem

## 3. Jalankan Seeders

Seeders akan mengisi data awal untuk testing:

```bash
php artisan db:seed
```

### Data yang Akan Di-seed:

#### Offices:
- Kantor Pusat Jakarta (radius 100m)
- Kantor Cabang Bandung (radius 150m)

#### Users:
| Role | Email | Password | Tipe Jam Kerja |
|------|-------|----------|----------------|
| Admin | admin@absensi.com | password123 | Tetap |
| Supervisor | supervisor@absensi.com | password123 | Tetap |
| Karyawan | budi@absensi.com | password123 | Tetap |
| Karyawan | siti@absensi.com | password123 | Bebas |

#### Holidays:
17 hari libur nasional tahun 2025 (Tahun Baru, Idul Fitri, dll)

## 4. Verifikasi Database

### Cek tabel yang sudah dibuat:
```bash
php artisan db:show
```

### Cek data users:
```bash
php artisan tinker
>>> User::all(['name', 'email', 'role']);
```

## 5. Reset Database (jika diperlukan)

Untuk reset semua data dan mulai dari awal:

```bash
php artisan migrate:fresh --seed
```

⚠️ **PERINGATAN**: Command ini akan **menghapus semua data**!

## 6. Rollback Migrations

Untuk rollback migration terakhir:
```bash
php artisan migrate:rollback
```

Untuk rollback semua migrations:
```bash
php artisan migrate:reset
```

## 7. Troubleshooting

### Error: "SQLSTATE[HY000] [1049] Unknown database"
**Solusi**: Pastikan database sudah dibuat terlebih dahulu.

### Error: "SQLSTATE[HY000] [2002] Connection refused"
**Solusi**: Pastikan MySQL/PostgreSQL server sudah running.

### Error: "Syntax error or access violation: 1071 Specified key was too long"
**Solusi**: Tambahkan di `app/Providers/AppServiceProvider.php`:
```php
use Illuminate\Support\Facades\Schema;

public function boot()
{
    Schema::defaultStringLength(191);
}
```

### Error saat migration: "Foreign key constraint fails"
**Solusi**: Jalankan migrations dalam urutan yang benar. Migration files sudah dibuat dengan timestamp yang tepat.

## 8. Backup & Restore

### Backup Database:
```bash
# MySQL
mysqldump -u root -p absenindotek > backup.sql

# PostgreSQL
pg_dump absenindotek > backup.sql
```

### Restore Database:
```bash
# MySQL
mysql -u root -p absenindotek < backup.sql

# PostgreSQL
psql absenindotek < backup.sql
```

## 9. Production Setup

Untuk production environment:

1. **Jangan jalankan seeder di production!**
2. **Gunakan migration dengan hati-hati:**
```bash
php artisan migrate --force
```

3. **Backup database sebelum migration:**
```bash
php artisan backup:run  # jika menggunakan spatie/laravel-backup
```

4. **Set environment yang tepat:**
```env
APP_ENV=production
APP_DEBUG=false
```

## 10. Maintenance

### Optimize Database:
```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
```

### Monitor Query Performance:
```bash
php artisan telescope:install  # untuk development
```

---

**Catatan**: Dokumentasi ini untuk setup initial. Untuk penambahan fitur baru, buat migration baru:
```bash
php artisan make:migration add_new_feature_to_table --table=table_name
```
