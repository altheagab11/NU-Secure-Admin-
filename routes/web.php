<?php
 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AlertsController;
use App\Http\Controllers\GuardController;
use App\Http\Controllers\GuardVisitorController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\VisitorMonitoringController;
 
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});
 
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
 
Route::middleware(['auth', 'role:1'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    });
 
    Route::get('/visitor', [VisitorMonitoringController::class, 'index'])->name('admin.visitor');
 
    Route::get('/alerts', [AlertsController::class, 'index']);
    Route::post('/alerts/{alertId}/resolve', [AlertsController::class, 'resolve']);
 
    Route::get('/user', function () {
        return view('admin.user');
    });
 
    Route::get('/user/guards', [GuardController::class, 'index']);
    Route::post('/user/guards', [GuardController::class, 'store']);
 
    Route::get('/user/offices', [OfficeController::class, 'index']);
    Route::post('/user/offices', [OfficeController::class, 'store']);
});
 
Route::middleware(['auth', 'role:2'])->prefix('guard')->group(function () {
    Route::get('/dashboard', function () {
        return view('guard.dashboard');
    });
 
    Route::get('/register', function () {
        return view('guard.register');
    });
 
    Route::get('/exit', function () {
        return view('guard.exit');
    });
 
    Route::get('/alert', function () {
        return view('guard.alert');
    });
 
    Route::get('/offices', [GuardVisitorController::class, 'getOffices']);
    Route::post('/capture', [GuardVisitorController::class, 'saveCapture']);
    Route::post('/parse-id', [GuardVisitorController::class, 'parseId']);
});