<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reset Your Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo {
            max-width: 150px;
        }
        .button {
            display: inline-block;
            background-color: #4CAF50;
            color: white !important;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 4px;
            margin-top: 20px;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Taskify Password Reset</h2>
        </div>

        <p>Hello,</p>

        <p>You are receiving this email because we received a password reset request for your account.</p>

        <div style="text-align: center;">
            <a href="{{ $url }}" class="button">Reset Password</a>
        </div>

        <p>This password reset link will expire in 60 minutes.</p>

        <p>If you did not request a password reset, no further action is required.</p>

        <p>Regards,<br>Taskify Team</p>

        <div class="footer">
            <p>If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:</p>
            <p>{{ $url }}</p>
        </div>
    </div>
</body>
</html>
