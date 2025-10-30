<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Kehadiran - Sistem Absensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8 max-w-5xl">
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-2xl font-bold">Riwayat Kehadiran</h1>
                <div class="flex items-center space-x-2">
                    <select id="month-filter" class="px-3 py-2 border rounded-lg">
                        <!-- Options will be filled by JS -->
                    </select>
                    <button id="filter-btn" class="bg-blue-600 text-white px-4 py-2 rounded-lg">Filter</button>
                </div>
            </div>

            <div id="history-table" class="overflow-x-auto">
                <table class="w-full text-left table-auto">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Check-in (Aktual/Valid)</th>
                            <th class="px-4 py-3">Check-out (Aktual/Valid)</th>
                            <th class="px-4 py-3">Durasi Valid (jam)</th>
                            <th class="px-4 py-3">Lembur (jam)</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody id="history-body">
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-500">Memuat...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="/js/api-helper.js"></script>
    <script>
        function getMonthOptions() {
            const options = [];
            const now = new Date();
            for (let i = 0; i < 12; i++) {
                const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
                const y = d.getFullYear();
                const m = (d.getMonth() + 1).toString().padStart(2, '0');
                options.push({ value: `${y}-${m}`, label: d.toLocaleString('id-ID', { month: 'long', year: 'numeric' }) });
            }
            return options;
        }

        function populateMonthFilter() {
            const select = document.getElementById('month-filter');
            const options = getMonthOptions();
            select.innerHTML = '';
            options.forEach(o => {
                const opt = document.createElement('option');
                opt.value = o.value;
                opt.textContent = o.label;
                select.appendChild(opt);
            });
        }

        async function loadHistory(month) {
            const tbody = document.getElementById('history-body');
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-8 text-gray-500">Memuat...</td></tr>';
            try {
                const response = await apiRequest(`/attendance/history?month=${month}`, 'GET');
                if (!response.success) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-8 text-red-500">Gagal memuat data</td></tr>';
                    return;
                }

                const sessions = response.data.sessions || [];
                const daily = response.data.daily_summary || [];

                if (daily.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-8 text-gray-500">Belum ada data untuk bulan ini</td></tr>';
                    return;
                }

                tbody.innerHTML = '';
                daily.forEach(d => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="px-4 py-3 align-top">${d.date}</td>
                        <td class="px-4 py-3 align-top">${d.first_check_in || '-'} / ${d.valid_start || '-'}</td>
                        <td class="px-4 py-3 align-top">${d.last_check_out || '-'} / ${d.valid_end || '-'}</td>
                        <td class="px-4 py-3 align-top">${(d.total_valid_duration / 60).toFixed(2)}</td>
                        <td class="px-4 py-3 align-top">${d.overtime_hours || 0}</td>
                        <td class="px-4 py-3 align-top">${d.is_insufficient_duration ? '<span class="text-yellow-600">Kurang Durasi</span>' : '<span class="text-green-600">OK</span>'}</td>
                    `;
                    tbody.appendChild(tr);
                });

            } catch (error) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-8 text-red-500">Terjadi kesalahan saat memuat data</td></tr>';
            }
        }

        window.addEventListener('load', () => {
            populateMonthFilter();
            const initial = document.getElementById('month-filter').value;
            loadHistory(initial);

            document.getElementById('filter-btn').addEventListener('click', () => {
                loadHistory(document.getElementById('month-filter').value);
            });
        });
    </script>
</body>
</html>