<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NU-Secure Login</title>
    <style>
        :root {
            --page-bg: #3a4797;
            --card-bg: #ececec;
            --form-bg: #e5e5e5;
            --brand-gold: #f8d33c;
            --brand-white: #ffffff;
            --text-dark: #323232;
            --muted-border: #d4d4d4;
            --input-border: #d0d0d0;
            --btn-bg: #6f6f6f;
            --link-blue: #4e59ad;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: var(--page-bg);
            font-family: Arial, Helvetica, sans-serif;
            color: var(--text-dark);
        }

        .page-wrap {
            width: 100%;
            max-width: 410px;
            padding: 0 0 0;
            text-align: center;
        }

        .brand {
            margin-bottom: 18px;
            line-height: 1;
        }

        .brand h1 {
            margin: 0;
            font-size: 50px;
            font-weight: 700;
            letter-spacing: 0.2px;
        }

        .brand .nu {
            color: var(--brand-gold);
        }

        .brand .secure {
            color: var(--brand-white);
        }

        .brand p {
            margin: 6px 0 0;
            font-size: 19px;
            font-weight: 400;
            color: var(--brand-white);
            letter-spacing: 0.2px;
            white-space: nowrap;
        }

        .brand p .highlight {
            color: var(--brand-gold);
        }

        .panel {
            width: 100%;
            border-radius: 15px;
            background: var(--card-bg);
            padding: 24px 0 36px;
        }

        .logo {
            display: block;
            width: 100px;
            height: auto;
            margin: 0 auto 16px;
        }

        .form-box {
            width: calc(100% - 48px);
            margin: 0 auto;
            background: var(--form-bg);
            border: 1px solid var(--muted-border);
            border-radius: 6px;
            padding: 19px 16px 23px;
            text-align: left;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-size: 17px;
            margin-bottom: 10px;
            color: #444;
        }

        .form-group input {
            width: 100%;
            height: 29px;
            border: 1px solid var(--input-border);
            border-radius: 8px;
            background: #ececec;
            padding: 4px 11px;
            font-size: 15px;
            outline: none;
        }

        .form-group input:focus {
            border-color: #b6b6b6;
        }

        .btn-submit {
            width: 100%;
            height: 34px;
            border: 0;
            border-radius: 5px;
            background: var(--btn-bg);
            color: #fff;
            font-size: 18px;
            cursor: pointer;
            margin-top: 4px;
        }

        .forgot {
            display: inline-block;
            margin-top: 13px;
            color: var(--link-blue);
            font-size: 17px;
            text-decoration: underline;
            text-underline-offset: 3px;
        }

        .alert {
            margin-bottom: 14px;
            padding: 8px 10px;
            border-radius: 6px;
            border: 1px solid #d7a8a8;
            background: #fbe4e4;
            color: #8a3030;
            font-size: 13px;
        }

        @media (max-width: 560px) {
            .page-wrap {
                max-width: 376px;
                padding-top: 34px;
            }

            .brand h1 {
                font-size: 46px;
            }

            .brand p {
                font-size: 17px;
            }

            .form-box {
                width: calc(100% - 48px);
                padding: 17px 16px 21px;
            }

            .form-group label {
                font-size: 16px;
            }

            .form-group input,
            .btn-submit {
                height: 31px;
                font-size: 16px;
            }

            .forgot {
                font-size: 17px;
            }
        }
    </style>
</head>
<body>
    <div class="page-wrap">
        <div class="brand">
            <h1><span class="nu">NU-</span><span class="secure">SECURE</span></h1>
            <p><span class="highlight">S</span>mart <span class="highlight">V</span>isitor <span class="highlight">M</span>onitoring <span class="highlight">S</span>ystem</p>
        </div>

        <section class="panel">
            <img class="logo" src="{{ asset('picture/nu-logo.png') }}" alt="NU-Secure Logo">

            <form class="form-box" method="POST" action="{{ route('login.submit') }}">
                @csrf
                @if ($errors->any())
                    <div class="alert">{{ $errors->first() }}</div>
                @endif
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" autocomplete="current-password" required>
                </div>

                <button class="btn-submit" type="submit">Sign In</button>
                <a href="#" class="forgot">Forgot password?</a>
            </form>
        </section>
    </div>
</body>
</html>
