<?php
require_once 'includes/db_connect.php';
session_start();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $completion_year = filter_input(INPUT_POST, 'completion_year', FILTER_SANITIZE_STRING);
    $ministry = filter_input(INPUT_POST, 'ministry', FILTER_SANITIZE_STRING);

    if (empty($name) || empty($email) || empty($phone)) {
        $errors[] = 'Name, email, and phone are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM associates WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email already registered.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO associates (name, email, phone, completion_year, ministry, submitted_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$name, $email, $phone, $completion_year, $ministry]);
            $success = 'Registration successful. Use your phone number to log in and set a password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - MUST CU</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center mb-4"><i class="fas fa-user-plus"></i> Register as Associate</h2>
                <?php if ($errors): ?>
                    <div class="alert alert-danger fade-in">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success fade-in"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                <form method="POST" class="card p-4 slide-in">
                    <div class="mb-3">
                        <label for="name" class="form-label"><i class="fas fa-user"></i> Name</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label"><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label"><i class="fas fa-phone"></i> Phone</label>
                        <input type="text" name="phone" id="phone" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="completion_year" class="form-label"><i class="fas fa-graduation-cap"></i> Completion Year</label>
                        <input type="text" name="completion_year" id="completion_year" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="ministry" class="form-label"><i class="fas fa-church"></i> Ministry Served</label>
                        <input type="text" name="ministry" id="ministry" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary btn-bounce">Register</button>
                </form>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>