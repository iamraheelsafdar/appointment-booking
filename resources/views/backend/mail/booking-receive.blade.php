<!-- resources/views/emails/admin-booking.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Booking Received</title>
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
    </style>
</head>
<body>
<div class="email-container">
    <div class="email-header">
        New Booking Received
    </div>
    <div class="email-body">
        <p><b>Hello {{$name ?? "There"}},</b></p>

        <p>You’ve received a new booking via Home Court Advantage. The details are as follows:</p>

        <pre
            style="background: #f4f4f4; padding: 12px; border-radius: 6px; white-space: pre-wrap;">{!! $description !!}</pre>

        <p>Log in to your dashboard for full details.</p>

        <p><b>Regards,</b></p>
        <p><b>Home Court Advantage System</b></p>
    </div>
    <div class="email-footer">
        &copy; {{ date('Y') }} Home Court Advantage. All rights reserved.
    </div>
</div>
</body>
</html>
