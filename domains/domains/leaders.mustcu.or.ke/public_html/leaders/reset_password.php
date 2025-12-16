<?php
require 'includes/db_connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'leader') {
    header("Location: ../index.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    try {
        $stmt = $pdo->prepare("UPDATE leaders SET password = ? WHERE id = ?");
        $stmt->execute([$hashed_password, $_SESSION['user_id']]);
        $stmt = $pdo->prepare("SELECT email FROM leaders WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        $subject = "New Password";
        $message = <<<EOD
<p>Dear Leader,</p>
<p>Your password for the MUST CU System has been updated. Below are your new login credentials:</p>
<ul>
    <li><strong>Email:</strong> {$user['email']}</li>
    <li><strong>Password:</strong> $password</li>
</ul>
<p>Please log in and change your password if needed. If you did not initiate this change, contact us immediately at <a href="mailto:support@mustcu.org" style="color: #003087; text-decoration: none;">support@mustcu.org</a>.</p>
<p>Best regards,<br>MUST CU System Team</p>
EOD;
        sendEmail($user['email'], $subject, $message, $pdo);
        $_SESSION['message'] = "Password updated successfully. Check your email for new credentials.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error updating password: " . $e->getMessage();
    }
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include '../includes/header.php'; ?>
    <div class="container mt-4">
        <h2 class="mb-4">Reset Password</h2>
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <form action="reset_password.php" method="POST" class="card p-4">
            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Password</button>
        </form>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>