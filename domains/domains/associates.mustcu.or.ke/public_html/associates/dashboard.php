<?php
require_once '../shared/includes/db_connect.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'associate') {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT name, email, phone, ministry, completion_year FROM associates WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$roles = [];
$stmt = $pdo->prepare("
    SELECT 'member' AS role FROM members WHERE email = ? UNION
    SELECT 'leader' AS role FROM leaders WHERE email = ? UNION
    SELECT 'admin' AS role FROM admins WHERE email = ?
");
$stmt->execute([$user['email'], $user['email'], $user['email']]);
$roles = array_column($stmt->fetchAll(), 'role');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Associate Dashboard - MUST CU</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container mt-5">
        <h2 class="text-center mb-4"><i class="fas fa-tachometer-alt"></i> Associate Dashboard</h2>
        <div class="row">
            <div class="col-md-6">
                <div class="card p-4 slide-in">
                    <h3>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h3>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
                    <p><strong>Ministry:</strong> <?php echo htmlspecialchars($user['ministry'] ?: 'None'); ?></p>
                    <p><strong>Completion Year:</strong> <?php echo htmlspecialchars($user['completion_year'] ?: 'None'); ?></p>
                    <?php if ($roles): ?>
                        <p><strong>Other Roles:</strong> <?php echo implode(', ', array_map('ucfirst', $roles)); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>