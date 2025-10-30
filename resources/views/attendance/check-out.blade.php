<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Check-out - Sistem Absensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <div class="bg-red-600 text-white py-4 shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold">Check-out Kehadiran</h1>
                    <p class="text-sm text-red-100" id="currentTime"></p>
                </div>
                <div class="text-right">
                    <p class="text-sm" id="userName">{{ auth()->user()->name ?? 'Karyawan' }}</p>
                    <p class="text-xs text-red-100">{{ auth()->user()->office->name ?? 'Office' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-6 max-w-4xl">
        <!-- Loading Active Session -->
        <div id="loading-session" class="bg-white rounded-xl shadow-lg p-8 text-center">
            <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
            <p class="text-gray-600">Mengambil data sesi aktif...</p>
        </div>

        <!-- No Active Session -->
        <div id="no-session" class="bg-white rounded-xl shadow-lg p-8 text-center hidden">
            <i class="fas fa-exclamation-circle text-5xl text-yellow-600 mb-4"></i>
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Tidak Ada Sesi Aktif</h2>
            <p class="text-gray-600 mb-6">Anda belum melakukan check-in hari ini atau sudah check-out.</p>
            <a href="/attendance/check-in" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700">
                <i class="fas fa-sign-in-alt mr-2"></i>Check-in
            </a>
        </div>

        <!-- Checkout Form -->
        <div id="checkout-form" class="hidden">
            <!-- Session Info -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <h2 class="text-xl font-bold mb-4 flex items-center">
                    <i class="fas fa-clock text-blue-600 mr-2"></i>
                    Informasi Sesi Kerja Hari Ini
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">Waktu Check-in</p>
                        <p class="text-lg font-bold text-blue-800" id="checkin-time"></p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">Durasi Kerja</p>
                        <p class="text-lg font-bold text-green-800" id="work-duration"></p>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">Target Durasi</p>
                        <p class="text-lg font-bold text-purple-800" id="target-duration"></p>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">Status</p>
                        <p class="text-lg font-bold" id="duration-status"></p>
                    </div>
                </div>
            </div>

            <!-- Warning for Insufficient Duration -->
            <div id="insufficient-warning" class="hidden bg-yellow-50 border-2 border-yellow-300 rounded-xl p-6 mb-6">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-3xl text-yellow-600 mr-4"></i>
                    <div>
                        <h3 class="text-lg font-bold text-yellow-800 mb-2">Durasi Kerja Kurang</h3>
                        <p class="text-yellow-700 mb-4">
                            Anda bekerja kurang dari durasi target. Apakah Anda tetap ingin check-out?
                        </p>
                        <div class="flex items-center space-x-4">
                            <button onclick="window.history.back()" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600">
                                <i class="fas fa-arrow-left mr-2"></i>Batal
                            </button>
                            <button onclick="confirmInsufficientDuration()" class="bg-yellow-600 text-white px-6 py-2 rounded-lg hover:bg-yellow-700">
                                <i class="fas fa-check mr-2"></i>Tetap Check-out
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overtime Confirmation -->
            <div id="overtime-section" class="hidden bg-blue-50 border-2 border-blue-300 rounded-xl p-6 mb-6">
                <div class="flex items-start">
                    <i class="fas fa-business-time text-3xl text-blue-600 mr-4"></i>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-blue-800 mb-2">Durasi Kerja Melebihi Target</h3>
                        <p class="text-blue-700 mb-4">
                            Anda bekerja lebih dari durasi target (<span id="overtime-hours" class="font-bold"></span> jam lebih).
                        </p>
                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input type="checkbox" id="is-overtime" class="w-5 h-5 text-blue-600">
                            <span class="font-semibold text-blue-800">Ya, saya lembur</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Work Detail Form -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <h2 class="text-xl font-bold mb-4 flex items-center">
                    <i class="fas fa-tasks text-blue-600 mr-2"></i>
                    Detail Pekerjaan Hari Ini
                </h2>
                
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Apa yang telah Anda kerjakan hari ini? <span class="text-red-500">*</span>
                    </label>
                    <textarea id="work-detail" rows="6" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Contoh: Menyelesaikan laporan penjualan bulan ini, meeting dengan klien XYZ untuk presentasi proposal, update database inventory, dll..."
                        minlength="10" maxlength="500"></textarea>
                    <div class="flex justify-between mt-1">
                        <p class="text-xs text-gray-500">10-500 karakter</p>
                        <p class="text-xs text-gray-500"><span id="char-count">0</span>/500</p>
                    </div>
                </div>
            </div>

            <!-- Location Warning -->
            <div id="location-warning" class="hidden bg-yellow-50 border-2 border-yellow-400 rounded-xl p-6 mb-6">
                <div class="flex items-start">
                    <i class="fas fa-map-marker-alt text-3xl text-yellow-600 mr-4"></i>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-yellow-800 mb-2">Anda Check-out di Luar Area Kantor</h3>
                        <p class="text-yellow-700 mb-4">
                            Lokasi check-out Anda berada di luar geofence kantor. Mohon konfirmasi untuk keperluan pencatatan dan perhitungan insentif.
                        </p>
                        
                        <!-- Clear Yes/No Choice -->
                        <div class="bg-white rounded-lg p-4 border-2 border-yellow-300">
                            <p class="font-semibold text-yellow-900 mb-3">
                                <i class="fas fa-question-circle mr-2"></i>
                                Apakah Anda sedang tugas/dinas luar kota?
                            </p>
                            <div class="space-y-3">
                                <label class="flex items-center space-x-3 cursor-pointer p-3 rounded-lg border-2 border-gray-200 hover:border-yellow-400 hover:bg-yellow-50 transition">
                                    <input type="radio" name="out-of-town" value="yes" class="w-5 h-5 text-yellow-600">
                                    <div class="flex-1">
                                        <span class="font-semibold text-gray-800">
                                            <i class="fas fa-plane-departure mr-2 text-blue-600"></i>
                                            Ya, tugas/dinas luar kota
                                        </span>
                                        <p class="text-sm text-gray-600">Penugasan di luar kota (berpotensi dapat insentif luar kota)</p>
                                    </div>
                                </label>
                                
                                <label class="flex items-center space-x-3 cursor-pointer p-3 rounded-lg border-2 border-gray-200 hover:border-yellow-400 hover:bg-yellow-50 transition">
                                    <input type="radio" name="out-of-town" value="no" class="w-5 h-5 text-yellow-600">
                                    <div class="flex-1">
                                        <span class="font-semibold text-gray-800">
                                            <i class="fas fa-building mr-2 text-green-600"></i>
                                            Tidak, masih dalam kota
                                        </span>
                                        <p class="text-sm text-gray-600">Meeting/kunjungan/tugas dalam kota (tidak dapat insentif luar kota)</p>
                                    </div>
                                </label>
                            </div>
                            
                            <div id="out-of-town-warning" class="hidden mt-3 p-3 bg-red-50 border-l-4 border-red-400 rounded">
                                <p class="text-sm text-red-700">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    <strong>Perhatian:</strong> Silakan pilih salah satu opsi sebelum melanjutkan check-out.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Section -->
            <div id="submit-section">
                <button onclick="submitCheckout()" class="w-full bg-red-600 text-white py-4 rounded-lg font-bold text-lg hover:bg-red-700 transition">
                    <i class="fas fa-sign-out-alt mr-2"></i>
                    Konfirmasi Check-out
                </button>
            </div>

            <div id="loading-submit" class="hidden text-center py-8">
                <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
                <p class="text-gray-600">Memproses check-out...</p>
            </div>
        </div>
    </div>

    <script src="/js/api-helper.js"></script>
    <script>
        let activeSession = null;
        let locationData = null;
        let isInsufficientConfirmed = false;

        // Update current time
        function updateTime() {
            const now = new Date();
            document.getElementById('currentTime').textContent = now.toLocaleString('id-ID', {
                weekday: 'long',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }
        updateTime();
        setInterval(updateTime, 1000);

        // Character counter
        document.getElementById('work-detail')?.addEventListener('input', function() {
            document.getElementById('char-count').textContent = this.value.length;
        });

        // Load active session
        async function loadActiveSession() {
            try {
                const response = await apiRequest('/attendance/history?month=' + new Date().toISOString().slice(0, 7));
                
                if (!response.success) {
                    showNoSession();
                    return;
                }

                // Find active session (no check_out_at)
                const sessions = response.data.sessions || [];
                activeSession = sessions.find(s => !s.check_out_at);

                if (!activeSession) {
                    showNoSession();
                    return;
                }

                displaySession(activeSession);
                getLocation();
                
            } catch (error) {
                console.error('Error loading session:', error);
                showNoSession();
            }
        }

        function showNoSession() {
            document.getElementById('loading-session').classList.add('hidden');
            document.getElementById('no-session').classList.remove('hidden');
        }

        function displaySession(session) {
            document.getElementById('loading-session').classList.add('hidden');
            document.getElementById('checkout-form').classList.remove('hidden');

            const checkinTime = new Date(session.check_in_at);
            const now = new Date();
            const durationMs = now - checkinTime;
            const durationHours = durationMs / (1000 * 60 * 60);
            const durationMinutes = Math.floor((durationMs / (1000 * 60)) % 60);
            const hours = Math.floor(durationHours);

            document.getElementById('checkin-time').textContent = checkinTime.toLocaleString('id-ID', {
                hour: '2-digit',
                minute: '2-digit'
            });

            document.getElementById('work-duration').textContent = `${hours} jam ${durationMinutes} menit`;

            // Determine target based on day of week
            const dayOfWeek = now.getDay();
            const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;
            const targetHours = isWeekend ? 5 : 8;
            
            document.getElementById('target-duration').textContent = `${targetHours} jam`;

            // Check if insufficient or overtime
            if (durationHours < targetHours) {
                const statusEl = document.getElementById('duration-status');
                statusEl.textContent = 'Kurang Durasi';
                statusEl.classList.add('text-yellow-600');
                document.getElementById('insufficient-warning').classList.remove('hidden');
            } else if (durationHours > targetHours) {
                const overtimeHours = (durationHours - targetHours).toFixed(1);
                const statusEl = document.getElementById('duration-status');
                statusEl.textContent = 'Melebihi Target';
                statusEl.classList.add('text-green-600');
                document.getElementById('overtime-section').classList.remove('hidden');
                document.getElementById('overtime-hours').textContent = overtimeHours;
            } else {
                const statusEl = document.getElementById('duration-status');
                statusEl.textContent = 'Sesuai Target';
                statusEl.classList.add('text-green-600');
            }
        }

        function confirmInsufficientDuration() {
            isInsufficientConfirmed = true;
            document.getElementById('insufficient-warning').classList.add('hidden');
        }

        // Get location for checkout
        function getLocation() {
            if (!navigator.geolocation) {
                alert('Browser Anda tidak mendukung geolokasi');
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    locationData = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        accuracy: position.coords.accuracy
                    };

                    // Simple check - show warning if accuracy is poor (might be out of geofence)
                    if (locationData.accuracy > 100) {
                        document.getElementById('location-warning').classList.remove('hidden');
                    }
                },
                (error) => {
                    alert('Gagal mendapatkan lokasi. Pastikan GPS aktif.');
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }

        // Submit checkout
        async function submitCheckout() {
            if (!activeSession) {
                alert('Sesi tidak ditemukan');
                return;
            }

            const workDetail = document.getElementById('work-detail').value.trim();
            
            if (workDetail.length < 10) {
                alert('Detail pekerjaan harus minimal 10 karakter');
                return;
            }

            if (workDetail.length > 500) {
                alert('Detail pekerjaan maksimal 500 karakter');
                return;
            }

            // Check if insufficient duration and not confirmed
            const insufficientWarning = document.getElementById('insufficient-warning');
            if (!insufficientWarning.classList.contains('hidden') && !isInsufficientConfirmed) {
                alert('Silakan konfirmasi check-out dengan durasi kurang');
                return;
            }

            if (!locationData) {
                alert('Lokasi belum tersedia. Menunggu GPS...');
                getLocation();
                return;
            }

            // Check if location warning is shown and radio not selected
            const locationWarning = document.getElementById('location-warning');
            if (!locationWarning.classList.contains('hidden')) {
                const selectedRadio = document.querySelector('input[name="out-of-town"]:checked');
                if (!selectedRadio) {
                    document.getElementById('out-of-town-warning').classList.remove('hidden');
                    alert('Silakan pilih opsi: Apakah Anda sedang tugas luar kota?');
                    return;
                }
                // Hide warning if previously shown
                document.getElementById('out-of-town-warning').classList.add('hidden');
            }

            document.getElementById('submit-section').classList.add('hidden');
            document.getElementById('loading-submit').classList.remove('hidden');

            try {
                const isOvertime = document.getElementById('is-overtime')?.checked || false;
                
                // Get out-of-town value from radio button
                const outOfTownRadio = document.querySelector('input[name="out-of-town"]:checked');
                const isOutOfTown = outOfTownRadio ? (outOfTownRadio.value === 'yes') : false;

                const response = await apiRequest('/attendance/check-out', 'POST', {
                    session_id: activeSession.id,
                    latitude: locationData.latitude,
                    longitude: locationData.longitude,
                    accuracy: locationData.accuracy,
                    photo_face: 'data:image/jpeg;base64,/9j/4AAQSkZJRg...', // Placeholder - will implement camera later
                    work_detail: workDetail,
                    is_overtime_confirmed: isOvertime,
                    is_out_of_town: isOutOfTown
                });

                if (response.success) {
                    window.location.href = '/attendance/checkout-success';
                } else {
                    alert('Check-out gagal: ' + (response.message || 'Terjadi kesalahan'));
                    document.getElementById('submit-section').classList.remove('hidden');
                    document.getElementById('loading-submit').classList.add('hidden');
                }
            } catch (error) {
                alert('Terjadi kesalahan: ' + error.message);
                document.getElementById('submit-section').classList.remove('hidden');
                document.getElementById('loading-submit').classList.add('hidden');
            }
        }

        // Initialize
        window.addEventListener('load', () => {
            loadActiveSession();
            
            // Add radio button interaction for visual feedback
            const radioButtons = document.querySelectorAll('input[name="out-of-town"]');
            radioButtons.forEach(radio => {
                radio.addEventListener('change', function() {
                    // Remove highlight from all labels
                    document.querySelectorAll('label:has(input[name="out-of-town"])').forEach(label => {
                        label.classList.remove('border-yellow-500', 'bg-yellow-100');
                        label.classList.add('border-gray-200');
                    });
                    
                    // Highlight selected label
                    const selectedLabel = this.closest('label');
                    selectedLabel.classList.remove('border-gray-200');
                    selectedLabel.classList.add('border-yellow-500', 'bg-yellow-100');
                    
                    // Hide warning if shown
                    document.getElementById('out-of-town-warning').classList.add('hidden');
                });
            });
        });
    </script>
</body>
</html>
