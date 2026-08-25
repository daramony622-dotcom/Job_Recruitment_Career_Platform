<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your verification code</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f5f7; font-family: Arial, Helvetica, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f5f7; padding:40px 0;">
        <tr>
            <td align="center">
                <table width="480" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,0.08);">
                    <tr>
                        <td style="background-color:#1f2937; padding:24px 32px;">
                            <h1 style="color:#ffffff; font-size:18px; margin:0;">Job Recruitment & Career Platform</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="font-size:15px; color:#111827; margin:0 0 16px;">
                                Hi {{ $user->name }},
                            </p>

                            <p style="font-size:15px; color:#374151; margin:0 0 24px;">
                                @if($purpose === 'password_reset')
                                    You requested to reset your password. Use the code below to continue.
                                @else
                                    Thanks for registering. Use the code below to verify your email address.
                                @endif
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding:16px 0;">
                                        <span style="display:inline-block; font-size:32px; letter-spacing:8px; font-weight:bold; color:#111827; background-color:#f3f4f6; padding:16px 24px; border-radius:6px;">
                                            {{ $code }}
                                        </span>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size:13px; color:#6b7280; margin:24px 0 0;">
                                This code expires in {{ $expiresInMinutes ?? 5 }} minutes. If you didn't request this, you can safely ignore this email.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px; background-color:#f9fafb; text-align:center;">
                            <p style="font-size:12px; color:#9ca3af; margin:0;">
                                &copy; {{ date('Y') }} Job Recruitment & Career Platform
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
