<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>NU-SECURE Login</title>

    <style nonce="{{ $cspNonce }}">
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        html {
            height: 100%;
            height: 100dvh;
            overflow: hidden;
        }

        body {
            width: 100%;
            max-width: 100%;
            height: 100%;
            height: 100dvh;
            overflow: hidden;
            background: linear-gradient(rgba(31, 52, 143, 0.72), rgba(31, 52, 143, 0.72)),
                        url('{{ asset('picture/lipa.png') }}') no-repeat center center/cover;
            -webkit-text-size-adjust: 100%;
            text-size-adjust: 100%;
        }

        .login-stage {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: clamp(8px, 1.6vh, 20px) clamp(12px, 2.4vw, 20px);
        }

        .login-wrapper {
            width: min(460px, 100%);
            text-align: center;
            transform-origin: center center;
        }

        .brand-title {
            font-size: clamp(32px, 6vh, 54px);
            font-weight: 800;
            margin-bottom: clamp(4px, 0.8vh, 8px);
            letter-spacing: 1px;
            line-height: 1.1;
        }

        .brand-title .nu {
            color: #f7c948;
        }

        .brand-title .secure {
            color: #ffffff;
        }

        .brand-subtitle {
            font-size: clamp(14px, 2.1vh, 18px);
            color: #ffffff;
            margin-bottom: clamp(12px, 2.6vh, 28px);
        }

        .brand-subtitle .highlight {
            color: #f7c948;
            font-weight: 600;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: clamp(18px, 2.4vh, 24px);
            padding: clamp(16px, 3vh, 32px) clamp(16px, 2.6vw, 30px);
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(6px);
        }

        .logo-box {
            margin-bottom: clamp(10px, 2vh, 22px);
        }

        .logo-box img {
            width: clamp(72px, 12vh, 110px);
            height: auto;
        }

        .form-group {
            text-align: left;
            margin-bottom: clamp(10px, 1.7vh, 18px);
        }

        .form-label {
            display: block;
            font-size: clamp(14px, 1.9vh, 16px);
            font-weight: 600;
            color: #1f348f;
            margin-bottom: clamp(4px, 0.8vh, 8px);
        }

        .form-control {
            width: 100%;
            height: clamp(42px, 6.2vh, 52px);
            border: 1px solid #cfd7ea;
            border-radius: 12px;
            padding: 0 16px;
            font-size: 15px;
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
            font-size: 13px;
            margin-top: 6px;
        }

        .alert-box {
            background: #fdecea;
            color: #b3261e;
            border: 1px solid #f5c2c7;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: clamp(10px, 1.7vh, 18px);
            text-align: left;
            font-size: 14px;
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
            margin-bottom: clamp(10px, 1.7vh, 18px);
            flex-wrap: wrap;
            gap: 10px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #333;
            font-size: 14px;
        }

        .remember-me input {
            transform: scale(1.1);
        }

        .forgot-link {
            color: #1f348f;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            height: clamp(42px, 6.2vh, 52px);
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #1f348f, #314dbd);
            color: white;
            font-size: clamp(16px, 2.1vh, 18px);
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
            margin-bottom: clamp(10px, 1.7vh, 18px);
            display: flex;
            flex-direction: column;
            align-items: stretch;
            width: 100%;
            max-width: 100%;
        }

        .cf-turnstile {
            width: 100% !important;
            max-width: 100%;
        }

        .footer-text {
            margin-top: clamp(10px, 1.7vh, 18px);
            font-size: 13px;
            color: #666;
        }

        @media (max-width: 576px) {
            .login-card {
                border-radius: 18px;
            }
        }

        @media (max-height: 700px) {
            .remember-row {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>

    <div class="login-stage">
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
    </div>

    @if (! empty($turnstileSiteKey))
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endif
    <script nonce="{{ $cspNonce }}">
        (function () {
            var form = document.getElementById('login-form');
            var button = document.getElementById('login-submit');
            var captchaError = document.getElementById('captcha-error');
            var stage = document.querySelector('.login-stage');
            var wrap = document.querySelector('.login-wrapper');
            var fitFrame = 0;

            function fitLoginToViewport() {
                if (!stage || !wrap) {
                    return;
                }

                var styles = window.getComputedStyle(stage);
                var availableWidth = stage.clientWidth
                    - (parseFloat(styles.paddingLeft) || 0)
                    - (parseFloat(styles.paddingRight) || 0);
                var availableHeight = stage.clientHeight
                    - (parseFloat(styles.paddingTop) || 0)
                    - (parseFloat(styles.paddingBottom) || 0);
                var scale = Math.min(
                    1,
                    availableWidth / Math.max(wrap.offsetWidth, 1),
                    availableHeight / Math.max(wrap.offsetHeight, 1)
                );

                wrap.style.transform = scale < 0.995 ? 'scale(' + scale + ')' : 'none';
            }

            function scheduleFit() {
                if (fitFrame) {
                    cancelAnimationFrame(fitFrame);
                }
                fitFrame = requestAnimationFrame(fitLoginToViewport);
            }

            window.addEventListener('resize', scheduleFit);
            window.addEventListener('orientationchange', scheduleFit);

            if (window.ResizeObserver && wrap) {
                new ResizeObserver(scheduleFit).observe(wrap);
            }

            scheduleFit();

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

            window.addEventListener('pageshow', function (event) {
                unlockButton();
                scheduleFit();
                // Only force-reset on back/forward cache restores. Resetting on every
                // normal load races the Turnstile widget and can reuse dummy tokens.
                if (event.persisted) {
                    resetCaptcha();
                }
            });
        })();
    </script>
</body>
</html>
