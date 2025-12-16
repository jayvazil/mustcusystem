<?php
require_once 'includes/db_connect.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $token = filter_var($_POST['token'], FILTER_SANITIZE_STRING);
    $password = $_POST['password'];
    $stmt = $pdo->prepare("SELECT * FROM associates WHERE email = ? AND reset_token = ? AND reset_expiry > NOW()");
    $stmt->execute([$email, $token]);
    $associate = $stmt->fetch();
    if ($associate) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE associates SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE email = ?");
        $stmt->execute([$hashed_password, $email]);
        $message = "Password reset successfully. <a href='login.php'>Login</a>";
    } else {
        $error = "Invalid or expired reset link.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <?php include 'includes/header.php'; ?>
    <div class="container bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <img src="https://via.placeholder.com/50" alt="CU Logo" class="mx-auto mb-4 h-16">
        <h2 class="text-2xl font-bold text-center text-blue-900">Reset Password</h2>
        <?php if (isset($error)): ?>
            <p class="error text-center"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if (isset($message)): ?>
            <p class="success text-center"><?php echo $message; ?></p>
        <?php else: ?>
        <form method="POST" onsubmit="return validateForm()" class="space-y-4">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($_GET['email']); ?>">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                <input type="password" name="password" id="password" required placeholder="Enter new password" class="w-full">
            </div>
            <button type="submit" class="w-full bg-orange-500 hover:bg-yellow-500 text-white">Reset Password</button>
        </form>
        <?php endif; ?>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>