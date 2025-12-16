<?php
// reset_password.php — Single unified file (works for all roles)
require_once __DIR__ . '/../shared/config/config.php';
require_once __DIR__ . '/../shared/lib/email_handler.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);




$errors = [];
$success = '';
$allowed_roles = ['members', 'associates', 'leaders'];

// Input
$email = trim($_POST['email'] ?? $_GET['email'] ?? '');
$role  = $_POST['role'] ?? $_GET['role'] ?? '';
$token = $_GET['token'] ?? ''; // Only from URL — NEVER trust session
$action = $_POST['action'] ?? '';

// ------------------------------------------------------------------
// 1. STEP 1: Enter email → find roles
// ------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'check_email') {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } else {
        $found_roles = [];
        foreach ($allowed_roles as $r) {
            $stmt = $pdo->prepare("SELECT id FROM `$r` WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $found_roles[] = $r;
            }
        }

        if (empty($found_roles)) {
            // Never reveal if email exists
            $success = "If your email is registered, a reset link has been sent.";
        } elseif (count($found_roles) === 1) {
            // Auto-select role
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_role'] = $found_roles[0];
            header("Location: ?step=send&email=" . urlencode($email) . "&role=" . $found_roles[0]);
            exit;
        } else {
            // Multiple roles → let user choose
            $_SESSION['reset_email'] = $email;
            $_SESSION['found_roles'] = $found_roles;
            header("Location: ?step=choose_role");
            exit;
        }
    }
}

// ------------------------------------------------------------------
// 2. STEP 2: Confirm role → generate token + send email
// ------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'send_reset') {
    $email = $_SESSION['reset_email'] ?? '';
    $role  = $_POST['role'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($role, $allowed_roles)) {
        die("Invalid request.");
    }

    // Rate limit: max 3 per day per email+role
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM password_reset_requests 
        WHERE email = ? AND role = ? AND created_at > NOW() - INTERVAL 1 DAY
    ");
    $stmt->execute([$email, $role]);
    if ($stmt->fetchColumn() >= 3) {
        $errors[] = "Too many reset attempts. Try again tomorrow.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM `$role` WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $raw_token = bin2hex(random_bytes(32));
            $token_hash = password_hash($raw_token, PASSWORD_BCRYPT); // Secure!
            $expires = date('Y-m-d H:i:s', time() + 1800); // 30 mins

            // Store hashed token
            $stmt = $pdo->prepare("
                UPDATE `$role` 
                SET reset_token_hash = ?, reset_token_expires_at = ? 
                WHERE id = ?
            ");
            $stmt->execute([$token_hash, $expires, $user['id']]);

            // Log request
            $pdo->prepare("
                INSERT INTO password_reset_requests (user_id, role, email) 
                VALUES (?, ?, ?)
            ")->execute([$user['id'], $role, $email]);

            // Build secure link
            $link = "https://{$role}.mustcu.or.ke/reset_password.php"
                  . "?token=" . urlencode($raw_token)
                  . "&email=" . urlencode($email)
                  . "&role=" . $role;

            $sent = sendResetEmail($email, $link); // You must implement this safely

            $success = $sent
                ? "Reset link sent! Check your email (valid for 30 minutes)."
                : "Failed to send email. Try again later.";
        }
    }
}

// ------------------------------------------------------------------
// 3. STEP 3: Reset password with token
// ------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'reset_password') {
    $token = $_POST['token'] ?? '';
    $email = $_POST['email'] ?? '';
    $role  = $_POST['role'] ?? '';
    $pass  = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (strlen($pass) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    } elseif ($pass !== $confirm) {
        $errors[] = "Passwords do not match.";
    } elseif (empty($token) || empty($email) || empty($role)) {
        $errors[] = "Invalid request.";
    } else {
        $stmt = $pdo->prepare("
            SELECT id, reset_token_hash, reset_token_expires_at 
            FROM `$role` 
            WHERE email = ? AND reset_token_expires_at > NOW()
            LIMIT 1
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($token, $user['reset_token_hash'])) {
            $new_hash = password_hash($pass, PASSWORD_BCRYPT);

            $pdo->beginTransaction();
            $stmt = $pdo->prepare("
                UPDATE `$role` 
                SET password = ?, 
                    reset_token_hash = NULL, 
                    reset_token_expires_at = NULL 
                WHERE id = ?
            ");
            $stmt->execute([$new_hash, $user['id']]);
            $pdo->commit();

            // Clear session
            unset($_SESSION['reset_email'], $_SESSION['found_roles']);

            $success = "Password changed successfully! You can now log in.";
            
            // Optional: send notification (not the password!)
            sendPasswordChangedEmail($email);
        } else {
            $errors[] = "Invalid or expired reset link.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUST CU - Reset Password</title>
    <style>
        /* Your beautiful CSS — keep it! */
        body { font-family: Arial, sans-serif; background: linear-gradient(135deg, #1e3a8a, #f97316); display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .container { background: white; padding: 2.5rem; border-radius: 1rem; box-shadow: 0 10px 25px rgba(0,0,0,0.2); max-width: 400px; width: 100%; }
        input, select, button { width: 100%; padding: 12px; margin: 10px 0; border-radius: 8px; border: 1px solid #ccc; }
        button { background: #ea580c; color: white; font-weight: bold; cursor: pointer; border: none; }
        button:hover { background: #d03801; }
        .error { background: #fee2e2; color: #dc2626; padding: 10px; border-radius: 8px; margin: 10px 0; }
        .success { background: #d1fae5; color: #065f46; padding: 10px; border-radius: 8px; margin: 10px 0; }
        .back { display: block; text-align: center; margin-top: 20px; color: white; text-decoration: none; }
    </style>
</head>
<body>
<div class="container">
    <img src="https://mustcu.or.ke/images/resized_image_1.jpg" alt="Logo" style="height:60px; display:block; margin:0 auto 20px;">
    <h2>Reset Password</h2>

    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php if (strpos($success, 'changed successfully') !== false): ?>
            <a href="https://<?= htmlspecialchars($role) ?>.mustcu.or.ke/login.php" class="back">Back to Login</a>
        <?php endif; ?>

    <?php elseif ($errors): ?>
        <div class="error"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
    <?php endif; ?>

    <!-- STEP 1: Enter email -->
    <?php if (!isset($_GET['step']) && empty($token)): ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Enter your email" required value="<?= htmlspecialchars($email) ?>">
            <input type="hidden" name="action" value="check_email">
            <button type="submit">Send Reset Link</button>
        </form>

    <!-- STEP 2: Choose role -->
    <?php elseif ($_GET['step'] === 'choose_role' && !empty($_SESSION['found_roles'])): ?>
        <form method="POST">
            <p>Email: <strong><?= htmlspecialchars($_SESSION['reset_email']) ?></strong></p>
            <select name="role" required>
                <option value="">Select your role</option>
                <?php foreach ($_SESSION['found_roles'] as $r): ?>
                    <option value="<?= $r ?>"><?= ucfirst($r) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="action" value="send_reset">
            <button type="submit">Send Reset Link</button>
        </form>

    <!-- STEP 3: Reset password -->
    <?php elseif (!empty($token)): ?>
        <form method="POST">
            <input type="password" name="password" placeholder="New Password" required minlength="8">
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <input type="hidden" name="email" value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">
            <input type="hidden" name="role" value="<?= htmlspecialchars($_GET['role'] ?? '') ?>">
            <input type="hidden" name="action" value="reset_password">
            <button type="submit">Change Password</button>
        </form>
    <?php endif; ?>

    <a href="https://mustcu.or.ke" class="back">Back to Main Site</a>
</div>
</body>
</html>