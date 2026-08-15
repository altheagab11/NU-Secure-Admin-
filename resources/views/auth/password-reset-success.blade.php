<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Successful - NU-SECURE</title>
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

            <h2 class="card-heading">Password Reset Successful</h2>
            <p class="card-copy">Your password has been changed successfully. You can now sign in using your new password.</p>

            <a href="{{ route('login') }}" class="btn-login" style="display:flex;align-items:center;justify-content:center;text-decoration:none;">Back to Sign In</a>
            <p class="footer-text">National University - Secure Visitor Access</p>
        </div>
    </div>
</body>
</html>
