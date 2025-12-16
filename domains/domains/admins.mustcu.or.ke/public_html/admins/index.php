<?php


session_start(); // Ensure session is started
require_once 'includes/db_connect.php';

$errors = [];
$success = '';
$login_attempts = isset($_SESSION['login_attempts']) ? $_SESSION['login_attempts'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email_submit'])) {
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        if ($row = $stmt->fetch()) {
            $_SESSION['login_email'] = $email;
            error_log("Session started. Email validated and set: " . $_SESSION['login_email']);
            $success = 'Email verified. Please enter your credentials.';
        } else {
            $errors[] = 'Email not registered. Please contact support.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if ($login_attempts >= 5) {
        $errors[] = 'Too many login attempts. Please try again later.';
    } else {
        $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
        $password = trim(filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING));

        if (empty($username) || empty($password)) {
            $errors[] = 'Username and password are required.';
            $_SESSION['login_attempts'] = $login_attempts + 1;
        } else {
            $email = trim($_SESSION['login_email'] ?? '');
            error_log("Login attempt - Session email: '$email', Username: '$username', Password: '$password'");
            if (empty($email)) {
                $errors[] = 'Please submit your email first.';
            } else {
                $stmt = $pdo->prepare("SELECT id, password FROM admins WHERE username = ? AND email = ? LIMIT 1");
                $stmt->execute([$username, $email]);
                $user = $stmt->fetch();
                error_log("Database query result: " . json_encode($user));

                if ($user && $user['password'] === $password) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = 'admin';
                    $_SESSION['login_attempts'] = 0;
                    unset($_SESSION['login_email']);
                    $success = 'Login successful! Redirecting to dashboard...';
                } else {
                    error_log("Login failed - User: " . json_encode($user) . ", Provided password: '$password'");
                    $errors[] = 'Invalid username or password.';
                    $_SESSION['login_attempts'] = $login_attempts + 1;
                }
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_request'])) {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } else {
        $stmt = $pdo->prepare("
            SELECT id, 'member' AS role FROM members WHERE email = ?
            UNION SELECT id, 'leader' AS role FROM leaders WHERE email = ?
            UNION SELECT id, 'associate' AS role FROM associates WHERE email = ?
            UNION SELECT id, 'admin' AS role FROM admins WHERE email = ?
        ");
        $stmt->execute([$email, $email, $email, $email]);
        $roles = $stmt->fetchAll();

        if (count($roles) === 0) {
            $errors[] = 'Email not found.';
        } elseif (count($roles) === 1) {
            $role = $roles[0]['role'];
            $user_id = $roles[0]['id'];
            $token = bin2hex(random_bytes(32));
            $stmt = $pdo->prepare("UPDATE $role"."s SET reset_token = ?, reset_expiry = NOW() + INTERVAL 24 HOUR WHERE id = ?");
            $stmt->execute([$token, $user_id]);
            sendResetEmail($email, $token, $role);
            $success = 'Reset link sent to your email. Redirecting...';
        } else {
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_roles'] = array_column($roles, 'role');
            $success = 'Multiple roles found. Redirecting to role selection...';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - MUST CU</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #0207ba;
            --accent-orange: #0207ba;
            --light-bg: #f8f9fa;
            --white: #ffffff;
            --text-dark: #2c3e50;
            --shadow: rgba(2, 7, 186, 0.15);
        }

        body {
            background: linear-gradient(135deg, var(--primary-blue) 0%, #1e3c72 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 0;
        }

        .logo {
            text-align: center;
            padding: 2rem 0;
            background: linear-gradient(90deg, var(--primary-blue), #1b0aa2);
            width: 100%;
            position: fixed;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        .logo img {
            width: 180px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .logo img:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }

        .login-container {
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 20px 40px var(--shadow);
            padding: 3.5rem;
            width: 100%;
            max-width: 650px;
            text-align: center;
            position: relative;
            overflow: hidden;
            margin-top: 150px;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-blue), var(--accent-orange));
        }

        .alert {
            border-radius: 12px;
            border: none;
            margin-bottom: 20px;
            font-size: 1.1rem;
            padding: 1.2rem;
            text-align: center;
            animation: slideIn 0.5s ease-in forwards, fadeOut 0.5s ease-out 4.5s forwards;
            opacity: 0;
            transform: translateY(-20px);
        }

        .alert-danger {
            background: linear-gradient(135deg, #fee2e2, #fce8e8);
            border-left: 4px solid #d63384;
            color: #dc2626;
        }

        .alert-success {
            background: linear-gradient(135deg, #d1fae5, #e6f7ed);
            border-left: 4px solid #a7f3d0;
            color: #065f46;
        }

        @keyframes slideIn {
            0% { opacity: 0; transform: translateY(-20px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeOut {
            0% { opacity: 1; }
            100% { opacity: 0; height: 0; margin: 0; padding: 0; }
        }

        .form-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.75rem;
            font-size: 1.1rem;
            text-align: center;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 1.2rem;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
            text-align: center;
            width: 100%;
            margin-left: auto;
            margin-right: auto;
        }

        .form-control:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 10px rgba(2, 7, 186, 0.3);
            background-color: var(--white);
            transform: scale(1.02);
        }

        .form-control:hover {
            border-color: var(--accent-orange);
            background-color: var(--white);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-orange));
            border: none;
            border-radius: 12px;
            padding: 1.2rem 2.5rem;
            font-weight: 600;
            font-size: 1.2rem;
            color: #fff000;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            width: 100%;
            margin-left: auto;
            margin-right: auto;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(2, 7, 186, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-outline-warning {
            color: var(--accent-orange);
            border: 2px solid var(--accent-orange);
            border-radius: 12px;
            font-weight: 600;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            background: rgba(255, 121, 0, 0.05);
            transition: all 0.3s ease;
            width: 100%;
            margin-left: auto;
            margin-right: auto;
        }

        .btn-outline-warning:hover {
            background: var(--accent-orange);
            color: #fff000;
            transform: translateY(-2px);
        }

        .mb-3 {
            margin-bottom: 1.5rem !important;
        }

        .mb-5 {
            margin-bottom: 2.5rem !important;
        }

        @media (max-width: 576px) {
            .login-container {
                padding: 2.5rem;
                margin: 10px;
                max-width: 90%;
            }
            .logo img {
                width: 150px;
            }
            .form-control, .btn-primary, .btn-outline-warning {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="logo">
        <img src="../Images/rond logo.png" alt="MUST CU Logo">
    </div>
    <div class="login-container">
        
        <?php if ($errors): ?>
            <?php foreach ($errors as $index => $error): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle me-2"></i>
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>
                <p><?php echo htmlspecialchars($success); ?></p>
            </div>
        <?php endif; ?>
        <?php if (!isset($_SESSION['login_email'])): ?>
            <h2 class="text-center mb-4"><i class="fas fa-sign-in-alt"></i> Admin Login</h2>
            <form action='index.php' method="POST" class="text-center">
                <div class="mb-5">
                    <label for="email" class="form-label"><i class="fas fa-envelope"></i> Enter Your Email</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>
                <button type="submit" name="email_submit" class="btn btn-primary">Next</button>
            </form>
        <?php else: ?>
            <h2 class="text-center mb-4"><i class="fas fa-sign-in-alt"></i> Admin Login</h2>
            <form action='index.php' method="POST" class="text-center">
                <div class="mb-5">
                    <label for="username" class="form-label"><i class="fas fa-user"></i> Username</label>
                    <input type="text" name="username" id="username" class="form-control" required>
                </div>
                <div class="mb-5">
                    <label for="password" class="form-label"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <button type="submit" name="login" class="btn btn-primary">Login</button>
            </form>
        <?php endif; ?>
        <div class="text-center mt-5">
            <a href="#" data-bs-toggle="modal" data-bs-target="#resetModal" class="btn btn-outline-warning"><i class="fas fa-key"></i> Forgot Password?</a>
        </div>
        <div class="modal fade" id="resetModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-key"></i> Reset Password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <form method="POST">
                            <div class="mb-5">
                                <label for="reset_email" class="form-label">Enter your email</label>
                                <input type="email" name="email" id="reset_email" class="form-control" required>
                            </div>
                            <button type="submit" name="reset_request" class="btn btn-primary w-100">Send Reset Link</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle alert animations and redirects
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                if (alert && alert.parentNode) {
                    alert.style.animation = 'fadeOut 0.5s ease-out forwards';
                    setTimeout(() => {
                        alert.remove();
                    }, 500);
                }
            }, 4000); // Display for 4 seconds
        });

        // Handle redirects for success messages
        <?php if ($success): ?>
            <?php if (strpos($success, 'Login successful') !== false): ?>
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
                }, 4500);
            <?php elseif (strpos($success, 'Reset link sent') !== false): ?>
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 4500);
            <?php elseif (strpos($success, 'Multiple roles found') !== false): ?>
                setTimeout(() => {
                    window.location.href = 'reset_password.php?select_role=1';
                }, 4500);
            <?php endif; ?>
        <?php endif; ?>

        // Form validation feedback
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const inputs = form.querySelectorAll('input');
                let isValid = true;
                
                inputs.forEach(input => {
                    if (!input.checkValidity()) {
                        isValid = false;
                        input.classList.add('is-invalid');
                        input.classList.remove('is-valid');
                    } else {
                        input.classList.remove('is-invalid');
                        input.classList.add('is-valid');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
        }

        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
                if (this.checkValidity()) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else if (this.value !== '') {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                }
            });

            input.addEventListener('input', function() {
                this.classList.remove('is-invalid', 'is-valid');
            });
        });
    </script>
</body>
</html>