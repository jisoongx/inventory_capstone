<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Password Reset - ShopLytix</title>
</head>

<body style="font-family: Arial, sans-serif; background: #f9f9f9; padding: 40px;">
    <div style="max-width: 600px; margin: auto; background: white; border-radius: 10px; padding: 30px; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <img src="{{ asset('assets/logo.png') }}" alt="ShopLytix Logo" style="width: 80px; margin-bottom: 15px;">
        <h2 style="color: #dc2626;">Reset Your Password</h2>
        <p style="color: #555; font-size: 15px;">
            Hello {{ $owner->owner_name ?? 'User' }},<br><br>
            We received a request to reset your password for your ShopLytix account.
        </p>
        <p>
            <a href="{{ $resetUrl }}" style="background-color: #dc2626; color: white; padding: 12px 20px; text-decoration: none; border-radius: 6px; display: inline-block; margin-top: 15px;">
                Reset Password
            </a>
        </p>
        <p style="color: #777; font-size: 13px; margin-top: 20px;">
            This link will expire in 1 hour.<br>
            If you didn’t request a password reset, you can safely ignore this email.
        </p>
    </div>
</body>

</html>