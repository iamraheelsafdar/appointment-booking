<!-- resources/views/emails/customer-booking.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
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
        Booking Confirmed
    </div>
    <div class="email-body">
        <p><b>Hi {{ $name }},</b></p>

        <p>Your booking has been confirmed! Below are the details:</p>

        <pre style="background: #f4f4f4; padding: 12px; border-radius: 6px; white-space: pre-wrap;">{!! $description !!}</pre>

        <p>If you have any questions, feel free to reach out to us.</p>

        <p><b>Need to cancel or edit your lessons?</b></p>
        <p>Please call us at <a href="tel:0421361946">0421361946</a> or email us at <a href="mailto:info@homecourtadvantage.net">info@homecourtadvantage.net</a> for any enquiries.</p>

        <p><b>Thank you,</b></p>
        <p><b>The Home Court Advantage Team</b></p>
    </div>
    <div class="email-footer">
        &copy; {{ date('Y') }} Home Court Advantage. All rights reserved.
    </div>
</div>
</body>
</html>
