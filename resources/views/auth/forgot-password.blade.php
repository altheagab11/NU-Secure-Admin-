<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - NU-SECURE</title>
    <style nonce="{{ $cspNonce }}">
        @include('auth.partials.auth-styles')

        .step-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 24px;
            font-size: 13px;
            color: #64748b;
        }

        .step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            min-width: 72px;
        }

        .step-dot {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 2px solid #cfd7ea;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            color: #94a3b8;
            background: #fff;
        }

        .step-dot.active {
            border-color: #1f348f;
            background: #1f348f;
            color: #fff;
        }

        .step-dot.done {
            border-color: #059669;
            background: #059669;
            color: #fff;
        }

        .step-label {
            font-weight: 600;
            color: #64748b;
        }

        .step-label.active { color: #1f348f; }
        .step-label.done { color: #059669; }

        .step-line {
            width: 36px;
            height: 2px;
            background: #e2e8f0;
            margin-bottom: 22px;
        }

        .otp-group {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 18px;
        }

        .otp-input {
            width: 44px;
            height: 52px;
            border: 1px solid #cfd7ea;
            border-radius: 12px;
            text-align: center;
            font-size: 20px;
            font-weight: 700;
            color: #1f348f;
            outline: none;
            transition: 0.3s ease;
            background: #fff;
        }

        .otp-input:focus {
            border-color: #1f348f;
            box-shadow: 0 0 0 4px rgba(31, 52, 143, 0.12);
        }

        .otp-hidden {
            position: absolute;
            opacity: 0;
            pointer-events: none;
            width: 0;
            height: 0;
        }

        .helper-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 14px;
            align-items: center;
        }

        .text-link {
            background: none;
            border: none;
            color: #1f348f;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            padding: 0;
        }

        .text-link:hover { text-decoration: underline; }
        .text-link:disabled {
            color: #94a3b8;
            cursor: not-allowed;
            text-decoration: none;
        }

        .password-toggle-wrap {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #1f348f;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .masked-email {
            font-weight: 700;
            color: #1f348f;
            word-break: break-all;
        }

        @media (max-width: 576px) {
            .otp-input {
                width: 40px;
                height: 48px;
                font-size: 18px;
            }

            .step-item { min-width: 58px; }
            .step-line { width: 20px; }
        }
    </style>
</head>
<body>
    @php
        $currentStep = $step ?? 'email';
        $steps = ['email', 'verify', 'password'];
        $stepIndex = array_search($currentStep, $steps, true);
        if ($stepIndex === false) {
            $stepIndex = 0;
            $currentStep = 'email';
        }
    @endphp

    <div class="login-wrapper">
        @include('auth.partials.auth-brand')

        <div class="login-card">
            <div class="logo-box">
                <img src="{{ asset('picture/nu-logo.png') }}" alt="NU Logo">
            </div>

            <div class="step-indicator" aria-label="Password reset progress">
                @foreach ($steps as $index => $label)
                    @php
                        $state = $index < $stepIndex ? 'done' : ($index === $stepIndex ? 'active' : '');
                        $symbol = $index < $stepIndex ? '✓' : '●';
                    @endphp
                    @if ($index > 0)
                        <div class="step-line"></div>
                    @endif
                    <div class="step-item">
                        <div class="step-dot {{ $state }}">{{ $symbol }}</div>
                        <span class="step-label {{ $state }}">{{ ucfirst($label === 'verify' ? 'Verify' : ($label === 'password' ? 'Password' : 'Email')) }}</span>
                    </div>
                @endforeach
            </div>

            @if (session('status'))
                <div class="alert-box success">{{ session('status') }}</div>
            @endif

            @if (session('error'))
                <div class="alert-box">{{ session('error') }}</div>
            @endif

            @if ($currentStep === 'email')
                <h2 class="card-heading">Forgot Password</h2>
                <p class="card-copy">Enter the email address associated with your account. We will send you a verification code.</p>

                <form method="POST" action="{{ route('password.email') }}" id="emailStepForm" novalidate>
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

                    <button type="submit" class="btn-login" id="emailSubmitBtn">Send Verification Code</button>
                </form>
            @elseif ($currentStep === 'verify')
                <h2 class="card-heading">Verify Your Email</h2>
                <p class="card-copy">
                    We sent a 6-digit verification code to:<br>
                    <span class="masked-email">{{ $maskedEmail ?? '' }}</span>
                </p>

                <form method="POST" action="{{ route('password.verify') }}" id="verifyStepForm" novalidate>
                    @csrf
                    <div class="form-group">
                        <label class="form-label" for="otp-hidden">Enter Verification Code</label>
                        <input type="text" id="otp-hidden" name="code" class="otp-hidden" inputmode="numeric" autocomplete="one-time-code" maxlength="6">
                        <div class="otp-group" id="otpGroup">
                            @for ($i = 0; $i < 6; $i++)
                                <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]*" aria-label="Digit {{ $i + 1 }}">
                            @endfor
                        </div>
                        <div class="error-text" @if (! $errors->has('code')) hidden @endif>{{ $errors->first('code') }}</div>
                    </div>

                    <button type="submit" class="btn-login" id="verifySubmitBtn">Verify Code</button>
                </form>

                <div class="helper-actions">
                    <form method="POST" action="{{ route('password.resend') }}" id="resendForm">
                        @csrf
                        <button type="submit" class="text-link" id="resendBtn" @if(($resendRetryAfter ?? 0) > 0) disabled @endif>
                            @if(($resendRetryAfter ?? 0) > 0)
                                Resend code in <span id="resendTimer">{{ str_pad((string) floor(($resendRetryAfter ?? 0) / 60), 2, '0', STR_PAD_LEFT) }}:{{ str_pad((string) (($resendRetryAfter ?? 0) % 60), 2, '0', STR_PAD_LEFT) }}</span>
                            @else
                                Resend Code
                            @endif
                        </button>
                    </form>

                    <form method="POST" action="{{ route('password.change-email') }}">
                        @csrf
                        <button type="submit" class="text-link">Change Email</button>
                    </form>
                </div>
            @else
                <h2 class="card-heading">Create New Password</h2>
                <p class="card-copy">Your identity has been verified. Create a new password for your account.</p>

                <form method="POST" action="{{ route('password.update') }}" id="passwordStepForm" novalidate>
                    @csrf
                    <div class="form-group">
                        <label class="form-label" for="password">New Password</label>
                        <div class="password-toggle-wrap">
                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-control"
                                placeholder="Enter new password"
                                minlength="8"
                                autocomplete="new-password"
                            >
                            <button type="button" class="password-toggle" data-target="password">Show</button>
                        </div>
                        <div class="error-text" @if (! $errors->has('password')) hidden @endif>{{ $errors->first('password') }}</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">Confirm New Password</label>
                        <div class="password-toggle-wrap">
                            <input
                                type="password"
                                name="password_confirmation"
                                id="password_confirmation"
                                class="form-control"
                                placeholder="Confirm new password"
                                minlength="8"
                                autocomplete="new-password"
                            >
                            <button type="button" class="password-toggle" data-target="password_confirmation">Show</button>
                        </div>
                    </div>

                    <button type="submit" class="btn-login" id="passwordSubmitBtn">Reset Password</button>
                </form>
            @endif

            <a href="{{ route('login') }}" class="back-link">Back to Sign In</a>
            <p class="footer-text">National University - Secure Visitor Access</p>
        </div>
    </div>

    <script nonce="{{ $cspNonce }}">
        (function () {
            const emailForm = document.getElementById('emailStepForm');
            if (emailForm) {
                emailForm.addEventListener('submit', function (event) {
                    const emailInput = document.getElementById('email');
                    const errorBox = emailInput?.parentElement?.querySelector('.error-text');
                    const email = (emailInput?.value || '').trim();
                    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                    if (!email) {
                        event.preventDefault();
                        if (errorBox) {
                            errorBox.textContent = 'Email address is required.';
                            errorBox.hidden = false;
                        }
                        return;
                    }

                    if (!emailPattern.test(email)) {
                        event.preventDefault();
                        if (errorBox) {
                            errorBox.textContent = 'Enter a valid email address.';
                            errorBox.hidden = false;
                        }
                        return;
                    }

                    const btn = document.getElementById('emailSubmitBtn');
                    if (btn) {
                        btn.disabled = true;
                        btn.textContent = 'Sending Code...';
                    }
                });
            }

            const hiddenOtp = document.getElementById('otp-hidden');
            const otpInputs = Array.from(document.querySelectorAll('.otp-input'));
            const verifyForm = document.getElementById('verifyStepForm');

            if (hiddenOtp && otpInputs.length === 6) {
                const syncHidden = () => {
                    hiddenOtp.value = otpInputs.map((input) => input.value.replace(/\D/g, '')).join('').slice(0, 6);
                };

                otpInputs.forEach((input, index) => {
                    input.addEventListener('input', () => {
                        input.value = input.value.replace(/\D/g, '').slice(-1);
                        syncHidden();
                        if (input.value && index < otpInputs.length - 1) {
                            otpInputs[index + 1].focus();
                        }
                    });

                    input.addEventListener('keydown', (event) => {
                        if (event.key === 'Backspace' && !input.value && index > 0) {
                            otpInputs[index - 1].focus();
                        }
                    });

                    input.addEventListener('paste', (event) => {
                        event.preventDefault();
                        const pasted = (event.clipboardData?.getData('text') || '').replace(/\D/g, '').slice(0, 6);
                        pasted.split('').forEach((digit, digitIndex) => {
                            if (otpInputs[digitIndex]) {
                                otpInputs[digitIndex].value = digit;
                            }
                        });
                        syncHidden();
                        const focusIndex = Math.min(pasted.length, otpInputs.length - 1);
                        otpInputs[focusIndex]?.focus();
                    });
                });

                verifyForm?.addEventListener('submit', function (event) {
                    syncHidden();
                    const code = hiddenOtp.value;
                    const errorBox = verifyForm.querySelector('.error-text');

                    if (code.length !== 6) {
                        event.preventDefault();
                        if (errorBox) {
                            errorBox.textContent = 'Enter the 6-digit verification code.';
                            errorBox.hidden = false;
                        }
                        return;
                    }

                    const btn = document.getElementById('verifySubmitBtn');
                    if (btn) {
                        btn.disabled = true;
                        btn.textContent = 'Verifying...';
                    }
                });

                otpInputs[0]?.focus();
            }

            const resendBtn = document.getElementById('resendBtn');
            const resendTimer = document.getElementById('resendTimer');
            if (resendBtn && resendTimer) {
                let remaining = {{ (int) ($resendRetryAfter ?? 0) }};
                const resendForm = document.getElementById('resendForm');

                const tick = () => {
                    if (remaining <= 0) {
                        resendBtn.disabled = false;
                        resendBtn.innerHTML = 'Resend Code';
                        return;
                    }

                    const minutes = String(Math.floor(remaining / 60)).padStart(2, '0');
                    const seconds = String(remaining % 60).padStart(2, '0');
                    resendTimer.textContent = `${minutes}:${seconds}`;
                    remaining -= 1;
                    window.setTimeout(tick, 1000);
                };

                tick();

                resendForm?.addEventListener('submit', function () {
                    resendBtn.disabled = true;
                    resendBtn.textContent = 'Sending Code...';
                });
            }

            document.querySelectorAll('.password-toggle').forEach((button) => {
                button.addEventListener('click', () => {
                    const targetId = button.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    if (!input) return;
                    const showing = input.type === 'text';
                    input.type = showing ? 'password' : 'text';
                    button.textContent = showing ? 'Show' : 'Hide';
                });
            });

            const passwordForm = document.getElementById('passwordStepForm');
            passwordForm?.addEventListener('submit', function () {
                const btn = document.getElementById('passwordSubmitBtn');
                if (btn) {
                    btn.disabled = true;
                    btn.textContent = 'Resetting Password...';
                }
            });
        })();
    </script>
</body>
</html>
