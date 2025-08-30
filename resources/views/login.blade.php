<!DOCTYPE html>
<html lang="en">
<!-- putikkkkk ka -->
<head>
    <meta charset="UTF-8" />
    <title>ShopLytix Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        
        * {
            box-sizing: border-box;
        }

        body {
            background-color: white;
            font-family: 'Inter', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 40px;
        }

        .login-container {
            width: 100%;
            max-width: 340px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        h1 {
            color: #DC2626;
            font-size: 26px;
            margin: 0;
            font-weight: bold;
        }

        form {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 20px;
            align-items: center;
        }

        .input-group {
            position: relative;
            width: 100%;
            max-width: 280px;
        }

        .input-group .fa-user,
        .input-group .fa-lock {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: black;
        }

        .input-group .toggle-password {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
            cursor: pointer;
            display: none;
        }

        .input-group input {
            width: 100%;
            padding: 16px 40px 16px 50px;
            font-size: 12px;
            border: 1px solid #000;
            border-radius: 20px;
            color: #000;
            background-color: #fff;
            font-weight: 500;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        ::placeholder {
            color: #333;
        }

        .login-btn {
            padding: 16px 40px;
            background-color: black;
            color: white;
            border: none;
            font-size: 12px;
            cursor: pointer;
            border-radius: 999px;
            transition: background 0.3s;
            width: 100%;
            max-width: 160px;
        }

        .login-btn:hover {
            background-color: #333;
        }

        .signup-text {
            font-size: 12px;
            color: #000;
        }

        .signup-text a {
            color: #DC2626;
            text-decoration: none;
            font-weight: bold;
        }

        @media screen and (max-width: 480px) {
            .login-container {
                max-width: 90%;
            }

            h1 {
                font-size: 24px;
            }

            .input-group {
                max-width: 100%;
            }

            .login-btn {
                max-width: 100%;
            }
        }

        /* MODAL STYLES */
        #messageModal {
            position: fixed;
            inset: 0;
            z-index: 1000;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            /* Ensure it uses flexbox for centering */
            align-items: center;
            justify-content: center;
        }

        .modal-box {
            background-color: white;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            max-width: 300px;
            width: 90%;
            text-align: center;
        }

        .modal-box p {
            color: #333;
            font-size: 14px;
            margin-bottom: 15px;
        }


        .modal-box button {
            background-color: #f44336;
            /* Default to error red */
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 999px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }

        .modal-box button:hover {
            background-color: #d32f2f;
            /* Darker red on hover */
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div style="margin-bottom: -20px; margin-left: -10px;">
            <img src="{{ asset('assets/logo.png') }}" style="width: 60px; height: auto;" alt="ShopLytix Logo">
        </div>
        <h1 style="margin-top: 0;">SHOPLYTIX</h1>

        {{-- NEW MODAL LOGIC --}}
        @php
        $modalMessage = '';
        $modalType = 'error'; // Default to error type
        $displayModal = false;

        // Priority: Success message first
        if (session('success')) {
        $displayModal = true;
        $modalType = 'success';
        $modalMessage = session('success');
        }
        // Then specific login error (if you use it)
        elseif (session('login_error')) {
        $displayModal = true;
        $modalType = 'error';
        $modalMessage = session('login_error');
        }
        // Then generic session error (if you use it)
        elseif (session('error')) {
        $displayModal = true;
        $modalType = 'error';
        $modalMessage = session('error');
        }
        // LASTLY, check for validation errors from the $errors bag
        elseif ($errors->any()) {
        $displayModal = true;
        $modalType = 'error';
        // You can choose to display the first error or all errors
        // For simplicity, let's display the first error:
        $modalMessage = $errors->first();

        // OR, to display ALL validation errors as a list:
        // $modalMessage = '<ul>';
            // foreach ($errors->all() as $error) {
            // $modalMessage .= '<li>' . e($error) . '</li>'; // e() is for HTML escaping
            // }
            // $modalMessage .= '</ul>';
        // If you choose this, remember to use {!! $modalMessage !!} below for raw HTML
        }
        @endphp

        @if($displayModal)
        <div id="messageModal">
            <div class="modal-box">
                {{-- Use the calculated message and apply dynamic class for text color --}}
                <p class="{{ $modalType }}-text">{{ $modalMessage }}</p>

                <button
                    onclick="closeModal()"
                    style="background-color: {{ $modalType === 'success' ? '#4CAF50' : '#f44336' }};">
                    OK
                </button>
            </div>
        </div>
        <script>
            // This small script is fine to be inline here as it's directly tied to the modal's existence.
            function closeModal() {
                document.getElementById('messageModal').style.display = 'none';
            }
        </script>
        @endif
        {{-- END NEW MODAL LOGIC --}}


        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="email" name="email" placeholder="User" required />
            </div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Password" id="password" oninput="handlePasswordInput()" required />
                <i class="fas fa-eye-slash toggle-password" id="togglePasswordIcon" onclick="togglePassword()"></i>
            </div>
            <button type="submit" class="login-btn">Login</button>
        </form>

        <div class="signup-text">
            Don’t have an account?
            <a href="{{ route('signup') }}">Sign up</a>
        </div>
    </div>

    <script>
        // --- Password Toggle Logic --- (Keep this at the bottom as it's separate from the modal)
        function togglePassword() {
            const passwordInput = document.getElementById("password");
            const toggleIcon = document.getElementById("togglePasswordIcon");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                toggleIcon.classList.remove("fa-eye-slash");
                toggleIcon.classList.add("fa-eye");
            } else {
                passwordInput.type = "password";
                toggleIcon.classList.remove("fa-eye");
                toggleIcon.classList.add("fa-eye-slash");
            }
        }

        function handlePasswordInput() {
            const passwordInput = document.getElementById("password");
            const toggleIcon = document.getElementById("togglePasswordIcon");

            if (passwordInput.value.length > 0) {
                toggleIcon.style.display = "block";
            } else {
                toggleIcon.style.display = "none";
                toggleIcon.classList.remove("fa-eye");
                toggleIcon.classList.add("fa-eye-slash");
                passwordInput.type = "password";
            }
        }
    </script>
</body>

</html>