<?php
require_once 'includes/db_connect.php';
require_once '../shared/lib/email_handler.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = filter_var($_POST['type'], FILTER_SANITIZE_STRING);
    $target_group = filter_var($_POST['target_group'], FILTER_SANITIZE_STRING);
    $content = filter_var($_POST['content'], FILTER_SANITIZE_STRING);
    $subject = ($type === 'email') ? filter_var($_POST['subject'], FILTER_SANITIZE_STRING) : null;
    if ($type === 'post') {
        $stmt = $pdo_leaders->prepare("INSERT INTO posts (admin_id, content, target_group, status) VALUES (?, ?, ?, 'approved')");
        $stmt->execute([$_SESSION['admin_id'], $content, $target_group]);
        send_notification_to_admins($pdo_admins, "New post by Admin ID {$_SESSION['admin_id']} for $target_group has been posted.");
        $message = "Post sent successfully.";
    } elseif ($type === 'email') {
        $stmt = $pdo_leaders->prepare("INSERT INTO emails (admin_id, subject, content, target_group, status) VALUES (?, ?, ?, ?, 'approved')");
        $stmt->execute([$_SESSION['admin_id'], $subject, $content, $target_group]);
        send_email_to_group($pdo_members, $pdo_leaders, $pdo_associates, $subject, $content, $target_group);
        send_notification_to_admins($pdo_admins, "New email by Admin ID {$_SESSION['admin_id']} for $target_group has been sent.");
        $message = "Email sent successfully.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Send Email/Post</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js"></script>
    <script>
        function toggleFields() {
            const type = document.getElementById('type').value;
            document.getElementById('subject_field').style.display = type === 'email' ? 'block' : 'none';
        }
    </script>
</head>
<body class="bg-gray-100" onload="toggleFields()">
    <?php include 'includes/header.php'; ?>
    <div class="container">
        <h2 class="text-2xl font-bold text-blue-900 mb-4">Bulk Send Email/Post</h2>
        <?php if (isset($message)): ?>
            <p class="success"><?php echo $message; ?></p>
        <?php endif; ?>
        <form method="POST" onsubmit="return validateForm()" class="space-y-4">
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                <select name="type" id="type" required class="w-full" onchange="toggleFields()">
                    <option value="">Select Type</option>
                    <option value="post">Post</option>
                    <option value="email">Email</option>
                </select>
            </div>
            <div>
                <label for="target_group" class="block text-sm font-medium text-gray-700">Target Group</label>
                <select name="target_group" id="target_group" required class="w-full">
                    <option value="">Select Group</option>
                    <option value="members">Members</option>
                    <option value="leaders">Leaders</option>
                    <option value="associates">Associates</option>
                    <option value="all">All</option>
                </select>
            </div>
            <div id="subject_field" style="display: none;">
                <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                <input type="text" name="subject" id="subject" placeholder="Subject" class="w-full">
            </div>
            <div>
                <label for="content" class="block text-sm font-medium text-gray-700">Content</label>
                <textarea name="content" id="content" required placeholder="Content" class="w-full"></textarea>
            </div>
            <button type="submit" class="w-full bg-orange-500 hover:bg-yellow-500 text-white">Send</button>
        </form>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>