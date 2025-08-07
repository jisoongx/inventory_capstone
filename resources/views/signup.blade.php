<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ShopLytix Sign Up</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            background: #fff;
        }

        .container {
            max-width: 800px;
            width: 100%;
        }

        .header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .header img {
            width: 40px;
            margin-right: 8px;
        }

        .header h1 {
            color: #DC2626;
            font-weight: bold;
            font-size: 20px;
        }

        form {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
            margin-bottom: 25px;
        }

        .input-group {
            position: relative;
            flex: 1 1 30%;
            margin-bottom: 20px;
        }

        ::placeholder {
            color: #333;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 14px 35px 14px 12px;
            border: 1px solid #000;
            border-radius: 20px;
            font-size: 12px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .eye-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            display: none;
            font-size: 14px;
            color: #888;
        }

        .checkbox-group {
            flex: 1 1 45%;
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 20px;
            font-size: 12px;
        }

        .checkbox-group label {
            display: flex;
            align-items: flex-start;
            gap: 6px;
        }

        .checkbox-group input[type="checkbox"] {
            margin-top: 2px;
        }

        .submit-btn {
            flex: 1 1 100%;
            text-align: center;
            margin-top: 10px;
        }

        button {
            background-color: #000;
            color: #fff;
            border: none;
            padding: 12px 25px;
            border-radius: 20px;
            font-size: 14px;
            cursor: pointer;
        }

        a {
            color: #3333cc;
            text-decoration: none;
            font-size: 12px;
        }

        a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {

            .input-group,
            .input-group.half {
                flex: 1 1 100%;
            }
        }

        /* Error Modal Style */
        .error-modal {
            background-color: #f5c6cb;
            color: #842029;
            padding: 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: relative;
            animation: fadeIn 0.3s ease-in-out;
        }

        .error-modal ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .error-modal li {
            margin-bottom: 6px;
            font-size: 14px;
        }

        .error-btn {
            background-color: #c53030;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12px;
            margin-top: 10px;
            cursor: pointer;
        }

        .error-btn:hover {
            background-color: #a91e1e;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    @if (session('success') || $errors->any())
    <div id="overlay-modal" style="
    position: fixed;
    top: 0; left: 0;
    width: 100vw; height: 100vh;
    background: rgba(0, 0, 0, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;">

        <div style="
        background: white;
        border-radius: 12px;
        padding: 20px 30px;
        max-width: 400px;
        width: 90%;
        text-align: center;
        box-shadow: 0 8px 20px rgba(0,0,0,0.25);
        animation: fadeIn 0.3s ease;">

            @if (session('success'))
            <p style="
                background-color: #d4edda;
                color: #155724;
                padding: 12px;
                border-radius: 8px;
                font-size: 14px;
                margin-bottom: 20px;">
                {{ session('success') }}
            </p>
            <a href="{{ route('login') }}" style="
                background-color: #28a745;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 20px;
                font-size: 14px;
                cursor: pointer;
                text-decoration: none;">
                Back to Login
            </a>

            @endif

            @if ($errors->any())
            <ul style="
                background-color: #f8d7da;
                color: #721c24;
                padding: 12px;
                border-radius: 8px;
                font-size: 14px;
                margin-bottom: 20px;
                list-style-type: none;
                padding-left: 0;">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button onclick="document.getElementById('overlay-modal').style.display='none'" style="
                background-color: #c53030;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 20px;
                font-size: 14px;
                cursor: pointer;">
                OK
            </button>
            @endif
        </div>
    </div>
    @endif

    <div class="container">
        <div class="header">
            <img src="{{ asset('assets/logo.png') }}" alt="Shoplytix Logo" class="w-10 h-10 object-contain" />
            <h1>SHOPLYTIX</h1>
        </div>



        <form method="POST" action="{{ route('signup.submit') }}">
            @csrf

            <div class="input-group"><input type="text" name="firstname" placeholder="First Name" required /></div>
            <div class="input-group"><input type="text" name="middlename" placeholder="Middle Name" /></div>
            <div class="input-group"><input type="text" name="lastname" placeholder="Last Name" required /></div>
            <div class="input-group"><input type="email" name="email" placeholder="Email Address" required /></div>

            <div class="input-group">
                <input type="password" name="password" id="password" placeholder="Password"
                    oninput="handlePasswordInput('password', 'togglePasswordIcon1')" required />
                <i class="fas fa-eye-slash eye-icon" id="togglePasswordIcon1"
                    onclick="togglePassword('password', 'togglePasswordIcon1')"></i>
            </div>

            <div class="input-group">
                <input type="password" name="password_confirmation" id="confirm" placeholder="Confirm Password"
                    oninput="handlePasswordInput('confirm', 'togglePasswordIcon2')" required />
                <i class="fas fa-eye-slash eye-icon" id="togglePasswordIcon2"
                    onclick="togglePassword('confirm', 'togglePasswordIcon2')"></i>
            </div>

            <div class="input-group"><input type="text" name="store_name" placeholder="Store Name" required /></div>
            <div class="input-group" style="flex: 1 1 64%;"><input type="text" name="store_address" placeholder="Store Address" required /></div>
            <div class="input-group"><input type="text" name="contact" placeholder="Contact Number" required /></div>

            <div class="checkbox-group">
                <label>
                    <input type="checkbox" required /> I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy.</a>
                </label>
                <label>
                    <input type="checkbox" name="marketing_opt_in" /> Yes, I would like to receive marketing communication.
                </label>
            </div>

            <div class="submit-btn">
                <button type="submit">Sign Up</button>
            </div>
        </form>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            const isHidden = input.type === "password";

            input.type = isHidden ? "text" : "password";
            icon.classList.toggle("fa-eye", isHidden);
            icon.classList.toggle("fa-eye-slash", !isHidden);
        }

        function handlePasswordInput(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            if (input.value.trim().length > 0) {
                icon.style.display = "block";
            } else {
                icon.style.display = "none";
                input.type = "password";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            }
        }
    </script>
</body>

</html>