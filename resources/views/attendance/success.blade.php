<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-in Berhasil - Sistem Absensi</title>
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
            <div class="inline-block p-6 bg-green-100 rounded-full mb-6 checkmark-animation">
                <i class="fas fa-check-circle text-6xl text-green-600"></i>
            </div>

            <h1 class="text-3xl font-bold text-gray-800 mb-2">Check-in Berhasil!</h1>
            <p class="text-gray-600 mb-8">Kehadiran Anda telah tercatat dalam sistem</p>

            <!-- Manual Review Warning -->
            <div id="manual-review-warning" class="hidden bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 text-left">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-xl mr-3 mt-1"></i>
                    <div>
                        <h3 class="font-semibold text-yellow-800 mb-1">⚠️ Perlu Review Manual</h3>
                        <p class="text-sm text-yellow-700">
                            Check-in Anda berhasil dicatat, namun memerlukan <strong>approval manual dari HRD</strong> karena akurasi GPS yang rendah.
                        </p>
                        <p class="text-sm text-yellow-700 mt-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Anda tetap dapat bekerja normal. HRD akan melakukan verifikasi dalam waktu dekat.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Summary Card -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-6 mb-6 text-left">
                <div class="flex items-center justify-between mb-4 pb-4 border-b border-blue-200">
                    <span class="text-gray-700">Waktu Check-in</span>
                    <span class="font-bold text-lg text-blue-800" id="checkin-time"></span>
                </div>
                <div class="flex items-center justify-between mb-4 pb-4 border-b border-blue-200">
                    <span class="text-gray-700">Tanggal</span>
                    <span class="font-semibold text-blue-800" id="checkin-date"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-700">Status</span>
                    <span class="font-semibold text-green-600">
                        <i class="fas fa-check-circle mr-1"></i>
                        Hadir
                    </span>
                </div>
            </div>

            <!-- Motivational Quote -->
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <p class="text-gray-700 italic">
                    <i class="fas fa-quote-left text-yellow-600 mr-2"></i>
                    Selamat bekerja! Semoga hari Anda produktif dan menyenangkan 
                    <i class="fas fa-quote-right text-yellow-600 ml-2"></i>
                </p>
            </div>

            <!-- Reminder -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Jangan lupa:</strong> Lakukan check-out di akhir jam kerja dan isi detail pekerjaan yang telah Anda selesaikan hari ini.
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
        
        document.getElementById('checkin-time').textContent = now.toLocaleString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });

        document.getElementById('checkin-date').textContent = now.toLocaleString('id-ID', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        // Check if there's manual review flag
        if (localStorage.getItem('needs_manual_review') === 'true') {
            document.getElementById('manual-review-warning').classList.remove('hidden');
            localStorage.removeItem('needs_manual_review');
        }

        // Auto redirect after 5 seconds
        setTimeout(() => {
            window.location.href = '/dashboard';
        }, 5000);
    </script>
</body>
</html>
