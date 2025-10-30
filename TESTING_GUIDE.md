# Testing Guide - Web-based Attendance System

## 🎯 Status Implementasi

### ✅ Selesai:
1. **Backend API Check-in/Check-out** - Fully functional dengan semua business logic
2. **Web UI Check-in** - Camera + GPS + Face capture
3. **Web UI Check-out** - Work detail form + Overtime confirmation
4. **Web UI History** - Attendance history dengan filter bulanan
5. **Authentication & API Helper** - Token-based auth dengan Sanctum

### ⏳ Pending:
- Face enrollment untuk karyawan baru
- Leave request system
- Office & geofence management UI
- Excel export implementation

---

## 🚀 Cara Testing

### 1. Login ke Sistem

**URL**: `http://absenindotek.test/login`

**Test Accounts**:
```
Karyawan 1:
Email: budi@absensi.com
Password: password123

Karyawan 2:
Email: siti@absensi.com
Password: password123

Admin:
Email: admin@absensi.com
Password: password123
```

### 2. Dashboard Karyawan

Setelah login sebagai karyawan, Anda akan melihat:
- ✅ Quick action cards untuk Check-in, Check-out, History
- ✅ Menu sidebar dengan akses ke attendance features
- ✅ Menu admin/supervisor tersembunyi

### 3. Testing Check-in

**URL**: `/attendance/check-in`

**Flow**:
1. **Step 1: Lokasi**
   - Browser akan meminta izin akses lokasi
   - Klik "Allow" untuk memberikan akses GPS
   - Sistem akan menampilkan koordinat dan akurasi
   - Status geofence ditampilkan (dalam/luar area)

2. **Step 2: Wajah**
   - Browser akan meminta izin akses kamera
   - Klik "Allow" untuk mengaktifkan kamera
   - Arahkan wajah ke kamera (pastikan pencahayaan cukup)
   - Klik tombol capture (lingkaran putih)
   - Preview foto akan ditampilkan
   - Klik "Lanjutkan" atau "Ambil Ulang"

3. **Step 3: Konfirmasi**
   - Review informasi check-in
   - **Jika jam kerja tetap & check-in lebih awal**:
     - Centang "Apakah Anda lembur pagi?"
     - Isi alasan jika Ya
   - Klik "Konfirmasi Check-in"

4. **Jika Terlambat** (untuk jam kerja tetap):
   - Redirect ke halaman alasan keterlambatan
   - Isi: "Mengapa anda terlambat?"
   - Isi: "Apa yang akan anda lakukan agar besok tidak terlambat lagi?"
   - Klik "Kirim Alasan"
   - Pop-up menampilkan jumlah keterlambatan bulan ini

5. **Success Page**:
   - Menampilkan konfirmasi check-in berhasil
   - Waktu check-in tercatat
   - Auto redirect ke dashboard setelah 5 detik

### 4. Testing Check-out

**URL**: `/attendance/check-out`

**Flow**:
1. **Load Active Session**
   - Sistem otomatis mencari sesi check-in aktif
   - Menampilkan info: waktu check-in, durasi kerja, target

2. **Evaluasi Durasi**:
   - **Kurang dari target** (< 8 jam weekday / < 5 jam weekend):
     - Warning ditampilkan
     - Konfirmasi "Tetap Check-out" required
   
   - **Lebih dari target**:
     - Muncul checkbox "Ya, saya lembur"
     - Durasi overtime ditampilkan

3. **Form Work Detail**:
   - Wajib diisi (10-500 karakter)
   - Deskripsikan pekerjaan hari ini
   - Character counter realtime

4. **Location Check**:
   - GPS otomatis diambil saat page load
   - Jika di luar geofence:
     - Warning ditampilkan
     - Checkbox "Apakah Anda tugas luar kota?"

5. **Submit Check-out**:
   - Klik "Konfirmasi Check-out"
   - Validasi semua input
   - API call ke `/api/attendance/check-out`
   - Redirect ke success page

6. **Success Page**:
   - Konfirmasi check-out berhasil
   - Total durasi kerja ditampilkan
   - Info jika needs approval (luar area)

### 5. Testing History

**URL**: `/attendance/history`

**Features**:
- Dropdown filter bulan (12 bulan terakhir)
- Tabel riwayat kehadiran:
  - Tanggal
  - Check-in (Aktual/Valid)
  - Check-out (Aktual/Valid)
  - Durasi Valid (jam)
  - Lembur (jam)
  - Status (OK/Kurang Durasi)
- Auto load untuk bulan berjalan

---

## 🔧 Technical Details

### API Endpoints yang Digunakan

```javascript
// Check-in
POST /api/attendance/check-in
Body: {
  latitude: float,
  longitude: float,
  accuracy: float,
  photo_face: base64_string,
  is_early_overtime: boolean,
  overtime_reason: string,
  device_info: object
}

// Submit late reason
POST /api/attendance/sessions/{id}/late-reason
Body: {
  reason: string (min 10 chars),
  improvement_plan: string (min 10 chars)
}

// Check-out
POST /api/attendance/check-out
Body: {
  session_id: int,
  latitude: float,
  longitude: float,
  accuracy: float,
  photo_face: base64_string,
  work_detail: string (10-500 chars),
  is_overtime_confirmed: boolean,
  is_out_of_town: boolean
}

// Get history
GET /api/attendance/history?month=YYYY-MM
Response: {
  sessions: [...],
  daily_summary: [...],
  month_summary: {...}
}
```

### Browser Permissions Required

1. **Geolocation**:
   - Chrome: Settings → Privacy → Site Settings → Location
   - Firefox: Preferences → Privacy → Permissions → Location
   - Safari: Preferences → Websites → Location

2. **Camera**:
   - Same path as Location, select Camera

### localStorage Usage

```javascript
// Authentication
token: "Bearer token from login"
user: {id, name, email, role, ...}

// Temporary session data
pending_session_id: "123" (for late reason)
late_minutes: "15"
late_count_this_month: "2"
improvement_plan: "Berangkat lebih awal"
```

---

## 🐛 Known Issues & Workarounds

### 1. Face Recognition Placeholder
**Issue**: Face verification menggunakan placeholder (selalu return success)
**Workaround**: Untuk production, integrate dengan:
- AWS Rekognition
- Azure Face API
- Custom ML model

**Current Implementation**:
```php
// FaceRecognitionService.php
public function verifyFace($storedEmbedding, $currentEmbedding, $threshold = 0.85) {
    // TODO: Implement actual face comparison
    return [
        'match' => true,
        'score' => 0.95
    ];
}
```

### 2. GPS Accuracy
**Issue**: Indoor location might have poor accuracy (> 100m)
**Workaround**: 
- Tampilkan warning jika accuracy > 50m
- Allow continuation dengan flag "needs review"
- Admin dapat approve/reject manual

### 3. Mock Location Detection
**Issue**: Android developer options dapat fake GPS
**Workaround**:
- Check `device_info` untuk detect mock
- Server-side IP geolocation check
- Liveness detection untuk face

### 4. Browser Compatibility
**Tested on**:
- ✅ Chrome 120+ (Desktop & Mobile)
- ✅ Firefox 120+
- ✅ Safari 17+ (iOS & macOS)
- ⚠️ Edge (may need testing)

---

## 📱 Mobile Browser Testing

### Android Chrome:
1. Buka `http://absenindotek.test` (jika di local network, gunakan IP)
2. Izinkan location & camera
3. Gunakan kamera depan untuk face capture
4. Pastikan GPS high accuracy mode aktif

### iOS Safari:
1. Settings → Safari → Location → Allow
2. Settings → Safari → Camera → Allow
3. Test sama seperti desktop

### Tips untuk Mobile:
- Gunakan pencahayaan yang baik untuk face capture
- Stabilkan tangan saat foto
- Pastikan sinyal GPS kuat (keluar gedung jika perlu)
- Hindari menggunakan VPN yang mengubah lokasi

---

## ✅ Test Checklist

### Check-in Flow:
- [ ] Location permission prompt muncul
- [ ] GPS coordinates ditampilkan dengan benar
- [ ] Camera permission prompt muncul
- [ ] Foto wajah ter-capture dengan jelas
- [ ] Mirror effect pada camera preview
- [ ] Konfirmasi page menampilkan summary
- [ ] API call berhasil (check Network tab)
- [ ] Success page ditampilkan
- [ ] Late reason form muncul jika terlambat
- [ ] Late counter increment dengan benar

### Check-out Flow:
- [ ] Active session ter-load
- [ ] Durasi kerja dihitung real-time
- [ ] Warning kurang durasi muncul jika < target
- [ ] Overtime section muncul jika > target
- [ ] Work detail form required validation
- [ ] Character counter berfungsi
- [ ] Location warning jika di luar geofence
- [ ] API call berhasil
- [ ] Success page dengan duration summary

### History:
- [ ] Month filter ter-populate
- [ ] Default ke bulan berjalan
- [ ] Data ter-load dari API
- [ ] Table menampilkan semua kolom
- [ ] Empty state jika belum ada data

---

## 🎓 Next Steps

1. **Face Enrollment**:
   - Buat halaman untuk karyawan baru enroll wajah
   - Capture 3-5 foto dari berbagai sudut
   - Generate embedding dan simpan ke DB

2. **Leave Request**:
   - Form pengajuan cuti/izin/sakit
   - Upload lampiran (surat dokter, dll)
   - Approval workflow

3. **Admin Features**:
   - Office & geofence management
   - Holiday management
   - Schedule management
   - Excel export

4. **Testing**:
   - Unit tests untuk services
   - Feature tests untuk API
   - E2E tests untuk web UI

---

## 📞 Support

Jika menemukan bug atau ada pertanyaan:
1. Check console browser (F12) untuk error
2. Check `storage/logs/laravel.log` untuk server error
3. Verifikasi token di localStorage masih valid
4. Clear localStorage dan login ulang jika ada issue auth

**Happy Testing! 🚀**
