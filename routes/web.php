<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminGuardDutyController;
use App\Http\Controllers\AlertsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DailyReportController;
use App\Http\Controllers\DateRangeReportController;
use App\Http\Controllers\EnrolleeProgressController;
use App\Http\Controllers\GuardAlertController;
use App\Http\Controllers\GuardController;
use App\Http\Controllers\GuardDashboardController;
use App\Http\Controllers\GuardDutyController;
use App\Http\Controllers\GuardVisitorController;
use App\Http\Controllers\LiveDataController;
use App\Http\Controllers\LoginAttemptController;
use App\Http\Controllers\Office\OfficeDashboardController;
use App\Http\Controllers\Office\OfficeProfileController;
use App\Http\Controllers\Office\OfficeScannerController;
use App\Http\Controllers\Office\OfficeVisitHistoryController;
use App\Http\Controllers\Office\OfficeVisitorController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\VisitorMonitoringController;
use App\Services\GuardDutyService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'store'])->name('password.email');
Route::post('/forgot-password/verify-code', [PasswordResetController::class, 'verifyCode'])->name('password.verify');
Route::post('/forgot-password/resend-code', [PasswordResetController::class, 'resendCode'])->name('password.resend');
Route::post('/forgot-password/change-email', [PasswordResetController::class, 'changeEmail'])->name('password.change-email');
Route::post('/forgot-password/reset', [PasswordResetController::class, 'update'])->name('password.update');
Route::get('/reset-password/success', [PasswordResetController::class, 'success'])->name('password.reset.success');
Route::get('/reset-password', [PasswordResetController::class, 'edit'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update.legacy');

Route::get('/password/setup/{token}', [AuthController::class, 'showPasswordSetupForm'])->name('password.setup.form');
Route::post('/password/setup', [AuthController::class, 'setupPassword'])->name('password.setup.submit');

// Public enrollee QR progress tracker (opened when enrollee QR is scanned in a browser/camera).
Route::get('/enrollee/progress/{token}/status', [EnrolleeProgressController::class, 'status'])
    ->where('token', '[^/]+')
    ->name('enrollee.progress.status');
Route::get('/enrollee/progress/{token}', [EnrolleeProgressController::class, 'show'])
    ->where('token', '[^/]+')
    ->name('enrollee.progress');

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'role:1,2,3'])->get('/live/status', [LiveDataController::class, 'status'])
    ->name('live.status');

Route::middleware(['auth', 'office.staff'])->prefix('office')->name('office.')->group(function () {
    Route::get('/dashboard', [OfficeDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/live', [OfficeDashboardController::class, 'liveData'])->name('dashboard.live');

    Route::get('/scanner', [OfficeScannerController::class, 'index'])->name('scanner');
    Route::post('/scanner/verify', [OfficeScannerController::class, 'verify'])->middleware('throttle:30,1')->name('scanner.verify');
    Route::post('/scanner/check-in', [OfficeScannerController::class, 'checkIn'])->middleware('throttle:20,1')->name('scanner.check-in');

    Route::get('/expected-visitors', [OfficeVisitorController::class, 'expected'])->name('expected-visitors');
    Route::get('/visitors/{visit}', [OfficeVisitorController::class, 'show'])->whereNumber('visit')->name('visitors.show');
    Route::get('/visitors/{visit}/details', [OfficeVisitorController::class, 'detailsJson'])->whereNumber('visit')->name('visitors.details');

    Route::get('/visit-history', [OfficeVisitHistoryController::class, 'index'])->name('visit-history');

    Route::get('/notifications', [OfficeProfileController::class, 'notifications'])->name('notifications');
    Route::post('/notifications/{notifId}/read', [OfficeProfileController::class, 'markNotificationRead'])
        ->whereNumber('notifId')
        ->name('notifications.read');
});

Route::middleware(['auth', 'role:1'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);

    Route::get('/visitor', [VisitorMonitoringController::class, 'index'])->name('admin.visitor');

    Route::get('/daily-reports', [DailyReportController::class, 'index'])->name('admin.daily-reports');
    Route::post('/daily-reports/generate', [DailyReportController::class, 'generate'])->name('admin.daily-reports.generate');
    Route::post('/daily-reports/{id}/regenerate', [DailyReportController::class, 'regenerate'])
        ->whereNumber('id')
        ->name('admin.daily-reports.regenerate');
    Route::get('/daily-reports/{id}/download', [DailyReportController::class, 'download'])
        ->whereNumber('id')
        ->name('admin.daily-reports.download');

    Route::get('/date-range-reports', [DateRangeReportController::class, 'index'])->name('admin.date-range-reports');
    Route::post('/date-range-reports/generate', [DateRangeReportController::class, 'generate'])
        ->name('admin.date-range-reports.generate');
    Route::get('/date-range-reports/{id}/download', [DateRangeReportController::class, 'download'])
        ->whereNumber('id')
        ->name('admin.date-range-reports.download');

    Route::get('/alerts', [AlertsController::class, 'index']);
    Route::post('/alerts/{alertId}/resolve', [AlertsController::class, 'resolve']);

    Route::get('/guard-duty', [AdminGuardDutyController::class, 'index'])->name('admin.guard-duty');

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

    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('admin.activity-logs');
    Route::get('/activity-logs/summary', [ActivityLogController::class, 'summary'])->name('admin.activity-logs.summary');
    Route::get('/activity-logs/filters', [ActivityLogController::class, 'filters'])->name('admin.activity-logs.filters');
    Route::get('/activity-logs/{log}', [ActivityLogController::class, 'show'])
        ->whereNumber('log')
        ->name('admin.activity-logs.show');

    Route::get('/login-attempts', [LoginAttemptController::class, 'index'])->name('admin.login-attempts');
    Route::get('/login-attempts/summary', [LoginAttemptController::class, 'summary'])->name('admin.login-attempts.summary');
});

Route::middleware(['auth', 'role:1,4'])->prefix('api/self-registration')->group(function () {
    Route::get('/guard-on-duty', [GuardDutyController::class, 'current'])
        ->name('self-registration.guard-on-duty');
});

Route::middleware(['auth', 'role:4'])->prefix('api/self-registration')->group(function () {
    Route::post('/guard-on-duty', [GuardDutyController::class, 'assign'])
        ->middleware('throttle:5,1')
        ->name('self-registration.guard-on-duty.assign');
    Route::post('/guard-on-duty/change', [GuardDutyController::class, 'change'])
        ->middleware('throttle:5,1')
        ->name('self-registration.guard-on-duty.change');
    Route::post('/guard-on-duty/end', [GuardDutyController::class, 'end'])
        ->middleware('throttle:5,1')
        ->name('self-registration.guard-on-duty.end');
});

Route::middleware(['auth', 'role:1'])->prefix('api/admin')->group(function () {
    Route::get('/activity-logs', [ActivityLogController::class, 'list'])->name('api.admin.activity-logs');
    Route::get('/activity-logs/summary', [ActivityLogController::class, 'summary'])->name('api.admin.activity-logs.summary');
    Route::get('/activity-logs/filters', [ActivityLogController::class, 'filters'])->name('api.admin.activity-logs.filters');
    Route::get('/activity-logs/{log}', [ActivityLogController::class, 'show'])
        ->whereNumber('log')
        ->name('api.admin.activity-logs.show');

    Route::get('/login-attempts', [LoginAttemptController::class, 'list'])->name('api.admin.login-attempts');
    Route::get('/login-attempts/summary', [LoginAttemptController::class, 'summary'])->name('api.admin.login-attempts.summary');

    Route::get('/guard-duty', [AdminGuardDutyController::class, 'list'])->name('api.admin.guard-duty');
    Route::get('/guard-duty/filters', [AdminGuardDutyController::class, 'filters'])->name('api.admin.guard-duty.filters');
    Route::get('/guard-duty/{shift}/visitors', [AdminGuardDutyController::class, 'visitors'])
        ->whereNumber('shift')
        ->name('api.admin.guard-duty.visitors');
    Route::get('/guard-duty/{shift}', [AdminGuardDutyController::class, 'show'])
        ->whereNumber('shift')
        ->name('api.admin.guard-duty.show');
});

Route::middleware(['auth', 'role:2'])->prefix('guard')->group(function () {
    Route::get('/dashboard', [GuardDashboardController::class, 'index']);
    Route::get('/dashboard/visits/{visitId}/details', [GuardDashboardController::class, 'visitDetails']);

    Route::get('/exit', function () {
        $activeAlertsCount = DB::table('alerts')
            ->whereRaw("LOWER(TRIM(COALESCE(status, ''))) = ?", ['unresolved'])
            ->count();

        return view('guard.exit', [
            'activeAlertsCount' => $activeAlertsCount,
        ]);
    });
    Route::post('/exit/scan', [GuardVisitorController::class, 'processExitScan']);

    Route::get('/alert', [GuardAlertController::class, 'index']);
    Route::post('/alerts/{alertId}/resolve', [GuardAlertController::class, 'resolve']);

});

Route::middleware(['auth', 'role:2,4'])->prefix('guard')->group(function () {
    Route::get('/register', function () {
        $user = auth()->user();
        $isSelfRegisteredRole = (int) optional($user)->role_id === 4;

        if ($isSelfRegisteredRole && request()->filled('type')) {
            $hasActiveGuard = app(GuardDutyService::class)
                ->hasActiveGuardForKiosk((int) $user->user_id);

            if (! $hasActiveGuard) {
                return redirect('/guard/register');
            }
        }

        $activeAlertsCount = DB::table('alerts')
            ->whereRaw("LOWER(TRIM(COALESCE(status, ''))) = ?", ['unresolved'])
            ->count();

        return view('guard.register', [
            'activeAlertsCount' => $activeAlertsCount,
        ]);
    });

    Route::post('/register/visitor', [GuardVisitorController::class, 'storeVisitorRegistration']);
    Route::get('/offices', [GuardVisitorController::class, 'getOffices']);
    Route::post('/capture', [GuardVisitorController::class, 'saveCapture']);
    Route::post('/parse-id', [GuardVisitorController::class, 'parseId']);
});
