<?php
require_once '../shared/config/config.php';
require_once '../shared/lib/email_handler.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'leader') {
    header('Location: index.php');
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING);
    $audience = filter_input(INPUT_POST, 'audience', FILTER_SANITIZE_STRING);

    if (empty($content) || empty($audience)) {
        $errors[] = 'Content and audience are required.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO posts (creator_id, creator_type, content, audience, status, submitted_at) VALUES (?, 'leader', ?, ?, 'pending', NOW())");
        $stmt->execute([$_SESSION['user_id'], $content, $audience]);

        $stmt = $pdo->prepare("SELECT email FROM admins WHERE position IN ('vice secretary', 'secretary') AND status = 'active'");
        $stmt->execute();
        $admins = $stmt->fetchAll();
        foreach ($admins as $admin) {
            sendApprovalNotification($admin['email'], 'post', 'New Post');
        }
        $success = 'Post submitted for approval.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post - MUST CU</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center mb-4"><i class="fas fa-edit"></i> Create Post</h2>
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
                        <label for="content" class="form-label"><i class="fas fa-comment"></i> Post Content</label>
                        <textarea name="content" id="content" class="form-control" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="audience" class="form-label"><i class="fas fa-users"></i> Audience</label>
                        <select name="audience" id="audience" class="form-control" required>
                            <option value="members">Members</option>
                            <option value="associates">Associates</option>
                            <option value="all">All</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-bounce">Submit Post</button>
                </form>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>