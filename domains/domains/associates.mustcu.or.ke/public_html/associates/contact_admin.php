<?php
require_once '../shared/includes/db_connect.php';
require_once '../shared/lib/email_handler.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'associate') {
    header('Location: index.php');
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_name = filter_input(INPUT_POST, 'sender_name', FILTER_SANITIZE_STRING);
    $sender_email = filter_input(INPUT_POST, 'sender_email', FILTER_SANITIZE_EMAIL);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
    $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING);
    $admin_id = filter_input(INPUT_POST, 'admin_id', FILTER_SANITIZE_NUMBER_INT);

    if (empty($sender_name) || empty($sender_email) || empty($subject) || empty($content) || empty($admin_id)) {
        $errors[] = 'All fields are required.';
    } elseif (!filter_var($sender_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    } else {
        $stmt = $pdo->prepare("SELECT email FROM admins WHERE id = ? AND position IN ('vice secretary', 'secretary')");
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch();

        if ($admin) {
            $stmt = $pdo->prepare("INSERT INTO messages (sender_id, sender_type, sender_name, sender_email, admin_id, subject, content, sent_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$_SESSION['user_id'], 'associate', $sender_name, $sender_email, $admin_id, $subject, $content]);

            sendContactMessage($admin['email'], $sender_name, $subject, $content);
            $success = 'Message sent successfully.';
        } else {
            $errors[] = 'Invalid admin selected.';
        }
    }
}

// Fetch admins for dropdown
$stmt = $pdo->query("SELECT id, name FROM admins WHERE position IN ('vice secretary', 'secretary') AND status = 'active'");
$admins = $stmt->fetchAll();

// Fetch recent messages sent by this associate
$stmt = $pdo->prepare("
    SELECT m.subject, m.content, m.sent_at, m.reply_content, m.replied_at, a.name as admin_name 
    FROM messages m 
    LEFT JOIN admins a ON m.admin_id = a.id 
    WHERE m.sender_id = ? AND m.sender_type = 'associate' 
    ORDER BY m.sent_at DESC 
    LIMIT 10
");
$stmt->execute([$_SESSION['user_id']]);
$recent_messages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Admin - MUST CU</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .message-content {
            max-width: 300px;
            white-space: normal;
            word-break: break-word;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center mb-4"><i class="fas fa-envelope"></i> Contact Admin</h2>
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
                        <label for="sender_name" class="form-label"><i class="fas fa-user"></i> Your Name</label>
                        <input type="text" name="sender_name" id="sender_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="sender_email" class="form-label"><i class="fas fa-envelope"></i> Your Email</label>
                        <input type="email" name="sender_email" id="sender_email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="admin_id" class="form-label"><i class="fas fa-user-tie"></i> Select Admin</label>
                        <select name="admin_id" id="admin_id" class="form-control" required>
                            <option value="">Select an admin</option>
                            <?php foreach ($admins as $admin): ?>
                                <option value="<?php echo $admin['id']; ?>"><?php echo htmlspecialchars($admin['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="subject" class="form-label"><i class="fas fa-heading"></i> Subject</label>
                        <input type="text" name="subject" id="subject" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label"><i class="fas fa-comment"></i> Message</label>
                        <textarea name="content" id="content" class="form-control" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-bounce">Send Message</button>
                </form>
            </div>
        </div>
        
        <div class="row mt-5">
            <div class="col-md-12">
                <h3 class="text-center mb-4"><i class="fas fa-history"></i> Recent Messages</h3>
                <?php if (empty($recent_messages)): ?>
                    <p class="text-center">No messages sent yet.</p>
                <?php else: ?>
                    <table class="table table-bordered">
                        <thead style="background-color: #0207ba; color: #fff000;">
                            <tr>
                                <th>Subject</th>
                                <th>Content</th>
                                <th>Sent To</th>
                                <th>Sent At</th>
                                <th>Reply</th>
                                <th>Replied At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_messages as $message): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($message['subject']); ?></td>
                                    <td class="message-content"><?php echo htmlspecialchars(substr($message['content'], 0, 100)); ?><?php echo strlen($message['content']) > 100 ? '...' : ''; ?></td>
                                    <td><?php echo htmlspecialchars($message['admin_name']); ?></td>
                                    <td><?php echo date('Y-m-d h:i A', strtotime($message['sent_at'])); ?></td>
                                    <td><?php echo $message['reply_content'] ? htmlspecialchars($message['reply_content']) : 'No reply yet'; ?></td>
                                    <td><?php echo $message['replied_at'] ? date('Y-m-d h:i A', strtotime($message['replied_at'])) : '-'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>