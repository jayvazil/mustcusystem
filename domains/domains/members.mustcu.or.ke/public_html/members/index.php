<?php
// Enable error reporting


require_once '../shared/config/config.php';
require_once '../shared/lib/email_handler.php';

$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: index.php");
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM members WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        if ($user['password'] && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = 'member';
            $_SESSION['login_time'] = time();
            $_SESSION['success'] = "Member login successful! Redirecting...";
            header("Location: dashboard.php");
            exit();
        } elseif ($user['phone'] == $password) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = 'member';
            $_SESSION['login_time'] = time();
            $_SESSION['success'] = "Member login successful! Redirecting...";
            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Invalid member credentials.";
            header("Location: index.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Invalid member credentials.";
        header("Location: index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUST CU Member Login</title>
    <style>
        body {
            background: linear-gradient(135deg, #0207ba, #0207ba);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .container {
            background: rgba(255, 255, 255, 0.95);
            padding: 2.5rem;
            border-radius: 1rem;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 32rem;
            text-align: center;
            backdrop-filter: blur(10px);
        }
        img {
            display: block;
            margin: 0 auto 1.5rem;
            height: 5rem;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }
        h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #0207ba;
            margin-bottom: 1.5rem;
            letter-spacing: 0.05em;
        }
        .message {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            opacity: 0;
            transform: translateY(-10px);
            animation: slideIn 0.3s ease-out forwards, fadeOut 0.3s ease-out 4.7s forwards;
        }
        .error {
            background-color: #fee2e2;
            color: #dc2626;
            border-left: 4px solid #dc2626;
        }
        .success {
            background-color: #d1fae5;
            color: #065f46;
            border-left: 4px solid #065f46;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        .preloader {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        .spinner {
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid #0207ba;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
            text-align: left;
        }
        .input-group {
            position: relative;
            margin-bottom: 1rem;
        }
        input {
            width: 80%;
            padding: 0.75rem 2.5rem 0.75rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            background: #f9fafb;
        }
        input:focus {
            outline: none;
            border-color: #1e40af;
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
            background: #ffffff;
        }
        .toggle-password {
            position: absolute;
            right: 0.75rem;
            top: 70%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #0207ba;
            font-size: 1.0rem;
            user-select: none;
            opacity: 0.9;
            transition: opacity 0.3s ease, color 0.3s ease;
        }
        .toggle-password:hover {
            color: #1e40af;
            opacity: 1;
        }
        button {
            width: 100%;
            padding: 0.85rem;
            background: linear-gradient(90deg, #f97316, #ea580c);
            color: #ffffff;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            letter-spacing: 0.05em;
        }
        button:hover {
            background: linear-gradient(90deg, #ea580c, #d03801);
            transform: translateY(-2px);
        }
        button[name="register"] {
            background: linear-gradient(#0207ba, #0207ba, #0207ba);
            margin-top: 0.75rem;
        }
        button[name="register"]:hover {
            background: linear-gradient(#fff000, #fff000, #fff000);
        }
        button[name="reset_password"] {
            background: linear-gradient(#0207ba, #0207ba, #0207ba);
            margin-top: 0.75rem;
        }
        button[name="reset_password"]:hover {
            background: linear-gradient(#fff000, #fff000, #fff000);
        }
        .reset-form {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
            text-align: center;
        }
        .reset-form h3 {
            font-size: 1.1rem;
            color: #1e3a8a;
            margin-bottom: 1rem;
        }
        @media (max-width: 640px) {
            .container { padding: 1.75rem; margin: 0.75rem; }
            h2 { font-size: 1.4rem; }
            img { height: 4rem; }
        }
    </style>
    <script>
        function togglePassword(fieldId, toggleId) {
            const field = document.getElementById(fieldId);
            const toggle = document.getElementById(toggleId);
            
            field.style.transition = 'all 0.3s ease';
            toggle.style.transition = 'all 0.3s ease';
            
            if (field.type === 'password') {
                field.type = 'text';
                toggle.textContent = 'Hide';
                toggle.style.opacity = '1';
            } else {
                field.type = 'password';
                toggle.textContent = 'Show';
                toggle.style.opacity = '0.7';
            }
        }

        function showPreloader() {
            const preloader = document.getElementById('preloader');
            preloader.style.display = 'flex';
            preloader.style.opacity = '0';
            setTimeout(() => {
                preloader.style.opacity = '1';
                preloader.style.transition = 'opacity 0.3s ease';
            }, 10);
            setTimeout(() => {
                preloader.style.opacity = '0';
                setTimeout(() => {
                    preloader.style.display = 'none';
                }, 300);
            }, 2000);
        }

        function redirectAfterDelay(url) {
            showPreloader();
            setTimeout(() => {
                window.location.href = url;
            }, 2000);
        }
    </script>
</head>
<body>
    <div id="preloader" class="preloader">
        <div class="spinner"></div>
    </div>
    <div class="container">
        <img src="../Images/rond logo.png" alt="MUST CU Logo" class="mx-auto mb-4 h-20" onerror="this.src='https://via.placeholder.com/50';">
        <h2>MUST CU Members Portal</h2>
        <?php if (isset($_SESSION['error'])): ?>
            <p class="message error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <p class="message success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
            <script>redirectAfterDelay('index.php');</script>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="login" value="1">
            <div class="input-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" required placeholder="Enter your email">
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required placeholder="Enter your registered Phone Number as the password">
                <span class="toggle-password" id="toggle-password" onclick="togglePassword('password', 'toggle-password')">Show</span>
            </div>
            <button type="submit">Log  In</button>
            <button type="button" name="register" onclick="location.href='register.php'">Register</button>
        </form>
        <div class="reset-form">
            <h3>Forgot Password?</h3>
            <button type="button" name="reset_password" onclick="redirectAfterDelay('reset_password.php')">Reset Password</button>
        </div>
    </div>
</body>
</html>