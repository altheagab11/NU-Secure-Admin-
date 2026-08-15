<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - NU-SECURE</title>
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

            <h2 class="card-heading">Forgot Password</h2>
            <p class="card-copy">Enter your registered email address and we'll send you a password reset link.</p>

            @if (session('status'))
                <div class="alert-box success">{{ session('status') }}</div>
            @endif

            @if (session('error'))
                <div class="alert-box">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" id="forgotPasswordForm" novalidate>
                @csrf

                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="form-control"
                        placeholder="Enter your email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                    >
                    <div class="error-text" @if (! $errors->has('email')) hidden @endif>{{ $errors->first('email') }}</div>
                </div>

                <button type="submit" class="btn-login" id="forgotSubmitBtn">Send Reset Link</button>
            </form>

            <a href="{{ route('login') }}" class="back-link">Back to Sign In</a>
            <p class="footer-text">National University - Secure Visitor Access</p>
        </div>
    </div>

    <script>
        document.getElementById('forgotPasswordForm')?.addEventListener('submit', function (event) {
            const emailInput = document.getElementById('email');
            const errorBox = emailInput?.parentElement?.querySelector('.error-text');
            const email = (emailInput?.value || '').trim();
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            const showError = (message) => {
                event.preventDefault();
                if (errorBox) {
                    errorBox.textContent = message;
                    errorBox.hidden = false;
                }
            };

            if (!email) {
                showError('Email address is required.');
                return;
            }

            if (!emailPattern.test(email)) {
                showError('Enter a valid email address.');
                return;
            }

            const btn = document.getElementById('forgotSubmitBtn');
            if (!btn) return;
            btn.disabled = true;
            btn.textContent = 'Sending reset link...';
        });
    </script>
</body>
</html>
