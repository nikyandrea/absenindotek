<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-out Berhasil - Sistem Absensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @keyframes checkmark {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        .checkmark-animation {
            animation: checkmark 0.6s ease-out;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <div class="bg-white rounded-xl shadow-lg p-8 text-center">
            <!-- Success Icon -->
            <div class="inline-block p-6 bg-red-100 rounded-full mb-6 checkmark-animation">
                <i class="fas fa-check-circle text-6xl text-red-600"></i>
            </div>

            <h1 class="text-3xl font-bold text-gray-800 mb-2">Check-out Berhasil!</h1>
            <p class="text-gray-600 mb-8">Terima kasih telah bekerja hari ini</p>

            <!-- Summary Card -->
            <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-6 mb-6 text-left">
                <div class="flex items-center justify-between mb-4 pb-4 border-b border-red-200">
                    <span class="text-gray-700">Waktu Check-out</span>
                    <span class="font-bold text-lg text-red-800" id="checkout-time"></span>
                </div>
                <div class="flex items-center justify-between mb-4 pb-4 border-b border-red-200">
                    <span class="text-gray-700">Total Durasi Kerja</span>
                    <span class="font-bold text-lg text-red-800" id="work-duration"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-700">Status</span>
                    <span class="font-semibold text-red-600">
                        <i class="fas fa-sign-out-alt mr-1"></i>
                        Check-out Selesai
                    </span>
                </div>
            </div>

            <!-- Motivational Quote -->
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                <p class="text-gray-700 italic">
                    <i class="fas fa-quote-left text-blue-600 mr-2"></i>
                    Selamat beristirahat! Semoga hari Anda menyenangkan
                    <i class="fas fa-quote-right text-blue-600 ml-2"></i>
                </p>
            </div>

            <!-- Info if needs approval -->
            <div id="needs-approval-info" class="hidden bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-yellow-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Perhatian:</strong> Check-out Anda di luar area kantor dan membutuhkan approval dari HRD.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3">
                <a href="/dashboard" class="block w-full bg-blue-600 text-white py-4 rounded-lg font-bold text-lg hover:bg-blue-700 transition">
                    <i class="fas fa-home mr-2"></i>
                    Ke Dashboard
                </a>
                <a href="/attendance/history" class="block w-full bg-gray-500 text-white py-3 rounded-lg font-semibold hover:bg-gray-600 transition">
                    <i class="fas fa-history mr-2"></i>
                    Lihat Riwayat Kehadiran
                </a>
            </div>
        </div>
    </div>

    <script>
        const now = new Date();
        
        document.getElementById('checkout-time').textContent = now.toLocaleString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });

        // Get duration from URL params or localStorage (if we stored it)
        const urlParams = new URLSearchParams(window.location.search);
        const duration = urlParams.get('duration') || '8 jam 0 menit';
        const needsApproval = urlParams.get('needs_approval') === 'true';

        document.getElementById('work-duration').textContent = duration;

        if (needsApproval) {
            document.getElementById('needs-approval-info').classList.remove('hidden');
        }

        // Auto redirect after 5 seconds
        setTimeout(() => {
            window.location.href = '/dashboard';
        }, 5000);
    </script>
</body>
</html>
