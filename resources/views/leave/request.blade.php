<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajukan Cuti/Izin - Sistem Absensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <!-- Header -->
        <div class="mb-6">
            <a href="/dashboard" class="inline-flex items-center text-blue-600 hover:text-blue-700 mb-4">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Dashboard
            </a>
            <h1 class="text-3xl font-bold text-gray-800">Ajukan Cuti/Izin</h1>
            <p class="text-gray-600">Silakan isi form di bawah untuk mengajukan cuti atau izin</p>
        </div>

        <!-- Quota Card -->
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 mb-6 border-2 border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-blue-700 mb-1">Sisa Cuti Tahunan</p>
                    <h2 class="text-4xl font-bold text-blue-900" id="quota-remaining">-</h2>
                    <p class="text-sm text-blue-600 mt-1">dari <span id="quota-total">-</span> hari</p>
                </div>
                <div class="text-center">
                    <div class="bg-white rounded-full w-24 h-24 flex items-center justify-center shadow-lg">
                        <i class="fas fa-calendar-check text-4xl text-blue-600"></i>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 pt-4 border-t border-blue-200 grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-blue-600">Digunakan</p>
                    <p class="font-bold text-blue-900"><span id="quota-used">-</span> hari</p>
                </div>
                <div>
                    <p class="text-blue-600">Menunggu Approval</p>
                    <p class="font-bold text-yellow-700"><span id="quota-pending">-</span> hari</p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <form id="leave-form">
                <!-- Leave Type -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-3">
                        <i class="fas fa-clipboard-list mr-2 text-blue-600"></i>
                        Jenis Permohonan <span class="text-red-500">*</span>
                    </label>
                    <div class="space-y-3">
                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition">
                            <input type="radio" name="leave_type" value="cuti_tahunan" class="w-5 h-5 text-blue-600" required>
                            <div class="ml-3 flex-1">
                                <span class="font-semibold text-gray-800">
                                    <i class="fas fa-umbrella-beach mr-2 text-green-600"></i>
                                    Cuti Tahunan
                                </span>
                                <p class="text-sm text-gray-600">Menggunakan quota cuti tahunan</p>
                            </div>
                        </label>
                        
                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition">
                            <input type="radio" name="leave_type" value="izin" class="w-5 h-5 text-blue-600" required>
                            <div class="ml-3 flex-1">
                                <span class="font-semibold text-gray-800">
                                    <i class="fas fa-clock mr-2 text-yellow-600"></i>
                                    Izin
                                </span>
                                <p class="text-sm text-gray-600">Tidak memotong cuti tahunan</p>
                            </div>
                        </label>
                        
                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition">
                            <input type="radio" name="leave_type" value="sakit" class="w-5 h-5 text-blue-600" required>
                            <div class="ml-3 flex-1">
                                <span class="font-semibold text-gray-800">
                                    <i class="fas fa-notes-medical mr-2 text-red-600"></i>
                                    Sakit
                                </span>
                                <p class="text-sm text-gray-600">Wajib upload surat dokter jika > 2 hari</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Date Range -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">
                        <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>
                        Tanggal <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Mulai</label>
                            <input type="date" name="start_date" id="start_date" required
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Selesai</label>
                            <input type="date" name="end_date" id="end_date" required
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 mt-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Durasi: <span id="duration-display" class="font-semibold text-blue-600">0 hari kerja</span>
                    </p>
                </div>

                <!-- Reason (only for izin and sakit) -->
                <div class="mb-6 hidden" id="reason-section">
                    <label class="block text-gray-700 font-semibold mb-2">
                        <i class="fas fa-comment-alt mr-2 text-blue-600"></i>
                        Alasan <span class="text-red-500">*</span>
                    </label>
                    <textarea name="reason" id="reason" rows="4"
                        placeholder="Jelaskan alasan pengajuan Anda (minimal 10 karakter)"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none resize-none"
                        maxlength="500"></textarea>
                    <p class="text-sm text-gray-500 mt-1">
                        <span id="char-count">0</span>/500 karakter
                    </p>
                </div>

                <!-- File Upload -->
                <div class="mb-6" id="attachment-section">
                    <label class="block text-gray-700 font-semibold mb-2">
                        <i class="fas fa-paperclip mr-2 text-blue-600"></i>
                        Lampiran <span id="attachment-required" class="hidden text-red-500">*</span>
                    </label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition">
                        <input type="file" name="attachment" id="attachment" accept=".jpg,.jpeg,.png,.pdf"
                            class="hidden" onchange="handleFileSelect(event)">
                        <label for="attachment" class="cursor-pointer">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                            <p class="text-gray-600">Klik untuk upload file</p>
                            <p class="text-sm text-gray-500 mt-1">JPG, PNG, atau PDF (Max 2MB)</p>
                        </label>
                        <div id="file-preview" class="hidden mt-4">
                            <div class="inline-flex items-center bg-blue-50 px-4 py-2 rounded-lg">
                                <i class="fas fa-file-alt text-blue-600 mr-2"></i>
                                <span id="file-name" class="text-sm text-gray-700"></span>
                                <button type="button" onclick="removeFile()" class="ml-3 text-red-500 hover:text-red-700">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <p class="text-sm text-yellow-700 mt-2 hidden" id="attachment-note">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Wajib upload surat dokter untuk sakit lebih dari 2 hari
                    </p>
                </div>

                <!-- Submit -->
                <div class="space-y-3">
                    <button type="submit" id="submit-btn"
                        class="w-full bg-blue-600 text-white py-4 rounded-lg font-bold text-lg hover:bg-blue-700 transition">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Ajukan Permohonan
                    </button>
                    <a href="/leave/history" class="block w-full bg-gray-500 text-white py-3 rounded-lg font-semibold text-center hover:bg-gray-600 transition">
                        <i class="fas fa-history mr-2"></i>
                        Lihat Riwayat Pengajuan
                    </a>
                </div>
            </form>

            <div id="loading-section" class="hidden text-center py-8">
                <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
                <p class="text-gray-600 text-lg">Memproses pengajuan...</p>
            </div>
        </div>
    </div>

    <script src="/js/api-helper.js"></script>
    <script>
        let quotaData = null;

        // Load quota on page load
        window.addEventListener('load', async () => {
            await loadQuota();
            setupFormHandlers();
        });

        async function loadQuota() {
            try {
                const response = await apiRequest('/leave-requests/quota', 'GET');
                if (response.success) {
                    quotaData = response.data;
                    document.getElementById('quota-total').textContent = quotaData.annual_quota;
                    document.getElementById('quota-remaining').textContent = quotaData.remaining;
                    document.getElementById('quota-used').textContent = quotaData.used;
                    document.getElementById('quota-pending').textContent = quotaData.pending;
                }
            } catch (error) {
                console.error('Error loading quota:', error);
            }
        }

        function setupFormHandlers() {
            // Radio button change - highlight selected
            const radioButtons = document.querySelectorAll('input[name="leave_type"]');
            radioButtons.forEach(radio => {
                radio.addEventListener('change', function() {
                    // Remove highlight from all
                    document.querySelectorAll('label:has(input[name="leave_type"])').forEach(label => {
                        label.classList.remove('border-blue-500', 'bg-blue-100');
                    });
                    // Highlight selected
                    this.closest('label').classList.add('border-blue-500', 'bg-blue-100');
                    
                    // Show/hide reason section based on leave type
                    const reasonSection = document.getElementById('reason-section');
                    const reasonField = document.getElementById('reason');
                    
                    if (this.value === 'cuti_tahunan') {
                        // Hide reason field for annual leave
                        reasonSection.classList.add('hidden');
                        reasonField.removeAttribute('required');
                        reasonField.value = ''; // Clear any existing value
                    } else {
                        // Show reason field for izin and sakit
                        reasonSection.classList.remove('hidden');
                        reasonField.setAttribute('required', 'required');
                        if (this.value === 'izin') {
                            reasonField.placeholder = 'Jelaskan alasan izin Anda (minimal 10 karakter)';
                        } else {
                            reasonField.placeholder = 'Jelaskan alasan sakit Anda (minimal 10 karakter)';
                        }
                    }
                    
                    // Show attachment note for sakit
                    if (this.value === 'sakit') {
                        document.getElementById('attachment-note').classList.remove('hidden');
                    } else {
                        document.getElementById('attachment-note').classList.add('hidden');
                    }
                });
            });

            // Date change - calculate duration
            document.getElementById('start_date').addEventListener('change', calculateDuration);
            document.getElementById('end_date').addEventListener('change', calculateDuration);

            // Reason character count
            document.getElementById('reason').addEventListener('input', function() {
                document.getElementById('char-count').textContent = this.value.length;
            });

            // Form submit
            document.getElementById('leave-form').addEventListener('submit', handleSubmit);
        }

        function calculateDuration() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;

            if (!startDate || !endDate) return;

            const start = new Date(startDate);
            const end = new Date(endDate);
            let duration = 0;

            // Count weekdays only
            for (let date = new Date(start); date <= end; date.setDate(date.getDate() + 1)) {
                const day = date.getDay();
                if (day !== 0 && day !== 6) { // Not Sunday or Saturday
                    duration++;
                }
            }

            document.getElementById('duration-display').textContent = `${duration} hari kerja`;

            // Validate quota for annual leave
            const leaveType = document.querySelector('input[name="leave_type"]:checked');
            if (leaveType && leaveType.value === 'cuti_tahunan' && quotaData) {
                if (duration > quotaData.available) {
                    alert(`⚠️ Quota cuti tidak cukup!\n\nDurasi: ${duration} hari\nSisa quota: ${quotaData.available} hari`);
                }
            }
        }

        function handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                // Validate size (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('File terlalu besar! Maksimal 2MB');
                    event.target.value = '';
                    return;
                }

                document.getElementById('file-name').textContent = file.name;
                document.getElementById('file-preview').classList.remove('hidden');
            }
        }

        function removeFile() {
            document.getElementById('attachment').value = '';
            document.getElementById('file-preview').classList.add('hidden');
        }

        async function handleSubmit(event) {
            event.preventDefault();

            const formData = new FormData(event.target);
            const leaveType = formData.get('leave_type');
            const startDate = formData.get('start_date');
            const endDate = formData.get('end_date');
            const reason = formData.get('reason');

            // Validate
            if (!leaveType || !startDate || !endDate) {
                alert('Harap isi semua field yang wajib!');
                return;
            }

            // Validate reason only for izin and sakit
            if ((leaveType === 'izin' || leaveType === 'sakit') && (!reason || reason.length < 10)) {
                alert('Alasan harus minimal 10 karakter untuk izin dan sakit!');
                return;
            }

            // Check duration for sakit > 2 days requires attachment
            const start = new Date(startDate);
            const end = new Date(endDate);
            let duration = 0;
            for (let date = new Date(start); date <= end; date.setDate(date.getDate() + 1)) {
                const day = date.getDay();
                if (day !== 0 && day !== 6) duration++;
            }

            if (leaveType === 'sakit' && duration > 2 && !formData.get('attachment')) {
                alert('Sakit lebih dari 2 hari wajib upload surat dokter!');
                return;
            }

            // Show loading
            document.getElementById('leave-form').classList.add('hidden');
            document.getElementById('loading-section').classList.remove('hidden');

            try {
                const response = await fetch('/api/leave-requests', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('token')}`,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    alert('✅ ' + data.message);
                    window.location.href = '/leave/history';
                } else {
                    alert('❌ ' + data.message);
                    document.getElementById('leave-form').classList.remove('hidden');
                    document.getElementById('loading-section').classList.add('hidden');
                }
            } catch (error) {
                alert('Terjadi kesalahan: ' + error.message);
                document.getElementById('leave-form').classList.remove('hidden');
                document.getElementById('loading-section').classList.add('hidden');
            }
        }
    </script>
</body>
</html>
