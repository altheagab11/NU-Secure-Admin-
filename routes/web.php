<?php
 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AlertsController;
use App\Http\Controllers\GuardController;
use App\Http\Controllers\GuardAlertController;
use App\Http\Controllers\GuardVisitorController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\VisitorMonitoringController;
 
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

Route::get('/password/setup/{token}', [AuthController::class, 'showPasswordSetupForm'])->name('password.setup.form');
Route::post('/password/setup', [AuthController::class, 'setupPassword'])->name('password.setup.submit');
 
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
 
Route::middleware(['auth', 'role:1'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    });
 
    Route::get('/visitor', [VisitorMonitoringController::class, 'index'])->name('admin.visitor');
    Route::get('/visitor/export', [VisitorMonitoringController::class, 'export'])->name('admin.visitor.export');
 
    Route::get('/alerts', [AlertsController::class, 'index']);
    Route::post('/alerts/{alertId}/resolve', [AlertsController::class, 'resolve']);
 
    Route::get('/user', function () {
        return view('admin.user');
    });
 
    Route::get('/user/guards', [GuardController::class, 'index']);
    Route::post('/user/guards', [GuardController::class, 'store']);
    Route::delete('/user/guards/{id}', [GuardController::class, 'recycle']);
    Route::post('/user/guards/{id}/restore', [GuardController::class, 'restore']);
 
    Route::get('/user/offices', [OfficeController::class, 'index']);
    Route::post('/user/offices', [OfficeController::class, 'store']);
    Route::put('/user/offices/{id}', [OfficeController::class, 'update']);
    Route::delete('/user/offices/{id}', [OfficeController::class, 'recycle']);
    Route::post('/user/offices/{id}/restore', [OfficeController::class, 'restore']);
});
 
Route::middleware(['auth', 'role:2'])->prefix('guard')->group(function () {
    Route::get('/dashboard', function () {
        return view('guard.dashboard');
    });
 
    Route::get('/exit', function () {
        return view('guard.exit');
    });
    Route::post('/exit/scan', [GuardVisitorController::class, 'processExitScan']);
 
    Route::get('/alert', [GuardAlertController::class, 'index']);
    Route::post('/alerts/{alertId}/resolve', [GuardAlertController::class, 'resolve']);

});
 
Route::middleware(['auth', 'role:2,4'])->prefix('guard')->group(function () {
    Route::get('/register', function () {
        return view('guard.register');
    });

    Route::post('/register/visitor', [GuardVisitorController::class, 'storeVisitorRegistration']);
    Route::get('/offices', [GuardVisitorController::class, 'getOffices']);
    Route::post('/capture', [GuardVisitorController::class, 'saveCapture']);
    Route::post('/parse-id', [GuardVisitorController::class, 'parseId']);
});