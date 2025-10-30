// API Helper Functions
const API_BASE = '/api';

// Get token from localStorage
function getToken() {
    return localStorage.getItem('token');
}

// Check if user is authenticated
function checkAuth() {
    const token = getToken();
    const user = JSON.parse(localStorage.getItem('user') || 'null');

    if (!user || !token) {
        window.location.href = '/login';
        return false;
    }
    return true;
}

// API request helper with proper method handling
async function apiRequest(endpoint, method = 'GET', body = null, customHeaders = {}) {
    const token = getToken();
    
    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...(token ? { 'Authorization': `Bearer ${token}` } : {}),
        ...customHeaders
    };

    const options = {
        method: method,
        headers: headers
    };

    // Add body for POST, PUT, PATCH
    if (body && ['POST', 'PUT', 'PATCH'].includes(method)) {
        options.body = typeof body === 'string' ? body : JSON.stringify(body);
    }

    try {
        const response = await fetch(endpoint.startsWith('http') ? endpoint : `${API_BASE}${endpoint}`, options);
        
        // Handle 401 Unauthorized
        if (response.status === 401) {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            window.location.href = '/login';
            throw new Error('Sesi Anda telah berakhir. Silakan login kembali.');
        }

        const data = await response.json();

        if (!response.ok) {
            // Create custom error with response data
            const error = new Error(data.message || 'Request failed');
            // Attach response data to error object for special handling
            error.responseData = data;
            throw error;
        }

        return data;
    } catch (error) {
        console.error('API Request Error:', error);
        throw error;
    }
}

// Auth API
const AuthAPI = {
    async login(email, password) {
        return apiRequest('/auth/login', 'POST', { email, password });
    },

    async logout() {
        return apiRequest('/auth/logout', 'POST');
    },

    async me() {
        return apiRequest('/auth/me', 'GET');
    }
};

// Users API
const UsersAPI = {
    async getAll(filters = {}) {
        const params = new URLSearchParams(filters);
        return apiRequest(`/admin/users?${params}`, 'GET');
    },

    async get(id) {
        return apiRequest(`/admin/users/${id}`, 'GET');
    },

    async create(data) {
        return apiRequest('/admin/users', 'POST', data);
    },

    async update(id, data) {
        return apiRequest(`/admin/users/${id}`, 'PUT', data);
    },

    async delete(id) {
        return apiRequest(`/admin/users/${id}`, 'DELETE');
    },

    async toggleActive(id) {
        return apiRequest(`/admin/users/${id}/toggle-active`, 'POST');
    }
};

// Reports API
const ReportsAPI = {
    async dailyDashboard(date = null, officeId = null) {
        const params = new URLSearchParams();
        if (date) params.append('date', date);
        if (officeId) params.append('office_id', officeId);
        return apiRequest(`/admin/dashboard/daily?${params}`, 'GET');
    },

    async monthlyReport(filters = {}) {
        const params = new URLSearchParams(filters);
        return apiRequest(`/admin/reports/monthly?${params}`, 'GET');
    },

    async weeklyTrend(filters = {}) {
        const params = new URLSearchParams(filters);
        return apiRequest(`/admin/reports/weekly-trend?${params}`, 'GET');
    },

    async export(filters = {}) {
        const params = new URLSearchParams(filters);
        return apiRequest(`/admin/reports/export?${params}`, 'GET');
    }
};

// Approvals API
const ApprovalsAPI = {
    async getPendingCheckouts() {
        return apiRequest('/admin/approvals/checkouts/pending', 'GET');
    },

    async approveCheckout(id) {
        return apiRequest(`/admin/approvals/checkouts/${id}/approve`, 'POST');
    },

    async rejectCheckout(id, reason) {
        return apiRequest(`/admin/approvals/checkouts/${id}/reject`, 'POST', { reason });
    },

    async getPendingLeaves() {
        return apiRequest('/admin/approvals/leaves/pending', 'GET');
    },

    async approveLeave(id) {
        return apiRequest(`/admin/approvals/leaves/${id}/approve`, 'POST');
    },

    async rejectLeave(id, reason) {
        return apiRequest(`/admin/approvals/leaves/${id}/reject`, 'POST', { reason });
    }
};

// Offices API
const OfficesAPI = {
    async getAll(filters = {}) {
        const params = new URLSearchParams(filters);
        return apiRequest(`/admin/offices?${params}`, 'GET');
    },

    async get(id) {
        return apiRequest(`/admin/offices/${id}`, 'GET');
    },

    async create(data) {
        return apiRequest('/admin/offices', 'POST', data);
    },

    async update(id, data) {
        return apiRequest(`/admin/offices/${id}`, 'PUT', data);
    },

    async delete(id) {
        return apiRequest(`/admin/offices/${id}`, 'DELETE');
    },

    async toggleActive(id) {
        return apiRequest(`/admin/offices/${id}/toggle-active`, 'POST');
    },

    async testGeofence(id, latitude, longitude) {
        return apiRequest(`/admin/offices/${id}/test-geofence`, 'POST', { latitude, longitude });
    }
};

// UI Helper - Show alert/toast
function showAlert(message, type = 'success') {
    const alertDiv = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';

    alertDiv.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-opacity duration-300`;
    alertDiv.textContent = message;

    document.body.appendChild(alertDiv);

    setTimeout(() => {
        alertDiv.style.opacity = '0';
        setTimeout(() => alertDiv.remove(), 300);
    }, 3000);
}

// Check if user has specific role
function hasRole(...roles) {
    const user = JSON.parse(localStorage.getItem('user') || 'null');
    if (!user) return false;
    return roles.includes(user.role);
}

// Hide navigation items based on role
function setupRoleBasedNavigation() {
    const user = JSON.parse(localStorage.getItem('user') || 'null');
    if (!user) return;

    // Hide admin-only menus for karyawan
    const adminMenus = document.querySelectorAll('[data-role-required]');
    adminMenus.forEach(menu => {
        const requiredRoles = menu.getAttribute('data-role-required').split(',');
        if (!requiredRoles.includes(user.role)) {
            menu.classList.add('hidden');
            menu.style.display = 'none';
        } else {
            menu.classList.remove('hidden');
            menu.style.display = '';
        }
    });
    
    // Show employee-specific menus
    const employeeMenus = document.querySelectorAll('[data-role-show]');
    employeeMenus.forEach(menu => {
        const showForRoles = menu.getAttribute('data-role-show').split(',');
        if (showForRoles.includes(user.role)) {
            menu.classList.remove('hidden');
            menu.style.display = 'block';
        } else {
            menu.classList.add('hidden');
            menu.style.display = 'none';
        }
    });
}
