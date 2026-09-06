<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NU-SECURE Login</title>

    <style nonce="{{ $cspNonce }}">
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        html,
        body {
            height: 100%;
            overflow: hidden;
        }

        body {
            min-height: 100vh;
            min-height: 100dvh;
            max-height: 100vh;
            max-height: 100dvh;
            background: linear-gradient(rgba(31, 52, 143, 0.72), rgba(31, 52, 143, 0.72)),
                        url('{{ asset('picture/lipa.png') }}') no-repeat center center/cover;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(8px, 1.5vh, 20px) clamp(12px, 2vw, 20px);
        }

        .login-wrapper {
            width: 100%;
            max-width: min(460px, 100%);
            max-height: 100%;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .brand-title {
            font-size: clamp(28px, 5.2vh, 54px);
            font-weight: 800;
            margin-bottom: clamp(2px, 0.6vh, 8px);
            letter-spacing: 1px;
            line-height: 1.05;
        }

        .brand-title .nu {
            color: #f7c948;
        }

        .brand-title .secure {
            color: #ffffff;
        }

        .brand-subtitle {
            font-size: clamp(13px, 2vh, 18px);
            color: #ffffff;
            margin-bottom: clamp(8px, 2vh, 20px);
            line-height: 1.25;
        }

        .brand-subtitle .highlight {
            color: #f7c948;
            font-weight: 600;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: clamp(14px, 2vh, 24px);
            padding: clamp(12px, 2.2vh, 28px) clamp(14px, 2.5vw, 30px);
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(6px);
            overflow: hidden;
        }

        .logo-box {
            margin-bottom: clamp(6px, 1.4vh, 16px);
        }

        .logo-box img {
            width: clamp(64px, 10vh, 100px);
            height: auto;
            display: block;
            margin: 0 auto;
        }

        .form-group {
            text-align: left;
            margin-bottom: clamp(8px, 1.4vh, 14px);
        }

        .form-label {
            display: block;
            font-size: clamp(13px, 1.8vh, 15px);
            font-weight: 600;
            color: #1f348f;
            margin-bottom: clamp(4px, 0.7vh, 8px);
        }

        .form-control {
            width: 100%;
            height: clamp(40px, 5.5vh, 48px);
            border: 1px solid #cfd7ea;
            border-radius: 12px;
            padding: 0 14px;
            font-size: clamp(13px, 1.8vh, 15px);
            outline: none;
            transition: 0.3s ease;
            background: #fff;
        }

        .form-control:focus {
            border-color: #1f348f;
            box-shadow: 0 0 0 4px rgba(31, 52, 143, 0.12);
        }

        .error-text {
            color: #d93025;
            font-size: 12px;
            margin-top: 4px;
        }

        .alert-box {
            background: #fdecea;
            color: #b3261e;
            border: 1px solid #f5c2c7;
            border-radius: 10px;
            padding: 8px 12px;
            margin-bottom: clamp(8px, 1.2vh, 14px);
            text-align: left;
            font-size: 13px;
        }

        .alert-box.success {
            background: #ecfdf5;
            color: #065f46;
            border-color: #a7f3d0;
        }

        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: clamp(8px, 1.2vh, 14px);
            flex-wrap: wrap;
            gap: 8px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #333;
            font-size: clamp(12px, 1.6vh, 14px);
        }

        .remember-me input {
            transform: scale(1.05);
        }

        .forgot-link {
            color: #1f348f;
            font-size: clamp(12px, 1.6vh, 14px);
            font-weight: 600;
            text-decoration: none;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            height: clamp(40px, 5.5vh, 48px);
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #1f348f, #314dbd);
            color: white;
            font-size: clamp(15px, 2vh, 17px);
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 20px rgba(31, 52, 143, 0.25);
        }

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .captcha-group {
            margin-bottom: clamp(8px, 1.2vh, 14px);
            display: flex;
            flex-direction: column;
            align-items: stretch;
        }

        .cf-turnstile {
            width: 100% !important;
            max-width: 100%;
            transform-origin: left top;
        }

        .footer-text {
            margin-top: clamp(8px, 1.2vh, 14px);
            font-size: clamp(11px, 1.5vh, 13px);
            color: #666;
        }

        /* Short laptop screens — compress further so nothing scrolls */
        @media (max-height: 780px) {
            .brand-title {
                font-size: clamp(24px, 4.5vh, 36px);
            }

            .logo-box img {
                width: clamp(52px, 8vh, 72px);
            }

            .login-card {
                padding: 12px 18px 14px;
            }
        }

        @media (max-height: 680px) {
            .brand-title {
                display: none;
            }

            .brand-subtitle {
                margin-bottom: 8px;
                font-size: 14px;
            }

            .logo-box {
                margin-bottom: 6px;
            }

            .logo-box img {
                width: 56px;
            }
        }

        @media (max-width: 576px) {
            body {
                overflow-y: auto;
                overflow-x: hidden;
                align-items: flex-start;
                padding-top: 16px;
                padding-bottom: 16px;
            }

            .login-wrapper {
                max-height: none;
            }

            .login-card {
                border-radius: 18px;
            }
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <h1 class="brand-title">
            <span class="nu">NU-</span><span class="secure">SECURE</span>
        </h1>

        <p class="brand-subtitle">
            <span class="highlight">V</span>isitor
            <span class="highlight">M</span>onitoring
            <span class="highlight">S</span>ystem
        </p>

        <div class="login-card">
            <div class="logo-box">
                <img src="{{ asset('picture/nu-logo.png') }}" alt="NU Logo">
            </div>

            @if (session('status'))
                <div class="alert-box success">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert-box">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}" id="login-form">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="form-control"
                        placeholder="Enter your email"
                        value="{{ old('email') }}"
                        required
                    >
                    @error('email')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="form-control"
                        placeholder="Enter your password"
                        required
                    >
                    @error('password')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>

                <div class="remember-row">
                    <label class="remember-me">
                        <input type="checkbox" name="remember">
                        Remember me
                    </label>

                    <a href="{{ route('password.request') }}" class="forgot-link">
                        Forgot password?
                    </a>
                </div>

                <div class="form-group captcha-group">
                    @if (! empty($turnstileSiteKey))
                        <div
                            class="cf-turnstile"
                            data-sitekey="{{ $turnstileSiteKey }}"
                            data-theme="light"
                            data-size="flexible"
                        ></div>
                    @endif
                    @error('cf-turnstile-response')
                        <div class="error-text" id="captcha-error">{{ $message }}</div>
                    @else
                        <div class="error-text" id="captcha-error" hidden></div>
                    @enderror
                </div>

                <button type="submit" class="btn-login" id="login-submit">Sign In</button>
            </form>

            <p class="footer-text">National University - Secure Visitor Access</p>
        </div>
    </div>

    @if (! empty($turnstileSiteKey))
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endif
    <script nonce="{{ $cspNonce }}">
        (function () {
            var form = document.getElementById('login-form');
            var button = document.getElementById('login-submit');
            var captchaError = document.getElementById('captcha-error');

            if (!form || !button) {
                return;
            }

            function captchaToken() {
                var input = form.querySelector('input[name="cf-turnstile-response"]');
                return input ? String(input.value || '').trim() : '';
            }

            function showCaptchaError(message) {
                if (!captchaError) {
                    return;
                }
                captchaError.hidden = false;
                captchaError.textContent = message;
            }

            function resetCaptcha() {
                if (window.turnstile && typeof window.turnstile.reset === 'function') {
                    try {
                        window.turnstile.reset();
                    } catch (e) {}
                }
            }

            function unlockButton() {
                button.disabled = false;
                button.textContent = 'Sign In';
                button.dataset.submitting = '0';
            }

            form.addEventListener('submit', function (event) {
                if (button.dataset.submitting === '1') {
                    event.preventDefault();
                    return;
                }

                if (!captchaToken()) {
                    event.preventDefault();
                    showCaptchaError('Please complete the security verification.');
                    resetCaptcha();
                    unlockButton();
                    return;
                }

                button.dataset.submitting = '1';
                button.disabled = true;
                button.textContent = 'Signing in...';
            });

            window.addEventListener('pageshow', function () {
                unlockButton();
                resetCaptcha();
            });
        })();
    </script>
</body>
</html>
