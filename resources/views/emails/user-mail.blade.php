<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NU-Secure Account Setup</title>
</head>
<body style="margin:0;padding:24px;background:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="max-width:620px;margin:0 auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
        <tr>
            <td style="padding:20px 22px;background:#39459a;color:#ffffff;">
                <h2 style="margin:0;font-size:20px;">NU-Secure Account Setup</h2>
            </td>
        </tr>
        <tr>
            <td style="padding:20px 22px;line-height:1.6;">
                <p style="margin:0 0 12px;">Hello {{ $fullName ?: 'User' }},</p>
                <p style="margin:0 0 12px;">Your account has been created in the NU-Secure system.</p>

                <p style="margin:0 0 8px;"><strong>Email:</strong> {{ $email }}</p>
                <p style="margin:0 0 16px;"><strong>Temporary Password:</strong> {{ $temporaryPassword }}</p>

                <p style="margin:0 0 14px;">For security, please set your own password immediately using this link:</p>

                <p style="margin:0 0 18px;">
                    <a href="{{ $setupUrl }}" style="display:inline-block;background:#4b5cd1;color:#ffffff;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600;">Set / Reset Password</a>
                </p>

                <p style="margin:0 0 10px;color:#475569;font-size:13px;">This link expires in {{ config('auth.passwords.users.expire', 60) }} minutes.</p>
                <p style="margin:0;color:#64748b;font-size:12px;word-break:break-all;">If button does not work, copy this URL:<br>{{ $setupUrl }}</p>
            </td>
        </tr>
    </table>
</body>
</html>
