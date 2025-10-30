<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peringatan Keterlambatan - Sistem Absensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <div class="bg-white rounded-xl shadow-lg p-6 text-center">
            <!-- Warning Icon -->
            <div class="inline-block p-4 bg-yellow-100 rounded-full mb-4">
                <i class="fas fa-exclamation-triangle text-5xl text-yellow-600"></i>
            </div>

            <h1 class="text-2xl font-bold text-gray-800 mb-4">Peringatan Keterlambatan</h1>

            <!-- Late Count Info -->
            <div class="bg-yellow-50 border-2 border-yellow-300 rounded-lg p-6 mb-6">
                <p class="text-lg text-gray-700 mb-2">
                    Anda telah terlambat
                </p>
                <p class="text-4xl font-bold text-yellow-600 mb-2">
                    <span id="late-count"></span>× 
                </p>
                <p class="text-lg text-gray-700">
                    bulan ini
                </p>
            </div>

            <!-- Improvement Plan -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 text-left">
                <p class="text-sm font-semibold text-blue-800 mb-2">
                    <i class="fas fa-lightbulb mr-2"></i>Rencana Perbaikan Anda:
                </p>
                <p class="text-blue-700" id="improvement-plan"></p>
            </div>

            <!-- Warning if > 3 times -->
            <div id="warning-incentive" class="hidden bg-red-50 border-2 border-red-300 rounded-lg p-4 mb-6">
                <p class="text-red-800 font-semibold">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    Perhatian! Anda telah terlambat lebih dari 3 kali bulan ini.
                </p>
                <p class="text-red-700 text-sm mt-2">
                    Insentif kehadiran tepat waktu bulan ini akan hangus secara otomatis.
                </p>
            </div>

            <!-- Motivational Message -->
            <div class="mb-6">
                <p class="text-gray-600">
                    Mari kita perbaiki kebiasaan ini bersama-sama agar tidak terlambat lagi! 💪
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3">
                <a href="/dashboard" class="block w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
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
        const lateCount = localStorage.getItem('late_count_this_month') || 1;
        const improvementPlan = localStorage.getItem('improvement_plan') || 'Akan berusaha lebih baik';

        document.getElementById('late-count').textContent = lateCount;
        document.getElementById('improvement-plan').textContent = improvementPlan;

        if (parseInt(lateCount) > 3) {
            document.getElementById('warning-incentive').classList.remove('hidden');
        }

        // Clear storage
        localStorage.removeItem('late_count_this_month');
        localStorage.removeItem('improvement_plan');
    </script>
</body>
</html>
