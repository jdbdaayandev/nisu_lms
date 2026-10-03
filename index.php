<?php
session_start();

// Redirect to dashboard if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: /student/dashboard");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NISU Library System - Login</title>
    <!-- FontAwesome for the input icons -->
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            display: flex;
            background-color: #f8f9fa;
        }

        /* --- Left Side: Full Page System Info with Image --- */
        .left-half {
            flex: 1.2;
            position: relative; /* Required to anchor the mask */
            background-image: url('assets/nisu.png');
            background-size: cover;
            background-position: center;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 10%;
            z-index: 1; /* Keeps content above the mask */
        }

        /* --- The Mask Overlay --- */
        .left-half::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            /* Solid or gradient color mask. Adjust the 0.85 opacity as needed */
            background: linear-gradient(135deg, rgba(20, 45, 100, 0.9) 0%, rgba(60, 100, 200, 0.8) 100%);
            z-index: -1; /* Puts the mask behind the text but over the image */
        }

        .info-logo {
            width: 90px;
            margin-bottom: 25px;
            background: white;
            border-radius: 50%;
            padding: 5px;
        }

        .info-title {
            font-size: 32px;
            font-weight: 800;
            margin: 0 0 10px 0;
            line-height: 1.2;
        }

        .info-subtitle {
            font-size: 20px;
            font-weight: 400;
            color: #e0e6ff;
            margin: 0 0 30px 0;
        }

        .info-description {
            font-size: 15px;
            line-height: 1.7;
            margin-bottom: 30px;
            max-width: 450px;
            color: #eef2ff;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .feature-list li {
            font-size: 15px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .feature-list i {
            color: #a3c2f0;
            margin-right: 12px;
            font-size: 18px;
            width: 20px;
        }

        .right-half {
            flex: 1;
            background-color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }

        .login-box {
            width: 100%;
            max-width: 400px;
            padding: 40px;
            background: #ffffff;
            border: 1px solid #e0e4e8;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .portal-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .portal-header h2 {
            font-size: 22px;
            color: #1a3673;
            margin: 0;
        }

        .portal-header p {
            color: #666;
            font-size: 13px;
            margin: 5px 0 0 0;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            margin-bottom: 6px;
            color: #444;
            font-weight: 600;
        }

        .input-icon-wrapper {
            position: relative;
        }

        .input-icon-wrapper i {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
        }

        .input-icon-wrapper i.left-icon { left: 14px; }
        .input-icon-wrapper i.right-icon { right: 14px; cursor: pointer; }

        .form-control {
            width: 100%;
            padding: 12px 12px 12px 40px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            background: #f9fbfd;
            outline: none;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #5b7de8;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(91, 125, 232, 0.1);
        }

        .btn-login {
            background-color: #1a3673;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 14px;
            width: 100%;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.3s;
        }

        .btn-login:hover { 
            background-color: #5b7de8; 
        }

        .footer-links {
            display: flex;
            justify-content: center;
            margin-top: 25px;
            font-size: 13px;
        }

        .footer-links a { color: #5b7de8; text-decoration: none; }
        .footer-links a:hover { text-decoration: underline; }
        
        .error-msg {
            color: #d9534f;
            font-size: 13px;
            margin-bottom: 20px;
            background: #fdf7f7;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ebccd1;
            display: <?php echo isset($_GET['error']) ? 'block' : 'none'; ?>;
            text-align: center;
        }

        /* --- Responsive Design (Mobile) --- */
        @media (max-width: 850px) {
            body {
                flex-direction: column;
                height: auto;
                min-height: 100vh;
            }
            .left-half {
                flex: none;
                padding: 40px 20px;
                text-align: center;
            }
            .info-logo {
                margin: 0 auto 20px auto;
            }
            .info-description {
                margin: 0 auto 30px auto;
            }
            .feature-list li {
                justify-content: center;
            }
            .right-half {
                flex: 1;
                padding: 30px 20px;
            }
            .login-box {
                border: none;
                box-shadow: none;
                padding: 20px 0;
            }
        }
    </style>
</head>
<body>

    <div class="left-half">
        <img src="assets/images/nisu-logo.png" alt="NISU Logo" class="info-logo">
        <h1 class="info-title">Northern Iloilo State University</h1>
        <h2 class="info-subtitle">Library Management System</h2>
        <p class="info-description">
            Welcome to the digital portal for the NISU Lemery Campus library. Access our catalog, track your borrowed materials, and explore academic resources.
        </p>
        <ul class="feature-list">
            <li><i class="fa-solid fa-magnifying-glass"></i> Browse physical and digital assets</li>
            <li><i class="fa-solid fa-book-open-reader"></i> Check real-time shelf availability</li>
            <li><i class="fa-solid fa-clock-rotate-left"></i> Track history and due dates</li>
        </ul>
    </div>

    <div class="right-half">
        <div class="login-box">
            <div class="portal-header">
                <h2>User Login</h2>
                <p>Please enter your credentials to continue</p>
            </div>

            <div class="error-msg">Invalid Student ID or Password.</div>

            <form action="/actions/auth_login" method="POST">
                
                <div class="form-group">
                    <label for="student_id">Username</label>
                    <div class="input-icon-wrapper">
                        <i class="fas fa-user left-icon"></i>
                        <input type="text" id="student_id" name="student_id" class="form-control" placeholder="Enter Username" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-icon-wrapper">
                        <i class="fas fa-lock left-icon"></i>
                        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••••••" required>
                        <i class="fas fa-eye-slash right-icon" id="togglePassword"></i>
                    </div>
                </div>

                <button type="submit" class="btn-login">Sign In</button>
            </form>

            <div class="footer-links">
                <a href="#">Forgot Password?</a>
            </div>
        </div>
    </div>

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>

</body>
</html>