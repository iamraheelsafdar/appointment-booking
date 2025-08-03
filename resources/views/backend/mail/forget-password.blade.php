<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f7f7f7;
        }

        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }

        .email-header {
            background-color: #06402b;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 24px;
        }

        .email-body {
            padding: 20px;
            color: #333333;
            line-height: 1.6;
        }

        .email-body h2 {
            margin-top: 0;
        }

        .email-body p {
            margin: 10px 0;
        }

        .reset-button {
            display: inline-block;
            background-color: #06402b;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            margin-top: 12px;
        }

        .reset-button:hover {
            background-color: #072018FF;
        }

        .email-footer {
            text-align: center;
            font-size: 12px;
            color: #888888;
            margin-top: 20px;
            padding: 20px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
<div class="email-container">
    <div class="email-header">
        Password Reset Request
    </div>
    <div class="email-body">
        <p><b>Hi {{ $name }},</b></p>

        <p>We received a request to reset your password for your <b>Home Court Advantage</b> account.</p>

        <p>If you made this request, click the button below to reset your password:</p>

        <p>
            <a href="{{ route('setPasswordView', ['email' => $email, 'token' => $token]) }}" class="reset-button" style="color: #ffffff">
                Reset Password
            </a>
        </p>

        <p>If the button doesn’t work, copy and paste this link into your browser:<br>
            {{ route('setPasswordView', ['email' => $email, 'token' => $token]) }}</p>

        <p>If you didn’t request a password reset, you can safely ignore this email. Your password will remain unchanged.</p>

        <p>Need help? Contact us at <a href="mailto:support@homecourtadvantage.net">support@homecourtadvantage.net</a></p>

        <p><b>Thanks,</b></p>
        <p><b>The Home Court Advantage Team</b></p>
        <p>🌐 <a href="{{ env('APP_URL') }}" target="_blank">{{ env('APP_URL') }}</a></p>
    </div>
    <div class="email-footer">
        &copy; {{ date('Y') }} Home Court Advantage. All rights reserved.
    </div>
</div>
</body>
</html>
