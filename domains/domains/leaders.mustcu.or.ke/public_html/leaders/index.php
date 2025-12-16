<?php
require_once 'includes/db_connect.php';

session_start();

$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: index.php");
        exit();
    } else {
        $stmt = $pdo->prepare("SELECT * FROM leaders WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            if ($user['password'] && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = 'leader';
                $_SESSION['login_time'] = time();
                $success = "Leader login successful! Redirecting...";
            } elseif ($user['phone'] == $password) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = 'leader';
                $_SESSION['login_time'] = time();
                $success = "Leader login successful! Redirecting...";
            } else {
                $_SESSION['error'] = "Invalid leader credentials.";
                header("Location: index.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Invalid leader credentials.";
            header("Location: index.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUST CU Leader Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            background-color: #ffffff;
            padding: 2.5rem;
            border-radius: 0.75rem;
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 28rem;
            text-align: center;
            position: relative;
        }
        img {
            height: 5rem;
            margin-bottom: 1.5rem;
        }
        h2 {
            font-size: 1.75rem;
            font-weight: 600;
            color: #0207ba;
            margin-bottom: 1.5rem;
        }
        .error, .success {
            padding: 0.75rem;
            border-radius: 0.375rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        }
        .error.show, .success.show {
            opacity: 1;
        }
        .error {
            background-color: #fee2e2;
            color: #dc2626;
        }
        .success {
            background-color: #d1fae5;
            color: #065f46;
        }
        label {
            display: block;
            font-size: 0.9rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
            text-align: left;
        }
        .input-wrapper {
            position: relative;
            margin-bottom: 1rem;
        }
        input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 0.9rem;
            transition: border-color 0.2s;
        }
        .input-wrapper input {
            padding-right: 0.5rem;
        }
        input:focus {
            border-color: #0207ba;
            outline: none;
        }
        .toggle-password {
            position: absolute;
            right: 0.75rem;
            top: 70%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6b7280;
        }
        button {
            width: 100%;
            padding: 0.75rem;
            background-color: #0207ba;
            color: #ffffff;
            border: none;
            border-radius: 0.375rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        button:hover {
            background-color: #001f7a;
        }
        button[name="reset_password"] {
            background-color: #ff7900;
            margin-top: 1rem;
        }
        button[name="reset_password"]:hover {
            background-color: #e66900;
        }
        .preloader {
            display: none;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            align-items: center;
            justify-content: center;
        }
        .preloader.active {
            display: flex;
        }
        .spinner {
            border: 4px solid #f3f4f6;
            border-top: 4px solid #fff000;
            border-radius: 50%;
            width: 2rem;
            height: 2rem;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @media (max-width: 640px) {
            .container {
                padding: 2rem;
                margin: 1rem;
            }
            h2 {
                font-size: 1.5rem;
            }
            img {
                height: 4rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="../Images/rond logo.png" alt="MUST CU Logo" onerror="this.src='https://via.placeholder.com/50';">
        <h2>MUST CU Leader Portal</h2>
        <?php if (!empty($error)): ?>
            <p class="error show"><?php echo htmlspecialchars($error); unset($_SESSION['error']); ?></p>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <p class="success show"><?php echo htmlspecialchars($success); ?></p>
            <script>
                setTimeout(function() {
                    window.location.href = 'dashboard.php';
                }, 3000);
            </script>
        <?php endif; ?>
        <form method="POST" id="loginForm">
            <div>
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" required placeholder="Enter your email">
            </div>
            <div class="input-wrapper">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required placeholder="Enter your password">
                <span class="toggle-password"><i class="fas fa-eye"></i></span>
            </div>
            <button type="submit">Sign In</button>
            <button type="button" name="reset_password" onclick="location.href='reset_password.php'">Reset Password</button>
        </form>
        <div class="preloader" id="preloader">
            <div class="spinner"></div>
        </div>
    </div>
    <script>
        const form = document.getElementById('loginForm');
        const preloader = document.getElementById('preloader');
        
        form.addEventListener('submit', function() {
            preloader.classList.add('active');
        });

        document.querySelector('.toggle-password').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>