<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Cuti/Izin - Sistem Absensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Header -->
        <div class="mb-6">
            <a href="/dashboard" class="inline-flex items-center text-blue-600 hover:text-blue-700 mb-4">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Dashboard
            </a>
            <h1 class="text-3xl font-bold text-gray-800">Riwayat Cuti & Izin</h1>
            <p class="text-gray-600">Daftar pengajuan cuti dan izin Anda</p>
        </div>

        <!-- Quota Summary -->
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 mb-6 border-2 border-blue-200">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                <div class="bg-white rounded-lg p-4 shadow">
                    <p class="text-sm text-gray-600 mb-1">Total Quota</p>
                    <p class="text-2xl font-bold text-blue-900" id="quota-total">-</p>
                    <p class="text-xs text-gray-500">hari</p>
                </div>
                <div class="bg-white rounded-lg p-4 shadow">
                    <p class="text-sm text-gray-600 mb-1">Sisa</p>
                    <p class="text-2xl font-bold text-green-600" id="quota-remaining">-</p>
                    <p class="text-xs text-gray-500">hari</p>
                </div>
                <div class="bg-white rounded-lg p-4 shadow">
                    <p class="text-sm text-gray-600 mb-1">Digunakan</p>
                    <p class="text-2xl font-bold text-gray-700" id="quota-used">-</p>
                    <p class="text-xs text-gray-500">hari</p>
                </div>
                <div class="bg-white rounded-lg p-4 shadow">
                    <p class="text-sm text-gray-600 mb-1">Pending</p>
                    <p class="text-2xl font-bold text-yellow-600" id="quota-pending">-</p>
                    <p class="text-xs text-gray-500">hari</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mb-6">
            <a href="/leave/request" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                <i class="fas fa-plus mr-2"></i>
                Ajukan Cuti/Izin Baru
            </a>
        </div>

        <!-- Filter -->
        <div class="bg-white rounded-xl shadow p-4 mb-6">
            <div class="flex flex-wrap gap-3">
                <button onclick="filterByStatus('all')" class="filter-btn active px-4 py-2 rounded-lg font-semibold transition" data-status="all">
                    <i class="fas fa-list mr-2"></i>Semua
                </button>
                <button onclick="filterByStatus('pending')" class="filter-btn px-4 py-2 rounded-lg font-semibold transition" data-status="pending">
                    <i class="fas fa-clock mr-2"></i>Pending
                </button>
                <button onclick="filterByStatus('approved')" class="filter-btn px-4 py-2 rounded-lg font-semibold transition" data-status="approved">
                    <i class="fas fa-check-circle mr-2"></i>Disetujui
                </button>
                <button onclick="filterByStatus('rejected')" class="filter-btn px-4 py-2 rounded-lg font-semibold transition" data-status="rejected">
                    <i class="fas fa-times-circle mr-2"></i>Ditolak
                </button>
            </div>
        </div>

        <!-- Loading -->
        <div id="loading" class="text-center py-12">
            <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
            <p class="text-gray-600">Memuat data...</p>
        </div>

        <!-- No Data -->
        <div id="no-data" class="hidden bg-white rounded-xl shadow-lg p-12 text-center">
            <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-bold text-gray-700 mb-2">Belum Ada Pengajuan</h3>
            <p class="text-gray-600 mb-6">Anda belum pernah mengajukan cuti atau izin</p>
            <a href="/leave/request" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                <i class="fas fa-plus mr-2"></i>
                Ajukan Sekarang
            </a>
        </div>

        <!-- Leave Requests List -->
        <div id="leave-list" class="space-y-4">
            <!-- Will be populated by JavaScript -->
        </div>
    </div>

    <script src="/js/api-helper.js"></script>
    <script>
        let allLeaveRequests = [];
        let currentFilter = 'all';

        window.addEventListener('load', async () => {
            await loadQuota();
            await loadLeaveRequests();
        });

        async function loadQuota() {
            try {
                const response = await apiRequest('/leave-requests/quota', 'GET');
                if (response.success) {
                    const quota = response.data;
                    document.getElementById('quota-total').textContent = quota.annual_quota;
                    document.getElementById('quota-remaining').textContent = quota.remaining;
                    document.getElementById('quota-used').textContent = quota.used;
                    document.getElementById('quota-pending').textContent = quota.pending;
                }
            } catch (error) {
                console.error('Error loading quota:', error);
            }
        }

        async function loadLeaveRequests() {
            try {
                const response = await apiRequest('/leave-requests', 'GET');
                
                if (response.success) {
                    allLeaveRequests = response.data.leave_requests;
                    document.getElementById('loading').classList.add('hidden');
                    
                    if (allLeaveRequests.length === 0) {
                        document.getElementById('no-data').classList.remove('hidden');
                    } else {
                        renderLeaveRequests();
                    }
                }
            } catch (error) {
                console.error('Error loading leave requests:', error);
                document.getElementById('loading').classList.add('hidden');
                alert('Gagal memuat data: ' + error.message);
            }
        }

        function filterByStatus(status) {
            currentFilter = status;
            
            // Update active button
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active', 'bg-blue-600', 'text-white');
                btn.classList.add('bg-gray-200', 'text-gray-700');
            });
            const activeBtn = document.querySelector(`[data-status="${status}"]`);
            activeBtn.classList.add('active', 'bg-blue-600', 'text-white');
            activeBtn.classList.remove('bg-gray-200', 'text-gray-700');
            
            renderLeaveRequests();
        }

        function renderLeaveRequests() {
            const container = document.getElementById('leave-list');
            
            // Filter requests
            let filtered = allLeaveRequests;
            if (currentFilter !== 'all') {
                filtered = allLeaveRequests.filter(req => req.status === currentFilter);
            }

            if (filtered.length === 0) {
                container.innerHTML = `
                    <div class="bg-white rounded-xl shadow p-8 text-center">
                        <i class="fas fa-filter text-4xl text-gray-300 mb-3"></i>
                        <p class="text-gray-600">Tidak ada data untuk filter ini</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = filtered.map(req => createLeaveCard(req)).join('');
        }

        function createLeaveCard(leave) {
            const statusConfig = {
                pending: { color: 'yellow', icon: 'clock', text: 'Menunggu Approval' },
                approved: { color: 'green', icon: 'check-circle', text: 'Disetujui' },
                rejected: { color: 'red', icon: 'times-circle', text: 'Ditolak' },
                cancelled: { color: 'gray', icon: 'ban', text: 'Dibatalkan' }
            };

            const typeConfig = {
                cuti_tahunan: { icon: 'umbrella-beach', text: 'Cuti Tahunan', color: 'green' },
                izin: { icon: 'clock', text: 'Izin', color: 'yellow' },
                sakit: { icon: 'notes-medical', text: 'Sakit', color: 'red' }
            };

            const status = statusConfig[leave.status];
            const type = typeConfig[leave.leave_type];
            
            const startDate = new Date(leave.start_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
            const endDate = new Date(leave.end_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
            
            const canCancel = leave.status === 'pending';

            return `
                <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center">
                            <div class="bg-${type.color}-100 rounded-full p-3 mr-4">
                                <i class="fas fa-${type.icon} text-2xl text-${type.color}-600"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 text-lg">${type.text}</h3>
                                <p class="text-sm text-gray-600">${leave.duration_days} hari kerja</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold bg-${status.color}-100 text-${status.color}-700">
                                <i class="fas fa-${status.icon} mr-1"></i>
                                ${status.text}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-2 mb-4">
                        <div class="flex items-center text-gray-700">
                            <i class="fas fa-calendar-alt w-5 mr-3 text-blue-600"></i>
                            <span class="font-semibold">${startDate} - ${endDate}</span>
                        </div>
                        <div class="flex items-start text-gray-700">
                            <i class="fas fa-comment-alt w-5 mr-3 text-blue-600 mt-1"></i>
                            <span class="text-sm">${leave.reason}</span>
                        </div>
                        ${leave.attachment_path ? `
                            <div class="flex items-center text-gray-700">
                                <i class="fas fa-paperclip w-5 mr-3 text-blue-600"></i>
                                <a href="/storage/${leave.attachment_path}" target="_blank" class="text-sm text-blue-600 hover:underline">
                                    Lihat Lampiran
                                </a>
                            </div>
                        ` : ''}
                    </div>

                    ${leave.rejection_reason ? `
                        <div class="bg-red-50 border-l-4 border-red-400 p-3 mb-4">
                            <p class="text-sm text-red-700">
                                <strong>Alasan Penolakan:</strong> ${leave.rejection_reason}
                            </p>
                        </div>
                    ` : ''}

                    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                        <p class="text-xs text-gray-500">
                            Diajukan: ${new Date(leave.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })}
                        </p>
                        ${canCancel ? `
                            <button onclick="cancelLeave(${leave.id})" class="text-sm text-red-600 hover:text-red-700 font-semibold">
                                <i class="fas fa-times mr-1"></i>
                                Batalkan
                            </button>
                        ` : ''}
                    </div>
                </div>
            `;
        }

        async function cancelLeave(leaveId) {
            if (!confirm('Yakin ingin membatalkan pengajuan ini?')) {
                return;
            }

            try {
                const response = await apiRequest(`/leave-requests/${leaveId}/cancel`, 'POST');
                
                if (response.success) {
                    alert('✅ Pengajuan berhasil dibatalkan');
                    await loadLeaveRequests();
                    await loadQuota();
                } else {
                    alert('❌ ' + response.message);
                }
            } catch (error) {
                alert('Terjadi kesalahan: ' + error.message);
            }
        }
    </script>
    
    <style>
        .filter-btn.active {
            background-color: #2563eb;
            color: white;
        }
        .filter-btn:not(.active) {
            background-color: #e5e7eb;
            color: #374151;
        }
        .filter-btn:not(.active):hover {
            background-color: #d1d5db;
        }
    </style>
</body>
</html>
