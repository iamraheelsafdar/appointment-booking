<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Cancelled</title>
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

        .contact-info {
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
        Appointment Cancelled
    </div>
    <div class="email-body">
        <p><b>Dear {{ $customerName }},</b></p>
        
        <p>We regret to inform you that your appointment has been cancelled.</p>
        
        <div class="booking-details">
            <h3>📋 Cancelled Appointment Details:</h3>
            <pre style="white-space: pre-wrap; font-family: Arial, sans-serif;">{{ $bookingDetails }}</pre>
        </div>
        
        <p><b>What happens next?</b></p>
        <ul>
            <li>If you made a payment, you will receive a full refund within 3-5 business days</li>
            <li>You can book a new appointment at any time</li>
            <li>If you have any questions, please don't hesitate to contact us</li>
        </ul>
        
        <div class="contact-info">
            <h4>📞 Need Help?</h4>
            <p>If you have any questions or concerns about this cancellation, please contact us:</p>
            <p><b>Email:</b> support@homecourtadvantage.com<br>
            <b>Phone:</b> (555) 123-4567</p>
        </div>
        
        <p>We apologize for any inconvenience and look forward to serving you in the future.</p>
        
        <p><b>Best regards,</b></p>
        <p><b>The Home Court Advantage Team</b></p>
    </div>
    <div class="email-footer">
        <p>This is an automated message. Please do not reply to this email.</p>
        <p>&copy; {{ date('Y') }} Home Court Advantage. All rights reserved.</p>
    </div>
</div>
</body>
</html>
