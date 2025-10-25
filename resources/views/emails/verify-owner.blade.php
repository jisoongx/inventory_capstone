<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Verify Email</title>
</head>

<body style="font-family: Arial, sans-serif; background: #f9fafb; padding: 30px;">
    <div style="background: #fff; padding: 30px; border-radius: 8px;">
        <h2>Hello {{ $owner->firstname }},</h2>
        <p>Welcome to <strong>Shoplytix</strong>! Please verify your email address by clicking the button below:</p>
        <p>
            <a href="{{ $verifyUrl }}"
                style="background-color:#e3342f;color:#fff;padding:10px 20px;text-decoration:none;border-radius:6px;">
                Verify Email
            </a>
        </p>
        <p>If you didn’t create this account, please ignore this message.</p>
        <p>Thank you,<br>— Shoplytix Team</p>
    </div>
</body>

</html>