<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NU-Secure Password Reset</title>
</head>
<body style="margin:0;padding:24px;background:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="max-width:620px;margin:0 auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
        <tr>
            <td style="padding:20px 22px;background:#1f348f;color:#ffffff;">
                <h2 style="margin:0;font-size:20px;">NU-Secure Password Reset</h2>
            </td>
        </tr>
        <tr>
            <td style="padding:20px 22px;line-height:1.6;">
                <p style="margin:0 0 12px;">Hello {{ $fullName ?: 'User' }},</p>
                <p style="margin:0 0 12px;">We received a request to reset the password for your NU-Secure account.</p>
                <p style="margin:0 0 18px;">
                    <a href="{{ $resetUrl }}" style="display:inline-block;background:#1f348f;color:#ffffff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:700;">Reset Password</a>
                </p>
                <p style="margin:0 0 10px;color:#475569;font-size:13px;">This password reset link will expire in {{ $expiresInMinutes }} minutes.</p>
                <p style="margin:0 0 12px;color:#475569;font-size:13px;">If you did not request a password reset, you can safely ignore this email.</p>
                <p style="margin:0;color:#64748b;font-size:12px;word-break:break-all;">If the button does not work, copy this URL:<br>{{ $resetUrl }}</p>
            </td>
        </tr>
    </table>
</body>
</html>
