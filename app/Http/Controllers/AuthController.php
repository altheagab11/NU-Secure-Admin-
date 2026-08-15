<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectByRole((int) Auth::user()->role_id);
        }

        return view('welcome');
    }

    public function login(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectByRole((int) Auth::user()->role_id);
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! is_string($user->password_hash) || ! $this->isValidPassword($user, $credentials['password'])) {
            ActivityLogService::log(
                action: 'Failed Login',
                module: 'Authentication',
                description: 'Failed login attempt for '.$credentials['email'].'.',
                entityType: 'User',
                entityId: $user?->user_id,
                status: ActivityLogService::STATUS_FAILED,
                userId: null
            );

            throw ValidationException::withMessages([
                'email' => 'Invalid email or password.',
            ]);
        }

        Auth::login($user);

        $request->session()->regenerate();

        $roleId = (int) $user->role_id;

        if (! in_array($roleId, [1, 2, 3, 4], true)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            ActivityLogService::log(
                action: 'Failed Login',
                module: 'Authentication',
                description: 'Failed login attempt for '.$credentials['email'].'. This account is not allowed in the web app.',
                entityType: 'User',
                entityId: $user->user_id ?? null,
                status: ActivityLogService::STATUS_FAILED,
                userId: null
            );

            throw ValidationException::withMessages([
                'email' => 'This account is not allowed in the web app.',
            ]);
        }

        if ($roleId === 3) {
            $hasOffice = DB::table('office_staff')
                ->where('user_id', (int) $user->user_id)
                ->whereNotNull('office_id')
                ->exists();

            if (! $hasOffice) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                ActivityLogService::log(
                    action: 'Failed Login',
                    module: 'Authentication',
                    description: 'Failed login attempt for '.$credentials['email'].'. Account is not assigned to an office.',
                    entityType: 'User',
                    entityId: $user->user_id ?? null,
                    status: ActivityLogService::STATUS_FAILED,
                    userId: null
                );

                throw ValidationException::withMessages([
                    'email' => 'Your account is not assigned to an office. Contact an administrator.',
                ]);
            }
        }

        ActivityLogService::log(
            action: 'Login',
            module: 'Authentication',
            description: ActivityLogService::actorLabel($user).' successfully logged in.',
            entityType: 'User',
            entityId: $user->user_id ?? null
        );

        return $this->redirectByRole($roleId);
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        ActivityLogService::log(
            action: 'Logout',
            module: 'Authentication',
            description: ActivityLogService::actorLabel($user instanceof User ? $user : null).' logged out of the system.',
            entityType: 'User',
            entityId: $user->user_id ?? null
        );

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showPasswordSetupForm(Request $request, string $token): View
    {
        return view('auth.password-setup', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function setupPassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $tokenRow = DB::table('password_reset_tokens')->where('email', $data['email'])->first();

        if (! $tokenRow || ! Hash::check($data['token'], (string) $tokenRow->token)) {
            return back()->withErrors(['email' => 'Invalid or expired password setup link.'])->withInput($request->except('password', 'password_confirmation'));
        }

        $expiresInMinutes = (int) config('auth.passwords.users.expire', 60);
        $createdAt = isset($tokenRow->created_at) ? strtotime((string) $tokenRow->created_at) : null;
        if ($createdAt && (time() - $createdAt) > ($expiresInMinutes * 60)) {
            return back()->withErrors(['email' => 'This password setup link has expired.'])->withInput($request->except('password', 'password_confirmation'));
        }

        $user = User::query()->where('email', $data['email'])->first();
        if (! $user) {
            return back()->withErrors(['email' => 'User account not found.'])->withInput($request->except('password', 'password_confirmation'));
        }

        $newPasswordHash = Hash::make($data['password']);
        $user->password_hash = $newPasswordHash;

        if (Schema::hasColumn('users', 'password')) {
            $user->password = $newPasswordHash;
        }

        if (Schema::hasColumn('users', 'remember_token')) {
            $user->remember_token = Str::random(60);
        }

        $user->save();

        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

        ActivityLogService::log(
            action: 'Password Setup',
            module: 'Authentication',
            description: ActivityLogService::userDisplayName($user).' completed password setup.',
            entityType: 'User',
            entityId: $user->user_id ?? null,
            userId: (int) ($user->user_id ?? 0) ?: null
        );

        // Ensure clean auth/session state before sending user back to login page.
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('login')->with('status', 'Password has been set successfully. You can now log in.');
    }

    private function redirectByRole(int $roleId): RedirectResponse
    {
        if ($roleId === 1) {
            return redirect()->to('/admin/dashboard');
        }

        if ($roleId === 3) {
            return redirect()->route('office.dashboard');
        }

        if ($roleId === 4) {
            return redirect()->to('/guard/register');
        }

        return redirect()->to('/guard/dashboard');
    }

    private function isValidPassword(User $user, string $inputPassword): bool
    {
        $stored = (string) $user->password_hash;

        // Bcrypt/argon hashes generated by password_hash() use this format.
        if (str_starts_with($stored, '$')) {
            return password_verify($inputPassword, $stored);
        }

        // Backward compatibility for plain-text seeded/demo records.
        if (! hash_equals($stored, $inputPassword)) {
            return false;
        }

        // Upgrade plain-text password to hashed value after successful login.
        $user->password_hash = Hash::make($inputPassword);
        $user->save();

        return true;
    }
}
