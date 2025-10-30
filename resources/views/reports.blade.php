<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Sistem Absensi</title>
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
            <a href="/attendance" class="flex items-center px-6 py-3 hover:bg-gray-800 transition">
                <i class="fas fa-clipboard-check w-5 mr-3"></i>
                Kehadiran
            </a>
            <a href="/reports" data-role-required="admin,supervisor" class="flex items-center px-6 py-3 bg-gray-800 border-l-4 border-blue-500">
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
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h2 class="text-3xl font-bold text-gray-800">Laporan Kehadiran</h2>
                <p class="text-gray-600">Analisis dan ringkasan data kehadiran karyawan</p>
            </div>
            <button onclick="exportReport()" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg shadow flex items-center transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export Excel
            </button>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Periode</label>
                    <select id="periodFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="monthly">Bulanan</option>
                        <option value="weekly">Mingguan</option>
                        <option value="custom">Custom</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                    <select id="monthFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
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
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kantor</label>
                    <select id="officeFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Kantor</option>
                        <option value="1">Jakarta</option>
                        <option value="2">Bandung</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Karyawan</label>
                    <select id="employeeFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Karyawan</option>
                        <option value="3">Budi Santoso</option>
                        <option value="4">Siti Rahayu</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button onclick="generateReport()" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition flex items-center justify-center">
                        <i class="fas fa-chart-line mr-2"></i>
                        Generate
                    </button>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-center">
                    <p class="text-sm text-gray-600 mb-2">Total Hari Kerja</p>
                    <p class="text-4xl font-bold text-gray-800">22</p>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-center">
                    <p class="text-sm text-gray-600 mb-2">Hadir</p>
                    <p class="text-4xl font-bold text-green-600">0</p>
                    <p class="text-xs text-gray-500 mt-1">0%</p>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-center">
                    <p class="text-sm text-gray-600 mb-2">Terlambat</p>
                    <p class="text-4xl font-bold text-yellow-600">0</p>
                    <p class="text-xs text-gray-500 mt-1">0x</p>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-center">
                    <p class="text-sm text-gray-600 mb-2">Lembur</p>
                    <p class="text-4xl font-bold text-blue-600">0</p>
                    <p class="text-xs text-gray-500 mt-1">0 jam</p>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-center">
                    <p class="text-sm text-gray-600 mb-2">Total Insentif</p>
                    <p class="text-3xl font-bold text-purple-600">Rp 0</p>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Trend Kehadiran Bulanan</h3>
                <canvas id="attendanceTrendChart"></canvas>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Distribusi Status Kehadiran</h3>
                <canvas id="statusDistributionChart"></canvas>
            </div>
        </div>

        <!-- Detailed Report Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-800">Laporan Detail</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Karyawan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hadir</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terlambat</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Jam</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lembur</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Insentif Ontime</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Insentif Lembur</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Insentif</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-lg font-medium">Belum ada data</p>
                                <p class="text-sm mt-2">Klik "Generate" untuk membuat laporan</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sample data template (hidden) -->
        <div id="sampleDataTemplate" class="hidden">
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold">
                            B
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">Budi Santoso</div>
                            <div class="text-sm text-gray-500">Jakarta</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">0 / 22</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-sm text-yellow-600">0x</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">0 jam</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">0 jam</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp 0</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp 0</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Rp 0</td>
            </tr>
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

        // Load filter data on page load
        loadOfficesForFilter();
        loadEmployeesForFilter();
        setCurrentMonth();

        function setCurrentMonth() {
            const now = new Date();
            const currentMonth = now.getMonth() + 1; // JavaScript months are 0-indexed
            document.getElementById('monthFilter').value = currentMonth;
        }

        async function loadOfficesForFilter() {
            try {
                const result = await OfficesAPI.getAll();
                
                if (result.success) {
                    const officeSelect = document.getElementById('officeFilter');
                    officeSelect.innerHTML = '<option value="">Semua Kantor</option>';
                    
                    result.data.forEach(office => {
                        if (office.is_active) {
                            const option = document.createElement('option');
                            option.value = office.id;
                            option.textContent = office.name;
                            officeSelect.appendChild(option);
                        }
                    });
                }
            } catch (error) {
                console.error('Failed to load offices:', error);
            }
        }

        async function loadEmployeesForFilter() {
            try {
                // Load all users then filter by role 'karyawan'
                const result = await UsersAPI.getAll();
                
                if (result.success) {
                    const employeeSelect = document.getElementById('employeeFilter');
                    employeeSelect.innerHTML = '<option value="">Semua Karyawan</option>';
                    
                    // Filter only karyawan role
                    const employees = result.data.filter(emp => emp.role === 'karyawan');
                    
                    console.log('Total karyawan:', employees.length, 'from', result.data.length, 'total users');
                    
                    employees.forEach(emp => {
                        const option = document.createElement('option');
                        option.value = emp.id;
                        option.textContent = emp.name + (emp.is_active ? '' : ' (Nonaktif)');
                        employeeSelect.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Failed to load employees:', error);
            }
        }

        function logout() {
            localStorage.removeItem('user');
            window.location.href = '/login';
        }

        // Attendance Trend Chart
        const trendCtx = document.getElementById('attendanceTrendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                datasets: [{
                    label: 'Hadir',
                    data: [0, 0, 0, 0],
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Terlambat',
                    data: [0, 0, 0, 0],
                    borderColor: 'rgb(234, 179, 8)',
                    backgroundColor: 'rgba(234, 179, 8, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
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

        // Status Distribution Chart
        const statusCtx = document.getElementById('statusDistributionChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Hadir', 'Terlambat', 'Cuti/Izin', 'Tanpa Keterangan'],
                datasets: [{
                    data: [0, 0, 0, 0],
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(234, 179, 8, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(239, 68, 68, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        function generateReport() {
            alert('Laporan akan di-generate menggunakan data dari API');
            // API call: GET /api/admin/reports/monthly with filters
        }

        function exportReport() {
            alert('Fitur export akan mengunduh file Excel');
            // API call: GET /api/admin/reports/export with filters
        }
    </script>
</body>
</html>
