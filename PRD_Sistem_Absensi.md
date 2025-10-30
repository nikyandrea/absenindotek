# PRD — Sistem Absensi Karyawan (Face Recognition + Geolocation)

> **Versi:** 1.0  
> **Dokumen:** Product Requirements Document (PRD)  
> **Aplikasi:** Mobile (Karyawan) & Web (Admin/HRD)

---

## Daftar Isi
1. [Ringkasan & Tujuan](#1-ringkasan--tujuan)  
2. [Ruang Lingkup (MVP)](#2-ruang-lingkup-mvp)  
3. [Persona & Peran](#3-persona--peran)  
4. [Definisi Istilah Utama](#4-definisi-istilah-utama)  
5. [Kebutuhan Fungsional](#5-kebutuhan-fungsional)  
6. [Aturan Bisnis & Perhitungan](#6-aturan-bisnis--perhitungan)  
7. [Alur Pengguna (Ringkas)](#7-alur-pengguna-ringkas)  
8. [Desain Data (Skema Tingkat Tinggi)](#8-desain-data-skema-tingkat-tinggi)  
9. [API Kontrak (Contoh, REST/JSON)](#9-api-kontrak-contoh-restjson)  
10. [UI/UX & Copy (Kutipan Penting)](#10-uiux--copy-kutipan-penting)  
11. [Dashboard & Laporan](#11-dashboard--laporan)  
12. [Non-Fungsional](#12-non-fungsional)  
13. [Anti-Fraud & Kepatuhan](#13-anti-fraud--kepatuhan)  
14. [Edge Cases & Aturan Tambahan](#14-edge-cases--aturan-tambahan)  
15. [Kriteria Penerimaan (MVP)](#15-kriteria-penerimaan-mvp)  
16. [Analitik & Pelacakan (Event)](#16-analitik--pelacakan-event)  
17. [Kebijakan & Parameter yang Dapat Dikonfigurasi](#17-kebijakan--parameter-yang-dapat-dikonfigurasi)  
18. [Risiko & Mitigasi](#18-risiko--mitigasi)  
19. [Rencana Rilis (Fase)](#19-rencana-rilis-fase)  
20. [Contoh Kolom Ekspor Excel](#20-contoh-kolom-ekspor-excel)  
21. [Catatan Implementasi](#21-catatan-implementasi)

---

## 1) Ringkasan & Tujuan
**Masalah:** Perlu pencatatan kehadiran akurat, anti‑titip absen, mendukung mobilitas, dan otomatisasi perhitungan lembur/insentif.  
**Solusi:** Aplikasi (mobile‑first, web admin) yang memungkinkan karyawan absen dari mana saja dengan **face recognition** + **geofence**, mendukung **multi check‑in/out**, **multi‑hari**, serta alur khusus **jam kerja tetap** vs **jam kerja bebas**. Admin/HRD mendapat dashboard, kontrol kebijakan, persetujuan, dan ekspor.

**Sasaran Bisnis**
- Akurasi kehadiran ≥ 99% (geofence & face match valid).  
- Pengurangan kasus “titip absen” ≥ 90%.  
- Penghematan waktu rekap HR ≥ 70%.  
- Peningkatan kepatuhan jadwal (late rate turun ≥ 30% dalam 3 bulan).

---

## 2) Ruang Lingkup (MVP)
**Termasuk**
- Mobile karyawan (iOS/Android PWA/native), Web admin/HRD.
- Face recognition + liveness check.
- Geolocation + geofence kantor/cabang.
- Skema **multi check‑in/out** & **multi‑hari**.
- Alur jam kerja **bebas** & **tetap**.
- Lembur & insentif (luar kota, hari libur, tepat waktu).
- Pengajuan cuti/izin/sakit (mundur & maju).
- Dashboard harian & laporan bulanan per karyawan.
- Ekspor Excel.
- Audit log & approval alur yang dibutuhkan.

**Di luar (fase berikutnya)**
- Integrasi payroll/ERP.
- Shift kompleks multi‑timezone lintas negara.
- Pengelolaan proyek/billing timesheet.

---

## 3) Persona & Peran
- **Karyawan** (Jam Kerja Tetap / Jam Kerja Bebas).  
- **Admin/HRD** (pengaturan, approval, laporan, koreksi data).  
- **Supervisor/Atasan** (opsional, approval & monitoring tim).

---

## 4) Definisi Istilah Utama
- **Jam Aktual:** waktu nyata saat check‑in/out dilakukan.  
- **Jam Valid:** waktu yang dihitung sebagai dasar durasi kerja.  
- **Geofence:** batas lokasi kantor/cabang (radius atau poligon).  
- **Sesi Kerja:** pasangan check‑in → check‑out; sehari bisa >1 sesi.  
- **Hari Kerja Target:** 8 jam (Sen–Jum) atau 5 jam (Sab–Min & hari libur yang ditetapkan admin).  
- **Lembur:** durasi di atas target (atau di luar jam tetap) yang disetujui.

---

## 5) Kebutuhan Fungsional

### 5.1 Autentikasi & Pendaftaran Wajah
- Login: email/nomor HP + OTP atau SSO perusahaan (opsional).  
- Enrol wajah: 3–5 foto (berbagai sudut) + liveness check.  
- Verifikasi wajah setiap **check‑in/out** (toleransi score match dikonfigurasi, mis. ≥ 0,85).  
- Simpan **embedding** (bukan foto mentah) + opsional bukti foto saat absen untuk audit (retensi dikonfigurasi).

### 5.2 Geolocation & Geofence
- Izin lokasi wajib saat absen.  
- Geofence per kantor/cabang (radius default 100 m; bisa diatur per lokasi; dukung poligon).  
- Akurasi minimal GPS (mis. ≤ 50 m); jika lebih buruk → beri peringatan & opsi lanjut dengan flag “butuh review”.  
- **Pesan wajib saat check‑out di luar area:**  
  > **“Anda check-out di luar area kantor dan membutuhkan approval manual dari HRD untuk validasi, pastikan lokasi sesuai dengan penugasan”**  
  Tampilkan pilihan **Lanjut** / **Batal**. Jika **Lanjut**, ajukan:  
  > **“Apakah anda tugas luar kota atau tidak?”** (Ya/Tidak).  
  - Jika **Ya** → tandai status **Luar Kota** untuk insentif & approval HRD.  
  - Jika **Tidak** → tetap lanjut, status **Luar Geofence** untuk review HRD.

### 5.3 Skema Absen (Multi Hari & Multi Sesi)
- **Tidak dibatasi hari:** check‑in hari X boleh di‑check‑out hari Y (lintas tanggal).  
- **Multi sesi per hari:** karyawan dapat check‑in/out berkali‑kali (mis. tugas tambahan). Sistem menjumlahkan durasi semua sesi dalam satu hari kalender (berbasis zona kantor).

### 5.4 Alur Jam Kerja
**A. Karyawan Jam Kerja Bebas**
- Check‑in kapan saja.  
- Saat **check‑out**:  
  - Hitung total durasi kerja harian (akumulasi sesi).  
  - Tentukan target: **8 jam** (Sen–Jum) / **5 jam** (Sab–Min & hari libur).  
  - Jika **kurang**: tampilkan  
    > **“Anda bekerja kurang dari durasi, apakah anda tetap mau check-out?”**  
    Jika lanjut → isi **Detail Pekerjaan Hari Ini** → submit (flag “kurang durasi”).  
  - Jika **lebih**: tampilkan  
    > **“Anda bekerja lebih dari durasi, apakah anda lembur?”**  
    - Jika **Ya** → catat jam keluar aktual sebagai lembur; **Jam Valid** = Jam Aktual; isi detail pekerjaan → submit.  
    - Jika **Tidak** → **Jam Valid** = target (kelebihan tidak dihitung lembur); isi detail pekerjaan → submit.

**B. Karyawan Jam Kerja Tetap**
- Jam **check‑in** ditentukan admin per karyawan/per lokasi/per shift.  
- **Check‑in lebih awal** dari jam masuk:  
  - Tanyakan: **“Apakah anda lembur pagi?” (Ya/Tidak)**  
    - Jika **Ya** → **“Anda lembur pagi untuk mengerjakan apa?”** (isian wajib) → submit. **Jam Valid** dimulai dari jam aktual.  
    - Jika **Tidak** → submit. **Jam Valid** dimulai dari **jam masuk** sesuai setting admin; jam aktual tetap tercatat (untuk audit) namun **tidak** dihitung durasi kerja sampai jam masuk.  
- **Check‑in terlambat** dari jam masuk:  
  - Tanyakan: **“Mengapa anda terlambat?”** (wajib).  
  - Lanjut: **“Apa yang akan anda lakukan agar besok tidak terlambat lagi?”** (wajib).  
  - Setelah submit, tampilkan pop‑up:  
    > **“Anda telah terlambat X kali bulan ini dan anda akan melakukan (rencana karyawan) agar anda tidak telat lagi.”**  
  - Late counter di‑reset tiap awal bulan.  
- **Check‑out** (jam tetap):  
  - Jam Valid berakhir saat check‑out aktual (kecuali kebijakan lain). Bila melebihi jam kerja harian yang ditetapkan → minta konfirmasi “Lembur?” seperti jam bebas.

### 5.5 Detail Pekerjaan Saat Check‑out
- Form **“Detail pekerjaan yang dilakukan hari ini”** (teks bebas, 10–500 karakter).  
- Terlihat di ringkasan harian & laporan.

### 5.6 Tampilan Absen & Lembur
- Riwayat absen dengan **filter bulanan** (default: bulan berjalan).  
- Ringkasan: total hari hadir, total jam kerja, total lembur, total keterlambatan, status luar kota/luar geofence yang masih pending.  
- Perhitungan lembur otomatis berdasar aturan (lihat §6).

### 5.7 Pengajuan Cuti/Izin/Sakit/Libur
- Ajukan **ke depan** maupun **ke belakang** (retroaktif) agar data harian lengkap.  
- Tipe: Cuti Tahunan, Izin, Sakit (opsi lampiran surat dokter), Libur Khusus.  
- Status: Draft → Diajukan → Disetujui/Ditolak.  
- Kuota cuti tahunan per karyawan diatur admin; sisa kuota terlihat di aplikasi.  
- Jika disetujui, sistem otomatis menandai hari tsb dan mengecualikan dari keterlambatan/durasi kerja.

### 5.8 Fitur Admin/HRD
- CRUD karyawan (aktif/nonaktif).  
- Kelola **lokasi kantor & cabang** + geofence.  
- Dashboard **harian** (semua karyawan): hadir/tidak, terlambat, di luar geofence, luar kota, lembur, ringkasan jam.  
- **Laporan bulanan** per karyawan (jam kerja, lembur, insentif, potongan, keterlambatan, cuti/izin/sakit).  
- Tetapkan tipe jam kerja per karyawan: **bebas** atau **tetap**; atur jam masuk/jam pulang (jam tetap).  
- Kelola **hari libur** (daftar nasional & khusus perusahaan/lokasi).  
- Tetapkan **kuota cuti** tahunan per karyawan.  
- **Approve**: cuti/izin/sakit; absen di luar geofence; lembur (jika kebijakan perlu approval).  
- Set nilai **nominal lembur/jam** per karyawan.  
- Set **insentif luar kota** (per hari) & **insentif hari libur** (per hari).  
- Set **insentif harian kehadiran tepat waktu** (karyawan jam tetap). **Kebijakan:** jika terlambat > 3× dalam 1 bulan, insentif **bulanan** otomatis hangus (opsi konfigurasi: hangus total atau pro‑rata).  
- Tambah **insentif lain** / **potongan manual** ke laporan bulanan karyawan (tiap entri wajib ada alasan).  
- Tambah/hapus/revisi data kehadiran (audit required).  
- **Ekspor Excel** laporan karyawan.

### 5.9 Notifikasi
- Push/in‑app: pengingat check‑in (jam tetap), pengingat check‑out, approval status, peringatan di luar geofence, keterlambatan X kali, lembur perlu konfirmasi/approval.

---

## 6) Aturan Bisnis & Perhitungan

### 6.1 Penentuan Target Durasi Harian (Jam Bebas)
- **Sen–Jum:** Target = **8:00** jam.  
- **Sab–Min & Hari Libur:** Target = **5:00** jam.  
- Durasi harian = Σ semua **(check‑out − check‑in)** dalam tanggal kalender kantor.

### 6.2 Jam Kerja Tetap
- **Jam Valid Mulai:**  
  - Early & **Lembur Pagi = Ya** → jam aktual.  
  - Early & **Lembur Pagi = Tidak** → jam masuk (setting admin).  
  - **Terlambat** → jam aktual (tetap dihitung terlambat = jam aktual − jam masuk).  
- **Jam Valid Selesai:** jam check‑out aktual (kecuali kebijakan khusus).  
- **Durasi Kerja Valid** = Jam Valid Selesai − Jam Valid Mulai − (istirahat otomatis, jika diset admin — opsional).

### 6.3 Lembur
- **Jam Bebas:** jika durasi > target dan karyawan konfirmasi “Lembur = Ya”, **lembur = durasi − target**. Jika “Tidak”, lembur = 0 (kelebihan diabaikan).  
- **Jam Tetap:** lembur = durasi valid di atas jam kerja harian yang ditetapkan (atau di luar jam masuk/keluar yang diset).  
- Tarif lembur/jam ditetapkan per karyawan di admin. **Nominal lembur = jam lembur × tarif/jam** (pembulatan ke 0,5 jam, dikonfigurasi).

### 6.4 Insentif
- **Luar Kota (per hari):** jika sesi hari itu berstatus “Luar Kota” (dari alur luar geofence) dan disetujui HRD → tambahkan insentif luar kota.  
- **Hari Libur (per hari):** jika bekerja di tanggal yang ditandai libur → tambahkan insentif hari libur.  
- **Kehadiran Tepat Waktu (jam tetap):** insentif harian ditambahkan hanya saat check‑in **≤ jam masuk**. Jika total keterlambatan **> 3×** dalam 1 bulan, insentif **bulanan** hangus otomatis (kebijakan dapat diubah admin: hangus total / pro‑rata / tidak hangus).

### 6.5 Keterlambatan
- Tercatat saat jam aktual > jam masuk. Sistem menyimpan **alasan** & **rencana** per kejadian. Pop‑up info “telah terlambat X kali bulan ini”.

### 6.6 Validasi Lokasi
- **Di luar geofence:** flag “butuh approval”. Admin dapat set: auto‑approve jika “Luar Kota = Ya” & ada Surat Tugas; jika tidak, manual review.

---

## 7) Alur Pengguna (Ringkas)

**Check‑in (semua tipe)**  
1. Buka aplikasi → verifikasi wajah + lokasi → hasil match OK + di dalam geofence → Check‑in sukses.  
2. Jika akurasi lokasi buruk → prompt lanjut dengan flag review.

**Check‑out (jam bebas)**  
1. Verifikasi wajah + lokasi → hitung durasi hari ini.  
2. Jika < target → tampilkan peringatan “kurang durasi” (lanjut/batal).  
3. Jika > target → tanya “lembur?” (Ya/Tidak).  
4. Isi **Detail Pekerjaan** → submit.

**Check‑in lebih awal (jam tetap)**  
- Tanyakan “Lembur pagi?” → jika Ya, alasan lembur; set Jam Valid mulai = aktual. Jika Tidak, Jam Valid mulai = jam masuk.

**Check‑in terlambat (jam tetap)**  
- Tanyakan **alasan** & **rencana** → pop‑up rekap jumlah terlambat.

**Check‑out di luar geofence (semua tipe)**  
- Tampilkan pesan wajib → jika **Lanjut** → tanya “Tugas luar kota?” → flag untuk approval & insentif.

**Pengajuan Cuti/Izin/Sakit**  
- Pilih tipe, rentang tanggal, alasan (+ lampiran), submit → menunggu approval HRD.

---

## 8) Desain Data (Skema Tingkat Tinggi)

**Tabel inti**
- `users` (id, nama, email, hp, role, status_aktif, tipe_jam_kerja, kantor_id, tarif_lembur_per_jam, insentif_tepat_waktu_per_hari, insentif_luar_kota_per_hari, insentif_hari_libur_per_hari, …)  
- `offices` (id, nama, timezone, geofence_type [radius/polygon], radius_m, polygon_geojson, alamat)  
- `holidays` (id, tanggal, nama, office_id/global, tipe [nasional/perusahaan])  
- `schedules` (id, user_id, jam_masuk, jam_pulang, hari_aktif_mask, aturan_istirahat_opsional)  
- `face_profiles` (user_id, embedding, liveness_threshold, created_at)  
- `attendance_sessions` (id, user_id, check_in_at, check_in_loc, check_in_face_score, check_out_at, check_out_loc, check_out_face_score, status_loc [in/out_geofence], luar_kota_bool, needs_human_approval_bool, detail_pekerjaan_text, source_device_info)  
- `attendance_daily` (id, user_id, tanggal, total_durasi_valid, total_durasi_aktual, target_durasi, lembur_jam, lembur_nominal, flags: kurang_durasi/tepat_waktu/terlambat_count, notes)  
- `late_events` (id, user_id, tanggal, menit_terlambat, alasan, rencana)  
- `leave_requests` (id, user_id, tipe, start_date, end_date, alasan, lampiran_url, status, approver_id, decided_at)  
- `incentive_adjustments` (id, user_id, bulan, tipe [luar_kota/hari_libur/tepat_waktu/lain], nominal, alasan, source [auto/manual], related_id)  
- `deduction_adjustments` (id, user_id, bulan, nominal, alasan)  
- `audit_logs` (id, actor_id, action, entity, entity_id, before_json, after_json, at)  
- `export_jobs` (id, bulan, status, file_url, requested_by)

> **Catatan:** `attendance_daily` di‑generate dari `attendance_sessions` per tanggal (rebuilder harian/idempotent).

---

## 9) API Kontrak (Contoh, REST/JSON)
- `POST /auth/login` (OTP/SSO)  
- `POST /face/enroll`  
- `POST /attendance/check-in` → body: (timestamp, lat, lng, accuracy, photo_face?, device), response: `session_id`  
- `POST /attendance/check-out` → body: (session_id?, timestamp, lat, lng, accuracy, photo_face?, luar_geofence_continue?, tugas_luar_kota?, detail_pekerjaan, lembur_konfirmasi?)  
- `GET /attendance/daily?user_id&month=YYYY-MM`  
- `POST /leave` (create), `PATCH /leave/{id}` (approve/reject)  
- `GET /reports/monthly?user_id|team_id&month=YYYY-MM`  
- `POST /admin/users`, `PATCH /admin/users/{id}`  
- `POST /admin/offices`, `PATCH /admin/offices/{id}`  
- `POST /admin/holidays`  
- `POST /admin/adjustments` (insentif/potongan)  
- `POST /admin/attendance/correct` (tambah/hapus/revisi sesi)  
- `POST /exports/monthly?month=YYYY-MM`

---

## 10) UI/UX & Copy (Kutipan Penting)

- **Di luar area** (checkout):  
  > **“Anda check-out di luar area kantor dan membutuhkan approval manual dari HRD untuk validasi, pastikan lokasi sesuai dengan penugasan”** → **[Lanjut] [Batal]**  
  Jika lanjut: **“Apakah anda tugas luar kota atau tidak?”** → **[Ya] [Tidak]**

- **Jam tetap, early:** **“Apakah anda lembur pagi? (Ya/Tidak)”** → **“Anda lembur pagi untuk mengerjakan apa?”**

- **Jam tetap, terlambat:**  
  > **“Mengapa anda terlambat?”** → **“Apa yang akan anda lakukan agar besok tidak terlambat lagi?”**  
  Pop‑up: **“Anda telah terlambat X kali bulan ini dan anda akan melakukan (rencana) agar anda tidak telat lagi.”**

- **Jam bebas, kurang durasi:**  
  > **“Anda bekerja kurang dari durasi, apakah anda tetap mau check-out?”**

- **Jam bebas, lebih durasi:**  
  > **“Anda bekerja lebih dari durasi, apakah anda lembur?”**

**Catatan UX**
- Tampilkan **jam aktual** & **jam valid** di ringkasan sesi agar transparan.  
- Badge **“Luar Geofence”** / **“Luar Kota”** pada kartu sesi.  
- Indikator akurasi GPS & panduan perbaikan (aktifkan high accuracy, nyalakan lokasi, pindah area terbuka).

---

## 11) Dashboard & Laporan

**Dashboard Harian (Admin/HRD)**
- Kartu ringkasan: Total hadir, terlambat, lembur, luar geofence (butuh approval), on leave.  
- Tabel: Nama, jam masuk (aktual & valid), status (ontime/late), lokasi (ikon geofence), total durasi hari berjalan, catatan.

**Laporan Bulanan per Karyawan**
- Kolom contoh: Tanggal, tipe hari (Kerja/Libur), target durasi, total durasi aktual, total durasi **valid**, terlambat (menit), lembur (jam & nominal), insentif (tepat waktu/luar kota/hari libur/lain), potongan manual, catatan.  
- Ringkasan atas: total jam kerja valid, total lembur & nominal, total insentif, total potongan, keterlambatan (count & total menit).  
- **Ekspor Excel:** format `.xlsx`; sheet1 ringkasan, sheet2 detil harian, sheet3 penyesuaian.

---

## 12) Non‑Fungsional

**Keamanan & Privasi**
- Enkripsi at‑rest (AES‑256) & in‑transit (TLS 1.2+).  
- Simpan **embedding wajah** bukan foto; foto bukti absensi opsional dengan retensi (mis. 90 hari) dan akses terbatas.  
- Liveness detection (anti-foto/video). Deteksi perangkat di‑root/jailbreak & **mock location**.  
- Izin & transparansi sesuai regulasi (persetujuan biometrik, kebijakan privasi, hak hapus data saat karyawan nonaktif).

**Kinerja**
- Operasi check‑in/out ≤ 2 detik (jika jaringan normal).  
- Tahan beban 5× puncak jam masuk (autoscaling).

**Reliabilitas**
- Offline mode: cache lokasi & foto; “check‑in/out sementara” disimpan lokal dengan timestamp & coarse location; disinkronkan saat online dan ditandai “perlu review” jika akurasi tidak memenuhi.

**Kompatibilitas**
- iOS/Android modern; web admin desktop. Dukungan dark mode (opsional).

**Auditability**
- Semua perubahan manual (HRD) tercatat lengkap.

---

## 13) Anti‑Fraud & Kepatuhan
- Face match + liveness + deteksi spoofing.  
- Cek **mock location** (Android developer options), anomali koordinat, dan perbedaan IP/geohash.  
- Batas jarak minimum perpindahan yang wajar per sesi (heuristik).  
- Kebijakan retensi & penghapusan data biometrik untuk karyawan nonaktif.

---

## 14) Edge Cases & Aturan Tambahan
- Check‑out tanpa check‑in: tampilkan “tidak ada sesi aktif”; tawarkan **lapor HRD** untuk koreksi.  
- Sesi lintas tengah malam: tanggal **attendance_daily** mengikuti jam kantor (timezone `offices.timezone`).  
- Perubahan jadwal di tengah bulan: gunakan versi jadwal efektif per tanggal.  
- Akurasi GPS buruk di gedung tinggi: minta foto bukti/QR lokasi (opsional fase berikutnya).  
- Dinas luar beberapa hari: tandai semua sesi pada rentang itu sebagai **Luar Kota** (berdasar surat tugas).  
- Konflik sesi (overlap): blokir pada sumber, atau normalisasi oleh proses konsolidasi harian; log ke audit.

---

## 15) Kriteria Penerimaan (MVP)

**Karyawan**
- Dapat check‑in/out sukses dengan face+geo; alur pesan di luar geofence muncul sesuai deskripsi.  
- Jam tetap: alur **lembur pagi** & **terlambat** muncul, dan Jam Valid dihitung tepat.  
- Jam bebas: peringatan **kurang/lebih durasi** muncul & memengaruhi perhitungan lembur.  
- Dapat melakukan >1 sesi per hari & lintas hari tanpa error.  
- Dapat mengajukan cuti/izin/sakit (lampiran opsional), melihat status.

**Admin/HRD**
- Dapat menambah/edit/nonaktifkan karyawan.  
- Dapat menambah lokasi kantor & geofence.  
- Dapat set jam kerja (tetap/bebas), hari libur, kuota cuti, tarif lembur, insentif.  
- Dapat approve pengajuan & absen luar geofence.  
- Dapat koreksi data kehadiran, semuanya tercatat di audit log.  
- Dapat melihat dashboard harian & laporan bulanan; **ekspor Excel** berisi metrik & detail sesuai §11.

---

## 16) Analitik & Pelacakan (Event)
- `check_in_success` (lat,lng,accuracy,face_score,in_geofence)  
- `check_out_success` (… , durasi_hari, target, lembur_konfirmasi)  
- `outside_geofence_prompt_shown` / `proceed_outside_geofence`  
- `late_reason_submitted` / `early_overtime_confirmed`  
- `leave_submitted` / `leave_approved`  
- `report_exported`  
- Funnel error: `face_fail`, `gps_fail`, `network_fail`.

---

## 17) Kebijakan & Parameter yang Dapat Dikonfigurasi
- Radius & tipe geofence per lokasi.  
- Batas akurasi GPS & waktu tunggu perbaikan.  
- Threshold face match & liveness.  
- Pembulatan lembur (15/30 menit).  
- Kebijakan **insentif tepat waktu** saat >3 kali terlambat: **hangus total / pro‑rata / tidak hangus** (default: **hangus total**).  
- Retensi foto bukti & log.

---

## 18) Risiko & Mitigasi
- **GPS tidak akurat** → pengaturan toleransi + bukti tambahan + review HRD.  
- **False negative face match** → kalibrasi threshold, fallback manual (PIN + foto bukti) dengan flag review.  
- **Keberatan privasi** → transparansi, consent, penyimpanan embedding & retensi terbatas.  
- **Beban jam sibuk** → autoscaling, prefetch, queue.

---

## 19) Rencana Rilis (Fase)
1) **MVP:** alur absen inti, jam tetap/bebas, geofence radius, laporan & ekspor, cuti/izin/sakit, insentif/lembur dasar, approval.  
2) **Fase 2:** geofence poligon, integrasi kalender libur nasional, integrasi payroll, QR lokasi (opsional), supervisor layer.  
3) **Fase 3:** timesheet proyek, analitik keterlambatan prediktif, SSO perusahaan.

---

## 20) Contoh Kolom Ekspor Excel
- **Sheet Ringkasan:** Nama, NIK, Bulan, Total Jam Valid, Total Lembur (jam/nominal), Total Insentif (rincian), Potongan, Terlambat (kali/menit), Cuti/Izin/Sakit (hari).  
- **Sheet Harian:** Tanggal, Tipe Hari, Jam Masuk (aktual/valid), Jam Keluar (aktual), Durasi Aktual, Durasi Valid, Target, Lembur Jam, Lembur Nominal, Status Geofence, Luar Kota, Detail Pekerjaan.  
- **Sheet Penyesuaian:** Tanggal, Tipe (insentif/potongan), Nominal, Alasan, Pembuat, Waktu.

---

## 21) Catatan Implementasi
- Gunakan **“attendance_sessions → konsolidasi harian”** untuk akurasi dan rekalkulasi mudah.  
- Tampilan **Jam Valid** vs **Jam Aktual** harus jelas agar kebijakan mudah diaudit.  
- Semua “keputusan” sistem yang mengurangi/menambah hak karyawan (mis. hangus insentif) **harus** terlacak & dapat dijelaskan (explainable).
