<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Bulanan - Sistem Absensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/js/api-helper.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .table-scroll {
            overflow-x: auto;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            font-size: 0.875rem;
        }
        th, td {
            padding: 0.5rem;
            border: 1px solid #e5e7eb;
            white-space: nowrap;
        }
        th {
            background: #f9fafb;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .total-row {
            background: #fef3c7;
            font-weight: 600;
        }
        .adjustment-row {
            background: #dbeafe;
        }
        .grand-total-row {
            background: #86efac;
            font-weight: 700;
            font-size: 1rem;
        }
    </style>
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
            <a href="/reports" data-role-required="admin,supervisor" class="flex items-center px-6 py-3 hover:bg-gray-800 transition">
                <i class="fas fa-chart-bar w-5 mr-3"></i>
                Laporan
            </a>
            <a href="/reports-monthly" data-role-required="admin,supervisor" class="flex items-center px-6 py-3 bg-gray-800 border-l-4 border-blue-500">
                <i class="fas fa-file-invoice w-5 mr-3"></i>
                Laporan Bulanan
            </a>
            <a href="/approvals" data-role-required="admin,supervisor" class="flex items-center px-6 py-3 hover:bg-gray-800 transition">
                <i class="fas fa-check-circle w-5 mr-3"></i>
                Persetujuan
            </a>
        </nav>

        <div class="absolute bottom-0 w-64 p-6 border-t border-gray-800">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold">
                    A
                </div>
                <div class="ml-3">
                    <p class="text-sm font-semibold" id="userName">Loading...</p>
                    <p class="text-xs text-gray-400" id="userRole">Role</p>
                </div>
            </div>
            <button onclick="logout()" class="w-full flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm transition">
                <i class="fas fa-sign-out-alt mr-2"></i>
                Logout
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="ml-64 p-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Laporan Bulanan Karyawan</h1>
            <p class="text-gray-600">Laporan kehadiran, lembur, dan insentif bulanan</p>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Karyawan</label>
                    <select id="employeeSelect" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">Pilih Karyawan...</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun</label>
                    <select id="yearSelect" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <!-- Will be populated by JS -->
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Bulan</label>
                    <select id="monthSelect" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
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
                    <button onclick="loadReport()" class="w-full bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-search mr-2"></i>
                        Tampilkan
                    </button>
                </div>
            </div>
        </div>

        <!-- Report Content -->
        <div id="reportContent" class="hidden">
            <!-- Report Header -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">LAPORAN KEHADIRAN BULANAN</h2>
                        <div class="mt-2 space-y-1">
                            <p class="text-gray-600"><strong>Nama:</strong> <span id="reportName"></span></p>
                            <p class="text-gray-600"><strong>Periode:</strong> <span id="reportPeriod"></span></p>
                            <p class="text-gray-600"><strong>Kantor:</strong> <span id="reportOffice"></span></p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="exportToExcel()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                            <i class="fas fa-file-excel mr-2"></i>
                            Export Excel
                        </button>
                        <button onclick="manageAdjustments()" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                            <i class="fas fa-cog mr-2"></i>
                            Kelola Adjustment
                        </button>
                    </div>
                </div>

                <!-- Warning if incentive hangus -->
                <div id="incentiveWarning" class="hidden bg-red-50 border-l-4 border-red-500 p-4 mb-4">
                    <p class="text-red-700">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <strong>PERHATIAN:</strong> Insentif On Time bulan ini <strong>HANGUS</strong> karena karyawan telat lebih dari 3x (<span id="lateCountText"></span>x)
                    </p>
                </div>
            </div>

            <!-- Report Table -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <div class="table-scroll" style="max-height: 600px;">
                    <table id="reportTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Check-in Aktual</th>
                                <th>Check-out Aktual</th>
                                <th>Check-in Valid</th>
                                <th>Check-out Valid</th>
                                <th>Durasi Kerja Valid</th>
                                <th>Durasi Kerja Normal</th>
                                <th>Durasi Lembur</th>
                                <th>Nominal Lembur</th>
                                <th>Insentif On Time</th>
                                <th>Insentif Luar Kota</th>
                                <th>Insentif Hari Libur</th>
                                <th>Daily Report</th>
                            </tr>
                        </thead>
                        <tbody id="reportTableBody">
                            <!-- Will be populated by JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Summary & Totals -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Ringkasan & Total</h3>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="text-sm text-blue-600 mb-1">Total Hari Kerja</p>
                        <p class="text-2xl font-bold text-blue-800" id="totalDays">0</p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <p class="text-sm text-green-600 mb-1">Total Lembur</p>
                        <p class="text-2xl font-bold text-green-800" id="totalOvertime">0:00:00</p>
                    </div>
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <p class="text-sm text-yellow-600 mb-1">Total Telat</p>
                        <p class="text-2xl font-bold text-yellow-800" id="totalLate">0x</p>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <p class="text-sm text-purple-600 mb-1">Status Insentif OnTime</p>
                        <p class="text-lg font-bold text-purple-800" id="incentiveStatus">-</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex justify-between py-3 border-b-2 border-gray-300">
                        <span class="font-semibold text-gray-700">GRAND TOTAL</span>
                        <span class="text-xl font-bold text-gray-900" id="grandTotal">Rp 0</span>
                    </div>

                    <!-- Adjustments Section -->
                    <div id="adjustmentsSection" class="hidden">
                        <div class="mt-4">
                            <h4 class="font-semibold text-gray-700 mb-2">Insentif Tambahan:</h4>
                            <div id="additionalIncentives" class="space-y-1 mb-3">
                                <!-- Will be populated by JS -->
                            </div>
                        </div>

                        <div class="mt-4">
                            <h4 class="font-semibold text-gray-700 mb-2">Potongan:</h4>
                            <div id="deductions" class="space-y-1 mb-3">
                                <!-- Will be populated by JS -->
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between py-3 bg-green-100 px-4 rounded-lg border-2 border-green-500">
                        <span class="text-lg font-bold text-gray-800">TOTAL AKHIR</span>
                        <span class="text-2xl font-bold text-green-800" id="finalTotal">Rp 0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="bg-white rounded-xl shadow-lg p-12 text-center">
            <i class="fas fa-file-invoice text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Belum Ada Laporan</h3>
            <p class="text-gray-600">Pilih karyawan, tahun, dan bulan untuk menampilkan laporan</p>
        </div>
    </div>

    <!-- Adjustment Modal -->
    <div id="adjustmentModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-800">Kelola Potongan & Insentif Tambahan</h2>
                <button onclick="closeAdjustmentModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            
            <div class="p-6">
                <!-- Add New Adjustment Form -->
                <div class="bg-blue-50 rounded-lg p-4 mb-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Tambah Baru</h3>
                    <form id="adjustmentForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tipe</label>
                            <select id="adjType" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="incentive">Insentif Tambahan</option>
                                <option value="deduction">Potongan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nama</label>
                            <input type="text" id="adjName" required placeholder="Contoh: Bonus Performa" 
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nominal (Rp)</label>
                            <input type="number" id="adjAmount" required min="0" placeholder="50000" 
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Catatan</label>
                            <input type="text" id="adjNotes" placeholder="Catatan tambahan" 
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="md:col-span-2">
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                                <i class="fas fa-plus mr-2"></i>
                                Tambah
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Existing Adjustments -->
                <div>
                    <h3 class="font-semibold text-gray-800 mb-4">Adjustment yang Ada</h3>
                    <div id="adjustmentsList" class="space-y-2">
                        <!-- Will be populated by JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentReport = null;
        let currentUserId = null;
        let currentYear = null;
        let currentMonth = null;
        let employees = [];
        let adjustments = [];

        window.addEventListener('load', () => {
            checkAuth();
            setupRoleBasedNavigation();
            initializePage();
        });

        function initializePage() {
            // Set current year and month
            const now = new Date();
            const currentYear = now.getFullYear();
            const currentMonth = now.getMonth() + 1;

            // Populate year dropdown (last 3 years + current year)
            const yearSelect = document.getElementById('yearSelect');
            for (let year = currentYear; year >= currentYear - 3; year--) {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                if (year === currentYear) option.selected = true;
                yearSelect.appendChild(option);
            }

            // Set current month
            document.getElementById('monthSelect').value = currentMonth;

            // Load employees
            loadEmployees();
        }

        async function loadEmployees() {
            try {
                const response = await apiRequest('/admin/users?per_page=1000', 'GET');
                if (response.success) {
                    employees = response.data.data || response.data;
                    const select = document.getElementById('employeeSelect');
                    select.innerHTML = '<option value="">Pilih Karyawan...</option>';
                    
                    employees.forEach(emp => {
                        const option = document.createElement('option');
                        option.value = emp.id;
                        option.textContent = `${emp.name} - ${emp.role}`;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                showAlert('Gagal memuat data karyawan: ' + error.message, 'error');
            }
        }

        async function loadReport() {
            currentUserId = document.getElementById('employeeSelect').value;
            currentYear = document.getElementById('yearSelect').value;
            currentMonth = document.getElementById('monthSelect').value;

            if (!currentUserId) {
                showAlert('Pilih karyawan terlebih dahulu', 'error');
                return;
            }

            try {
                showLoading();

                const response = await apiRequest(
                    `/admin/users/${currentUserId}/monthly-report?year=${currentYear}&month=${currentMonth}`,
                    'GET'
                );

                if (response.success) {
                    currentReport = response.data;
                    renderReport();
                    
                    // Load adjustments
                    await loadAdjustments();
                }
            } catch (error) {
                showAlert('Gagal memuat laporan: ' + error.message, 'error');
                document.getElementById('emptyState').classList.remove('hidden');
                document.getElementById('reportContent').classList.add('hidden');
            }
        }

        function renderReport() {
            const report = currentReport;

            // Update header
            document.getElementById('reportName').textContent = report.user.name;
            document.getElementById('reportPeriod').textContent = report.period.month_name;
            document.getElementById('reportOffice').textContent = report.user.office?.name || '-';

            // Check if incentive hangus
            if (report.summary.monthly_late_count > 3) {
                document.getElementById('incentiveWarning').classList.remove('hidden');
                document.getElementById('lateCountText').textContent = report.summary.monthly_late_count;
            } else {
                document.getElementById('incentiveWarning').classList.add('hidden');
            }

            // Render table rows
            const tbody = document.getElementById('reportTableBody');
            tbody.innerHTML = '';

            report.rows.forEach(row => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="text-center">${row.no}</td>
                    <td>${row.date_formatted}</td>
                    <td class="text-center">${row.check_in_actual}</td>
                    <td class="text-center">${row.check_out_actual}</td>
                    <td class="text-center">${row.check_in_valid}</td>
                    <td class="text-center">${row.check_out_valid}</td>
                    <td class="text-center">${row.valid_duration_formatted}</td>
                    <td class="text-center">${row.normal_duration_formatted}</td>
                    <td class="text-center">${row.overtime_duration_formatted}</td>
                    <td class="text-right">${formatCurrency(row.overtime_amount)}</td>
                    <td class="text-right">${formatCurrency(row.incentive_on_time)}</td>
                    <td class="text-right">${formatCurrency(row.incentive_out_of_town)}</td>
                    <td class="text-right">${formatCurrency(row.incentive_holiday)}</td>
                    <td class="text-left" style="max-width: 200px; white-space: normal;">${row.daily_report}</td>
                `;
                tbody.appendChild(tr);
            });

            // Add total row
            const totalRow = document.createElement('tr');
            totalRow.className = 'total-row';
            totalRow.innerHTML = `
                <td colspan="6" class="text-center"><strong>TOTAL</strong></td>
                <td class="text-center"><strong>${report.summary.total_valid_duration_formatted}</strong></td>
                <td class="text-center"><strong>${report.summary.total_normal_duration_formatted}</strong></td>
                <td class="text-center"><strong>${report.summary.total_overtime_duration_formatted}</strong></td>
                <td class="text-right"><strong>${formatCurrency(report.summary.total_overtime_amount)}</strong></td>
                <td class="text-right"><strong>${formatCurrency(report.summary.total_incentive_on_time)}</strong></td>
                <td class="text-right"><strong>${formatCurrency(report.summary.total_incentive_out_of_town)}</strong></td>
                <td class="text-right"><strong>${formatCurrency(report.summary.total_incentive_holiday)}</strong></td>
                <td></td>
            `;
            tbody.appendChild(totalRow);

            // Update summary cards
            document.getElementById('totalDays').textContent = report.summary.total_days;
            document.getElementById('totalOvertime').textContent = report.summary.total_overtime_duration_formatted;
            document.getElementById('totalLate').textContent = report.summary.monthly_late_count + 'x';
            document.getElementById('incentiveStatus').textContent = report.summary.incentive_on_time_status;

            // Update totals
            document.getElementById('grandTotal').textContent = formatCurrency(report.totals.grand_total);
            document.getElementById('finalTotal').textContent = formatCurrency(report.totals.final_total);

            // Show report
            document.getElementById('emptyState').classList.add('hidden');
            document.getElementById('reportContent').classList.remove('hidden');
        }

        async function loadAdjustments() {
            try {
                const response = await apiRequest(
                    `/admin/users/${currentUserId}/adjustments?year=${currentYear}&month=${currentMonth}`,
                    'GET'
                );

                if (response.success) {
                    adjustments = response.data;
                    renderAdjustments();
                }
            } catch (error) {
                console.error('Failed to load adjustments:', error);
            }
        }

        function renderAdjustments() {
            const incentivesDiv = document.getElementById('additionalIncentives');
            const deductionsDiv = document.getElementById('deductions');
            const section = document.getElementById('adjustmentsSection');

            const incentiveAdj = adjustments.filter(a => a.type === 'incentive');
            const deductionAdj = adjustments.filter(a => a.type === 'deduction');

            if (incentiveAdj.length === 0 && deductionAdj.length === 0) {
                section.classList.add('hidden');
                return;
            }

            section.classList.remove('hidden');

            // Render incentives
            incentivesDiv.innerHTML = incentiveAdj.map(adj => `
                <div class="flex justify-between py-2 px-3 bg-blue-50 rounded">
                    <span class="text-gray-700">${adj.name} ${adj.notes ? `<span class="text-xs text-gray-500">(${adj.notes})</span>` : ''}</span>
                    <span class="font-semibold text-green-700">+ ${formatCurrency(adj.amount)}</span>
                </div>
            `).join('') || '<p class="text-gray-500 text-sm">Tidak ada insentif tambahan</p>';

            // Render deductions
            deductionsDiv.innerHTML = deductionAdj.map(adj => `
                <div class="flex justify-between py-2 px-3 bg-red-50 rounded">
                    <span class="text-gray-700">${adj.name} ${adj.notes ? `<span class="text-xs text-gray-500">(${adj.notes})</span>` : ''}</span>
                    <span class="font-semibold text-red-700">- ${formatCurrency(adj.amount)}</span>
                </div>
            `).join('') || '<p class="text-gray-500 text-sm">Tidak ada potongan</p>';
        }

        function manageAdjustments() {
            if (!currentUserId) {
                showAlert('Pilih karyawan terlebih dahulu', 'error');
                return;
            }

            // Render adjustments list in modal
            const list = document.getElementById('adjustmentsList');
            
            if (adjustments.length === 0) {
                list.innerHTML = '<p class="text-gray-500 text-center py-4">Belum ada adjustment</p>';
            } else {
                list.innerHTML = adjustments.map(adj => `
                    <div class="flex justify-between items-center p-3 border rounded-lg ${adj.type === 'incentive' ? 'bg-green-50' : 'bg-red-50'}">
                        <div class="flex-1">
                            <p class="font-semibold">${adj.name}</p>
                            <p class="text-sm text-gray-600">${adj.type === 'incentive' ? 'Insentif' : 'Potongan'} - ${formatCurrency(adj.amount)}</p>
                            ${adj.notes ? `<p class="text-xs text-gray-500">${adj.notes}</p>` : ''}
                        </div>
                        <button onclick="deleteAdjustment(${adj.id})" class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `).join('');
            }

            document.getElementById('adjustmentModal').classList.remove('hidden');
        }

        function closeAdjustmentModal() {
            document.getElementById('adjustmentModal').classList.add('hidden');
            document.getElementById('adjustmentForm').reset();
        }

        document.getElementById('adjustmentForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();

            const data = {
                year: parseInt(currentYear),
                month: parseInt(currentMonth),
                type: document.getElementById('adjType').value,
                name: document.getElementById('adjName').value,
                amount: parseFloat(document.getElementById('adjAmount').value),
                notes: document.getElementById('adjNotes').value || null,
            };

            try {
                const response = await apiRequest(`/admin/users/${currentUserId}/adjustments`, 'POST', data);
                
                if (response.success) {
                    showAlert('Adjustment berhasil ditambahkan');
                    document.getElementById('adjustmentForm').reset();
                    await loadAdjustments();
                    await loadReport();
                    manageAdjustments(); // Refresh modal
                }
            } catch (error) {
                showAlert('Gagal menambahkan adjustment: ' + error.message, 'error');
            }
        });

        async function deleteAdjustment(id) {
            if (!confirm('Yakin ingin menghapus adjustment ini?')) return;

            try {
                const response = await apiRequest(`/admin/users/${currentUserId}/adjustments/${id}`, 'DELETE');
                
                if (response.success) {
                    showAlert('Adjustment berhasil dihapus');
                    await loadAdjustments();
                    await loadReport();
                    manageAdjustments(); // Refresh modal
                }
            } catch (error) {
                showAlert('Gagal menghapus adjustment: ' + error.message, 'error');
            }
        }

        async function exportToExcel() {
            if (!currentUserId) return;

            try {
                showLoading('Generating Excel...');

                const response = await apiRequest(
                    `/admin/users/${currentUserId}/monthly-report/export?year=${currentYear}&month=${currentMonth}`,
                    'GET'
                );

                if (response.success) {
                    // Convert to CSV for now (Excel export will need library)
                    const data = response.data;
                    const csv = convertToCSV(data);
                    downloadCSV(csv, `Laporan_${currentReport.user.name}_${currentYear}-${currentMonth}.csv`);
                    showAlert('File berhasil di-export!');
                }
            } catch (error) {
                showAlert('Gagal export file: ' + error.message, 'error');
            }
        }

        function convertToCSV(data) {
            return data.map(row => row.join('\t')).join('\n');
        }

        function downloadCSV(csv, filename) {
            const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = filename;
            link.click();
        }

        function formatCurrency(amount) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
        }

        function showLoading(message = 'Loading...') {
            // Simple loading implementation
            console.log(message);
        }

        async function logout() {
            try {
                await AuthAPI.logout();
            } catch (error) {
                console.error('Logout error:', error);
            } finally {
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                window.location.href = '/login';
            }
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Check authentication
            const user = JSON.parse(localStorage.getItem('user') || 'null');
            if (!user) {
                window.location.href = '/login';
                return;
            }

            // Display user info
            document.getElementById('userName').textContent = user.name;
            document.getElementById('userRole').textContent = user.role === 'admin' ? 'Administrator' : 
                                                              user.role === 'supervisor' ? 'Supervisor' : 'Karyawan';

            // Setup navigation
            setupRoleBasedNavigation();

            // Load employees list
            loadEmployees();
        });
    </script>
</body>
</html>
