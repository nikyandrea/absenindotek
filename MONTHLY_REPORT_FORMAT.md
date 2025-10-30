# FORMAT LAPORAN BULANAN KARYAWAN

## Overview
Format laporan bulanan telah disesuaikan dengan format Google Sheet yang sudah ada sebelumnya untuk konsistensi dan kemudahan migrasi data.

## Struktur Kolom Laporan

### Header Informasi
- Nama Karyawan
- Periode (Bulan Tahun)
- Kantor

### Kolom Data Harian

| No | Kolom | Deskripsi | Sumber Data |
|----|-------|-----------|-------------|
| 1 | No | Nomor urut | Auto-increment |
| 2 | Tanggal | Tanggal lengkap (hari, DD MMMM YYYY) | `check_in_at` |
| 3 | Check-in Aktual | Jam check-in sebenarnya (HH:MM:SS) | `attendance_sessions.check_in_at` |
| 4 | Check-out Aktual | Jam check-out sebenarnya (HH:MM:SS) | `attendance_sessions.check_out_at` |
| 5 | Check-in Valid | Jam mulai kerja valid (HH:MM:SS) | `attendance_sessions.valid_start_at` |
| 6 | Check-out Valid | Jam selesai kerja valid (HH:MM:SS) | `attendance_sessions.valid_end_at` |
| 7 | Durasi Kerja Valid | Selisih valid_end - valid_start (HH:MM:SS) | Calculated |
| 8 | Durasi Kerja Normal | 8 jam (weekday) / 5 jam (weekend/holiday) | Calculated by day type |
| 9 | Durasi Lembur | valid_duration - normal_duration (HH:MM:SS) | Calculated |
| 10 | Nominal Lembur | Bayaran lembur detail sampai detik | `(hours + minutes/60 + seconds/3600) * rate` |
| 11 | Insentif On Time | Insentif datang tepat waktu | `users.incentive_on_time` (hangus jika telat >3x) |
| 12 | Insentif Luar Kota | Insentif dinas luar kota | `users.incentive_out_of_town` |
| 13 | Insentif Hari Libur | Insentif masuk hari libur/weekend | `users.incentive_holiday` |
| 14 | Daily Report | Apa yang dikerjakan hari itu | `attendance_sessions.daily_report` |

## Perhitungan Detail

### 1. Durasi Kerja Valid
```
Durasi Kerja Valid = Check-out Valid - Check-in Valid
```
Menggunakan `valid_start_at` dan `valid_end_at` dari `attendance_sessions`.

### 2. Durasi Kerja Normal
```
Senin - Jumat: 8 jam (480 menit)
Sabtu - Minggu: 5 jam (300 menit)
Hari Libur: 5 jam (300 menit)
```

### 3. Durasi Lembur
```
Durasi Lembur = MAX(0, Durasi Kerja Valid - Durasi Kerja Normal)
```

### 4. Nominal Lembur (Detail Sampai Detik)
```
Formula Excel: =((HOUR(cell) + MINUTE(cell)/60 + SECOND(cell)/3600) * rate)

Implementasi PHP:
$overtimeHours = $overtimeDurationMinutes / 60;
$overtimeAmount = $overtimeHours * $hourlyRate;
```
- Default rate: Rp 5,000/jam
- Rate dapat dikustomisasi per karyawan di field `users.hourly_overtime_rate`

### 5. Insentif On Time
```
Syarat:
- Datang tepat waktu atau sebelum jam kerja (is_on_time = true)
- Khusus untuk karyawan dengan jam tetap (schedule_type = 'fixed')
- Telat maksimal 3x dalam sebulan

Jika telat > 3x dalam sebulan: SELURUH insentif on time bulan tersebut HANGUS
```
- Default: Rp 10,000/hari
- Kustomisasi: `users.incentive_on_time`

### 6. Insentif Luar Kota
```
Syarat:
- Flag is_out_of_town = true pada check-in
```
- Default: Rp 50,000/hari
- Kustomisasi: `users.incentive_out_of_town`

### 7. Insentif Hari Libur
```
Syarat:
- Masuk pada hari Sabtu, Minggu, atau hari libur nasional
```
- Default: Rp 25,000/hari
- Kustomisasi: `users.incentive_holiday`

## Section Bawah Laporan

### Grand Total
```
Grand Total = Sum(Nominal Lembur) + Sum(Insentif On Time) + 
              Sum(Insentif Luar Kota) + Sum(Insentif Hari Libur)
```

### Insentif Tambahan
Admin dapat menambahkan insentif tambahan manual:
- Bonus performa
- Bonus project
- Tunjangan khusus
- dll.

### Potongan
Admin dapat menambahkan potongan manual:
- BPJS
- Pinjaman
- Denda
- dll.

### Total Akhir
```
Total Akhir = Grand Total + Sum(Insentif Tambahan) - Sum(Potongan)
```

## Database Schema

### Tabel Baru

#### 1. `monthly_adjustments`
```sql
- id
- user_id (FK to users)
- year (integer)
- month (integer 1-12)
- type (enum: 'deduction', 'incentive')
- name (string: nama adjustment)
- amount (decimal: nominal)
- notes (text: catatan)
- created_by (FK to users: admin yang menambahkan)
- timestamps
```

### Field Baru

#### 1. `attendance_sessions`
```sql
+ daily_report (text): Apa yang dikerjakan hari ini
```

#### 2. `attendance_daily`
```sql
+ incentive_on_time (decimal): Nominal insentif on time
+ incentive_out_of_town (decimal): Nominal insentif luar kota
+ incentive_holiday (decimal): Nominal insentif hari libur
+ monthly_late_count (integer): Total telat dalam sebulan
```

#### 3. `users`
```sql
+ hourly_overtime_rate (decimal): Upah lembur per jam (default: 5000)
+ incentive_on_time (decimal): Insentif datang tepat waktu (default: 10000)
+ incentive_out_of_town (decimal): Insentif dinas luar kota (default: 50000)
+ incentive_holiday (decimal): Insentif masuk hari libur (default: 25000)
```

## API Endpoints

### 1. Get Monthly Report
```
GET /api/admin/users/{userId}/monthly-report?year=2025&month=10
```
Response:
```json
{
  "success": true,
  "data": {
    "user": {...},
    "period": {...},
    "rows": [...],
    "summary": {
      "total_days": 20,
      "total_overtime_amount": 150000,
      "total_incentive_on_time": 200000,
      "monthly_late_count": 2,
      "incentive_on_time_status": "AKTIF"
    },
    "adjustments": {
      "incentives": [...],
      "deductions": [...],
      "total_incentives": 50000,
      "total_deductions": 30000
    },
    "totals": {
      "grand_total": 500000,
      "final_total": 520000
    }
  }
}
```

### 2. Export Monthly Report (Excel Format)
```
GET /api/admin/users/{userId}/monthly-report/export?year=2025&month=10
```
Returns array format ready for Excel export.

### 3. Get Adjustments
```
GET /api/admin/users/{userId}/adjustments?year=2025&month=10
```

### 4. Add Adjustment
```
POST /api/admin/users/{userId}/adjustments
Body:
{
  "year": 2025,
  "month": 10,
  "type": "deduction",
  "name": "Potongan BPJS",
  "amount": 150000,
  "notes": "BPJS Kesehatan bulan Oktober"
}
```

### 5. Update Adjustment
```
PUT /api/admin/users/{userId}/adjustments/{id}
Body:
{
  "name": "Potongan BPJS (Updated)",
  "amount": 175000,
  "notes": "Updated notes"
}
```

### 6. Delete Adjustment
```
DELETE /api/admin/users/{userId}/adjustments/{id}
```

## Migration Commands

```bash
# Run migrations to add new fields
php artisan migrate
```

Migrations yang dibuat:
1. `2025_10_29_000001_add_report_fields_to_attendance_tables.php`
2. `2025_10_29_000002_create_monthly_adjustments_table.php`
3. `2025_10_29_000003_add_rates_to_users_table.php`

## Service Layer

### MonthlyReportService
- `generateMonthlyReport($userId, $year, $month)`: Generate complete report data
- `exportToArray($userId, $year, $month)`: Export to Excel-compatible array format
- Automatically calculates all durations and amounts
- Handles incentive on-time hangus logic (>3x late)

## Notes

1. **Precision**: Nominal lembur dihitung dengan presisi penuh sampai detik
2. **Incentive Logic**: Insentif on-time akan hangus untuk SELURUH bulan jika telat > 3x
3. **Flexibility**: Admin dapat menambah/hapus adjustment kapan saja
4. **Audit Trail**: Semua perubahan adjustment tercatat dengan created_by
5. **Format Konsisten**: Output Excel akan match persis dengan format Google Sheet sebelumnya

## TODO Next Steps

1. ✅ Create migrations
2. ✅ Create models (MonthlyAdjustment)
3. ✅ Create service (MonthlyReportService)
4. ✅ Create controllers (MonthlyReportController, MonthlyAdjustmentController)
5. ✅ Add API routes
6. ⏳ Run migrations pada database
7. ⏳ Create frontend UI untuk view/export laporan
8. ⏳ Create frontend UI untuk manage adjustments
9. ⏳ Integrate dengan Excel export library (PhpSpreadsheet)
10. ⏳ Testing end-to-end
