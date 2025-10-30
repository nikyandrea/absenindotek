<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kehadiran - Sistem Absensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
            <a href="/dashboard" class="flex items-center px-6 py-3 hover:bg-gray-800 transition">
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
            <a href="/attendance" class="flex items-center px-6 py-3 bg-gray-800 border-l-4 border-blue-500">
                <i class="fas fa-clipboard-check w-5 mr-3"></i>
                Kehadiran
            </a>
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
            <h2 class="text-3xl font-bold text-gray-800">Monitoring Kehadiran</h2>
            <p class="text-gray-600">Real-time monitoring kehadiran karyawan</p>
        </div>

        <!-- Date Filter -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                        <input type="date" id="dateFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kantor</label>
                        <select id="officeFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Kantor</option>
                            <option value="1">Jakarta</option>
                            <option value="2">Bandung</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select id="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Status</option>
                            <option value="present">Hadir</option>
                            <option value="late">Terlambat</option>
                            <option value="absent">Belum Hadir</option>
                        </select>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600">Waktu Saat Ini</p>
                    <p class="text-2xl font-bold text-gray-800" id="currentTime">--:--:--</p>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Karyawan</p>
                        <p class="text-3xl font-bold text-gray-800">4</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Sudah Hadir</p>
                        <p class="text-3xl font-bold text-green-600" id="presentCount">0</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Terlambat</p>
                        <p class="text-3xl font-bold text-yellow-600" id="lateCount">0</p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Belum Hadir</p>
                        <p class="text-3xl font-bold text-red-600" id="absentCount">4</p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-800">Detail Kehadiran Hari Ini</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Karyawan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Masuk</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Keluar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Verifikasi Wajah</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="attendanceTableBody">
                    <!-- Sample data showing empty state -->
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="text-gray-400">
                                <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                </svg>
                                <p class="text-lg font-medium">Belum ada data kehadiran hari ini</p>
                                <p class="text-sm mt-2">Karyawan dapat melakukan check-in melalui mobile app</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Sample attendance data (when data exists) - hidden by default -->
        <div id="sampleDataTemplate" class="hidden">
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold">
                            B
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">Budi Santoso</div>
                            <div class="text-sm text-gray-500">Jakarta Office</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">08:30:00</div>
                    <div class="text-xs text-red-600">Terlambat 30 menit</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-sm text-gray-400">Belum checkout</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                        Terlambat
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                        Di Area
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm text-gray-900">95%</span>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    --
                </td>
            </tr>
        </div>
    </div>

    <!-- Detail Modal -->
    <div id="detailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-screen overflow-y-auto m-4">
            <div class="p-6 border-b">
                <h3 class="text-xl font-semibold text-gray-800">Detail Kehadiran</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-semibold text-gray-800 mb-4">Informasi Karyawan</h4>
                        <dl class="space-y-2">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Nama:</dt>
                                <dd class="text-sm font-medium text-gray-900">Budi Santoso</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Kantor:</dt>
                                <dd class="text-sm font-medium text-gray-900">Jakarta</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Tipe Jam Kerja:</dt>
                                <dd class="text-sm font-medium text-gray-900">Tetap</dd>
                            </div>
                        </dl>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-800 mb-4">Informasi Check-in</h4>
                        <dl class="space-y-2">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Waktu:</dt>
                                <dd class="text-sm font-medium text-gray-900">08:30:00</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Koordinat:</dt>
                                <dd class="text-sm font-medium text-gray-900">-6.2088, 106.8456</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Skor Wajah:</dt>
                                <dd class="text-sm font-medium text-gray-900">95%</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="p-6 border-t flex justify-end">
                <button onclick="closeDetailModal()" class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition flex items-center">
                    <i class="fas fa-times mr-2"></i>
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        // Check login
        const user = JSON.parse(localStorage.getItem('user') || 'null');
        if (!user) {
            window.location.href = '/login';
        } else {
            document.getElementById('userName').textContent = user.name;
            document.getElementById('userRole').textContent = user.role === 'admin' ? 'Administrator' : user.role;
        }

        // Setup role-based navigation
        setupRoleBasedNavigation();

        function logout() {
            localStorage.removeItem('user');
            window.location.href = '/login';
        }

        // Set today's date
        document.getElementById('dateFilter').valueAsDate = new Date();

        // Update current time
        function updateTime() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID');
            document.getElementById('currentTime').textContent = timeStr;
        }
        updateTime();
        setInterval(updateTime, 1000);

        function closeDetailModal() {
            document.getElementById('detailModal').classList.add('hidden');
        }

        // Sample function to show detail
        function showDetail(id) {
            document.getElementById('detailModal').classList.remove('hidden');
        }

        // Event listeners for filters
        document.getElementById('dateFilter').addEventListener('change', function() {
            console.log('Fetch attendance for:', this.value);
            // API call will be integrated here
        });

        document.getElementById('officeFilter').addEventListener('change', function() {
            console.log('Filter by office:', this.value);
            // Filter logic will be integrated here
        });

        document.getElementById('statusFilter').addEventListener('change', function() {
            console.log('Filter by status:', this.value);
            // Filter logic will be integrated here
        });
    </script>
</body>
</html>
