<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\CaptchaService;
use App\Services\LoginAttemptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectByRole((int) Auth::user()->role_id);
        }

        return view('welcome', [
            'turnstileSiteKey' => app(CaptchaService::class)->siteKey(),
        ]);
    }

    public function login(Request $request, CaptchaService $captcha): RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectByRole((int) Auth::user()->role_id);
        }

        $email = LoginAttemptService::normalizeEmail((string) $request->input('email', ''));

        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
            ]);
        } catch (ValidationException $e) {
            if ($email !== '') {
                $knownUser = User::findByEmail($email);
                LoginAttemptService::recordFailure($knownUser, $email, LoginAttemptService::REASON_INVALID_CREDENTIALS, $request);
            }

            throw $e;
        }

        $email = LoginAttemptService::normalizeEmail($credentials['email']);

        $this->assertWebCaptchaPassed($request, $captcha, $email);

        if (LoginAttemptService::tooManyAttempts($request, $email)) {
            $knownUser = User::findByEmail($email);
            LoginAttemptService::recordBlocked($knownUser, $email, $request);

            throw ValidationException::withMessages([
                'email' => 'Too many login attempts. Please try again later.',
            ]);
        }

        $user = User::findByEmail($email);

        if (! $user) {
            LoginAttemptService::hit($request, $email);
            LoginAttemptService::recordFailure(null, $email, LoginAttemptService::REASON_ACCOUNT_NOT_FOUND, $request);
            $this->logFailedAuthentication(null, 'Failed login attempt for '.$email.'.');

            throw ValidationException::withMessages([
                'email' => 'Invalid email or password.',
            ]);
        }

        if (! $this->isValidPassword($user, $credentials['password'])) {
            LoginAttemptService::hit($request, $email);
            LoginAttemptService::recordFailure($user, $email, LoginAttemptService::REASON_INCORRECT_PASSWORD, $request);
            $this->logFailedAuthentication($user, 'Failed login attempt for '.$email.'.');

            throw ValidationException::withMessages([
                'email' => 'Invalid email or password.',
            ]);
        }

        if (! $this->isAccountActive($user)) {
            LoginAttemptService::hit($request, $email);
            LoginAttemptService::recordFailure($user, $email, LoginAttemptService::inactiveReason($user), $request);
            $this->logFailedAuthentication($user, 'Failed login attempt for '.$email.'. Account is inactive.');

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

            LoginAttemptService::hit($request, $email);
            LoginAttemptService::recordFailure($user, $email, LoginAttemptService::REASON_UNAUTHORIZED_ROLE, $request);
            $this->logFailedAuthentication(
                $user,
                'Failed login attempt for '.$email.'. This account is not allowed in the web app.'
            );

            throw ValidationException::withMessages([
                'email' => 'This account is not allowed in the web app.',
            ]);
        }

        if ($roleId === 3) {
            $hasOffice = DB::table('office_staff as staff')
                ->where('staff.user_id', (int) $user->user_id)
                ->whereNotNull('staff.office_id')
                ->exists();

            if (! $hasOffice) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                LoginAttemptService::hit($request, $email);
                LoginAttemptService::recordFailure($user, $email, LoginAttemptService::REASON_OFFICE_NOT_ASSIGNED, $request);
                $this->logFailedAuthentication(
                    $user,
                    'Failed login attempt for '.$email.'. Account is not assigned to an office.'
                );

                throw ValidationException::withMessages([
                    'email' => 'Your account is not assigned to an office. Contact an administrator.',
                ]);
            }
        }

        LoginAttemptService::clear($request, $email);
        LoginAttemptService::recordSuccess($user, $email, $request);

        ActivityLogService::log(
            action: 'Login',
            module: 'Authentication',
            description: ActivityLogService::actorLabel($user).' successfully logged in.',
            entityType: 'User',
            entityId: $user->user_id ?? null
        );

        return $this->redirectByRole($roleId);
    }

    public function apiLogin(Request $request): JsonResponse
    {
        $email = LoginAttemptService::normalizeEmail((string) $request->input('email', ''));

        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
                'device_name' => ['nullable', 'string', 'max:100'],
            ]);
        } catch (ValidationException $e) {
            if ($email !== '') {
                $knownUser = User::findByEmail($email);
                LoginAttemptService::recordFailure($knownUser, $email, LoginAttemptService::REASON_INVALID_CREDENTIALS, $request);
            }

            throw $e;
        }

        $email = LoginAttemptService::normalizeEmail($credentials['email']);

        if (LoginAttemptService::tooManyAttempts($request, $email)) {
            $knownUser = User::findByEmail($email);
            LoginAttemptService::recordBlocked($knownUser, $email, $request);

            return response()->json([
                'success' => false,
                'message' => 'Too many login attempts. Please try again later.',
            ], 429);
        }

        $user = User::findByEmail($email);

        if (! $user) {
            LoginAttemptService::hit($request, $email);
            LoginAttemptService::recordFailure(null, $email, LoginAttemptService::REASON_ACCOUNT_NOT_FOUND, $request);
            $this->logFailedAuthentication(null, 'Failed mobile login attempt for '.$email.'.');

            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 422);
        }

        if (! $this->isValidPassword($user, $credentials['password'])) {
            LoginAttemptService::hit($request, $email);
            LoginAttemptService::recordFailure($user, $email, LoginAttemptService::REASON_INCORRECT_PASSWORD, $request);
            $this->logFailedAuthentication($user, 'Failed mobile login attempt for '.$email.'.');

            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 422);
        }

        if (! $this->isAccountActive($user)) {
            LoginAttemptService::hit($request, $email);
            LoginAttemptService::recordFailure($user, $email, LoginAttemptService::inactiveReason($user), $request);
            $this->logFailedAuthentication($user, 'Failed mobile login attempt for '.$email.'. Account is inactive.');

            return response()->json([
                'success' => false,
                'message' => 'Your account is inactive. Contact an administrator.',
            ], 403);
        }

        $roleId = (int) $user->role_id;

        if (! in_array($roleId, [1, 2, 3, 4], true)) {
            LoginAttemptService::hit($request, $email);
            LoginAttemptService::recordFailure($user, $email, LoginAttemptService::REASON_UNAUTHORIZED_ROLE, $request);
            $this->logFailedAuthentication(
                $user,
                'Failed mobile login attempt for '.$email.'. This account is not allowed in the mobile application.'
            );

            return response()->json([
                'success' => false,
                'message' => 'This account is not allowed to use the mobile application.',
            ], 403);
        }

        if ($roleId === 3) {
            $hasOffice = DB::table('office_staff as staff')
                ->where('staff.user_id', (int) $user->user_id)
                ->whereNotNull('staff.office_id')
                ->exists();

            if (! $hasOffice) {
                LoginAttemptService::hit($request, $email);
                LoginAttemptService::recordFailure($user, $email, LoginAttemptService::REASON_OFFICE_NOT_ASSIGNED, $request);
                $this->logFailedAuthentication(
                    $user,
                    'Failed mobile login attempt for '.$email.'. Account is not assigned to an office.'
                );

                return response()->json([
                    'success' => false,
                    'message' => 'Your account is not assigned to an office. Contact an administrator.',
                ], 403);
            }
        }

        $deviceName = $credentials['device_name'] ?? 'NU-Secure Mobile';

        $token = $user->createToken(
            $deviceName,
            ['mobile']
        )->plainTextToken;

        LoginAttemptService::clear($request, $email);
        LoginAttemptService::recordSuccess($user, $email, $request);

        ActivityLogService::log(
            action: 'Login',
            module: 'Authentication',
            description: ActivityLogService::actorLabel($user).' successfully logged in using the mobile application.',
            entityType: 'User',
            entityId: $user->user_id ?? null,
            userId: (int) $user->user_id
        );

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'token_type' => 'Bearer',
            'token' => $token,
            'user' => $user->toApiUser(),
        ]);
    }

    public function apiLogout(Request $request): JsonResponse
    {
        $user = $request->user();

        ActivityLogService::log(
            action: 'Logout',
            module: 'Authentication',
            description: ActivityLogService::actorLabel($user instanceof User ? $user : null).' logged out from the mobile application.',
            entityType: 'User',
            entityId: $user?->user_id,
            userId: $user?->user_id !== null ? (int) $user->user_id : null
        );

        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
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

    private function assertWebCaptchaPassed(Request $request, CaptchaService $captcha, string $email): void
    {
        $token = $captcha->tokenFrom((string) $request->input('cf-turnstile-response', ''));
        $knownUser = $this->findUserForAttempt($email);

        if ($token === '') {
            LoginAttemptService::recordFailure(
                $knownUser,
                $email,
                LoginAttemptService::REASON_CAPTCHA_MISSING,
                $request
            );

            throw ValidationException::withMessages([
                'cf-turnstile-response' => 'Please complete the security verification.',
            ]);
        }

        if (! $captcha->verify($token, $request->ip())) {
            LoginAttemptService::recordFailure(
                $knownUser,
                $email,
                LoginAttemptService::REASON_CAPTCHA_FAILED,
                $request
            );

            throw ValidationException::withMessages([
                'cf-turnstile-response' => 'Security verification failed. Please try again.',
            ]);
        }
    }

    private function findUserForAttempt(string $email): ?User
    {
        if ($email === '') {
            return null;
        }

        try {
            return User::findByEmail($email);
        } catch (Throwable) {
            return null;
        }
    }

    private function logFailedAuthentication(?User $user, string $description): void
    {
        ActivityLogService::log(
            action: 'Failed Login',
            module: 'Authentication',
            description: $description,
            entityType: 'User',
            entityId: $user?->user_id,
            status: 'Failed',
            userId: null
        );
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

    private function isAccountActive(User $user): bool
    {
        $status = strtolower(trim((string) ($user->status ?? 'active')));

        return ! in_array($status, ['inactive', 'disabled', 'suspended', 'recycle_bin', 'deleted'], true);
    }

    private function isValidPassword(User $user, string $inputPassword): bool
    {
        $stored = (string) ($user->getAttributes()['password_hash'] ?? '');

        if ($stored === '') {
            return false;
        }

        // Bcrypt/argon hashes generated by password_hash() / Hash::make().
        if (str_starts_with($stored, '$')) {
            return Hash::check($inputPassword, $stored);
        }

        // Backward compatibility for plain-text seeded/demo records.
        if (! hash_equals($stored, $inputPassword)) {
            return false;
        }

        $this->persistPasswordHash($user, $inputPassword);

        return true;
    }

    private function persistPasswordHash(User $user, string $plainPassword): void
    {
        $hash = Hash::make($plainPassword);
        $payload = ['password_hash' => $hash];

        if (Schema::hasColumn('users', 'password')) {
            $payload['password'] = $hash;
        }

        DB::table('users')
            ->where('user_id', (int) $user->user_id)
            ->update($payload);
    }
}
