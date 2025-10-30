<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Wajah - Sistem Absensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .camera-container {
            position: relative;
            width: 100%;
            max-width: 480px;
            margin: 0 auto;
        }
        #video {
            width: 100%;
            border-radius: 0.75rem;
            transform: scaleX(-1);
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
        .capture-button.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .photo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
        }
        .photo-item {
            position: relative;
            aspect-ratio: 1;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        .photo-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .photo-label {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0,0,0,0.7);
            color: white;
            text-align: center;
            padding: 0.25rem;
            font-size: 0.75rem;
        }
        .overlay-guide {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 200px;
            height: 250px;
            border: 3px dashed rgba(255,255,255,0.5);
            border-radius: 50%;
            pointer-events: none;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8 max-w-3xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-block p-4 bg-blue-100 rounded-full mb-4">
                <i class="fas fa-user-circle text-5xl text-blue-600"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Pendaftaran Wajah</h1>
            <p class="text-gray-600 mb-4">Ambil 3-5 foto wajah Anda dari berbagai sudut untuk verifikasi kehadiran</p>
            
            <!-- Back to Dashboard Button -->
            <a href="/dashboard" class="inline-flex items-center px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Dashboard
            </a>
        </div>

        <!-- Progress -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-semibold text-gray-700">Progress</span>
                <span class="text-sm font-semibold text-blue-600"><span id="photo-count">0</span>/5 Foto</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div id="progress-bar" class="bg-blue-600 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-2">Minimal 3 foto, maksimal 5 foto</p>
        </div>

        <!-- Instructions -->
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
            <div class="flex">
                <i class="fas fa-info-circle text-blue-600 mr-3 mt-1"></i>
                <div>
                    <p class="font-semibold text-blue-800 mb-2">Panduan Pengambilan Foto:</p>
                    <ul class="text-sm text-blue-700 space-y-1">
                        <li>✓ Foto 1: Wajah menghadap <strong>lurus ke kamera</strong></li>
                        <li>✓ Foto 2: Wajah sedikit <strong>miring ke kiri</strong></li>
                        <li>✓ Foto 3: Wajah sedikit <strong>miring ke kanan</strong></li>
                        <li>✓ Foto 4-5: Variasi ekspresi atau sudut lain</li>
                        <li>⚠️ Pastikan pencahayaan cukup dan wajah terlihat jelas</li>
                        <li>⚠️ Lepas kacamata hitam dan masker saat pengambilan foto</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Current Photo Guide -->
        <div id="photo-guide" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <p class="text-center font-semibold text-yellow-800">
                <i class="fas fa-camera mr-2"></i>
                <span id="guide-text">Ambil foto #1: Wajah menghadap lurus</span>
            </p>
        </div>

        <!-- Camera -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <div class="camera-container" id="camera-container">
                <video id="video" autoplay playsinline></video>
                <div class="overlay-guide"></div>
                <canvas id="canvas"></canvas>
                <button class="capture-button" id="capture-btn" onclick="capturePhoto()">
                    <i class="fas fa-camera text-2xl text-blue-600"></i>
                </button>
            </div>
            
            <div id="camera-loading" class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-3xl text-blue-600 mb-2"></i>
                <p class="text-gray-600">Memulai kamera...</p>
            </div>
        </div>

        <!-- Collected Photos -->
        <div id="photos-section" class="bg-white rounded-xl shadow-lg p-6 mb-6 hidden">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Foto Yang Sudah Diambil</h3>
            <div id="photos-grid" class="photo-grid"></div>
        </div>

        <!-- Actions -->
        <div id="actions-section">
            <button id="submit-btn" onclick="submitEnrollment()" class="w-full bg-green-600 text-white py-4 rounded-lg font-bold text-lg hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                <i class="fas fa-check-circle mr-2"></i>
                Selesai & Simpan
            </button>
            
            <button onclick="resetEnrollment()" class="w-full bg-gray-500 text-white py-3 rounded-lg font-semibold hover:bg-gray-600 transition mt-3">
                <i class="fas fa-redo mr-2"></i>
                Mulai Ulang
            </button>
        </div>

        <div id="loading-section" class="hidden text-center py-8">
            <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
            <p class="text-gray-600 text-lg">Memproses pendaftaran wajah...</p>
            <p class="text-gray-500 text-sm mt-2">Mohon tunggu, ini mungkin memakan waktu beberapa detik</p>
        </div>
    </div>

    <script src="/js/api-helper.js"></script>
    <script>
        let videoStream = null;
        let photos = [];
        const MAX_PHOTOS = 5;
        const MIN_PHOTOS = 3;

        const guides = [
            "Ambil foto #1: Wajah menghadap lurus",
            "Ambil foto #2: Wajah sedikit miring ke kiri",
            "Ambil foto #3: Wajah sedikit miring ke kanan",
            "Ambil foto #4: Variasi sudut atau ekspresi",
            "Ambil foto #5: Variasi sudut atau ekspresi"
        ];

        // Start camera on page load
        window.addEventListener('load', async () => {
            // Check if user already has face profile
            await checkExistingProfile();
            startCamera();
        });

        // Check if user already enrolled
        async function checkExistingProfile() {
            try {
                const response = await apiRequest('/face/profile', 'GET');
                if (response.success && response.data.has_profile) {
                    // User already has face profile
                    const shouldContinue = confirm(
                        '⚠️ Anda sudah memiliki profil wajah yang terdaftar.\n\n' +
                        'Melanjutkan proses ini akan MENGGANTI profil wajah lama Anda dengan yang baru.\n\n' +
                        'Apakah Anda yakin ingin melanjutkan?\n\n' +
                        'Klik "OK" untuk melanjutkan pendaftaran ulang\n' +
                        'Klik "Cancel" untuk kembali ke dashboard'
                    );
                    
                    if (!shouldContinue) {
                        window.location.href = '/dashboard';
                        return false;
                    }
                }
                return true;
            } catch (error) {
                console.error('Error checking profile:', error);
                return true; // Continue anyway if check fails
            }
        }

        async function startCamera() {
            try {
                document.getElementById('camera-loading').style.display = 'block';
                document.getElementById('camera-container').style.display = 'none';

                videoStream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        facingMode: 'user',
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    } 
                });
                
                document.getElementById('video').srcObject = videoStream;
                document.getElementById('camera-loading').style.display = 'none';
                document.getElementById('camera-container').style.display = 'block';
            } catch (error) {
                alert('Tidak dapat mengakses kamera. Pastikan izin kamera diaktifkan.');
                window.location.href = '/dashboard';
            }
        }

        function stopCamera() {
            if (videoStream) {
                videoStream.getTracks().forEach(track => track.stop());
                videoStream = null;
            }
        }

        function capturePhoto() {
            if (photos.length >= MAX_PHOTOS) {
                alert('Maksimal 5 foto sudah tercapai');
                return;
            }

            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const context = canvas.getContext('2d');

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            
            // Flip horizontally
            context.translate(canvas.width, 0);
            context.scale(-1, 1);
            context.drawImage(video, 0, 0);

            const photoBase64 = canvas.toDataURL('image/jpeg', 0.8);
            
            photos.push(photoBase64);
            updateUI();
        }

        function updateUI() {
            const count = photos.length;
            
            // Update counter
            document.getElementById('photo-count').textContent = count;
            
            // Update progress bar
            const progress = (count / MAX_PHOTOS) * 100;
            document.getElementById('progress-bar').style.width = progress + '%';
            
            // Update guide text
            if (count < MAX_PHOTOS) {
                document.getElementById('guide-text').textContent = guides[count];
            } else {
                document.getElementById('guide-text').textContent = 'Semua foto sudah diambil!';
            }
            
            // Show photos section
            if (count > 0) {
                document.getElementById('photos-section').classList.remove('hidden');
                updatePhotosGrid();
            }
            
            // Enable submit button if minimum met
            const submitBtn = document.getElementById('submit-btn');
            if (count >= MIN_PHOTOS) {
                submitBtn.disabled = false;
            }
            
            // Disable capture if max reached
            const captureBtn = document.getElementById('capture-btn');
            if (count >= MAX_PHOTOS) {
                captureBtn.classList.add('disabled');
                captureBtn.onclick = null;
            }
        }

        function updatePhotosGrid() {
            const grid = document.getElementById('photos-grid');
            grid.innerHTML = '';
            
            photos.forEach((photo, index) => {
                const div = document.createElement('div');
                div.className = 'photo-item';
                div.innerHTML = `
                    <img src="${photo}" alt="Photo ${index + 1}">
                    <div class="photo-label">Foto #${index + 1}</div>
                    <button onclick="deletePhoto(${index})" class="absolute top-2 right-2 bg-red-500 text-white w-6 h-6 rounded-full hover:bg-red-600 transition">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                `;
                grid.appendChild(div);
            });
        }

        function deletePhoto(index) {
            if (confirm('Hapus foto ini?')) {
                photos.splice(index, 1);
                updateUI();
                
                // Re-enable capture button
                const captureBtn = document.getElementById('capture-btn');
                captureBtn.classList.remove('disabled');
                captureBtn.onclick = capturePhoto;
            }
        }

        function resetEnrollment() {
            if (photos.length > 0 && !confirm('Mulai ulang? Semua foto akan dihapus.')) {
                return;
            }
            
            photos = [];
            updateUI();
            document.getElementById('photos-section').classList.add('hidden');
            document.getElementById('submit-btn').disabled = true;
        }

        async function submitEnrollment() {
            if (photos.length < MIN_PHOTOS) {
                alert(`Minimal ${MIN_PHOTOS} foto diperlukan`);
                return;
            }

            if (!confirm(`Yakin ingin menyimpan ${photos.length} foto ini untuk pendaftaran wajah?`)) {
                return;
            }

            document.getElementById('actions-section').classList.add('hidden');
            document.getElementById('loading-section').classList.remove('hidden');
            stopCamera();

            try {
                const response = await apiRequest('/face/enroll', 'POST', {
                    photos: photos
                });

                if (response.success) {
                    alert('✅ Pendaftaran wajah berhasil! Anda sekarang dapat melakukan check-in.');
                    window.location.href = '/attendance/check-in';
                } else {
                    throw new Error(response.message || 'Pendaftaran gagal');
                }
            } catch (error) {
                alert('❌ Terjadi kesalahan: ' + error.message);
                document.getElementById('actions-section').classList.remove('hidden');
                document.getElementById('loading-section').classList.add('hidden');
                startCamera();
            }
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            stopCamera();
        });
    </script>
</body>
</html>
