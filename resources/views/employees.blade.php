<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Karyawan - Sistem Absensi</title>
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
            <a href="/employees" data-role-required="admin,supervisor" class="flex items-center px-6 py-3 bg-gray-800 border-l-4 border-blue-500">
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
                <h2 class="text-3xl font-bold text-gray-800">Data Karyawan</h2>
                <p class="text-gray-600">Kelola informasi karyawan</p>
            </div>
            <button onclick="showAddModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg shadow flex items-center transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Karyawan
            </button>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                    <input type="text" id="searchInput" placeholder="Nama atau email..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kantor</label>
                    <select id="officeFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Semua Kantor</option>
                        <option value="1">Jakarta</option>
                        <option value="2">Bandung</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <select id="roleFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Semua Role</option>
                        <option value="karyawan">Karyawan</option>
                        <option value="supervisor">Supervisor</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Jam Kerja</label>
                    <select id="workTypeFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Semua Tipe</option>
                        <option value="tetap">Tetap</option>
                        <option value="bebas">Bebas</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Employees Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Karyawan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kantor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Kerja</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="employeeTableBody">
                    <!-- Data will be populated by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div id="employeeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-screen overflow-y-auto m-4">
            <div class="p-6 border-b">
                <h3 class="text-xl font-semibold text-gray-800" id="modalTitle">Tambah Karyawan</h3>
            </div>
            <div class="p-6">
                <form id="employeeForm" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap *</label>
                            <input type="text" id="empName" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                            <input type="email" id="empEmail" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                            <div class="relative">
                                <input type="password" id="empPassword" placeholder="Masukkan password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 pr-12">
                                <button type="button" onclick="togglePassword('empPassword', 'toggleEmpPasswordIcon')" 
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none">
                                    <i id="toggleEmpPasswordIcon" class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon</label>
                            <input type="tel" id="empPhone" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kantor *</label>
                            <select id="empOffice" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Pilih Kantor</option>
                                <option value="1">Jakarta</option>
                                <option value="2">Bandung</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Role *</label>
                            <select id="empRole" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="karyawan">Karyawan</option>
                                <option value="supervisor">Supervisor</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Jam Kerja *</label>
                            <select id="empWorkType" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="tetap">Tetap (Jam Masuk & Pulang Tetap)</option>
                                <option value="bebas">Bebas (Target Durasi)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Insentif Ontime (Rp)</label>
                            <input type="number" id="empOntimeIncentive" value="50000" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Insentif Luar Kota (Rp)</label>
                            <input type="number" id="empOutOfTownIncentive" value="100000" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Insentif Libur (Rp)</label>
                            <input type="number" id="empHolidayIncentive" value="150000" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fee Lembur Per Jam (Rp)</label>
                            <input type="number" id="empOvertimeRate" value="25000" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Contoh: 25000">
                            <p class="text-xs text-gray-500 mt-1">Nominal yang dibayarkan per jam lembur</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Cuti Tahunan (Hari)</label>
                            <input type="number" id="empAnnualLeaveQuota" value="12" min="0" max="365" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Contoh: 12">
                            <p class="text-xs text-gray-500 mt-1">Kuota cuti tahunan karyawan</p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="p-6 border-t flex justify-end space-x-3">
                <button onclick="closeModal()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center">
                    <i class="fas fa-times mr-2"></i>
                    Batal
                </button>
                <button onclick="saveEmployee()" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center">
                    <i class="fas fa-save mr-2"></i>
                    Simpan
                </button>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Global state
        let employees = [];
        let currentEditId = null;

        // Check authentication
        checkAuth();
        const user = JSON.parse(localStorage.getItem('user'));
        document.getElementById('userName').textContent = user.name;
        document.getElementById('userRole').textContent = user.role === 'admin' ? 'Administrator' :
            user.role === 'supervisor' ? 'Supervisor' : 'Karyawan';

        // Setup role-based navigation
        setupRoleBasedNavigation();

        // Logout function
        async function logout() {
            try {
                await AuthAPI.logout();
            } catch (error) {
                console.error('Logout error:', error);
            }
            localStorage.removeItem('user');
            localStorage.removeItem('token');
            window.location.href = '/login';
        }

        // Load employees from API
        async function loadEmployees() {
            try {
                const filters = {};
                const search = document.getElementById('searchInput').value;
                if (search) filters.search = search;

                const officeId = document.getElementById('officeFilter').value;
                if (officeId) filters.office_id = officeId;

                const role = document.getElementById('roleFilter').value;
                if (role) filters.role = role;

                const workType = document.getElementById('workTypeFilter').value;
                if (workType) filters.work_time_type = workType;

                const result = await UsersAPI.getAll(filters);

                if (result.success) {
                    employees = result.data;
                    renderEmployees();
                }
            } catch (error) {
                console.error('Failed to load employees:', error);
                showAlert('Gagal memuat data karyawan: ' + error.message, 'error');
            }
        }

        function renderEmployees() {
            const tbody = document.getElementById('employeeTableBody');

            if (employees.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            Tidak ada data karyawan
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = employees.map(emp => `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold">
                                ${emp.name.charAt(0).toUpperCase()}
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">${emp.name}</div>
                                <div class="text-sm text-gray-500">${emp.email}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm text-gray-900">${emp.office_name}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                            emp.role === 'admin' ? 'bg-purple-100 text-purple-800' :
                            emp.role === 'supervisor' ? 'bg-blue-100 text-blue-800' :
                            'bg-gray-100 text-gray-800'
                        }">
                            ${emp.role.charAt(0).toUpperCase() + emp.role.slice(1)}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                            emp.work_time_type === 'tetap' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                        }">
                            ${emp.work_time_type === 'tetap' ? 'Tetap' : 'Bebas'}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                            emp.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                        }">
                            ${emp.is_active ? 'Aktif' : 'Nonaktif'}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button onclick="editEmployee(${emp.id})" class="text-blue-600 hover:text-blue-900 mr-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <button onclick="deleteEmployee(${emp.id})" class="text-red-600 hover:text-red-900">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        function showAddModal() {
            currentEditId = null;
            document.getElementById('modalTitle').textContent = 'Tambah Karyawan';
            document.getElementById('employeeForm').reset();
            
            // Reset to default values for add mode
            document.getElementById('empOntimeIncentive').value = 50000;
            document.getElementById('empOutOfTownIncentive').value = 100000;
            document.getElementById('empHolidayIncentive').value = 150000;
            document.getElementById('empOvertimeRate').value = 25000;
            document.getElementById('empAnnualLeaveQuota').value = 12;
            document.getElementById('empPassword').value = '';
            document.getElementById('empPassword').required = true;
            document.getElementById('empPassword').placeholder = 'Masukkan password';
            
            loadOfficesForDropdown();
            document.getElementById('employeeModal').classList.remove('hidden');
        }

        async function loadOfficesForDropdown() {
            try {
                const result = await OfficesAPI.getAll();
                
                if (result.success) {
                    const officeSelect = document.getElementById('empOffice');
                    // Keep first option "Pilih Kantor"
                    officeSelect.innerHTML = '<option value="">Pilih Kantor</option>';
                    
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

        async function editEmployee(id) {
            try {
                // Load offices first
                await loadOfficesForDropdown();
                
                const result = await UsersAPI.get(id);

                if (result.success) {
                    const emp = result.data;
                    console.log('Employee data received:', emp);
                    console.log('Incentives:', {
                        ontime: emp.ontime_incentive,
                        outOfTown: emp.out_of_town_incentive,
                        holiday: emp.holiday_incentive,
                        overtime: emp.overtime_rate_per_hour
                    });
                    
                    currentEditId = id;

                    document.getElementById('modalTitle').textContent = 'Edit Karyawan';
                    document.getElementById('empName').value = emp.name;
                    document.getElementById('empEmail').value = emp.email;
                    document.getElementById('empPhone').value = emp.phone || '';
                    document.getElementById('empOffice').value = emp.office_id;
                    document.getElementById('empRole').value = emp.role;
                    document.getElementById('empWorkType').value = emp.work_time_type;
                    
                    // Parse incentives as numbers, handle both string and number formats
                    const ontimeIncentive = parseFloat(emp.ontime_incentive) || 0;
                    const outOfTownIncentive = parseFloat(emp.out_of_town_incentive) || 0;
                    const holidayIncentive = parseFloat(emp.holiday_incentive) || 0;
                    const overtimeRate = parseFloat(emp.overtime_rate_per_hour) || 0;
                    const annualLeaveQuota = parseInt(emp.annual_leave_quota) || 12;
                    
                    document.getElementById('empOntimeIncentive').value = ontimeIncentive;
                    document.getElementById('empOutOfTownIncentive').value = outOfTownIncentive;
                    document.getElementById('empHolidayIncentive').value = holidayIncentive;
                    document.getElementById('empOvertimeRate').value = overtimeRate;
                    document.getElementById('empAnnualLeaveQuota').value = annualLeaveQuota;
                    
                    console.log('Setting incentive values:', {
                        ontime: ontimeIncentive,
                        outOfTown: outOfTownIncentive,
                        holiday: holidayIncentive,
                        overtime: overtimeRate,
                        annualLeave: annualLeaveQuota
                    });
                    
                    // Clear password and make it optional for edit
                    document.getElementById('empPassword').value = '';
                    document.getElementById('empPassword').required = false;
                    document.getElementById('empPassword').placeholder = 'Kosongkan jika tidak ingin mengubah password';
                    
                    document.getElementById('employeeModal').classList.remove('hidden');
                }
            } catch (error) {
                showAlert('Gagal memuat data karyawan: ' + error.message, 'error');
            }
        }

        async function deleteEmployee(id) {
            if (!await confirm('Apakah Anda yakin ingin menghapus karyawan ini?')) {
                return;
            }

            try {
                const result = await UsersAPI.delete(id);

                if (result.success) {
                    showAlert('Karyawan berhasil dihapus');
                    loadEmployees();
                }
            } catch (error) {
                showAlert('Gagal menghapus karyawan: ' + error.message, 'error');
            }
        }

        async function saveEmployee() {
            const data = {
                name: document.getElementById('empName').value,
                email: document.getElementById('empEmail').value,
                phone: document.getElementById('empPhone').value,
                office_id: parseInt(document.getElementById('empOffice').value),
                role: document.getElementById('empRole').value,
                work_time_type: document.getElementById('empWorkType').value,
                ontime_incentive: parseInt(document.getElementById('empOntimeIncentive').value) || 0,
                out_of_town_incentive: parseInt(document.getElementById('empOutOfTownIncentive').value) || 0,
                holiday_incentive: parseInt(document.getElementById('empHolidayIncentive').value) || 0,
                overtime_rate_per_hour: parseInt(document.getElementById('empOvertimeRate').value) || 0,
                annual_leave_quota: parseInt(document.getElementById('empAnnualLeaveQuota').value) || 12,
            };

            const password = document.getElementById('empPassword').value;
            if (password) {
                data.password = password;
            }
            
            console.log('Saving employee with data:', data);

            try {
                let result;
                if (currentEditId) {
                    console.log('Updating employee ID:', currentEditId);
                    result = await UsersAPI.update(currentEditId, data);
                } else {
                    console.log('Creating new employee');
                    result = await UsersAPI.create(data);
                }

                console.log('Save result:', result);

                if (result.success) {
                    showAlert(currentEditId ? 'Karyawan berhasil diupdate' : 'Karyawan berhasil ditambahkan');
                    closeModal();
                    loadEmployees();
                }
            } catch (error) {
                console.error('Save error:', error);
                showAlert('Gagal menyimpan data: ' + error.message, 'error');
            }
        }

        function closeModal() {
            document.getElementById('employeeModal').classList.add('hidden');
        }

        // Event listeners
        document.getElementById('searchInput').addEventListener('input', loadEmployees);
        document.getElementById('officeFilter').addEventListener('change', loadEmployees);
        document.getElementById('roleFilter').addEventListener('change', loadEmployees);
        document.getElementById('workTypeFilter').addEventListener('change', loadEmployees);

        // Load approval badge count
        async function loadApprovalBadge() {
            try {
                const checkoutsPromise = ApprovalsAPI.getPendingCheckouts().catch(() => ({ success: true, data: [] }));
                const leavesPromise = ApprovalsAPI.getPendingLeaves().catch(() => ({ success: true, data: [] }));

                const [checkouts, leaves] = await Promise.all([checkoutsPromise, leavesPromise]);

                const checkoutCount = (checkouts.success && checkouts.data) ? checkouts.data.length : 0;
                const leaveCount = (leaves.success && leaves.data) ? leaves.data.length : 0;
                const totalPending = checkoutCount + leaveCount;

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
                }
            } catch (error) {
                console.error('Failed to load approval badge:', error);
            }
        }

        // Initial load
        loadEmployees();
        loadApprovalBadge();
        loadOfficesForFilters();

        async function loadOfficesForFilters() {
            try {
                const result = await OfficesAPI.getAll();
                
                if (result.success) {
                    const filterSelect = document.getElementById('officeFilter');
                    // Keep first option "Semua Kantor"
                    filterSelect.innerHTML = '<option value="">Semua Kantor</option>';
                    
                    result.data.forEach(office => {
                        if (office.is_active) {
                            const option = document.createElement('option');
                            option.value = office.id;
                            option.textContent = office.name;
                            filterSelect.appendChild(option);
                        }
                    });
                }
            } catch (error) {
                console.error('Failed to load offices for filters:', error);
            }
        }
    </script>
</body>
</html>
