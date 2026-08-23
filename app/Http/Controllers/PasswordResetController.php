<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use App\Services\PasswordResetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function __construct(private PasswordResetService $passwordResetService)
    {
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('login');
        }

        $step = (string) $request->session()->get('password_reset.step', 'email');
        $pendingEmail = (string) $request->session()->get('password_reset.pending_email', '');
        $maskedEmail = (string) $request->session()->get('password_reset.masked_email', '');

        if ($step === 'verify' && $pendingEmail === '') {
            $step = 'email';
        }

        if ($step === 'password' && ! $this->passwordResetService->hasWebVerifiedState($pendingEmail)) {
            $step = $pendingEmail !== '' ? 'verify' : 'email';
        }

        $cooldown = $pendingEmail !== ''
            ? $this->passwordResetService->resendCooldownStatus($pendingEmail)
            : ['allowed' => true, 'retry_after' => 0];

        return view('auth.forgot-password', [
            'step' => $step,
            'maskedEmail' => $maskedEmail,
            'resendRetryAfter' => (int) ($cooldown['retry_after'] ?? 0),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Email address is required.',
            'email.email' => 'Enter a valid email address.',
        ]);

        $email = Str::lower(trim($data['email']));

        if ($rateLimitResponse = $this->tooManyForgotAttempts($request, $email)) {
            return back()
                ->withInput(['email' => $email])
                ->with('error', $rateLimitResponse);
        }

        $result = $this->passwordResetService->requestReset($email);

        if (! $result['success']) {
            return back()
                ->withInput(['email' => $email])
                ->with('error', $result['message']);
        }

        $request->session()->put([
            'password_reset.step' => 'verify',
            'password_reset.pending_email' => $email,
            'password_reset.masked_email' => $this->passwordResetService->maskEmail($email),
        ]);

        $request->session()->forget([
            'password_reset.email',
            'password_reset.verified_at',
            'password_reset.reset_token',
        ]);

        return redirect()
            ->route('password.request')
            ->with('status', $result['message']);
    }

    public function verifyCode(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('login');
        }

        $email = Str::lower(trim((string) $request->session()->get('password_reset.pending_email', '')));

        if ($email === '') {
            return redirect()
                ->route('password.request')
                ->with('error', 'Please enter your email address first.');
        }

        $data = $request->validate([
            'code' => ['required', 'string', 'size:6', 'regex:/^\d{6}$/'],
        ], [
            'code.required' => 'Verification code is required.',
            'code.size' => 'Enter the 6-digit verification code.',
            'code.regex' => 'Enter the 6-digit verification code.',
        ]);

        $result = $this->passwordResetService->verifyCode($email, $data['code']);

        if (! $result['success']) {
            if (($result['code'] ?? '') === PasswordResetService::CODE_TOO_MANY_ATTEMPTS) {
                $request->session()->put('password_reset.step', 'email');
                $request->session()->forget([
                    'password_reset.pending_email',
                    'password_reset.masked_email',
                ]);
            }

            return redirect()
                ->route('password.request')
                ->with('error', $result['message']);
        }

        $resetToken = (string) ($result['reset_token'] ?? '');
        $this->passwordResetService->storeWebVerifiedState($email, $resetToken);

        $request->session()->put('password_reset.step', 'password');

        return redirect()->route('password.request');
    }

    public function changeEmail(Request $request): RedirectResponse
    {
        $this->passwordResetService->clearWebVerifiedState();
        $request->session()->put('password_reset.step', 'email');

        return redirect()->route('password.request');
    }

    public function resendCode(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('login');
        }

        $email = Str::lower(trim((string) $request->session()->get('password_reset.pending_email', '')));

        if ($email === '') {
            return redirect()
                ->route('password.request')
                ->with('error', 'Please enter your email address first.');
        }

        if ($rateLimitResponse = $this->tooManyForgotAttempts($request, $email)) {
            return redirect()
                ->route('password.request')
                ->with('error', $rateLimitResponse);
        }

        $result = $this->passwordResetService->resendVerificationCode($email);

        if (! $result['success']) {
            return redirect()
                ->route('password.request')
                ->with('error', $result['message']);
        }

        $request->session()->put([
            'password_reset.step' => 'verify',
            'password_reset.pending_email' => $email,
            'password_reset.masked_email' => $this->passwordResetService->maskEmail($email),
        ]);

        return redirect()
            ->route('password.request')
            ->with('status', $result['message']);
    }

    public function update(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('login');
        }

        $email = Str::lower(trim((string) $request->session()->get('password_reset.pending_email', '')));
        $resetToken = (string) $request->session()->get('password_reset.reset_token', '');

        if ($email === '' || $resetToken === '') {
            return redirect()
                ->route('password.request')
                ->with('error', 'Your verification has expired. Please request a new verification code.');
        }

        $ipKey = 'password-reset-update:'.$request->ip();
        if (RateLimiter::tooManyAttempts($ipKey, 10)) {
            return redirect()
                ->route('password.request')
                ->with('error', 'Too many password reset requests. Please try again later.');
        }
        RateLimiter::hit($ipKey, 60);

        $data = $request->validate([
            'password' => ['required', 'string', 'confirmed', 'min:8', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/'],
        ], $this->passwordValidationMessages());

        $result = $this->passwordResetService->resetPassword($email, $data['password'], $resetToken);

        if (! $result['success']) {
            return redirect()
                ->route('password.request')
                ->with('error', $result['message']);
        }

        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $this->passwordResetService->clearWebVerifiedState();
        $request->session()->put('password_reset_completed', true);

        return redirect()->route('password.reset.success');
    }

    public function edit(Request $request): RedirectResponse
    {
        return redirect()->route('password.request');
    }

    public function success(Request $request): View|RedirectResponse
    {
        if (! $request->session()->pull('password_reset_completed')) {
            return redirect()->route('login');
        }

        return view('auth.password-reset-success');
    }

    public function apiForgotPassword(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'email' => ['required', 'email'],
            ], [
                'email.required' => 'Email address is required.',
                'email.email' => 'Please enter a valid email address.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid.',
                'errors' => $e->errors(),
            ], 422);
        }

        $email = Str::lower(trim($data['email']));

        if ($message = $this->tooManyForgotAttempts($request, $email)) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 429);
        }

        $result = $this->passwordResetService->requestReset($email);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ], $result['success'] ? 200 : 500);
    }

    public function apiVerifyCode(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'email' => ['required', 'email'],
                'code' => ['required', 'string', 'size:6', 'regex:/^\d{6}$/'],
            ], [
                'email.required' => 'Email address is required.',
                'email.email' => 'Please enter a valid email address.',
                'code.required' => 'Verification code is required.',
                'code.size' => 'Enter the 6-digit verification code.',
                'code.regex' => 'Enter the 6-digit verification code.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid.',
                'errors' => $e->errors(),
            ], 422);
        }

        $email = Str::lower(trim($data['email']));
        $result = $this->passwordResetService->verifyCode($email, $data['code']);

        if (! $result['success']) {
            $status = match ($result['code'] ?? '') {
                PasswordResetService::TOKEN_EXPIRED => 410,
                PasswordResetService::CODE_TOO_MANY_ATTEMPTS => 429,
                PasswordResetService::TOKEN_INVALID => 400,
                default => 500,
            };

            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'code' => $result['code'] ?? null,
            ], $status);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'reset_token' => $result['reset_token'] ?? null,
        ]);
    }

    public function apiResendCode(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'email' => ['required', 'email'],
            ], [
                'email.required' => 'Email address is required.',
                'email.email' => 'Please enter a valid email address.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid.',
                'errors' => $e->errors(),
            ], 422);
        }

        $email = Str::lower(trim($data['email']));

        if ($message = $this->tooManyForgotAttempts($request, $email)) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 429);
        }

        $result = $this->passwordResetService->resendVerificationCode($email);

        if (! $result['success']) {
            $status = ($result['code'] ?? '') === PasswordResetService::RESEND_COOLDOWN ? 429 : 500;

            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'code' => $result['code'] ?? null,
                'retry_after' => $result['retry_after'] ?? null,
            ], $status);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
        ]);
    }

    public function apiResetPassword(Request $request): JsonResponse
    {
        $ipKey = 'password-reset-update:'.$request->ip();
        if (RateLimiter::tooManyAttempts($ipKey, 10)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many password reset requests. Please try again later.',
            ], 429);
        }
        RateLimiter::hit($ipKey, 60);

        try {
            $data = $request->validate([
                'reset_token' => ['required', 'string'],
                'email' => ['required', 'email'],
                'password' => ['required', 'string', 'confirmed', 'min:8', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/'],
            ], $this->passwordValidationMessages());
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid.',
                'errors' => $e->errors(),
            ], 422);
        }

        $email = Str::lower(trim($data['email']));
        $result = $this->passwordResetService->resetPassword($email, $data['password'], (string) $data['reset_token']);

        if (! $result['success']) {
            $status = match ($result['code'] ?? '') {
                PasswordResetService::TOKEN_EXPIRED => 410,
                PasswordResetService::TOKEN_INVALID => 400,
                default => 500,
            };

            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'code' => $result['code'] ?? null,
            ], $status);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
        ]);
    }

    /**
     * @return array<string, string>
     */
    protected function passwordValidationMessages(): array
    {
        return [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'New password is required.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.regex' => 'Password must include at least one uppercase letter, one lowercase letter, and one number.',
            'reset_token.required' => 'Verification is required before resetting your password.',
        ];
    }

    protected function tooManyForgotAttempts(Request $request, string $email): ?string
    {
        $ipKey = 'password-reset-ip:'.$request->ip();
        $emailKey = 'password-reset-email:'.$email;

        if (RateLimiter::tooManyAttempts($ipKey, 5) || RateLimiter::tooManyAttempts($emailKey, 5)) {
            ActivityLogService::log(
                action: 'Password Reset Request Rate-Limited',
                module: 'Authentication',
                description: 'Password reset code request was rate-limited.',
                entityType: 'User',
                status: ActivityLogService::STATUS_FAILED,
                userId: null
            );

            return 'Too many verification code requests. Please try again later.';
        }

        RateLimiter::hit($ipKey, 15 * 60);
        RateLimiter::hit($emailKey, 15 * 60);

        return null;
    }
}
