<?php

namespace App\Http\Controllers;

use App\Models\User;
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

    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('login');
        }

        return view('auth.forgot-password');
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

        return back()->with('status', $result['message']);
    }

    public function edit(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('login');
        }

        $token = trim((string) $request->query('token', ''));
        $email = Str::lower(trim((string) $request->query('email', '')));

        try {
            $tokenRow = ($token !== '' && $email !== '')
                ? $this->passwordResetService->findValidTokenRow($email, $token)
                : null;
            $user = $tokenRow ? User::findByEmail($email) : null;
            $valid = (bool) $tokenRow && $this->passwordResetService->isEligibleForReset($user);
        } catch (\Throwable $e) {
            $valid = false;
        }

        if (! $valid) {
            return view('auth.reset-password', [
                'token' => $token,
                'email' => $email,
                'invalidLink' => true,
            ]);
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email,
            'invalidLink' => false,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('login');
        }

        $ipKey = 'password-reset-update:'.$request->ip();
        if (RateLimiter::tooManyAttempts($ipKey, 10)) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', 'Too many password reset requests. Please try again later.');
        }
        RateLimiter::hit($ipKey, 60);

        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'confirmed', 'min:8', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/'],
        ], $this->passwordValidationMessages());

        $email = Str::lower(trim($data['email']));
        $token = (string) $data['token'];

        $result = $this->passwordResetService->resetPassword($email, $token, $data['password']);

        if (! $result['success']) {
            return redirect()->route('password.reset', [
                'token' => $token,
                'email' => $email,
            ])->with('error', $result['message']);
        }

        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $request->session()->put('password_reset_completed', true);

        return redirect()->route('password.reset.success');
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
                'token' => ['required', 'string'],
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
        $result = $this->passwordResetService->resetPassword($email, (string) $data['token'], $data['password']);

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
            'password.confirmed' => 'Passwords do not match.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.regex' => 'Password must include at least one uppercase letter, one lowercase letter, and one number.',
            'token.required' => 'Reset token is required.',
        ];
    }

    protected function tooManyForgotAttempts(Request $request, string $email): ?string
    {
        $ipKey = 'password-reset-ip:'.$request->ip();
        $emailKey = 'password-reset-email:'.$email;

        if (RateLimiter::tooManyAttempts($ipKey, 5) || RateLimiter::tooManyAttempts($emailKey, 5)) {
            return 'Too many password reset requests. Please try again later.';
        }

        RateLimiter::hit($ipKey, 15 * 60);
        RateLimiter::hit($emailKey, 15 * 60);

        return null;
    }
}
