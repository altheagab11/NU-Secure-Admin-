<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Password - NU-Secure</title>
    <style>
        :root {
            --page-bg: #3a4797;
            --card-bg: #ececec;
            --form-bg: #e5e5e5;
            --text-dark: #323232;
            --btn-bg: #4b5cd1;
            --error-bg: #fff1f2;
            --error-text: #9f1239;
            --success-bg: #ecfdf5;
            --success-text: #065f46;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: var(--page-bg);
            font-family: Arial, Helvetica, sans-serif;
            color: var(--text-dark);
            padding: 20px;
        }

        .panel {
            width: 100%;
            max-width: 480px;
            border-radius: 14px;
            background: var(--card-bg);
            padding: 24px;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 24px;
        }

        .subtitle {
            margin: 0 0 16px;
            color: #475569;
            font-size: 14px;
        }

        .alert {
            margin-bottom: 14px;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 13px;
        }

        .alert.error {
            background: var(--error-bg);
            color: var(--error-text);
        }

        .alert.success {
            background: var(--success-bg);
            color: var(--success-text);
        }

        form {
            background: var(--form-bg);
            border-radius: 10px;
            padding: 16px;
        }

        .form-group {
            margin-bottom: 12px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            color: #334155;
        }

        input {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 14px;
            outline: none;
            background: #fff;
        }

        input:focus {
            border-color: #4b5cd1;
        }

        .btn-submit {
            width: 100%;
            border: 0;
            border-radius: 8px;
            background: var(--btn-bg);
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            padding: 10px 14px;
            cursor: pointer;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <section class="panel">
        <h1>Set Your Password</h1>
        <p class="subtitle">Create your own password to activate your NU-Secure account.</p>

        @if (session('status'))
            <div class="alert success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.setup.submit') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $email ?? '') }}" required>
            </div>

            <div class="form-group">
                <label for="password">New Password</label>
                <input id="password" type="password" name="password" required minlength="8">
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required minlength="8">
            </div>

            <button type="submit" class="btn-submit">Save Password</button>
        </form>
    </section>
</body>
</html>
