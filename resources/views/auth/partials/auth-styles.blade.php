* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, Helvetica, sans-serif;
}

html {
    height: 100%;
}

body {
    width: 100%;
    max-width: 100%;
    min-height: 100%;
    min-height: 100dvh;
    overflow-x: hidden;
    background: linear-gradient(rgba(31, 52, 143, 0.72), rgba(31, 52, 143, 0.72)),
                url('{{ asset('picture/lipa.png') }}') no-repeat center center/cover;
    display: flex;
    align-items: center;
    justify-content: center;
    align-items: safe center;
    justify-content: safe center;
    padding: clamp(8px, 1.6vh, 20px) clamp(12px, 2.4vw, 20px);
    -webkit-text-size-adjust: 100%;
    text-size-adjust: 100%;
}

.login-wrapper {
    width: min(460px, 100%);
    text-align: center;
}

.brand-title {
    font-size: clamp(32px, 6vh, 54px);
    font-weight: 800;
    margin-bottom: clamp(4px, 0.8vh, 8px);
    letter-spacing: 1px;
    line-height: 1.1;
}

.brand-title .nu { color: #f7c948; }
.brand-title .secure { color: #ffffff; }

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

.logo-box { margin-bottom: clamp(10px, 2vh, 22px); }
.logo-box img { width: clamp(72px, 12vh, 110px); height: auto; }

.card-heading {
    margin: 0 0 8px;
    font-size: 24px;
    font-weight: 800;
    color: #1f348f;
}

.card-copy {
    margin: 0 0 22px;
    font-size: 14px;
    line-height: 1.5;
    color: #4b5563;
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
    margin-bottom: 18px;
    text-align: left;
    font-size: 14px;
}

.alert-box.success {
    background: #ecfdf5;
    color: #065f46;
    border-color: #a7f3d0;
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
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
}

.btn-login:hover {
    transform: translateY(-1px);
    box-shadow: 0 12px 20px rgba(31, 52, 143, 0.25);
}

.btn-login:disabled {
    opacity: 0.75;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.back-link {
    display: inline-block;
    margin-top: 16px;
    color: #1f348f;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
}

.back-link:hover { text-decoration: underline; }

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
