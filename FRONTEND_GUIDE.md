# Frontend Web Interface - Sistem Absensi

## Overview
Frontend admin dashboard untuk mengelola sistem absensi karyawan. Interface ini menyediakan tampilan visual untuk semua fungsi administrasi sebelum deployment ke cloud.

## Pages Created

### 1. Login Page (`/login`)
- Form login dengan email dan password
- Quick-fill buttons untuk 4 akun test:
  - **Admin HRD**: admin@absensi.com / password123
  - **Supervisor**: supervisor@absensi.com / password123
  - **Budi (Tetap)**: budi@absensi.com / password123
  - **Siti (Bebas)**: siti@absensi.com / password123
- Demo authentication menggunakan localStorage
- Auto-redirect ke dashboard setelah login

### 2. Dashboard (`/dashboard`)
**Fitur:**
- Summary cards: Total Karyawan, Hadir Hari Ini, Terlambat, Pending Approval
- Chart kehadiran mingguan (Line Chart)
- Chart statistik keterlambatan (Bar Chart)
- Section aktivitas terbaru (placeholder)
- User profile dengan logout button

### 3. Data Karyawan (`/employees`)
**Fitur:**
- Tabel daftar karyawan dengan data dari seeder
- Filter by: Nama/Email, Kantor, Role, Tipe Jam Kerja
- Badge indicators untuk role dan status
- Button Tambah Karyawan (modal form)
- Actions: Edit dan Delete untuk setiap karyawan
- Modal form dengan fields lengkap:
  - Nama, Email, Password, Phone
  - Kantor, Role, Tipe Jam Kerja
  - Insentif rates (ontime, out-of-town, holiday)

### 4. Monitoring Kehadiran (`/attendance`)
**Fitur:**
- Real-time clock display
- Filter: Tanggal, Kantor, Status (Hadir/Terlambat/Belum Hadir)
- Summary cards: Total, Hadir, Terlambat, Belum Hadir
- Tabel kehadiran dengan kolom:
  - Jam masuk/keluar
  - Status keterlambatan
  - Validasi lokasi (di area/luar area)
  - Skor verifikasi wajah
  - Durasi kerja
- Detail modal untuk info lengkap check-in/out
- Empty state saat belum ada data

### 5. Persetujuan (`/approvals`)
**Fitur:**
- Tab navigation:
  - **Checkout Luar Area**: 2 sample pending requests
  - **Cuti/Izin**: 1 sample leave request
  - **Konfirmasi Lembur**: Empty state
- Detailed request information:
  - Employee info dengan avatar
  - Check-in/out times dan lokasi
  - Geofence violation distance
  - Alasan dari karyawan
  - Durasi kerja
- Actions: Approve / Reject
- Rejection modal dengan field alasan
- Badge counters untuk pending items

### 6. Laporan (`/reports`)
**Fitur:**
- Filter panel:
  - Periode (Bulanan/Mingguan/Custom)
  - Bulan, Kantor, Karyawan
  - Generate button
- Summary cards: Hari Kerja, Hadir, Terlambat, Lembur, Total Insentif
- Charts:
  - Trend kehadiran bulanan (Line Chart)
  - Distribusi status (Doughnut Chart)
- Tabel laporan detail dengan kolom:
  - Hadir, Terlambat, Total Jam, Lembur
  - Insentif Ontime, Insentif Lembur, Total Insentif
- Export Excel button

## Navigation
**Sidebar menu** (konsisten di semua halaman):
- Dashboard
- Karyawan
- Kehadiran
- Laporan
- Persetujuan (dengan badge counter: 3)

**User section** (bottom sidebar):
- Avatar dengan initial
- Nama dan role
- Logout button

## Data Source
Saat ini menggunakan **sample/mock data** yang sesuai dengan database seeder:
- 4 employees (Admin HRD, Supervisor, Budi Santoso, Siti Rahayu)
- 2 offices (Jakarta, Bandung)
- Sample attendance sessions (untuk demo approvals)
- Sample leave requests

## Current State
✅ **Fully functional UI/UX** dengan navigation lengkap
✅ **Demo authentication** dengan localStorage
✅ **Sample data** dari seeder untuk demonstrasi
✅ **Interactive elements** (filters, modals, tabs, charts)
✅ **Responsive design** dengan Tailwind CSS
✅ **Empty states** untuk kondisi tanpa data

⏳ **Pending API Integration**:
- Login belum menggunakan /api/auth/login (masih localStorage)
- Data employees, attendance, approvals hardcoded (belum fetch dari API)
- CRUD operations (add, edit, delete) belum terintegrasi
- Charts menggunakan dummy data (semua nilai 0)
- Export report belum download file actual

## How to Use

### 1. Start Laravel Herd
```powershell
# Pastikan Herd sudah running
herd status
```

### 2. Access Login Page
Buka browser: `http://absenindotek.test/login`

### 3. Login dengan Test Account
Pilih salah satu quick-fill button atau manual input:
- Email: admin@absensi.com
- Password: password123

### 4. Explore Dashboard
Setelah login, akan redirect ke `/dashboard` dengan view:
- Statistics cards
- Charts (dummy data)
- Recent activity

### 5. Navigate Pages
Gunakan sidebar menu untuk explore:
- **Karyawan**: Lihat list 4 employees dari seeder, coba filter dan modal add/edit
- **Kehadiran**: Lihat empty state (belum ada data check-in hari ini)
- **Laporan**: Generate report dengan filters, lihat charts
- **Persetujuan**: Review 3 pending requests (2 checkout, 1 leave)

### 6. Test Interactions
- Filter data di employees dan attendance
- Klik approve/reject di approvals page
- Switch tabs di approvals
- Try add/edit employee modal
- Logout dan login kembali

## Next Steps (API Integration)

### 1. Implement AuthController
```php
// app/Http/Controllers/Api/AuthController.php
public function login(Request $request) {
    // Validate credentials
    // Generate Sanctum token
    // Return user + token
}
```

### 2. Update Frontend Authentication
Ganti localStorage dengan actual API calls:
```javascript
// login.blade.php
fetch('/api/auth/login', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({email, password})
})
.then(res => res.json())
.then(data => {
    localStorage.setItem('token', data.token);
    localStorage.setItem('user', JSON.stringify(data.user));
    window.location.href = '/dashboard';
});
```

### 3. Fetch Real Data
Ganti hardcoded arrays dengan API calls:
```javascript
// employees.blade.php
fetch('/api/admin/users', {
    headers: {'Authorization': `Bearer ${token}`}
})
.then(res => res.json())
.then(employees => renderEmployees(employees));
```

### 4. Integrate CRUD Operations
Connect form submissions ke API endpoints:
- POST /api/admin/users (create)
- PUT /api/admin/users/{id} (update)
- DELETE /api/admin/users/{id} (delete)

### 5. Real-time Attendance
Connect attendance page ke API:
- GET /api/admin/dashboard/daily (today's attendance)
- Update every 30 seconds untuk real-time monitoring

### 6. Reports Integration
- GET /api/admin/reports/monthly dengan filters
- GET /api/admin/reports/export untuk Excel download

## Technologies Used
- **Tailwind CSS**: Styling framework (via CDN)
- **Chart.js**: Charts visualization (via CDN)
- **Vanilla JavaScript**: Interactivity
- **Laravel Blade**: Templating engine
- **localStorage**: Demo authentication (temporary)

## Notes
- Interface sudah production-ready dari sisi UI/UX
- Architecture memisahkan frontend (Blade views) dan backend (API)
- Easy migration ke API dengan minimal code changes
- Semua placeholder text dalam Bahasa Indonesia
- Color scheme: Blue (primary), Green (success), Yellow (warning), Red (danger)

## Mobile App Note
Interface ini untuk **admin web dashboard** only. Karyawan tetap menggunakan **mobile app** untuk:
- Face recognition check-in/check-out
- GPS location tracking
- Submit late reasons
- Request leave/overtime
- View personal attendance history

Mobile app akan consume same REST API (`/api/attendance/*` endpoints).
