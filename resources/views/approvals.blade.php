<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Persetujuan - Sistem Absensi</title>
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
            <a href="/approvals" data-role-required="admin,supervisor" class="flex items-center px-6 py-3 bg-gray-800 border-l-4 border-blue-500">
                <i class="fas fa-check-circle w-5 mr-3"></i>
                Persetujuan
                <span id="badgeSidebar" class="ml-auto bg-red-500 text-xs px-2 py-1 rounded-full">0</span>
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
            <h2 class="text-3xl font-bold text-gray-800">Persetujuan</h2>
            <p class="text-gray-600">Kelola permohonan dan persetujuan karyawan</p>
        </div>

        <!-- Tabs -->
        <div class="mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button onclick="showTab('checkout')" id="tabCheckout" class="tab-btn border-b-2 border-blue-500 py-4 px-1 text-sm font-medium text-blue-600">
                        Checkout Luar Area
                        <span id="badgeCheckout" class="ml-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full">0</span>
                    </button>
                    <button onclick="showTab('leave')" id="tabLeave" class="tab-btn border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        Cuti/Izin
                        <span id="badgeLeave" class="ml-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full">0</span>
                    </button>
                    <button onclick="showTab('overtime')" id="tabOvertime" class="tab-btn border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        Konfirmasi Lembur
                        <span id="badgeOvertime" class="ml-2 bg-gray-300 text-gray-700 text-xs px-2 py-1 rounded-full">0</span>
                    </button>
                </nav>
            </div>
        </div>

        <!-- Checkout Tab -->
        <div id="contentCheckout" class="tab-content">
            <div class="bg-white rounded-lg shadow mb-4">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-800">Persetujuan Checkout Luar Area Geofence</h3>
                    <p class="text-sm text-gray-600 mt-1">Karyawan yang checkout di luar area kantor memerlukan persetujuan</p>
                </div>
                <div id="checkoutContainer" class="divide-y divide-gray-200">
                    <!-- Data will be loaded from API -->
                </div>
            </div>
        </div>

        <!-- Leave Tab -->
        <div id="contentLeave" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow mb-4">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-800">Permohonan Cuti dan Izin</h3>
                    <p class="text-sm text-gray-600 mt-1">Persetujuan untuk cuti, izin sakit, atau izin lainnya</p>
                </div>
                <div id="leaveContainer" class="divide-y divide-gray-200">
                    <!-- Data will be loaded from API -->
                </div>
            </div>
        </div>

        <!-- Overtime Tab -->
        <div id="contentOvertime" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Tidak Ada Konfirmasi Lembur</h3>
                <p class="text-gray-600">Semua lembur sudah dikonfirmasi</p>
            </div>
        </div>
    </div>

    <!-- Rejection Modal -->
    <div id="rejectionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md m-4">
            <div class="p-6 border-b">
                <h3 class="text-xl font-semibold text-gray-800">Alasan Penolakan</h3>
            </div>
            <div class="p-6">
                <textarea id="rejectionReason" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Masukkan alasan penolakan..."></textarea>
            </div>
            <div class="p-6 border-t flex justify-end space-x-3">
                <button onclick="closeRejectionModal()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center">
                    <i class="fas fa-times mr-2"></i>
                    Batal
                </button>
                <button onclick="confirmRejection()" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition flex items-center">
                    <i class="fas fa-ban mr-2"></i>
                    Tolak
                </button>
            </div>
        </div>
    </div>

    <script>
        // Check authentication
        checkAuth();
        const user = JSON.parse(localStorage.getItem('user'));
        document.getElementById('userName').textContent = user.name;
        document.getElementById('userRole').textContent = user.role === 'admin' ? 'Administrator' :
            user.role === 'supervisor' ? 'Supervisor' : 'Karyawan';

        // Setup role-based navigation
        setupRoleBasedNavigation();

        // Global state
        let pendingCheckouts = [];
        let pendingLeaves = [];
        let currentRejectionId = null;
        let currentRejectionType = null;
        let currentLeaveData = null;

        // Logout
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

        // Load pending checkouts
        async function loadPendingCheckouts() {
            try {
                const result = await ApprovalsAPI.getPendingCheckouts();
                if (result.success) {
                    pendingCheckouts = result.data;
                    renderCheckouts();
                    updateBadges();
                }
            } catch (error) {
                console.error('Failed to load pending checkouts:', error);
                showAlert('Gagal memuat data checkout: ' + error.message, 'error');
            }
        }

        // Load pending leaves
        async function loadPendingLeaves() {
            try {
                const result = await ApprovalsAPI.getPendingLeaves();
                if (result.success) {
                    pendingLeaves = result.data;
                    renderLeaves();
                    updateBadges();
                }
            } catch (error) {
                console.error('Failed to load pending leaves:', error);
                showAlert('Gagal memuat data cuti/izin: ' + error.message, 'error');
            }
        }

        // Render checkouts
        function renderCheckouts() {
            const container = document.getElementById('checkoutContainer');

            if (pendingCheckouts.length === 0) {
                container.innerHTML = `
                    <div class="p-12 text-center">
                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Tidak Ada Persetujuan Pending</h3>
                        <p class="text-gray-600">Semua checkout sudah disetujui</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = pendingCheckouts.map(checkout => {
                const checkInTime = new Date(checkout.check_in_time);
                const checkOutTime = new Date(checkout.check_out_time);
                const hours = Math.floor(checkout.duration_minutes / 60);
                const minutes = checkout.duration_minutes % 60;

                return `
                    <div class="p-6 hover:bg-gray-50 transition border-b">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start space-x-4 flex-1">
                                <div class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-lg">
                                    ${checkout.user.name.charAt(0).toUpperCase()}
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center">
                                        <h4 class="text-lg font-semibold text-gray-900">${checkout.user.name}</h4>
                                        <span class="ml-3 px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">${checkout.user.office} • ${checkout.date}</p>

                                    <div class="mt-4 grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-xs text-gray-500 mb-1">Check-in</p>
                                            <p class="text-sm font-medium text-gray-900">${checkInTime.toLocaleTimeString('id-ID')}</p>
                                            <p class="text-xs text-green-600">✓ Di area geofence</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 mb-1">Check-out</p>
                                            <p class="text-sm font-medium text-gray-900">${checkOutTime.toLocaleTimeString('id-ID')}</p>
                                            <p class="text-xs text-red-600">✗ Luar area geofence</p>
                                        </div>
                                    </div>

                                    ${checkout.out_of_office_reason ? `
                                    <div class="mt-4 bg-gray-50 p-4 rounded-lg">
                                        <p class="text-xs font-medium text-gray-700 mb-2">Alasan:</p>
                                        <p class="text-sm text-gray-900">${checkout.out_of_office_reason}</p>
                                    </div>
                                    ` : ''}

                                    <div class="mt-4 flex items-center text-sm text-gray-600">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Durasi kerja: ${hours} jam ${minutes} menit
                                    </div>
                                </div>
                            </div>

                            <div class="ml-6 flex flex-col space-y-2">
                                <button onclick="approveCheckout(${checkout.id})" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Setujui
                                </button>
                                <button onclick="rejectCheckout(${checkout.id})" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Tolak
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Render leaves
        function renderLeaves() {
            const container = document.getElementById('leaveContainer');

            if (pendingLeaves.length === 0) {
                container.innerHTML = `
                    <div class="p-12 text-center">
                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Tidak Ada Permohonan Pending</h3>
                        <p class="text-gray-600">Semua cuti/izin sudah disetujui</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = pendingLeaves.map(leave => {
                const startDate = new Date(leave.start_date);
                const endDate = new Date(leave.end_date);
                const createdAt = new Date(leave.created_at);
                const leaveTypeName = getLeaveTypeName(leave.type);
                
                // Determine badge color based on type
                let badgeColor = 'bg-blue-100 text-blue-800';
                if (leave.type === 'sakit') badgeColor = 'bg-red-100 text-red-800';
                if (leave.type === 'izin') badgeColor = 'bg-yellow-100 text-yellow-800';
                if (leave.type === 'cuti_tahunan') badgeColor = 'bg-green-100 text-green-800';

                return `
                    <div class="p-6 hover:bg-gray-50 transition border-b">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start space-x-4 flex-1">
                                <div class="w-12 h-12 rounded-full bg-green-600 flex items-center justify-center text-white font-bold text-lg">
                                    ${leave.user.name.charAt(0).toUpperCase()}
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center">
                                        <h4 class="text-lg font-semibold text-gray-900">${leave.user.name}</h4>
                                        <span class="ml-3 px-2 py-1 text-xs font-semibold rounded-full ${badgeColor}">
                                            ${leaveTypeName}
                                        </span>
                                        <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">${leave.user.office}</p>

                                    <div class="mt-4 grid grid-cols-3 gap-4">
                                        <div>
                                            <p class="text-xs text-gray-500 mb-1">Tanggal Mulai</p>
                                            <p class="text-sm font-medium text-gray-900">${startDate.toLocaleDateString('id-ID')}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 mb-1">Tanggal Selesai</p>
                                            <p class="text-sm font-medium text-gray-900">${endDate.toLocaleDateString('id-ID')}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 mb-1">Durasi</p>
                                            <p class="text-sm font-medium text-gray-900">${leave.days_count} hari</p>
                                        </div>
                                    </div>

                                    <div class="mt-4 bg-gray-50 p-4 rounded-lg">
                                        <p class="text-xs font-medium text-gray-700 mb-2">Alasan:</p>
                                        <p class="text-sm text-gray-900">${leave.reason}</p>
                                    </div>

                                    <div class="mt-4 text-xs text-gray-500">
                                        Diajukan pada: ${createdAt.toLocaleString('id-ID')}
                                    </div>
                                </div>
                            </div>

                            <div class="ml-6 flex flex-col space-y-2">
                                <button onclick="approveLeave(${leave.id})" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Setujui
                                </button>
                                <button onclick="rejectLeave(${leave.id})" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Tolak
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.tab-btn').forEach(el => {
                el.classList.remove('border-blue-500', 'text-blue-600');
                el.classList.add('border-transparent', 'text-gray-500');
            });

            // Show selected tab
            document.getElementById('content' + tabName.charAt(0).toUpperCase() + tabName.slice(1)).classList.remove('hidden');
            const tabBtn = document.getElementById('tab' + tabName.charAt(0).toUpperCase() + tabName.slice(1));
            tabBtn.classList.remove('border-transparent', 'text-gray-500');
            tabBtn.classList.add('border-blue-500', 'text-blue-600');
        }

        async function approveCheckout(id) {
            if (!confirm('Apakah Anda yakin ingin menyetujui checkout ini?')) {
                return;
            }

            try {
                const result = await ApprovalsAPI.approveCheckout(id);
                if (result.success) {
                    showAlert('Checkout berhasil disetujui');
                    loadPendingCheckouts();
                }
            } catch (error) {
                showAlert('Gagal menyetujui checkout: ' + error.message, 'error');
            }
        }

        function rejectCheckout(id) {
            currentRejectionId = id;
            currentRejectionType = 'checkout';
            document.getElementById('rejectionModal').classList.remove('hidden');
        }

        async function approveLeave(id) {
            // Find the leave data to get its type
            const leave = pendingLeaves.find(l => l.id === id);
            const leaveTypeName = getLeaveTypeName(leave ? leave.type : '');
            
            if (!confirm(`Apakah Anda yakin ingin menyetujui ${leaveTypeName} ini?`)) {
                return;
            }

            try {
                const result = await ApprovalsAPI.approveLeave(id);
                if (result.success) {
                    showAlert(`${leaveTypeName} berhasil disetujui`);
                    loadPendingLeaves();
                }
            } catch (error) {
                showAlert(`Gagal menyetujui ${leaveTypeName}: ` + error.message, 'error');
            }
        }

        function rejectLeave(id) {
            currentRejectionId = id;
            currentRejectionType = 'leave';
            // Store leave data for rejection message
            const leave = pendingLeaves.find(l => l.id === id);
            currentLeaveData = leave;
            document.getElementById('rejectionModal').classList.remove('hidden');
        }
        
        // Helper function to get friendly leave type name
        function getLeaveTypeName(type) {
            const typeMap = {
                'cuti_tahunan': 'Cuti Tahunan',
                'izin': 'Izin',
                'sakit': 'Sakit'
            };
            return typeMap[type] || 'Cuti/Izin';
        }

        function closeRejectionModal() {
            document.getElementById('rejectionModal').classList.add('hidden');
            document.getElementById('rejectionReason').value = '';
            currentRejectionId = null;
            currentRejectionType = null;
            currentLeaveData = null;
        }

        async function confirmRejection() {
            const reason = document.getElementById('rejectionReason').value.trim();
            if (!reason) {
                showAlert('Mohon masukkan alasan penolakan', 'error');
                return;
            }

            try {
                let result;
                if (currentRejectionType === 'checkout') {
                    result = await ApprovalsAPI.rejectCheckout(currentRejectionId, reason);
                    if (result.success) {
                        showAlert('Checkout berhasil ditolak');
                        loadPendingCheckouts();
                    }
                } else if (currentRejectionType === 'leave') {
                    const leaveTypeName = getLeaveTypeName(currentLeaveData ? currentLeaveData.type : '');
                    result = await ApprovalsAPI.rejectLeave(currentRejectionId, reason);
                    if (result.success) {
                        showAlert(`${leaveTypeName} berhasil ditolak`);
                        loadPendingLeaves();
                    }
                }
                closeRejectionModal();
            } catch (error) {
                showAlert('Gagal menolak: ' + error.message, 'error');
            }
        }

        // Update badge counts
        function updateBadges() {
            const totalPending = pendingCheckouts.length + pendingLeaves.length;

            // Update tab badges
            const badgeCheckout = document.getElementById('badgeCheckout');
            const badgeLeave = document.getElementById('badgeLeave');
            const badgeSidebar = document.getElementById('badgeSidebar');

            badgeCheckout.textContent = pendingCheckouts.length;
            badgeLeave.textContent = pendingLeaves.length;
            badgeSidebar.textContent = totalPending;

            // Update badge colors based on count
            if (pendingCheckouts.length === 0) {
                badgeCheckout.classList.remove('bg-red-500');
                badgeCheckout.classList.add('bg-gray-300', 'text-gray-700');
            } else {
                badgeCheckout.classList.remove('bg-gray-300', 'text-gray-700');
                badgeCheckout.classList.add('bg-red-500', 'text-white');
            }

            if (pendingLeaves.length === 0) {
                badgeLeave.classList.remove('bg-red-500');
                badgeLeave.classList.add('bg-gray-300', 'text-gray-700');
            } else {
                badgeLeave.classList.remove('bg-gray-300', 'text-gray-700');
                badgeLeave.classList.add('bg-red-500', 'text-white');
            }

            if (totalPending === 0) {
                badgeSidebar.classList.remove('bg-red-500');
                badgeSidebar.classList.add('bg-gray-300', 'text-gray-700');
            } else {
                badgeSidebar.classList.remove('bg-gray-300', 'text-gray-700');
                badgeSidebar.classList.add('bg-red-500', 'text-white');
            }
        }

        // Initial load
        loadPendingCheckouts();
        loadPendingLeaves();
    </script>
</body>
</html>
