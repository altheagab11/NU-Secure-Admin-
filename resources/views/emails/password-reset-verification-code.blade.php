<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NU-Secure Password Reset Verification Code</title>
</head>
<body style="margin:0;padding:24px;background:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="max-width:620px;margin:0 auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
        <tr>
            <td style="padding:20px 22px;background:#1f348f;color:#ffffff;">
                <h2 style="margin:0;font-size:20px;">NU-Secure</h2>
                <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">Visitor Monitoring System</p>
            </td>
        </tr>
        <tr>
            <td style="padding:24px 22px;line-height:1.6;">
                <p style="margin:0 0 12px;">We received a request to reset your password.</p>
                <p style="margin:0 0 8px;font-weight:600;">Your verification code is:</p>
                <p style="margin:0 0 18px;font-size:32px;font-weight:800;letter-spacing:6px;color:#1f348f;">{{ $verificationCode }}</p>
                <p style="margin:0 0 12px;color:#475569;font-size:14px;">This code will expire in {{ $expiresInMinutes }} minutes.</p>
                <p style="margin:0 0 12px;color:#475569;font-size:14px;">For your security, do not share this code with anyone.</p>
                <p style="margin:0 0 12px;color:#475569;font-size:14px;">If you did not request a password reset, you may safely ignore this email.</p>
                <p style="margin:0;color:#64748b;font-size:13px;">NU-Secure<br>National University</p>
            </td>
        </tr>
    </table>
</body>
</html>
