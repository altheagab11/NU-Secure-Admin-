<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GuardController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
});

Route::get('/admin/visitor', function () {
    return view('admin.visitor');
});

Route::get('/admin/alerts', function () {
    return view('admin.alert');
});

Route::get('/admin/user', function () {
    return view('admin.user');
});

Route::get('/admin/user/guards', [App\Http\Controllers\GuardController::class, 'index']);

// handle add guard POST
Route::post('/admin/user/guards', [GuardController::class, 'store']);

Route::get('/admin/user/offices', [App\Http\Controllers\OfficeController::class, 'index']);
// POST store for new office user
Route::post('/admin/user/offices', [App\Http\Controllers\OfficeController::class, 'store']);

Route::get('/guard/dashboard', function () {
    return view('guard.dashboard');
});

Route::get('/guard/register', function () {
    return view('guard.register');
});

Route::get('/guard/exit', function () {
    return view('guard.exit');
});

Route::get('/guard/alert', function () {
    return view('guard.alert');
});

Route::post('/guard/capture', [GuardController::class, 'saveCapture']);
