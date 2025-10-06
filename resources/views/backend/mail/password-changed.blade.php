<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Successfully Changed</title>
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

        .email-footer {
            text-align: center;
            font-size: 12px;
            color: #888888;
            margin-top: 20px;
            padding: 20px;
            border-top: 1px solid #ddd;
        }

        .button {
            display: inline-block;
            background-color: #06402b;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            margin-top: 12px;
        }

        .button:hover {
            background-color: #072018FF;
        }
    </style>
</head>
<body>
<div class="email-container">
    <div class="email-header">
        Password Changed Successfully
    </div>
    <div class="email-body">
        <p><b>Hi {{ $name }},</b></p>

        <p>This is a confirmation that your password has been successfully changed for your <b>Home Court Advantage</b> account.</p>

        <p>If you did not make this change, please contact our support team immediately.</p>

        <p>
            <a href="{{ route('login') }}" class="button" style="color: #ffffff;">
                Login to Dashboard
            </a>
        </p>

        <p>For any assistance, reach out to us at <a href="mailto:info@homecourtadvantage.net">info@homecourtadvantage.net</a></p>

        <p><b>Thank you,</b></p>
        <p><b>The Home Court Advantage Team</b></p>
        <p>🌐 <a href="{{ env('APP_URL') }}" target="_blank">{{ env('APP_URL') }}</a></p>
    </div>
    <div class="email-footer">
        &copy; {{ date('Y') }} Home Court Advantage. All rights reserved.
    </div>
</div>
</body>
</html>
