<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alasan Keterlambatan - Sistem Absensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <!-- Warning Header -->
            <div class="text-center mb-6">
                <div class="inline-block p-4 bg-yellow-100 rounded-full mb-4">
                    <i class="fas fa-clock text-4xl text-yellow-600"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Anda Terlambat</h1>
                <p class="text-gray-600">Anda terlambat <span id="late-minutes" class="font-bold text-yellow-600"></span> menit</p>
            </div>

            <!-- Form -->
            <form id="late-reason-form" class="space-y-6">
                <!-- Reason -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Mengapa anda terlambat? <span class="text-red-500">*</span>
                    </label>
                    <textarea id="reason" rows="4" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Contoh: Terjebak macet di jalan tol, ban motor bocor, dll..."
                        minlength="10"></textarea>
                    <p class="text-xs text-gray-500 mt-1">Minimal 10 karakter</p>
                </div>

                <!-- Improvement Plan -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Apa yang akan anda lakukan agar besok tidak terlambat lagi? <span class="text-red-500">*</span>
                    </label>
                    <textarea id="improvement-plan" rows="4" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Contoh: Berangkat 30 menit lebih awal, gunakan rute alternatif, dll..."
                        minlength="10"></textarea>
                    <p class="text-xs text-gray-500 mt-1">Minimal 10 karakter</p>
                </div>

                <!-- Submit Button -->
                <div id="submit-section">
                    <button type="submit" class="w-full bg-blue-600 text-white py-4 rounded-lg font-bold text-lg hover:bg-blue-700 transition">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Kirim Alasan
                    </button>
                </div>

                <div id="loading-section" class="hidden text-center py-4">
                    <i class="fas fa-spinner fa-spin text-3xl text-blue-600 mb-2"></i>
                    <p class="text-gray-600">Menyimpan data...</p>
                </div>
            </form>

            <!-- Info Box -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Catatan:</strong> Alasan dan rencana perbaikan Anda akan dicatat dalam sistem dan dapat dilihat oleh HRD.
                </p>
            </div>
        </div>
    </div>

    <script src="/js/api-helper.js"></script>
    <script>
        const sessionId = localStorage.getItem('pending_session_id');
        const lateMinutes = localStorage.getItem('late_minutes');

        if (!sessionId || !lateMinutes) {
            window.location.href = '/dashboard';
        }

        document.getElementById('late-minutes').textContent = lateMinutes;

        document.getElementById('late-reason-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const reason = document.getElementById('reason').value.trim();
            const improvementPlan = document.getElementById('improvement-plan').value.trim();

            if (reason.length < 10 || improvementPlan.length < 10) {
                alert('Alasan dan rencana perbaikan harus minimal 10 karakter');
                return;
            }

            document.getElementById('submit-section').classList.add('hidden');
            document.getElementById('loading-section').classList.remove('hidden');

            try {
                const response = await apiRequest(`/attendance/sessions/${sessionId}/late-reason`, 'POST', {
                    reason: reason,
                    improvement_plan: improvementPlan
                });

                if (response.success) {
                    // Clear storage
                    localStorage.removeItem('pending_session_id');
                    localStorage.removeItem('late_minutes');

                    // Store late count for success page
                    localStorage.setItem('late_count_this_month', response.data.late_count_this_month);
                    localStorage.setItem('improvement_plan', improvementPlan);

                    window.location.href = '/attendance/late-success';
                } else {
                    alert('Gagal menyimpan: ' + (response.message || 'Terjadi kesalahan'));
                    document.getElementById('submit-section').classList.remove('hidden');
                    document.getElementById('loading-section').classList.add('hidden');
                }
            } catch (error) {
                alert('Terjadi kesalahan: ' + error.message);
                document.getElementById('submit-section').classList.remove('hidden');
                document.getElementById('loading-section').classList.add('hidden');
            }
        });
    </script>
</body>
</html>
