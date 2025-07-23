<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Up Your Password & Get Started!</title>
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
        Welcome to Home Court Advantage!
    </div>
    <div class="email-body">
        <p><b>Dear{{$name}}</b></p>

        <p>We’re excited to welcome you as a coach on <b>Home Court Advantage</b>! 🏀</p>

        <p>Your account has been successfully created by our team. To get started, you’ll need to set your password and
            log in to your coach dashboard.</p>

        <h4>🔐 Set Your Password:</h4>
        <p>
            <a href="{{ route('setPasswordView', ['email' => $email, 'token' => $token]) }}" class="reset-button" style="color: #ffffff">
                Click Here to Set Your Password
            </a>
        </p>
        <p>If the button doesn’t work, copy and paste this link into your browser:<br>
            {{ route('setPasswordView', ['email' => $email, 'token' => $token]) }}</p>

        <h4>📋 Once You're In:</h4>
        <p>As a coach, you’ll be able to:</p>
        <ul>
            <li>✅ View and manage your upcoming sessions</li>
            <li>✅ See appointment details and court assignments</li>
            <li>✅ Update your availability and contact information</li>
            <li>✅ Stay informed about new bookings and changes</li>
        </ul>

        <h4>🔑 Login to Your Dashboard:</h4>
        <p>
            <a style="color: #ffffff" href="{{ route('login') }}" class="reset-button">Coach Login</a>
        </p>

        <h4>Need Help?</h4>
        <p>If you have any questions or need support, feel free to contact us at <a
                href="mailto:support@homecourtadvantage.net">support@homecourtadvantage.net</a></p>

        <p>We’re thrilled to have you on board and can’t wait to see the value you bring to our community!</p>

        <p><b>Warm regards,</b></p>
        <p><b>The Home Court Advantage Team</b></p>
        <p>🌐 <a href="{{ env('LIVE_URL') }}" target="_blank">www.homecourtadvantage.net</a></p>
    </div>
    <div class="email-footer">
        &copy; {{ date('Y') }} Home Court Advantage. All rights reserved.
    </div>
</div>
</body>
</html>
