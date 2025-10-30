<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Absensi Indotek</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <!-- Logo & Title -->
        <div class="text-center mb-8">
            <div class="bg-blue-600 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-800">Sistem Absensi</h1>
            <p class="text-gray-600 mt-2">PT Indotek Indonesia</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Login Admin/HRD</h2>

            <!-- Alert -->
            <div id="alert" class="hidden mb-4"></div>

            <!-- Login Form -->
            <form id="loginForm" onsubmit="handleLogin(event)">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" id="email" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="admin@absensi.com">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <input type="password" id="password" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent pr-12"
                               placeholder="••••••••">
                        <button type="button" onclick="togglePassword('password', 'togglePasswordIcon')" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none">
                            <i id="togglePasswordIcon" class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit"
                        class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-200">
                    Login
                </button>
            </form>

            <!-- Test Accounts -->
            <div class="mt-6 pt-6 border-t">
                <p class="text-sm text-gray-600 mb-3">Test Accounts:</p>
                <div class="space-y-2 text-xs">
                    <button onclick="fillCredentials('admin@absensi.com', 'password123')"
                            class="w-full text-left px-3 py-2 bg-red-50 rounded hover:bg-red-100 transition">
                        <span class="font-semibold text-red-600">Admin:</span> admin@absensi.com / password123
                    </button>
                    <button onclick="fillCredentials('supervisor@absensi.com', 'password123')"
                            class="w-full text-left px-3 py-2 bg-blue-50 rounded hover:bg-blue-100 transition">
                        <span class="font-semibold text-blue-600">Supervisor:</span> supervisor@absensi.com / password123
                    </button>
                    <button onclick="fillCredentials('budi@absensi.com', 'password123')"
                            class="w-full text-left px-3 py-2 bg-green-50 rounded hover:bg-green-100 transition">
                        <span class="font-semibold text-green-600">Karyawan:</span> budi@absensi.com / password123
                    </button>
                </div>
            </div>
        </div>

        <!-- Back to API Testing -->
        <div class="text-center mt-6">
            <a href="/" class="text-blue-600 hover:underline text-sm">← Kembali ke API Testing</a>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        function fillCredentials(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
        }

        function showAlert(message, type = 'error') {
            const alertDiv = document.getElementById('alert');
            const bgColor = type === 'error' ? 'bg-red-50 border-red-200 text-red-800' : 'bg-green-50 border-green-200 text-green-800';

            alertDiv.className = `p-4 rounded-lg border ${bgColor}`;
            alertDiv.textContent = message;
            alertDiv.classList.remove('hidden');

            setTimeout(() => alertDiv.classList.add('hidden'), 5000);
        }

        async function handleLogin(event) {
            event.preventDefault();

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            try {
                const response = await fetch('/api/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email, password })
                });

                const result = await response.json();

                if (result.success) {
                    // Save token and user data
                    localStorage.setItem('token', result.data.token);
                    localStorage.setItem('user', JSON.stringify(result.data.user));

                    showAlert('Login berhasil!', 'success');

                    // Redirect to dashboard
                    setTimeout(() => {
                        window.location.href = '/dashboard';
                    }, 500);
                } else {
                    showAlert(result.message || 'Login gagal!', 'error');
                }
            } catch (error) {
                showAlert('Terjadi kesalahan: ' + error.message, 'error');
            }
        }
    </script>
</body>
</html>
