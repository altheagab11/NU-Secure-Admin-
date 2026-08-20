<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'apiLogin']);

Route::post('/forgot-password', [PasswordResetController::class, 'apiForgotPassword'])
    ->middleware('throttle:5,15');

Route::post('/reset-password', [PasswordResetController::class, 'apiResetPassword'])
    ->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'user' => $request->user()?->toApiUser(),
        ]);
    });

    Route::post('/logout', [AuthController::class, 'apiLogout']);
});
