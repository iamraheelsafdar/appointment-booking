<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Cancellation Notification</title>
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

        .booking-details {
            background: #f4f4f4;
            padding: 12px;
            border-radius: 6px;
            margin: 20px 0;
            border-left: 4px solid #06402b;
        }

        .info-box {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
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
        Appointment Cancellation Notification
    </div>
    <div class="email-body">
        <p><b>Dear {{ $recipientName }},</b></p>
        
        <p>This is to notify you that an appointment has been cancelled.</p>
        
        <div class="booking-details">
            <h3>📋 Cancelled Appointment Details:</h3>
            <pre style="white-space: pre-wrap; font-family: Arial, sans-serif;">{{ $bookingDetails }}</pre>
        </div>
        
        @if($recipientType === 'coach')
        <div class="info-box">
            <h4>👨‍🏫 Coach Information:</h4>
            <ul>
                <li>Your calendar has been updated to reflect this cancellation</li>
                <li>This time slot is now available for new bookings</li>
                <li>You will receive notifications for any new appointments in this time slot</li>
            </ul>
        </div>
        @else
        <div class="info-box">
            <h4>👨‍💼 Admin Information:</h4>
            <ul>
                <li>This appointment has been removed from the system</li>
                <li>If payment was made, a refund will be processed automatically</li>
                <li>You can view all cancelled appointments in the admin dashboard</li>
            </ul>
        </div>
        @endif
        
        <p><b>Next Steps:</b></p>
        <ul>
            <li>Check your calendar to ensure the time slot is properly freed up</li>
            <li>Monitor for any new bookings in this time slot</li>
            <li>Contact support if you notice any discrepancies</li>
        </ul>
        
        <p>Thank you for your attention to this matter.</p>
        
        <p><b>Best regards,</b></p>
        <p><b>The Home Court Advantage Team</b></p>
    </div>
    <div class="email-footer">
        <p>This is an automated notification. Please do not reply to this email.</p>
        <p>&copy; {{ date('Y') }} Home Court Advantage. All rights reserved.</p>
    </div>
</div>
</body>
</html>
