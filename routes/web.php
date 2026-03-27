<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/admin/user/guards', function () {
    return view('admin.user', ['section' => 'guards']);
});

Route::get('/admin/user/offices', function () {
    return view('admin.user', ['section' => 'offices']);
});

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
