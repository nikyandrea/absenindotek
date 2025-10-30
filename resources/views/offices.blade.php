<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Kantor - Sistem Absensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/js/api-helper.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Sidebar -->
    <div class="fixed inset-y-0 left-0 w-64 bg-gray-900 text-white">
        <div class="p-6">
            <h1 class="text-xl font-bold">Sistem Absensi</h1>
            <p class="text-sm text-gray-400">PT Indotek</p>
        </div>

        <nav class="mt-6">
            <a href="/dashboard" class="flex items-center px-6 py-3 hover:bg-gray-800 transition">
                <i class="fas fa-home w-5 mr-3"></i>
                Dashboard
            </a>
            <a href="/employees" data-role-required="admin,supervisor" class="flex items-center px-6 py-3 hover:bg-gray-800 transition">
                <i class="fas fa-users w-5 mr-3"></i>
                Karyawan
            </a>
            <a href="/offices" data-role-required="admin" class="flex items-center px-6 py-3 bg-gray-800 border-l-4 border-blue-500">
                <i class="fas fa-building w-5 mr-3"></i>
                Kantor
            </a>
            <a href="/attendance" class="flex items-center px-6 py-3 hover:bg-gray-800 transition">
                <i class="fas fa-clipboard-check w-5 mr-3"></i>
                Kehadiran
            </a>
            <a href="/reports" data-role-required="admin,supervisor" class="flex items-center px-6 py-3 hover:bg-gray-800 transition">
                <i class="fas fa-chart-bar w-5 mr-3"></i>
                Laporan
            </a>
            <a href="/reports-monthly" data-role-required="admin,supervisor" class="flex items-center px-6 py-3 hover:bg-gray-800 transition">
                <i class="fas fa-file-invoice w-5 mr-3"></i>
                Laporan Bulanan
            </a>
            <a href="/approvals" data-role-required="admin,supervisor" class="flex items-center px-6 py-3 hover:bg-gray-800 transition">
                <i class="fas fa-check-circle w-5 mr-3"></i>
                Persetujuan
            </a>
        </nav>

        <div class="absolute bottom-0 w-64 p-6 border-t border-gray-800">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold">
                    A
                </div>
                <div class="ml-3">
                    <p class="text-sm font-semibold" id="userName">Loading...</p>
                    <p class="text-xs text-gray-400" id="userRole">Role</p>
                </div>
            </div>
            <button onclick="logout()" class="w-full flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm transition">
                <i class="fas fa-sign-out-alt mr-2"></i>
                Logout
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="ml-64 p-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Management Kantor</h1>
            <p class="text-gray-600">Kelola kantor, lokasi, dan geofence</p>
        </div>

        <!-- Actions -->
        <div class="flex justify-between items-center mb-6">
            <div class="flex gap-4">
                <input type="text" id="searchInput" placeholder="Cari kantor..." 
                    class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none w-64">
                <select id="filterStatus" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
            <button onclick="showAddModal()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition flex items-center">
                <i class="fas fa-plus mr-2"></i>
                Tambah Kantor
            </button>
        </div>

        <!-- Offices Grid -->
        <div id="officesGrid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <!-- Loading state -->
            <div class="col-span-full text-center py-12">
                <i class="fas fa-spinner fa-spin text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-600">Memuat data kantor...</p>
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div id="officeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b">
                <h2 id="modalTitle" class="text-2xl font-bold text-gray-800">Tambah Kantor</h2>
            </div>
            
            <form id="officeForm" class="p-6 space-y-4">
                <input type="hidden" id="officeId">
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Nama Kantor <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="PT Indotek - Jakarta">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Alamat <span class="text-red-500">*</span>
                    </label>
                    <textarea id="address" rows="3" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="Jl. Sudirman No. 123, Jakarta Pusat"></textarea>
                </div>

                <!-- Location Button -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-blue-800 mb-1">
                                <i class="fas fa-map-marker-alt mr-2"></i>
                                Koordinat Lokasi
                            </p>
                            <p class="text-xs text-blue-600">
                                Gunakan lokasi saat ini jika Anda sedang berada di kantor
                            </p>
                        </div>
                        <button type="button" onclick="useCurrentLocation()" 
                            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-semibold flex items-center">
                            <i class="fas fa-crosshairs mr-2"></i>
                            Gunakan Lokasi Saat Ini
                        </button>
                    </div>
                    <div id="locationStatus" class="hidden mt-3 text-sm"></div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Latitude <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="latitude" step="any" required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            placeholder="-6.200000">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Longitude <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="longitude" step="any" required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            placeholder="106.816666">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Radius Geofence (meter) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="radius" required min="10" max="5000" value="100"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="100">
                    <p class="text-sm text-gray-500 mt-1">Min: 10m, Max: 5000m (5km)</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Timezone
                    </label>
                    <select id="timezone" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="Asia/Jakarta">Asia/Jakarta (WIB)</option>
                        <option value="Asia/Makassar">Asia/Makassar (WITA)</option>
                        <option value="Asia/Jayapura">Asia/Jayapura (WIT)</option>
                    </select>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="is_active" checked class="w-5 h-5 text-blue-600">
                    <label for="is_active" class="ml-2 text-sm font-semibold text-gray-700">Kantor Aktif</label>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                        <i class="fas fa-save mr-2"></i>
                        Simpan
                    </button>
                    <button type="button" onclick="closeModal()" class="flex-1 bg-gray-500 text-white py-3 rounded-lg font-semibold hover:bg-gray-600 transition">
                        <i class="fas fa-times mr-2"></i>
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let offices = [];

        // Initialize
        window.addEventListener('load', () => {
            checkAuth();
            setupRoleBasedNavigation();
            loadOffices();
            setupEventListeners();
        });

        function setupEventListeners() {
            document.getElementById('searchInput').addEventListener('input', filterOffices);
            document.getElementById('filterStatus').addEventListener('change', filterOffices);
            document.getElementById('officeForm').addEventListener('submit', handleSubmit);
        }

        async function loadOffices() {
            try {
                const response = await apiRequest('/admin/offices', 'GET');
                if (response.success) {
                    offices = response.data;
                    renderOffices();
                }
            } catch (error) {
                showAlert('Gagal memuat data kantor: ' + error.message, 'error');
            }
        }

        function renderOffices() {
            const grid = document.getElementById('officesGrid');
            const search = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('filterStatus').value;

            let filtered = offices.filter(office => {
                const matchSearch = office.name.toLowerCase().includes(search) || 
                                   office.address.toLowerCase().includes(search);
                const matchStatus = statusFilter === '' || office.is_active.toString() === statusFilter;
                return matchSearch && matchStatus;
            });

            if (filtered.length === 0) {
                grid.innerHTML = `
                    <div class="col-span-full text-center py-12">
                        <i class="fas fa-building text-4xl text-gray-300 mb-4"></i>
                        <p class="text-gray-600">Tidak ada kantor ditemukan</p>
                    </div>
                `;
                return;
            }

            grid.innerHTML = filtered.map(office => `
                <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-800 mb-1">${office.name}</h3>
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full ${office.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                ${office.is_active ? 'Aktif' : 'Nonaktif'}
                            </span>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="editOffice(${office.id})" class="text-blue-600 hover:text-blue-700" title="Edit">
                                <i class="fas fa-edit text-lg"></i>
                            </button>
                            <button onclick="toggleActive(${office.id})" class="text-yellow-600 hover:text-yellow-700" title="Toggle Status">
                                <i class="fas fa-power-off text-lg"></i>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                        <div class="flex items-start">
                            <i class="fas fa-map-marker-alt w-5 mt-0.5 text-gray-400"></i>
                            <span>${office.address}</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-crosshairs w-5 text-gray-400"></i>
                            <span>${office.latitude}, ${office.longitude}</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-circle-notch w-5 text-gray-400"></i>
                            <span>Radius: ${office.radius}m</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-users w-5 text-gray-400"></i>
                            <span>${office.users_count || 0} Karyawan</span>
                        </div>
                    </div>

                    <button onclick="testGeofence(${office.id})" class="w-full bg-gray-100 text-gray-700 py-2 rounded-lg hover:bg-gray-200 transition text-sm font-semibold">
                        <i class="fas fa-location-arrow mr-2"></i>
                        Test Geofence
                    </button>
                </div>
            `).join('');
        }

        function filterOffices() {
            renderOffices();
        }

        // Use Current Location
        function useCurrentLocation() {
            const statusDiv = document.getElementById('locationStatus');
            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');

            if (!navigator.geolocation) {
                statusDiv.innerHTML = `
                    <div class="bg-red-50 border border-red-200 rounded p-3">
                        <i class="fas fa-times-circle text-red-600 mr-2"></i>
                        <span class="text-red-700">Browser Anda tidak mendukung geolokasi</span>
                    </div>
                `;
                statusDiv.classList.remove('hidden');
                return;
            }

            // Show loading
            statusDiv.innerHTML = `
                <div class="bg-blue-50 border border-blue-200 rounded p-3">
                    <i class="fas fa-spinner fa-spin text-blue-600 mr-2"></i>
                    <span class="text-blue-700">Mengambil lokasi Anda... Pastikan GPS aktif</span>
                </div>
            `;
            statusDiv.classList.remove('hidden');

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const accuracy = position.coords.accuracy;

                    // Set values
                    latInput.value = lat.toFixed(8);
                    lngInput.value = lng.toFixed(8);

                    // Show success
                    statusDiv.innerHTML = `
                        <div class="bg-green-50 border border-green-200 rounded p-3">
                            <div class="flex items-start">
                                <i class="fas fa-check-circle text-green-600 mr-2 mt-0.5"></i>
                                <div class="flex-1">
                                    <p class="text-green-700 font-semibold">Lokasi berhasil diambil!</p>
                                    <p class="text-sm text-green-600 mt-1">
                                        <strong>Koordinat:</strong> ${lat.toFixed(6)}, ${lng.toFixed(6)}
                                    </p>
                                    <p class="text-sm text-green-600">
                                        <strong>Akurasi:</strong> ${accuracy.toFixed(1)} meter
                                    </p>
                                    ${accuracy > 50 ? `
                                        <p class="text-xs text-yellow-600 mt-2">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Akurasi GPS kurang baik. Untuk hasil terbaik, pindah ke area terbuka atau dekat jendela.
                                        </p>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    `;

                    // Scroll to coordinates
                    latInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
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
                        default:
                            errorMessage += 'Kesalahan tidak diketahui.';
                    }

                    statusDiv.innerHTML = `
                        <div class="bg-red-50 border border-red-200 rounded p-3">
                            <i class="fas fa-times-circle text-red-600 mr-2"></i>
                            <span class="text-red-700">${errorMessage}</span>
                        </div>
                    `;
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }

        function showAddModal() {
            document.getElementById('modalTitle').textContent = 'Tambah Kantor';
            document.getElementById('officeForm').reset();
            document.getElementById('officeId').value = '';
            document.getElementById('radius').value = 100; // Set default radius
            document.getElementById('timezone').value = 'Asia/Jakarta'; // Set default timezone
            document.getElementById('is_active').checked = true;
            
            // Hide location status
            document.getElementById('locationStatus').classList.add('hidden');
            document.getElementById('locationStatus').innerHTML = '';
            
            document.getElementById('officeModal').classList.remove('hidden');
        }

        function editOffice(id) {
            const office = offices.find(o => o.id === id);
            if (!office) return;

            document.getElementById('modalTitle').textContent = 'Edit Kantor';
            document.getElementById('officeId').value = office.id;
            document.getElementById('name').value = office.name;
            document.getElementById('address').value = office.address;
            document.getElementById('latitude').value = office.latitude;
            document.getElementById('longitude').value = office.longitude;
            document.getElementById('radius').value = office.radius;
            document.getElementById('timezone').value = office.timezone || 'Asia/Jakarta';
            document.getElementById('is_active').checked = office.is_active;
            
            // Hide location status
            document.getElementById('locationStatus').classList.add('hidden');
            document.getElementById('locationStatus').innerHTML = '';
            
            document.getElementById('officeModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('officeModal').classList.add('hidden');
        }

        async function handleSubmit(e) {
            e.preventDefault();

            const id = document.getElementById('officeId').value;
            const data = {
                name: document.getElementById('name').value,
                address: document.getElementById('address').value,
                latitude: parseFloat(document.getElementById('latitude').value),
                longitude: parseFloat(document.getElementById('longitude').value),
                radius: parseInt(document.getElementById('radius').value),
                timezone: document.getElementById('timezone').value,
                is_active: document.getElementById('is_active').checked
            };

            try {
                const method = id ? 'PUT' : 'POST';
                const endpoint = id ? `/admin/offices/${id}` : '/admin/offices';
                
                const response = await apiRequest(endpoint, method, data);
                
                if (response.success) {
                    showAlert(response.message);
                    closeModal();
                    loadOffices();
                }
            } catch (error) {
                showAlert('Gagal menyimpan kantor: ' + error.message, 'error');
            }
        }

        async function toggleActive(id) {
            if (!confirm('Apakah Anda yakin ingin mengubah status kantor ini?')) {
                return;
            }

            try {
                const response = await OfficesAPI.toggleActive(id);
                if (response.success) {
                    showAlert(response.message);
                    loadOffices();
                }
            } catch (error) {
                showAlert('Gagal mengubah status: ' + error.message, 'error');
            }
        }

        async function testGeofence(id) {
            if (!navigator.geolocation) {
                alert('Browser Anda tidak mendukung geolokasi');
                return;
            }

            showAlert('Mengambil lokasi Anda...', 'success');

            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    try {
                        const response = await apiRequest(`/admin/offices/${id}/test-geofence`, 'POST', {
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude
                        });

                        if (response.success) {
                            const data = response.data;
                            const status = data.within_geofence ? '✅ DALAM RADIUS' : '❌ LUAR RADIUS';
                            const message = `
${status}

Jarak: ${data.distance}m
Radius: ${data.allowed_radius}m
Akurasi GPS: ${position.coords.accuracy.toFixed(1)}m

Lokasi Kantor:
Lat: ${data.office_location.latitude}
Lon: ${data.office_location.longitude}

Lokasi Anda:
Lat: ${data.test_location.latitude}
Lon: ${data.test_location.longitude}
                            `.trim();
                            alert(message);
                        }
                    } catch (error) {
                        showAlert('Gagal test geofence: ' + error.message, 'error');
                    }
                },
                (error) => {
                    alert('Gagal mendapatkan lokasi: ' + error.message);
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        }

        async function logout() {
            try {
                await AuthAPI.logout();
            } catch (error) {
                console.error('Logout error:', error);
            } finally {
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                window.location.href = '/login';
            }
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Check authentication
            const user = JSON.parse(localStorage.getItem('user') || 'null');
            if (!user) {
                window.location.href = '/login';
                return;
            }

            // Display user info
            document.getElementById('userName').textContent = user.name;
            document.getElementById('userRole').textContent = user.role === 'admin' ? 'Administrator' : 
                                                              user.role === 'supervisor' ? 'Supervisor' : 'Karyawan';

            // Setup navigation
            setupRoleBasedNavigation();

            // Load offices
            loadOffices();
        });
    </script>
</body>
</html>
