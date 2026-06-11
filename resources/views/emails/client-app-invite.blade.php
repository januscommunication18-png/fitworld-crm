<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background-color:#f4f5f8; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f5f8; padding: 24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius: 12px; overflow:hidden;">
                    <tr>
                        <td style="padding: 32px;">
                            <h1 style="margin:0 0 8px; font-size: 20px; color:#20212B;">Welcome to the {{ $appName }} app</h1>
                            <p style="margin:0 0 20px; font-size: 14px; color:#6F717D; line-height: 1.6;">
                                Hi {{ $client->first_name }}, here is your personal Client ID for signing in to the {{ $studioName }} mobile app.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin: 0 0 20px;">
                                <tr>
                                    <td align="center" style="background-color:#f4f5f8; border-radius: 10px; padding: 18px;">
                                        <span style="font-family: 'SF Mono', Menlo, Consolas, monospace; font-size: 22px; font-weight: 700; letter-spacing: 1px; color:#20212B;">{{ $clientCode }}</span>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 6px; font-size: 14px; color:#20212B; font-weight: 600;">How to sign in</p>
                            <ol style="margin:0 0 20px; padding-left: 18px; font-size: 14px; color:#6F717D; line-height: 1.8;">
                                <li>Download the {{ $appName }} app.</li>
                                <li>Sign in with this email address ({{ $client->email }}).</li>
                                <li>Confirm with the code we email you (or your password).</li>
                            </ol>
                            <p style="margin:0 0 20px; font-size: 13px; color:#6F717D; line-height: 1.6;">
                                Your Client ID above is your member reference for check-ins and support.
                            </p>

                            <p style="margin:0; font-size: 12px; color:#9aa0ae; line-height: 1.6;">
                                Keep this ID private — it identifies your account at {{ $studioName }}.
                                If you didn't expect this email, you can ignore it.
                            </p>
                        </td>
                    </tr>
                </table>
                <p style="font-size: 11px; color:#9aa0ae; margin-top: 16px;">Sent by {{ $studioName }}</p>
            </td>
        </tr>
    </table>
</body>
</html>
