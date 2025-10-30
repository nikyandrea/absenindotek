<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Check-in - Sistem Absensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .camera-container {
            position: relative;
            width: 100%;
            max-width: 640px;
            margin: 0 auto;
        }
        #video {
            width: 100%;
            border-radius: 0.75rem;
            transform: scaleX(-1); /* Mirror effect */
        }
        #canvas {
            display: none;
        }
        .capture-button {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: white;
            border: 5px solid #3b82f6;
            cursor: pointer;
            transition: all 0.3s;
        }
        .capture-button:hover {
            transform: translateX(-50%) scale(1.1);
        }
        .capture-button:active {
            transform: translateX(-50%) scale(0.95);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <div class="bg-blue-600 text-white py-4 shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold">Check-in Kehadiran</h1>
                    <p class="text-sm text-blue-100" id="currentTime"></p>
                </div>
                <div class="text-right">
                    <p class="text-sm" id="userName">{{ auth()->user()->name ?? 'Karyawan' }}</p>
                    <p class="text-xs text-blue-100">{{ auth()->user()->office->name ?? 'Office' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-6 max-w-4xl">
        <!-- Steps Indicator -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-2">
                <div class="flex-1 text-center" id="step1-indicator">
                    <div class="w-10 h-10 mx-auto rounded-full bg-blue-600 text-white flex items-center justify-center font-bold">1</div>
                    <p class="text-xs mt-1 font-semibold text-blue-600">Lokasi</p>
                </div>
                <div class="flex-1 border-t-2 border-gray-300 mx-2"></div>
                <div class="flex-1 text-center" id="step2-indicator">
                    <div class="w-10 h-10 mx-auto rounded-full bg-gray-300 text-white flex items-center justify-center font-bold">2</div>
                    <p class="text-xs mt-1 text-gray-500">Wajah</p>
                </div>
                <div class="flex-1 border-t-2 border-gray-300 mx-2"></div>
                <div class="flex-1 text-center" id="step3-indicator">
                    <div class="w-10 h-10 mx-auto rounded-full bg-gray-300 text-white flex items-center justify-center font-bold">3</div>
                    <p class="text-xs mt-1 text-gray-500">Konfirmasi</p>
                </div>
            </div>
        </div>

        <!-- Step 1: Location -->
        <div id="step1" class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-bold mb-4 flex items-center">
                <i class="fas fa-map-marker-alt text-blue-600 mr-2"></i>
                Verifikasi Lokasi
            </h2>
            
            <div class="text-center py-8" id="location-loading">
                <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
                <p class="text-gray-600">Mengambil lokasi Anda...</p>
                <p class="text-sm text-gray-500 mt-2">Pastikan GPS aktif dan izinkan akses lokasi</p>
            </div>

            <div id="location-result" class="hidden">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-600 text-xl mr-3 mt-1"></i>
                        <div class="flex-1">
                            <p class="font-semibold text-green-800">Lokasi Terdeteksi</p>
                            <p class="text-sm text-green-700 mt-1">
                                Koordinat: <span id="coordinates"></span>
                            </p>
                            <p class="text-sm text-green-700">
                                Akurasi: <span id="accuracy"></span> meter
                            </p>
                            <p class="text-sm text-green-700" id="geofence-status"></p>
                        </div>
                    </div>
                </div>

                <button onclick="nextStep(2)" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                    Lanjut ke Verifikasi Wajah
                    <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </div>

            <div id="location-error" class="hidden">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl mr-3 mt-1"></i>
                        <div class="flex-1">
                            <p class="font-semibold text-red-800">Gagal Mendapatkan Lokasi</p>
                            <p class="text-sm text-red-700 mt-1" id="location-error-message"></p>
                        </div>
                    </div>
                </div>

                <button onclick="getLocation()" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                    <i class="fas fa-redo mr-2"></i>
                    Coba Lagi
                </button>
            </div>
        </div>

        <!-- Step 2: Face Recognition -->
        <div id="step2" class="bg-white rounded-xl shadow-lg p-6 hidden">
            <h2 class="text-xl font-bold mb-4 flex items-center">
                <i class="fas fa-user-circle text-blue-600 mr-2"></i>
                Verifikasi Wajah
            </h2>

            <div class="mb-4">
                <div class="camera-container">
                    <video id="video" autoplay playsinline></video>
                    <canvas id="canvas"></canvas>
                    <button class="capture-button" onclick="capturePhoto()">
                        <i class="fas fa-camera text-2xl text-blue-600"></i>
                    </button>
                </div>
            </div>

            <div id="photo-preview" class="hidden mb-4">
                <p class="text-sm text-gray-600 mb-2">Foto yang diambil:</p>
                <img id="captured-image" class="w-full max-w-sm mx-auto rounded-lg shadow-md">
                <div class="flex gap-3 mt-4">
                    <button onclick="retakePhoto()" class="flex-1 bg-gray-500 text-white py-2 rounded-lg hover:bg-gray-600">
                        <i class="fas fa-redo mr-2"></i>Ambil Ulang
                    </button>
                    <button onclick="nextStep(3)" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
                        Lanjutkan<i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Tips:</strong> Pastikan wajah Anda terlihat jelas, pencahayaan cukup, dan tidak tertutup masker/kacamata hitam.
                </p>
            </div>

            <button onclick="prevStep(1)" class="w-full bg-gray-500 text-white py-3 rounded-lg font-semibold hover:bg-gray-600 transition mt-4">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali
            </button>
        </div>

        <!-- Step 3: Confirmation -->
        <div id="step3" class="bg-white rounded-xl shadow-lg p-6 hidden">
            <h2 class="text-xl font-bold mb-4 flex items-center">
                <i class="fas fa-clipboard-check text-blue-600 mr-2"></i>
                Konfirmasi Check-in
            </h2>

            <!-- For Fixed Schedule - Early Check -->
            <div id="early-overtime-section" class="hidden mb-6">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                    <p class="text-yellow-800 font-semibold">
                        <i class="fas fa-clock mr-2"></i>
                        Anda check-in lebih awal dari jam masuk
                    </p>
                </div>
                
                <div class="mb-4">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" id="is-early-overtime" class="w-5 h-5 text-blue-600">
                        <span class="font-semibold">Apakah Anda lembur pagi?</span>
                    </label>
                </div>

                <div id="overtime-reason-section" class="hidden">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Anda lembur pagi untuk mengerjakan apa? <span class="text-red-500">*</span>
                    </label>
                    <textarea id="overtime-reason" rows="3" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Contoh: Menyelesaikan laporan bulanan, persiapan meeting dengan klien, dll..."></textarea>
                </div>
            </div>

            <!-- Summary -->
            <div class="space-y-3 mb-6">
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Waktu Check-in:</span>
                    <span class="font-semibold" id="summary-time"></span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Lokasi:</span>
                    <span class="font-semibold" id="summary-location"></span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Status Geofence:</span>
                    <span class="font-semibold" id="summary-geofence"></span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Verifikasi Wajah:</span>
                    <span class="font-semibold text-green-600">
                        <i class="fas fa-check-circle mr-1"></i>Terverifikasi
                    </span>
                </div>
            </div>

            <div id="submit-loading" class="hidden text-center py-4">
                <i class="fas fa-spinner fa-spin text-3xl text-blue-600 mb-2"></i>
                <p class="text-gray-600">Memproses check-in...</p>
            </div>

            <div id="submit-buttons">
                <button onclick="submitCheckIn()" class="w-full bg-green-600 text-white py-4 rounded-lg font-bold text-lg hover:bg-green-700 transition mb-3">
                    <i class="fas fa-check-circle mr-2"></i>
                    Konfirmasi Check-in
                </button>

                <button onclick="prevStep(2)" class="w-full bg-gray-500 text-white py-3 rounded-lg font-semibold hover:bg-gray-600 transition">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </button>
            </div>
        </div>
    </div>

    <script src="/js/api-helper.js"></script>
    <script>
        let locationData = null;
        let facePhotoBase64 = null;
        let videoStream = null;

        // Update current time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleString('id-ID', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('currentTime').textContent = timeString;
        }
        updateTime();
        setInterval(updateTime, 1000);

        // Step navigation
        function updateStepIndicator(step) {
            for (let i = 1; i <= 3; i++) {
                const indicator = document.getElementById(`step${i}-indicator`);
                const circle = indicator.querySelector('div');
                const text = indicator.querySelector('p');
                
                if (i < step) {
                    circle.className = 'w-10 h-10 mx-auto rounded-full bg-green-600 text-white flex items-center justify-center font-bold';
                    circle.innerHTML = '<i class="fas fa-check"></i>';
                    text.className = 'text-xs mt-1 font-semibold text-green-600';
                } else if (i === step) {
                    circle.className = 'w-10 h-10 mx-auto rounded-full bg-blue-600 text-white flex items-center justify-center font-bold';
                    circle.textContent = i;
                    text.className = 'text-xs mt-1 font-semibold text-blue-600';
                } else {
                    circle.className = 'w-10 h-10 mx-auto rounded-full bg-gray-300 text-white flex items-center justify-center font-bold';
                    circle.textContent = i;
                    text.className = 'text-xs mt-1 text-gray-500';
                }
            }
        }

        function nextStep(step) {
            for (let i = 1; i <= 3; i++) {
                document.getElementById(`step${i}`).classList.add('hidden');
            }
            document.getElementById(`step${step}`).classList.remove('hidden');
            updateStepIndicator(step);

            if (step === 2) {
                startCamera();
            } else if (step === 3) {
                updateSummary();
                stopCamera();
            }
        }

        function prevStep(step) {
            nextStep(step);
        }

        // Step 1: Get Location
        function getLocation() {
            document.getElementById('location-loading').classList.remove('hidden');
            document.getElementById('location-result').classList.add('hidden');
            document.getElementById('location-error').classList.add('hidden');

            if (!navigator.geolocation) {
                showLocationError('Browser Anda tidak mendukung geolokasi');
                return;
            }

            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    locationData = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        accuracy: position.coords.accuracy
                    };

                    document.getElementById('coordinates').textContent = 
                        `${locationData.latitude.toFixed(6)}, ${locationData.longitude.toFixed(6)}`;
                    document.getElementById('accuracy').textContent = 
                        locationData.accuracy.toFixed(1);

                    // Check geofence status (optional - for display only)
                    const withinGeofence = locationData.accuracy < 100; // Simplified check
                    const statusText = withinGeofence ? 
                        '<i class="fas fa-check-circle text-green-600"></i> Dalam area kantor' : 
                        '<i class="fas fa-exclamation-circle text-yellow-600"></i> Di luar area kantor (perlu approval)';
                    document.getElementById('geofence-status').innerHTML = statusText;

                    document.getElementById('location-loading').classList.add('hidden');
                    document.getElementById('location-result').classList.remove('hidden');
                },
                (error) => {
                    let errorMessage = 'Tidak dapat mengakses lokasi. ';
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage += 'Izin lokasi ditolak. Silakan aktifkan di pengaturan browser.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage += 'Informasi lokasi tidak tersedia.';
                            break;
                        case error.TIMEOUT:
                            errorMessage += 'Timeout. Coba lagi.';
                            break;
                    }
                    showLocationError(errorMessage);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }

        function showLocationError(message) {
            document.getElementById('location-loading').classList.add('hidden');
            document.getElementById('location-error-message').textContent = message;
            document.getElementById('location-error').classList.remove('hidden');
        }

        // Step 2: Camera & Face
        async function startCamera() {
            try {
                videoStream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        facingMode: 'user',
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    } 
                });
                document.getElementById('video').srcObject = videoStream;
            } catch (error) {
                alert('Tidak dapat mengakses kamera. Pastikan izin kamera diaktifkan.');
                prevStep(1);
            }
        }

        function stopCamera() {
            if (videoStream) {
                videoStream.getTracks().forEach(track => track.stop());
                videoStream = null;
            }
        }

        function capturePhoto() {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const context = canvas.getContext('2d');

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            
            // Flip horizontally to match mirror view
            context.translate(canvas.width, 0);
            context.scale(-1, 1);
            context.drawImage(video, 0, 0);

            facePhotoBase64 = canvas.toDataURL('image/jpeg', 0.8);
            
            document.getElementById('captured-image').src = facePhotoBase64;
            document.getElementById('photo-preview').classList.remove('hidden');
            video.classList.add('hidden');
            document.querySelector('.capture-button').classList.add('hidden');
        }

        function retakePhoto() {
            document.getElementById('photo-preview').classList.add('hidden');
            document.getElementById('video').classList.remove('hidden');
            document.querySelector('.capture-button').classList.remove('hidden');
            facePhotoBase64 = null;
        }

        // Step 3: Confirmation
        document.getElementById('is-early-overtime')?.addEventListener('change', function() {
            const reasonSection = document.getElementById('overtime-reason-section');
            if (this.checked) {
                reasonSection.classList.remove('hidden');
            } else {
                reasonSection.classList.add('hidden');
            }
        });

        function updateSummary() {
            const now = new Date();
            document.getElementById('summary-time').textContent = now.toLocaleString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('summary-location').textContent = 
                `${locationData.latitude.toFixed(6)}, ${locationData.longitude.toFixed(6)}`;
            
            const withinGeofence = locationData.accuracy < 100;
            document.getElementById('summary-geofence').innerHTML = withinGeofence ?
                '<span class="text-green-600"><i class="fas fa-check-circle mr-1"></i>Dalam Area</span>' :
                '<span class="text-yellow-600"><i class="fas fa-exclamation-circle mr-1"></i>Luar Area</span>';
        }

        // Submit Check-in
        async function submitCheckIn() {
            if (!locationData || !facePhotoBase64) {
                alert('Data tidak lengkap. Silakan ulangi proses.');
                return;
            }

            const isEarlyOvertime = document.getElementById('is-early-overtime')?.checked || false;
            const overtimeReason = document.getElementById('overtime-reason')?.value || '';

            if (isEarlyOvertime && !overtimeReason.trim()) {
                alert('Silakan isi alasan lembur pagi');
                return;
            }

            document.getElementById('submit-loading').classList.remove('hidden');
            document.getElementById('submit-buttons').classList.add('hidden');

            try {
                const response = await apiRequest('/attendance/check-in', 'POST', {
                    latitude: locationData.latitude,
                    longitude: locationData.longitude,
                    accuracy: locationData.accuracy,
                    photo_face: facePhotoBase64,
                    is_early_overtime: isEarlyOvertime,
                    overtime_reason: overtimeReason,
                    device_info: {
                        user_agent: navigator.userAgent,
                        platform: navigator.platform,
                        screen: `${screen.width}x${screen.height}`
                    }
                });

                if (response.success) {
                    // Check if late
                    if (response.late_info && response.late_info.need_reason) {
                        localStorage.setItem('pending_session_id', response.data.session_id);
                        localStorage.setItem('late_minutes', response.late_info.late_minutes);
                        window.location.href = '/attendance/late-reason';
                    } else {
                        window.location.href = '/attendance/success?type=checkin';
                    }
                } else {
                    // Check if backend allows force continue for poor GPS
                    if (response.allow_force_continue === true) {
                        showGpsAccuracyWarning();
                    } else {
                        throw new Error(response.message || 'Terjadi kesalahan');
                    }
                }
            } catch (error) {
                // Check if error has responseData with allow_force_continue
                if (error.responseData && error.responseData.allow_force_continue === true) {
                    showGpsAccuracyWarning();
                } else {
                    const errorMsg = error.message || 'Terjadi kesalahan';
                    alert('Check-in gagal: ' + errorMsg);
                    document.getElementById('submit-loading').classList.add('hidden');
                    document.getElementById('submit-buttons').classList.remove('hidden');
                }
            }
        }

        // Show GPS accuracy warning with option to continue
        function showGpsAccuracyWarning() {
            document.getElementById('submit-loading').classList.add('hidden');
            
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';
            modal.id = 'gps-warning-modal';
            modal.innerHTML = `
                <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-8">
                    <div class="text-center mb-6">
                        <div class="inline-block p-4 bg-yellow-100 rounded-full mb-4">
                            <i class="fas fa-exclamation-triangle text-4xl text-yellow-600"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800 mb-2">Akurasi GPS Kurang Baik</h2>
                        <p class="text-gray-600 text-sm mb-2">
                            Akurasi GPS Anda: <strong class="text-yellow-600">${locationData.accuracy.toFixed(1)}m</strong>
                        </p>
                        <p class="text-gray-600 text-sm">
                            Disarankan: <strong class="text-green-600">≤ 50m</strong>
                        </p>
                    </div>

                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                        <p class="text-sm text-blue-800">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Tips:</strong> Untuk akurasi lebih baik:
                        </p>
                        <ul class="text-sm text-blue-700 mt-2 ml-6 list-disc">
                            <li>Gunakan smartphone dengan GPS aktif</li>
                            <li>Pindah ke area terbuka (dekat jendela)</li>
                            <li>Restart browser dan izinkan akses lokasi</li>
                        </ul>
                    </div>

                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                        <p class="text-sm text-yellow-800">
                            <i class="fas fa-shield-alt mr-2"></i>
                            Jika Anda <strong>lanjutkan dengan GPS ini</strong>, check-in akan:
                        </p>
                        <ul class="text-sm text-yellow-700 mt-2 ml-6 list-disc">
                            <li>Ditandai untuk <strong>review manual HRD</strong></li>
                            <li>Tercatat dengan akurasi rendah</li>
                            <li>Memerlukan approval dari atasan</li>
                        </ul>
                    </div>

                    <div class="space-y-3">
                        <button onclick="closeGpsWarning()" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                            <i class="fas fa-redo mr-2"></i>
                            Coba Lagi (Perbaiki GPS)
                        </button>
                        <button onclick="forceCheckIn()" class="w-full bg-yellow-600 text-white py-3 rounded-lg font-semibold hover:bg-yellow-700 transition">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Lanjutkan dengan Review Manual
                        </button>
                        <button onclick="cancelCheckIn()" class="w-full bg-gray-500 text-white py-2 rounded-lg hover:bg-gray-600 transition">
                            Batal
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        function closeGpsWarning() {
            const modal = document.getElementById('gps-warning-modal');
            if (modal) modal.remove();
            document.getElementById('submit-buttons').classList.remove('hidden');
            // User can try to get better GPS location
            getLocation();
        }

        function cancelCheckIn() {
            const modal = document.getElementById('gps-warning-modal');
            if (modal) modal.remove();
            window.location.href = '/dashboard';
        }

        async function forceCheckIn() {
            const modal = document.getElementById('gps-warning-modal');
            if (modal) modal.remove();
            
            document.getElementById('submit-loading').classList.remove('hidden');

            const isEarlyOvertime = document.getElementById('is-early-overtime')?.checked || false;
            const overtimeReason = document.getElementById('overtime-reason')?.value || '';

            try {
                const response = await apiRequest('/attendance/check-in', 'POST', {
                    latitude: locationData.latitude,
                    longitude: locationData.longitude,
                    accuracy: locationData.accuracy,
                    photo_face: facePhotoBase64,
                    is_early_overtime: isEarlyOvertime,
                    overtime_reason: overtimeReason,
                    force_gps_continue: true, // Flag to allow poor GPS with review
                    device_info: {
                        user_agent: navigator.userAgent,
                        platform: navigator.platform,
                        screen: `${screen.width}x${screen.height}`
                    }
                });

                if (response.success) {
                    // Show success with warning about manual review
                    localStorage.setItem('needs_manual_review', 'true');
                    
                    if (response.late_info && response.late_info.need_reason) {
                        localStorage.setItem('pending_session_id', response.data.session_id);
                        localStorage.setItem('late_minutes', response.late_info.late_minutes);
                        window.location.href = '/attendance/late-reason';
                    } else {
                        window.location.href = '/attendance/success?type=checkin';
                    }
                } else {
                    alert('Check-in gagal: ' + (response.message || 'Terjadi kesalahan'));
                    document.getElementById('submit-loading').classList.add('hidden');
                    document.getElementById('submit-buttons').classList.remove('hidden');
                }
            } catch (error) {
                alert('Terjadi kesalahan: ' + error.message);
                document.getElementById('submit-loading').classList.add('hidden');
                document.getElementById('submit-buttons').classList.remove('hidden');
            }
        }

        // Initialize on page load
        window.addEventListener('load', () => {
            // Check if user has face profile
            checkFaceProfile();
        });

        // Check face profile before allowing check-in
        async function checkFaceProfile() {
            try {
                const response = await apiRequest('/face/profile', 'GET');
                
                if (!response.success || !response.data) {
                    // No face profile found - show modal and redirect
                    showEnrollmentRequired();
                    return false;
                }
                
                // Face profile exists, proceed with check-in
                getLocation();
                return true;
            } catch (error) {
                console.error('Error checking face profile:', error);
                // If error (404 = no profile), redirect to enrollment
                if (error.message.includes('404') || error.message.includes('tidak ditemukan')) {
                    showEnrollmentRequired();
                } else {
                    // Other errors - show warning but allow to proceed (for testing)
                    if (confirm('Tidak dapat memverifikasi profil wajah. Lanjutkan check-in? (Untuk testing only)')) {
                        getLocation();
                    } else {
                        window.location.href = '/dashboard';
                    }
                }
            }
        }

        function showEnrollmentRequired() {
            // Create modal
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';
            modal.innerHTML = `
                <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-8 text-center">
                    <div class="inline-block p-4 bg-yellow-100 rounded-full mb-4">
                        <i class="fas fa-exclamation-triangle text-5xl text-yellow-600"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Pendaftaran Wajah Diperlukan</h2>
                    <p class="text-gray-600 mb-6">
                        Anda belum mendaftarkan wajah. Silakan lakukan pendaftaran wajah terlebih dahulu untuk dapat melakukan check-in.
                    </p>
                    <div class="space-y-3">
                        <button onclick="window.location.href='/face/enroll'" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                            <i class="fas fa-user-circle mr-2"></i>
                            Daftar Wajah Sekarang
                        </button>
                        <button onclick="window.location.href='/dashboard'" class="w-full bg-gray-500 text-white py-3 rounded-lg font-semibold hover:bg-gray-600 transition">
                            <i class="fas fa-home mr-2"></i>
                            Kembali ke Dashboard
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            stopCamera();
        });
    </script>
</body>
</html>
