<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - NU-SECURE</title>
    <style>
        @include('auth.partials.auth-styles')
    </style>
</head>
<body>
    <div class="login-wrapper">
        @include('auth.partials.auth-brand')

        <div class="login-card">
            <div class="logo-box">
                <img src="{{ asset('picture/nu-logo.png') }}" alt="NU Logo">
            </div>

            @if(!empty($invalidLink))
                <h2 class="card-heading">Reset Link Expired</h2>
                <p class="card-copy">This password reset link is invalid or has expired.</p>
                <a href="{{ route('password.request') }}" class="btn-login" style="display:flex;align-items:center;justify-content:center;text-decoration:none;">Request New Reset Link</a>
                <a href="{{ route('login') }}" class="back-link">Back to Sign In</a>
            @else
                <h2 class="card-heading">Reset Password</h2>
                <p class="card-copy">Create a new password for your NU-Secure account.</p>

                @if (session('error'))
                    <div class="alert-box">{{ session('error') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert-box">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" id="resetPasswordForm" novalidate>
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ old('email', $email) }}">

                    <div class="form-group">
                        <label class="form-label" for="password">New Password</label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="form-control"
                            placeholder="Enter new password"
                            minlength="8"
                            autocomplete="new-password"
                        >
                        <div class="error-text" id="passwordError" @if (! $errors->has('password')) hidden @endif>{{ $errors->first('password') }}</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">Confirm New Password</label>
                        <input
                            type="password"
                            name="password_confirmation"
                            id="password_confirmation"
                            class="form-control"
                            placeholder="Confirm new password"
                            minlength="8"
                            autocomplete="new-password"
                        >
                        <div class="error-text" id="passwordConfirmationError" hidden></div>
                    </div>

                    <button type="submit" class="btn-login" id="resetSubmitBtn">Reset Password</button>
                </form>

                <a href="{{ route('login') }}" class="back-link">Back to Sign In</a>
            @endif

            <p class="footer-text">National University - Secure Visitor Access</p>
        </div>
    </div>

    <script nonce="{{ $cspNonce }}">
        document.getElementById('resetPasswordForm')?.addEventListener('submit', function (event) {
            const password = document.getElementById('password')?.value || '';
            const confirmation = document.getElementById('password_confirmation')?.value || '';
            const confirmError = document.getElementById('passwordConfirmationError');

            const passwordError = document.getElementById('passwordError');

            if (confirmError) {
                confirmError.hidden = true;
                confirmError.textContent = '';
            }

            if (passwordError) {
                passwordError.hidden = true;
                passwordError.textContent = '';
            }

            if (!password) {
                event.preventDefault();
                if (passwordError) {
                    passwordError.textContent = 'New password is required.';
                    passwordError.hidden = false;
                }
                return;
            }

            if (password !== confirmation) {
                event.preventDefault();
                if (confirmError) {
                    confirmError.textContent = 'Passwords do not match.';
                    confirmError.hidden = false;
                }
                return;
            }

            const btn = document.getElementById('resetSubmitBtn');
            if (!btn) return;
            btn.disabled = true;
            btn.textContent = 'Resetting password...';
        });
    </script>
</body>
</html>
