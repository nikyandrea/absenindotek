<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::get('/employees', function () {
    return view('employees');
});

Route::get('/attendance', function () {
    return view('attendance');
});

Route::get('/reports', function () {
    return view('reports');
});

Route::get('/reports-monthly', function () {
    return view('reports-monthly');
});

Route::get('/approvals', function () {
    return view('approvals');
});

Route::get('/offices', function () {
    return view('offices');
});

// Attendance routes for employees (check token in localStorage via JS)
Route::prefix('attendance')->group(function () {
    Route::get('/check-in', function () {
        return view('attendance.check-in');
    });
    
    Route::get('/check-out', function () {
        return view('attendance.check-out');
    });
    
    Route::get('/late-reason', function () {
        return view('attendance.late-reason');
    });
    
    Route::get('/late-success', function () {
        return view('attendance.late-success');
    });
    
    Route::get('/success', function () {
        return view('attendance.success');
    });
    
    Route::get('/checkout-success', function () {
        return view('attendance.checkout-success');
    });
    
    Route::get('/history', function () {
        return view('attendance.history');
    });
});

// Face enrollment route
Route::get('/face/enroll', function () {
    return view('face.enroll');
});

// Leave request routes
Route::prefix('leave')->group(function () {
    Route::get('/request', function () {
        return view('leave.request');
    });
    
    Route::get('/history', function () {
        return view('leave.history');
    });
});
