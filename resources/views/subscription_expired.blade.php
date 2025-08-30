<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Expired</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f4f8;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
    </style>
</head>

<body>
    <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md text-center border border-gray-200">
        <div class="mb-6">
            <svg class="mx-auto h-16 w-16 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.054 0 1.918-.816 1.995-1.85l.007-.15V6c0-1.054-.816-1.918-1.85-1.995L18.918 4H5.082c-1.054 0-1.918.816-1.995 1.85L3.08 6v11.999c0 1.054.816 1.918 1.85 1.995l.15.006z">
                </path>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 mb-3">Subscription Expired</h2>
        <p class="text-gray-700 mb-6">
            Your account has been deactivated because your subscription has expired.
            Please subscribe again to continue using your account.
        </p>
        <a href="{{ route('subscription.selection') }}"
            class="inline-block bg-pink-500 hover:bg-pink-600 text-white font-semibold py-2 px-6 rounded-lg shadow-md transition duration-200 ease-in-out transform hover:scale-105">
            Subscribe Now
        </a>
    </div>
</body>

</html>