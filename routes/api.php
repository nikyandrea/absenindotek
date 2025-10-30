<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FaceController;
use App\Http\Controllers\Api\LeaveRequestController;
use App\Http\Controllers\Api\Admin\ApprovalController;
use App\Http\Controllers\Api\Admin\MonthlyAdjustmentController;
use App\Http\Controllers\Api\Admin\MonthlyReportController;
use App\Http\Controllers\Api\Admin\OfficeController;
use App\Http\Controllers\Api\Admin\ReportController;
use App\Http\Controllers\Api\Admin\UserManagementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Face Management
    Route::post('/face/enroll', [FaceController::class, 'enroll']);
    Route::post('/face/verify', [FaceController::class, 'verify']);
    Route::get('/face/profile', [FaceController::class, 'getProfile']);

    // Attendance
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut']);
    Route::post('/attendance/sessions/{id}/late-reason', [AttendanceController::class, 'submitLateReason']);
    Route::get('/attendance/history', [AttendanceController::class, 'getHistory']);
    Route::get('/attendance/daily', [AttendanceController::class, 'getDailyAttendance']);

    // Leave Requests
    Route::get('/leave-requests/quota', [LeaveRequestController::class, 'quota']);
    Route::post('/leave-requests/{id}/cancel', [LeaveRequestController::class, 'cancel']);
    Route::apiResource('leave-requests', LeaveRequestController::class)->only(['index', 'store', 'show']);

    // Employee Monthly Report (uses own user ID automatically)
    Route::get('/employee/monthly-report', function (Illuminate\Http\Request $request) {
        $controller = app(MonthlyReportController::class);
        return $controller->show($request, auth()->id());
    })->middleware('role:karyawan');

    // Admin & HRD Routes
    Route::middleware('role:admin,supervisor')->prefix('admin')->group(function () {

        // User Management
        Route::apiResource('users', UserManagementController::class);
        Route::patch('users/{id}/toggle-active', [UserManagementController::class, 'toggleActive']);
        Route::post('users/{id}/reset-password', [UserManagementController::class, 'resetPassword']);

        // Office Management
        Route::apiResource('offices', OfficeController::class);
        Route::post('offices/{id}/toggle-active', [OfficeController::class, 'toggleActive']);
        Route::post('offices/{id}/test-geofence', [OfficeController::class, 'testGeofence']);

        // Holidays
        Route::get('holidays', [OfficeController::class, 'listHolidays']);
        Route::post('holidays', [OfficeController::class, 'createHoliday']);
        Route::delete('holidays/{id}', [OfficeController::class, 'deleteHoliday']);

        // Schedules
        Route::post('schedules', [OfficeController::class, 'createSchedule']);
        Route::put('schedules/{id}', [OfficeController::class, 'updateSchedule']);

        // Reports & Dashboard
        Route::get('dashboard/daily', [ReportController::class, 'dailyDashboard']);
        Route::get('reports/monthly', [ReportController::class, 'monthlyReport']);
        Route::get('reports/employee/{userId}/monthly', [ReportController::class, 'employeeMonthlyReport']);
        Route::post('reports/export', [ReportController::class, 'exportExcel']);

        // Monthly Report (New format matching Google Sheet)
        Route::get('users/{userId}/monthly-report', [MonthlyReportController::class, 'show']);
        Route::get('users/{userId}/monthly-report/export', [MonthlyReportController::class, 'export']);

        // Monthly Adjustments (Potongan & Insentif Tambahan)
        Route::get('users/{userId}/adjustments', [MonthlyAdjustmentController::class, 'index']);
        Route::post('users/{userId}/adjustments', [MonthlyAdjustmentController::class, 'store']);
        Route::put('users/{userId}/adjustments/{id}', [MonthlyAdjustmentController::class, 'update']);
        Route::delete('users/{userId}/adjustments/{id}', [MonthlyAdjustmentController::class, 'destroy']);

        // Approvals
        Route::get('approvals/checkouts/pending', [ApprovalController::class, 'pendingCheckouts']);
        Route::post('approvals/checkouts/{id}/approve', [ApprovalController::class, 'approveCheckout']);
        Route::post('approvals/checkouts/{id}/reject', [ApprovalController::class, 'rejectCheckout']);
        Route::get('approvals/leaves/pending', [ApprovalController::class, 'pendingLeaves']);
        Route::post('approvals/leaves/{id}/approve', [ApprovalController::class, 'approveLeave']);
        Route::post('approvals/leaves/{id}/reject', [ApprovalController::class, 'rejectLeave']);

        // Adjustments (Incentives & Deductions)
        Route::post('adjustments/incentives', [ApprovalController::class, 'addIncentive']);
        Route::post('adjustments/deductions', [ApprovalController::class, 'addDeduction']);
        Route::delete('adjustments/incentives/{id}', [ApprovalController::class, 'deleteIncentive']);
        Route::delete('adjustments/deductions/{id}', [ApprovalController::class, 'deleteDeduction']);

        // Attendance Corrections
        Route::post('attendance/correct', [ApprovalController::class, 'correctAttendance']);
        Route::delete('attendance/sessions/{id}', [ApprovalController::class, 'deleteAttendanceSession']);

        // Audit Logs
        Route::get('audit-logs', [ApprovalController::class, 'getAuditLogs']);
    });
});
