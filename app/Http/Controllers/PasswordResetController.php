<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetMail;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
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
        $ipKey = 'password-reset-ip:'.$request->ip();
        $emailKey = 'password-reset-email:'.$email;

        if (RateLimiter::tooManyAttempts($ipKey, 5) || RateLimiter::tooManyAttempts($emailKey, 5)) {
            return back()
                ->withInput(['email' => $email])
                ->with('error', 'Too many password reset requests. Please try again later.');
        }

        RateLimiter::hit($ipKey, 15 * 60);
        RateLimiter::hit($emailKey, 15 * 60);

        $genericMessage = 'If an account exists for this email address, a password reset link has been sent.';

        try {
            $user = User::findByEmail($email);

            if ($this->isEligibleForReset($user)) {
                try {
                    $this->sendResetLink($user);
                } catch (\Throwable $e) {
                    Log::warning('Password reset email could not be sent.', [
                        'user_id' => $user->user_id ?? null,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Password reset request could not be processed.');

            return back()
                ->withInput(['email' => $email])
                ->with('error', 'Unable to process your request right now. Please try again.');
        }

        return back()->with('status', $genericMessage);
    }

    public function edit(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('login');
        }

        $token = trim((string) $request->query('token', ''));
        $email = Str::lower(trim((string) $request->query('email', '')));

        try {
            $tokenRow = ($token !== '' && $email !== '') ? $this->findValidTokenRow($email, $token) : null;
            $user = $tokenRow ? User::findByEmail($email) : null;
            $valid = (bool) $tokenRow && $this->isEligibleForReset($user);
        } catch (\Throwable $e) {
            Log::warning('Password reset link could not be validated.');
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
        ], [
            'email.required' => 'Email address is required.',
            'email.email' => 'Enter a valid email address.',
            'password.required' => 'New password is required.',
            'password.confirmed' => 'Passwords do not match.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.regex' => 'Password must include at least one uppercase letter, one lowercase letter, and one number.',
        ]);

        $email = Str::lower(trim($data['email']));
        $token = (string) $data['token'];

        try {
            $tokenRow = $this->findValidTokenRow($email, $token);
        } catch (\Throwable $e) {
            Log::warning('Password reset could not be completed.');

            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', 'Unable to process your request right now. Please try again.');
        }

        if (! $tokenRow) {
            ActivityLogService::log(
                action: 'Password Reset Failed',
                module: 'Authentication',
                description: 'Password reset failed because the reset link was invalid or expired.',
                entityType: 'User',
                status: ActivityLogService::STATUS_FAILED,
                userId: null
            );

            return redirect()->route('password.reset', [
                'token' => $token,
                'email' => $email,
            ]);
        }

        $user = User::findByEmail($email);

        if (! $user || ! $this->isEligibleForReset($user)) {
            ActivityLogService::log(
                action: 'Password Reset Failed',
                module: 'Authentication',
                description: 'Password reset failed because the account could not be updated.',
                entityType: 'User',
                status: ActivityLogService::STATUS_FAILED,
                userId: null
            );

            return redirect()->route('password.reset', [
                'token' => $token,
                'email' => $email,
            ]);
        }

        try {
            $newPasswordHash = Hash::make($data['password']);
            $payload = ['password_hash' => $newPasswordHash];

            if (Schema::hasColumn('users', 'password')) {
                $payload['password'] = $newPasswordHash;
            }

            if (Schema::hasColumn('users', 'remember_token')) {
                $payload['remember_token'] = Str::random(60);
            }

            $updated = DB::table('users')
                ->where('user_id', (int) $user->user_id)
                ->update($payload);

            if ($updated < 1) {
                throw new \RuntimeException('Password update did not persist.');
            }
        } catch (\Throwable $e) {
            Log::warning('Password reset could not be completed.', [
                'user_id' => $user->user_id ?? null,
            ]);

            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', 'Unable to process your request right now. Please try again.');
        }

        try {
            DB::table('password_reset_tokens')
                ->whereRaw('LOWER(TRIM(COALESCE(email, \'\'))) = ?', [$email])
                ->delete();

            $this->invalidateUserSessions((int) $user->user_id);
        } catch (\Throwable $e) {
            Log::warning('Password reset cleanup could not be completed.', [
                'user_id' => $user->user_id ?? null,
            ]);
        }

        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        ActivityLogService::log(
            action: 'Password Reset',
            module: 'Authentication',
            description: 'Password was successfully reset for the user account.',
            entityType: 'User',
            entityId: $user->user_id ?? null,
            userId: null
        );

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

    protected function sendResetLink(User $user): void
    {
        $plainToken = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($plainToken),
                'created_at' => now('Asia/Manila'),
            ]
        );

        $resetUrl = route('password.reset', [
            'token' => $plainToken,
            'email' => $user->email,
        ]);

        $fullName = trim(trim((string) ($user->first_name ?? '')).' '.trim((string) ($user->last_name ?? '')));

        Mail::to($user->email)->send(new PasswordResetMail(
            $fullName !== '' ? $fullName : 'User',
            $resetUrl,
            (int) config('auth.passwords.users.expire', 60)
        ));

        ActivityLogService::log(
            action: 'Password Reset Requested',
            module: 'Authentication',
            description: 'A password reset link was requested for a NU-Secure account.',
            entityType: 'User',
            entityId: $user->user_id ?? null,
            userId: null
        );
    }

    protected function findValidTokenRow(string $email, string $token): ?object
    {
        if ($email === '' || $token === '') {
            return null;
        }

        $tokenRow = DB::table('password_reset_tokens')
            ->whereRaw('LOWER(TRIM(COALESCE(email, \'\'))) = ?', [$email])
            ->first();

        if (! $tokenRow || ! Hash::check($token, (string) $tokenRow->token)) {
            return null;
        }

        $expiresInMinutes = (int) config('auth.passwords.users.expire', 60);
        $createdAt = isset($tokenRow->created_at) ? strtotime((string) $tokenRow->created_at) : null;
        if (! $createdAt || (time() - $createdAt) > ($expiresInMinutes * 60)) {
            return null;
        }

        return $tokenRow;
    }

    protected function isEligibleForReset(?User $user): bool
    {
        if (! $user || ! filled($user->email)) {
            return false;
        }

        $status = Str::lower(trim((string) ($user->status ?? 'active')));

        return ! in_array($status, ['inactive', 'disabled', 'suspended', 'recycle_bin', 'deleted'], true);
    }

    protected function invalidateUserSessions(int $userId): void
    {
        if ($userId <= 0 || config('session.driver') !== 'database' || ! Schema::hasTable('sessions')) {
            return;
        }

        try {
            DB::table('sessions')->where('user_id', $userId)->delete();
        } catch (\Throwable $e) {
            Log::warning('Unable to invalidate sessions after password reset.', [
                'user_id' => $userId,
            ]);
        }
    }
}
