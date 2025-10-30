<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Absensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="/js/api-helper.js"></script>
</head>
<body class="bg-gray-50">
    <!-- Sidebar -->
    <div class="fixed inset-y-0 left-0 w-64 bg-gray-900 text-white">
        <div class="p-6">
            <h1 class="text-xl font-bold">Sistem Absensi</h1>
            <p class="text-sm text-gray-400">PT Indotek</p>
        </div>

        <nav class="mt-6">
            <a href="/dashboard" class="flex items-center px-6 py-3 bg-gray-800 border-l-4 border-blue-500">
                <i class="fas fa-home w-5 mr-3"></i>
                Dashboard
            </a>
            <a href="/employees" data-role-required="admin,supervisor" class="flex items-center px-6 py-3 hover:bg-gray-800 transition">
                <i class="fas fa-users w-5 mr-3"></i>
                Karyawan
            </a>
            <a href="/offices" data-role-required="admin" class="flex items-center px-6 py-3 hover:bg-gray-800 transition">
                <i class="fas fa-building w-5 mr-3"></i>
                Kantor
            </a>
            <a href="/attendance" class="flex items-center px-6 py-3 hover:bg-gray-800 transition" data-role-required="admin,supervisor">
                <i class="fas fa-clipboard-check w-5 mr-3"></i>
                Kehadiran
            </a>
            
            <!-- Employee Menu (Dropdown) -->
            <div data-role-show="karyawan">
                <!-- Kehadiran Dropdown -->
                <div>
                    <button onclick="toggleAttendanceMenu()" class="w-full flex items-center justify-between px-6 py-3 hover:bg-gray-800 transition text-left">
                        <div class="flex items-center">
                            <i class="fas fa-clipboard-check w-5 mr-3"></i>
                            Kehadiran
                        </div>
                        <i id="attendanceMenuIcon" class="fas fa-chevron-up transition-transform"></i>
                    </button>
                    <div id="attendanceSubmenu" class="bg-gray-800" style="display: block;">
                        <a href="/face/enroll" class="flex items-center px-6 py-2 pl-14 hover:bg-gray-700 transition">
                            <i class="fas fa-user-circle w-4 mr-3"></i>
                            Daftar Wajah
                        </a>
                        <a href="/attendance/check-in" class="flex items-center px-6 py-2 pl-14 hover:bg-gray-700 transition">
                            <i class="fas fa-sign-in-alt w-4 mr-3"></i>
                            Check-in
                        </a>
                        <a href="/attendance/check-out" class="flex items-center px-6 py-2 pl-14 hover:bg-gray-700 transition">
                            <i class="fas fa-sign-out-alt w-4 mr-3"></i>
                            Check-out
                        </a>
                        <a href="/attendance/history" class="flex items-center px-6 py-2 pl-14 hover:bg-gray-700 transition">
                            <i class="fas fa-history w-4 mr-3"></i>
                            Riwayat Kehadiran
                        </a>
                    </div>
                </div>

                <!-- Cuti/Izin Menu -->
                <a href="/leave/request" class="flex items-center px-6 py-3 hover:bg-gray-800 transition">
                    <i class="fas fa-calendar-plus w-5 mr-3"></i>
                    Ajukan Cuti/Izin
                </a>
                <a href="/leave/history" class="flex items-center px-6 py-3 hover:bg-gray-800 transition">
                    <i class="fas fa-calendar-check w-5 mr-3"></i>
                    Riwayat Cuti/Izin
                </a>
            </div>
            
            <a href="/reports" data-role-required="admin,supervisor" class="flex items-center px-6 py-3 hover:bg-gray-800 transition">
                <i class="fas fa-chart-bar w-5 mr-3"></i>
                Laporan
            </a>
            <a href="/reports-monthly" data-role-required="admin,supervisor" class="flex items-center px-6 py-3 hover:bg-gray-800 transition">
                <i class="fas fa-file-invoice w-5 mr-3"></i>
                Laporan Bulanan
            </a>
            <a href="/approvals" data-role-required="admin,supervisor" class="flex items-center px-6 py-3 hover:bg-gray-800 transition">
                <i class="fas fa-check-circle w-5 mr-3"></i>
                Persetujuan
                <span id="badgeApprovals" class="ml-auto bg-gray-300 text-gray-700 text-xs px-2 py-1 rounded-full">0</span>
            </a>
        </nav>

        <div class="absolute bottom-0 w-64 p-6 border-t border-gray-800">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold">
                    A
                </div>
                <div class="ml-3">
                    <p class="text-sm font-semibold" id="userName">Admin HRD</p>
                    <p class="text-xs text-gray-400" id="userRole">Administrator</p>
                </div>
            </div>
            <button onclick="logout()" class="mt-4 w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg text-sm transition flex items-center justify-center">
                <i class="fas fa-sign-out-alt mr-2"></i>
                Logout
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="ml-64 p-8">
        <!-- Header -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800">Dashboard</h2>
            <p class="text-gray-600">Selamat datang di sistem absensi karyawan</p>
        </div>

        <!-- Quick Actions for Employees -->
        <div id="employeeQuickActions" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8" style="display:none;">
            <a href="/face/enroll" class="block bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl shadow-lg p-6 hover:shadow-xl transition transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <i class="fas fa-user-circle text-4xl"></i>
                    <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full text-sm">Baru</span>
                </div>
                <h3 class="text-xl font-bold mb-2">Daftar Wajah</h3>
                <p class="text-sm text-purple-100">Pendaftaran wajah untuk verifikasi</p>
            </a>

            <a href="/attendance/check-in" class="block bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl shadow-lg p-6 hover:shadow-xl transition transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <i class="fas fa-sign-in-alt text-4xl"></i>
                    <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full text-sm">Mulai Kerja</span>
                </div>
                <h3 class="text-xl font-bold mb-2">Check-in</h3>
                <p class="text-sm text-green-100">Absen masuk dengan face recognition & GPS</p>
            </a>

            <a href="/attendance/check-out" class="block bg-gradient-to-br from-red-500 to-red-600 text-white rounded-xl shadow-lg p-6 hover:shadow-xl transition transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <i class="fas fa-sign-out-alt text-4xl"></i>
                    <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full text-sm">Selesai Kerja</span>
                </div>
                <h3 class="text-xl font-bold mb-2">Check-out</h3>
                <p class="text-sm text-red-100">Absen pulang dan isi detail pekerjaan</p>
            </a>

            <a href="/attendance/history" class="block bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl shadow-lg p-6 hover:shadow-xl transition transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <i class="fas fa-history text-4xl"></i>
                    <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full text-sm">Riwayat</span>
                </div>
                <h3 class="text-xl font-bold mb-2">Riwayat Kehadiran</h3>
                <p class="text-sm text-blue-100">Lihat rekap absensi bulanan Anda</p>
            </a>
        </div>

        <!-- Leave Quota Card for Employees -->
        <div id="employeeLeaveQuota" class="mb-8" style="display:none;">
            <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl shadow-lg p-6 border-2 border-indigo-200">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-xl font-bold text-indigo-900 mb-1">
                            <i class="fas fa-calendar-check mr-2"></i>
                            Cuti Tahunan Anda
                        </h3>
                        <p class="text-sm text-indigo-700">Kelola cuti dan izin Anda</p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-indigo-700 mb-1">Sisa Cuti</p>
                        <p class="text-5xl font-bold text-indigo-900" id="leaveQuotaRemaining">-</p>
                        <p class="text-sm text-indigo-600">dari <span id="leaveQuotaTotal">-</span> hari</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-3 gap-3 mb-4">
                    <div class="bg-white rounded-lg p-3 text-center shadow">
                        <p class="text-xs text-gray-600 mb-1">Digunakan</p>
                        <p class="text-2xl font-bold text-gray-700" id="leaveQuotaUsed">-</p>
                    </div>
                    <div class="bg-white rounded-lg p-3 text-center shadow">
                        <p class="text-xs text-gray-600 mb-1">Pending</p>
                        <p class="text-2xl font-bold text-yellow-600" id="leaveQuotaPending">-</p>
                    </div>
                    <div class="bg-white rounded-lg p-3 text-center shadow">
                        <p class="text-xs text-gray-600 mb-1">Tersedia</p>
                        <p class="text-2xl font-bold text-green-600" id="leaveQuotaAvailable">-</p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <a href="/leave/request" class="flex-1 bg-indigo-600 text-white py-3 rounded-lg font-semibold text-center hover:bg-indigo-700 transition">
                        <i class="fas fa-plus mr-2"></i>
                        Ajukan Cuti/Izin
                    </a>
                    <a href="/leave/history" class="flex-1 bg-white text-indigo-600 py-3 rounded-lg font-semibold text-center hover:bg-indigo-50 transition border-2 border-indigo-600">
                        <i class="fas fa-list mr-2"></i>
                        Riwayat
                    </a>
                </div>
            </div>
        </div>

        <!-- Monthly Report for Employees -->
        <div id="employeeMonthlyReport" class="mb-8" style="display:none;">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800">
                            <i class="fas fa-file-invoice mr-2"></i>
                            Laporan Kehadiran Bulanan
                        </h3>
                        <p class="text-gray-600">Laporan kehadiran, lembur, dan insentif bulanan Anda</p>
                    </div>
                    <div class="flex gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun</label>
                            <select id="employeeYearFilter" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                <!-- Will be populated by JS -->
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Bulan</label>
                            <select id="employeeMonthFilter" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                <option value="1">Januari</option>
                                <option value="2">Februari</option>
                                <option value="3">Maret</option>
                                <option value="4">April</option>
                                <option value="5">Mei</option>
                                <option value="6">Juni</option>
                                <option value="7">Juli</option>
                                <option value="8">Agustus</option>
                                <option value="9">September</option>
                                <option value="10">Oktober</option>
                                <option value="11">November</option>
                                <option value="12">Desember</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button onclick="loadEmployeeMonthlyReport()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                                <i class="fas fa-search mr-2"></i>
                                Tampilkan
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Report Header -->
                <div id="employeeReportHeader" class="hidden mb-6 p-4 bg-gray-50 rounded-lg">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Nama</p>
                            <p class="font-semibold text-gray-800" id="employeeReportName">-</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Periode</p>
                            <p class="font-semibold text-gray-800" id="employeeReportPeriod">-</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Kantor</p>
                            <p class="font-semibold text-gray-800" id="employeeReportOffice">-</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Status Insentif On Time</p>
                            <p class="font-semibold" id="employeeIncentiveStatus">-</p>
                        </div>
                    </div>

                    <!-- Warning if incentive hangus -->
                    <div id="employeeIncentiveWarning" class="hidden bg-red-50 border-l-4 border-red-500 p-4 mt-4">
                        <p class="text-red-700">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <strong>PERHATIAN:</strong> Insentif On Time bulan ini <strong>HANGUS</strong> karena Anda telat lebih dari 3x (<span id="employeeLateCountText"></span>x)
                        </p>
                    </div>
                </div>

                <div id="employeeReportLoading" class="hidden text-center py-8">
                    <i class="fas fa-spinner fa-spin text-4xl text-blue-500 mb-4"></i>
                    <p class="text-gray-600">Memuat data laporan...</p>
                </div>

                <div id="employeeReportEmpty" class="text-center py-8">
                    <i class="fas fa-file-invoice text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-600">Pilih tahun dan bulan untuk menampilkan laporan</p>
                </div>

                <div id="employeeReportContent" class="hidden">
                    <!-- Report Table -->
                    <div class="bg-white rounded-lg border mb-6 overflow-hidden">
                        <div class="overflow-x-auto" style="max-height: 500px;">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 sticky top-0">
                                    <tr>
                                        <th class="px-3 py-3 text-left border text-xs">No</th>
                                        <th class="px-3 py-3 text-left border text-xs">Tanggal</th>
                                        <th class="px-3 py-3 text-left border text-xs">Check-in</th>
                                        <th class="px-3 py-3 text-left border text-xs">Check-out</th>
                                        <th class="px-3 py-3 text-left border text-xs">Durasi Lembur</th>
                                        <th class="px-3 py-3 text-right border text-xs">Nominal Lembur</th>
                                        <th class="px-3 py-3 text-right border text-xs">Insentif On Time</th>
                                        <th class="px-3 py-3 text-right border text-xs">Insentif Luar Kota</th>
                                        <th class="px-3 py-3 text-right border text-xs">Insentif Libur</th>
                                        <th class="px-3 py-3 text-left border text-xs">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="employeeReportTableBody">
                                    <!-- Will be populated by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Summary & Totals -->
                    <div class="bg-white rounded-lg border p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Ringkasan & Total</h3>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <p class="text-sm text-blue-600 mb-1">Total Hari Kerja</p>
                                <p class="text-2xl font-bold text-blue-800" id="employeeTotalDays">0</p>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <p class="text-sm text-green-600 mb-1">Total Lembur</p>
                                <p class="text-2xl font-bold text-green-800" id="employeeTotalOvertime">0:00:00</p>
                            </div>
                            <div class="bg-yellow-50 p-4 rounded-lg">
                                <p class="text-sm text-yellow-600 mb-1">Total Telat</p>
                                <p class="text-2xl font-bold text-yellow-800" id="employeeTotalLate">0x</p>
                            </div>
                            <div class="bg-red-50 p-4 rounded-lg">
                                <p class="text-sm text-red-600 mb-1">Total Absen</p>
                                <p class="text-2xl font-bold text-red-800" id="employeeTotalAbsent">0</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex justify-between py-3 border-b-2 border-gray-300">
                                <span class="font-semibold text-gray-700">GRAND TOTAL</span>
                                <span class="text-xl font-bold text-gray-900" id="employeeGrandTotal">Rp 0</span>
                            </div>

                            <!-- Adjustments Section -->
                            <div id="employeeAdjustmentsSection" class="hidden">
                                <div class="mt-4">
                                    <h4 class="font-semibold text-gray-700 mb-2">Insentif Tambahan:</h4>
                                    <div id="employeeAdditionalIncentives" class="space-y-1 mb-3">
                                        <!-- Will be populated by JS -->
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <h4 class="font-semibold text-gray-700 mb-2">Potongan:</h4>
                                    <div id="employeeDeductions" class="space-y-1 mb-3">
                                        <!-- Will be populated by JS -->
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-between py-3 bg-green-100 px-4 rounded-lg border-2 border-green-500">
                                <span class="text-lg font-bold text-gray-800">TOTAL AKHIR</span>
                                <span class="text-2xl font-bold text-green-800" id="employeeFinalTotal">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div id="adminStats" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Karyawan</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2">4</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Hadir Hari Ini</p>
                        <p id="presentCount" class="text-3xl font-bold text-green-600 mt-2">0</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Terlambat</p>
                        <p id="lateCount" class="text-3xl font-bold text-yellow-600 mt-2">0</p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Pending Approval</p>
                        <p id="pendingApprovalCount" class="text-3xl font-bold text-orange-600 mt-2">0</p>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Kehadiran Minggu Ini</h3>
                <canvas id="attendanceChart"></canvas>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistik Keterlambatan</h3>
                <canvas id="lateChart"></canvas>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-800">Aktivitas Terbaru</h3>
            </div>
            <div class="p-6">
                <div class="text-center text-gray-500 py-8">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p>Belum ada aktivitas hari ini</p>
                    <p class="text-sm mt-2">Karyawan dapat melakukan check-in melalui mobile app</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Check login
        const token = localStorage.getItem('token');
        const user = JSON.parse(localStorage.getItem('user') || 'null');

        if (!user || !token) {
            window.location.href = '/login';
        } else {
            document.getElementById('userName').textContent = user.name;
            document.getElementById('userRole').textContent = user.role === 'admin' ? 'Administrator' :
                user.role === 'supervisor' ? 'Supervisor' : 'Karyawan';
        }

        async function logout() {
            try {
                // Call logout API
                await fetch('/api/auth/logout', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });
            } catch (error) {
                console.error('Logout error:', error);
            }

            // Clear local storage
            localStorage.removeItem('user');
            localStorage.removeItem('token');
            window.location.href = '/login';
        }

        // Fetch dashboard data from API
        async function loadDashboardData() {
            try {
                // Only for admin/supervisor
                if (!hasRole('admin', 'supervisor')) {
                    return;
                }

                const response = await fetch('/api/admin/dashboard/daily', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    // Update stats cards (with null check)
                    const presentCount = document.getElementById('presentCount');
                    const lateCount = document.getElementById('lateCount');
                    const pendingApprovalCount = document.getElementById('pendingApprovalCount');

                    if (presentCount) presentCount.textContent = result.data.summary.present || 0;
                    if (lateCount) lateCount.textContent = result.data.summary.late || 0;

                    // Update pending approval count from badge data
                    if (pendingApprovalCount) {
                        loadPendingApprovalCount();
                    }
                }
            } catch (error) {
                console.error('Failed to load dashboard data:', error);
            }
        }

        // Initialize employee monthly report
        function initEmployeeMonthlyReport() {
            const yearFilter = document.getElementById('employeeYearFilter');
            const monthFilter = document.getElementById('employeeMonthFilter');
            
            if (!yearFilter || !monthFilter) {
                console.error('Employee filters not found');
                return;
            }
            
            // Populate year dropdown (last 3 years)
            const currentYear = new Date().getFullYear();
            for (let year = currentYear; year >= currentYear - 3; year--) {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                if (year === currentYear) option.selected = true;
                yearFilter.appendChild(option);
            }
            
            // Set current month
            const currentMonth = new Date().getMonth() + 1;
            monthFilter.value = currentMonth;
            
            console.log('✅ Employee monthly report initialized');
        }

        // Load employee monthly report
        async function loadEmployeeMonthlyReport() {
            const year = document.getElementById('employeeYearFilter').value;
            const month = document.getElementById('employeeMonthFilter').value;
            
            const loadingEl = document.getElementById('employeeReportLoading');
            const contentEl = document.getElementById('employeeReportContent');
            const emptyEl = document.getElementById('employeeReportEmpty');
            const headerEl = document.getElementById('employeeReportHeader');
            
            // Show loading
            loadingEl.classList.remove('hidden');
            contentEl.classList.add('hidden');
            emptyEl.classList.add('hidden');
            headerEl.classList.add('hidden');
            
            try {
                const response = await apiRequest(`/employee/monthly-report?year=${year}&month=${month}`, 'GET');
                
                if (response.success && response.data) {
                    displayEmployeeMonthlyReport(response.data, year, month);
                    loadingEl.classList.add('hidden');
                    headerEl.classList.remove('hidden');
                    contentEl.classList.remove('hidden');
                } else {
                    loadingEl.classList.add('hidden');
                    emptyEl.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Failed to load employee monthly report:', error);
                loadingEl.classList.add('hidden');
                emptyEl.classList.remove('hidden');
            }
        }

        // Display employee monthly report
        function displayEmployeeMonthlyReport(data, year, month) {
            console.log('Displaying report data:', data);
            
            // Update header info
            document.getElementById('employeeReportName').textContent = data.user.name || '-';
            document.getElementById('employeeReportPeriod').textContent = `${getMonthName(month)} ${year}`;
            document.getElementById('employeeReportOffice').textContent = data.user.office?.name || '-';
            
            // Update incentive status
            const statusEl = document.getElementById('employeeIncentiveStatus');
            const warningEl = document.getElementById('employeeIncentiveWarning');
            const lateCountTextEl = document.getElementById('employeeLateCountText');
            
            if (data.summary.monthly_late_count > 3) {
                statusEl.textContent = '❌ HANGUS';
                statusEl.className = 'font-semibold text-red-600';
                warningEl.classList.remove('hidden');
                lateCountTextEl.textContent = data.summary.monthly_late_count;
            } else {
                statusEl.textContent = '✅ BERLAKU';
                statusEl.className = 'font-semibold text-green-600';
                warningEl.classList.add('hidden');
            }
            
            // Populate table
            const tbody = document.getElementById('employeeReportTableBody');
            tbody.innerHTML = '';
            
            // Check if rows exist
            if (!data.rows || data.rows.length === 0) {
                const emptyRow = document.createElement('tr');
                emptyRow.innerHTML = `
                    <td colspan="10" class="px-3 py-4 border text-center text-gray-500">
                        Tidak ada data kehadiran untuk periode ini
                    </td>
                `;
                tbody.appendChild(emptyRow);
                
                // Update summary with zeros
                document.getElementById('employeeTotalDays').textContent = '0';
                document.getElementById('employeeTotalOvertime').textContent = '0:00:00';
                document.getElementById('employeeTotalLate').textContent = '0x';
                document.getElementById('employeeTotalAbsent').textContent = '0';
                document.getElementById('employeeGrandTotal').textContent = formatCurrency(0);
                document.getElementById('employeeFinalTotal').textContent = formatCurrency(0);
                
                document.getElementById('employeeAdjustmentsSection').classList.add('hidden');
                return;
            }
            
            // Populate rows
            data.rows.forEach((row, index) => {
                const tr = document.createElement('tr');
                
                // Determine status badge
                let statusBadge = '<span class="px-2 py-1 bg-green-200 text-green-800 rounded text-xs">Hadir</span>';
                
                tr.innerHTML = `
                    <td class="px-3 py-2 border text-center">${row.no}</td>
                    <td class="px-3 py-2 border text-sm">${row.date_formatted}</td>
                    <td class="px-3 py-2 border">${row.check_in_actual}</td>
                    <td class="px-3 py-2 border">${row.check_out_actual}</td>
                    <td class="px-3 py-2 border">${row.overtime_duration_formatted}</td>
                    <td class="px-3 py-2 border text-right">${formatCurrency(row.overtime_amount)}</td>
                    <td class="px-3 py-2 border text-right">${formatCurrency(row.incentive_on_time)}</td>
                    <td class="px-3 py-2 border text-right">${formatCurrency(row.incentive_out_of_town)}</td>
                    <td class="px-3 py-2 border text-right">${formatCurrency(row.incentive_holiday)}</td>
                    <td class="px-3 py-2 border">${statusBadge}</td>
                `;
                tbody.appendChild(tr);
            });
            
            // Add total row
            const totalRow = document.createElement('tr');
            totalRow.className = 'bg-yellow-100 font-semibold';
            totalRow.innerHTML = `
                <td colspan="5" class="px-3 py-2 border text-right">TOTAL</td>
                <td class="px-3 py-2 border text-right">${formatCurrency(data.summary.total_overtime_amount)}</td>
                <td class="px-3 py-2 border text-right">${formatCurrency(data.summary.total_incentive_on_time)}</td>
                <td class="px-3 py-2 border text-right">${formatCurrency(data.summary.total_incentive_out_of_town)}</td>
                <td class="px-3 py-2 border text-right">${formatCurrency(data.summary.total_incentive_holiday)}</td>
                <td class="px-3 py-2 border"></td>
            `;
            tbody.appendChild(totalRow);
            
            // Update summary stats
            document.getElementById('employeeTotalDays').textContent = data.summary.total_days || 0;
            document.getElementById('employeeTotalOvertime').textContent = data.summary.total_overtime_duration_formatted || '0:00:00';
            document.getElementById('employeeTotalLate').textContent = (data.summary.monthly_late_count || 0) + 'x';
            document.getElementById('employeeTotalAbsent').textContent = '0'; // Not tracked in this structure
            
            // Grand total
            document.getElementById('employeeGrandTotal').textContent = formatCurrency(data.totals.grand_total);
            
            // Handle adjustments
            const adjustmentsSection = document.getElementById('employeeAdjustmentsSection');
            const additionalIncentives = document.getElementById('employeeAdditionalIncentives');
            const deductions = document.getElementById('employeeDeductions');
            
            if (data.adjustments && (data.adjustments.incentives.length > 0 || data.adjustments.deductions.length > 0)) {
                adjustmentsSection.classList.remove('hidden');
                
                // Populate incentives
                additionalIncentives.innerHTML = '';
                data.adjustments.incentives.forEach(adj => {
                    const div = document.createElement('div');
                    div.className = 'flex justify-between py-2 border-b';
                    div.innerHTML = `
                        <span class="text-gray-700">${adj.name}${adj.notes ? ` <span class="text-xs text-gray-500">(${adj.notes})</span>` : ''}</span>
                        <span class="font-semibold text-green-600">${formatCurrency(adj.amount)}</span>
                    `;
                    additionalIncentives.appendChild(div);
                });
                
                // Populate deductions
                deductions.innerHTML = '';
                data.adjustments.deductions.forEach(adj => {
                    const div = document.createElement('div');
                    div.className = 'flex justify-between py-2 border-b';
                    div.innerHTML = `
                        <span class="text-gray-700">${adj.name}${adj.notes ? ` <span class="text-xs text-gray-500">(${adj.notes})</span>` : ''}</span>
                        <span class="font-semibold text-red-600">${formatCurrency(adj.amount)}</span>
                    `;
                    deductions.appendChild(div);
                });
                
                // Final total
                document.getElementById('employeeFinalTotal').textContent = formatCurrency(data.totals.final_total);
            } else {
                adjustmentsSection.classList.add('hidden');
                document.getElementById('employeeFinalTotal').textContent = formatCurrency(data.totals.grand_total);
            }
        }

        // Helper functions
        function getMonthName(month) {
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                          'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            return months[month - 1];
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', { 
                weekday: 'short', 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
        }

        function formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(amount);
        }

        // Load pending approval count for stats card
        async function loadPendingApprovalCount() {
            try {
                // Only for admin/supervisor
                if (!hasRole('admin', 'supervisor')) {
                    return;
                }

                // Check if ApprovalsAPI is available
                if (typeof ApprovalsAPI === 'undefined') {
                    console.error('ApprovalsAPI is not available');
                    return;
                }

                const [checkoutsResult, leavesResult] = await Promise.all([
                    ApprovalsAPI.getPendingCheckouts(),
                    ApprovalsAPI.getPendingLeaves()
                ]);

                const checkoutCount = Array.isArray(checkoutsResult) ? checkoutsResult.length : 0;
                const leaveCount = Array.isArray(leavesResult) ? leavesResult.length : 0;
                const totalPending = checkoutCount + leaveCount;

                const pendingApprovalCount = document.getElementById('pendingApprovalCount');

                if (pendingApprovalCount) {
                    pendingApprovalCount.textContent = totalPending;
                    console.log('[Dashboard] Pending approval count updated to:', totalPending);
                }
            } catch (error) {
                console.error('Failed to load pending approval count:', error);
                const pendingApprovalCount = document.getElementById('pendingApprovalCount');
                if (pendingApprovalCount) {
                    pendingApprovalCount.textContent = '0';
                }
            }
        }

        // Setup role-based navigation
        setupRoleBasedNavigation();

        // Toggle attendance submenu for employees
        function toggleAttendanceMenu() {
            const submenu = document.getElementById('attendanceSubmenu');
            const icon = document.getElementById('attendanceMenuIcon');
            
            if (submenu.style.display === 'none' || submenu.style.display === '') {
                submenu.style.display = 'block';
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
                localStorage.setItem('attendanceMenuOpen', 'true');
            } else {
                submenu.style.display = 'none';
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
                localStorage.setItem('attendanceMenuOpen', 'false');
            }
        }

        // Restore attendance menu state on load
        window.addEventListener('load', function() {
            const menuOpen = localStorage.getItem('attendanceMenuOpen');
            const submenu = document.getElementById('attendanceSubmenu');
            const icon = document.getElementById('attendanceMenuIcon');
            
            if (submenu && icon) {
                // If never set before, default to open
                if (menuOpen === null) {
                    localStorage.setItem('attendanceMenuOpen', 'true');
                }
                
                // Apply saved state
                if (menuOpen === 'false') {
                    submenu.style.display = 'none';
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                } else {
                    submenu.style.display = 'block';
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                }
            }
        });

        // Load approval badge count
        async function loadApprovalBadge() {
            console.log('[Dashboard] Loading approval badge...');

            if (!hasRole('admin', 'supervisor')) {
                console.log('[Dashboard] User is not admin/supervisor, skipping badge load');
                return;
            }

            try {
                console.log('[Dashboard] Fetching pending checkouts and leaves...');
                const checkoutsPromise = ApprovalsAPI.getPendingCheckouts().catch(err => {
                    console.error('[Dashboard] Checkouts API error:', err);
                    return { success: true, data: [] };
                });
                const leavesPromise = ApprovalsAPI.getPendingLeaves().catch(err => {
                    console.error('[Dashboard] Leaves API error:', err);
                    return { success: true, data: [] };
                });

                const [checkouts, leaves] = await Promise.all([checkoutsPromise, leavesPromise]);

                const checkoutCount = (checkouts.success && checkouts.data) ? checkouts.data.length : 0;
                const leaveCount = (leaves.success && leaves.data) ? leaves.data.length : 0;
                const totalPending = checkoutCount + leaveCount;

                console.log('[Dashboard] Checkout count:', checkoutCount);
                console.log('[Dashboard] Leave count:', leaveCount);
                console.log('[Dashboard] Total pending:', totalPending);

                const badge = document.getElementById('badgeApprovals');

                if (badge) {
                    badge.textContent = totalPending;
                    if (totalPending > 0) {
                        badge.classList.remove('bg-gray-300', 'text-gray-700');
                        badge.classList.add('bg-red-500', 'text-white');
                    } else {
                        badge.classList.remove('bg-red-500', 'text-white');
                        badge.classList.add('bg-gray-300', 'text-gray-700');
                    }
                    console.log('[Dashboard] Badge updated successfully to:', totalPending);
                } else {
                    console.error('[Dashboard] Badge element not found!');
                }
            } catch (error) {
                console.error('[Dashboard] Failed to load approval badge:', error);
            }
        }

        // Load data on page load
        document.addEventListener('DOMContentLoaded', function() {
            const user = JSON.parse(localStorage.getItem('user') || 'null');
            
            if (user) {
                // Setup role-based navigation
                setupRoleBasedNavigation();
                
                // Show/hide sections based on role
                if (user.role === 'karyawan') {
                    // Show quick actions for employees
                    const employeeQuickActions = document.getElementById('employeeQuickActions');
                    const employeeLeaveQuota = document.getElementById('employeeLeaveQuota');
                    const employeeMonthlyReport = document.getElementById('employeeMonthlyReport');
                    const adminStats = document.getElementById('adminStats');
                    
                    if (employeeQuickActions) employeeQuickActions.style.display = 'grid';
                    if (employeeLeaveQuota) employeeLeaveQuota.style.display = 'block';
                    if (employeeMonthlyReport) employeeMonthlyReport.style.display = 'block';
                    if (adminStats) adminStats.style.display = 'none';
                    
                    // Load employee data
                    loadLeaveQuota();
                    initEmployeeMonthlyReport();
                    
                    console.log('✅ Employee dashboard initialized');
                } else {
                    // Show admin stats
                    const adminStats = document.getElementById('adminStats');
                    if (adminStats) adminStats.style.display = 'grid';
                    
                    // Load dashboard data for admin
                    loadDashboardData();
                    loadApprovalBadge();
                    
                    console.log('✅ Admin dashboard initialized');
                }
            }
        });

        // Load leave quota for employees
        async function loadLeaveQuota() {
            try {
                const response = await apiRequest('/leave-requests/quota', 'GET');
                if (response.success) {
                    const quota = response.data;
                    document.getElementById('leaveQuotaTotal').textContent = quota.annual_quota;
                    document.getElementById('leaveQuotaRemaining').textContent = quota.remaining;
                    document.getElementById('leaveQuotaUsed').textContent = quota.used;
                    document.getElementById('leaveQuotaPending').textContent = quota.pending;
                    document.getElementById('leaveQuotaAvailable').textContent = quota.available;
                }
            } catch (error) {
                console.error('Error loading leave quota:', error);
            }
        }

        // Attendance Chart
        const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
        new Chart(attendanceCtx, {
            type: 'line',
            data: {
                labels: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
                datasets: [{
                    label: 'Hadir',
                    data: [0, 0, 0, 0, 0, 0, 0],
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Late Chart
        const lateCtx = document.getElementById('lateChart').getContext('2d');
        new Chart(lateCtx, {
            type: 'bar',
            data: {
                labels: ['< 15 min', '15-30 min', '30-60 min', '> 60 min'],
                datasets: [{
                    label: 'Jumlah',
                    data: [0, 0, 0, 0],
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(234, 179, 8, 0.8)',
                        'rgba(249, 115, 22, 0.8)',
                        'rgba(239, 68, 68, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
