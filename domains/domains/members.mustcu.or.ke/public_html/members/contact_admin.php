<?php
require_once '../shared/config/config.php';
require_once '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header('Location: index.php');
    exit;
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle success message from redirect
$success = '';
if (isset($_GET['success']) && $_GET['success'] == '1' && isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Clear after display
}

function sendContactMessage($admin_email, $sender_name, $sender_email, $subject, $content) {
    $admin_body = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
                .header { background-color: #f8f9fa; padding: 10px; text-align: center; border-bottom: 2px solid #007bff; }
                .content { padding: 20px; }
                .footer { font-size: 12px; color: #777; text-align: center; padding-top: 10px; }
                h2 { color: #007bff; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>New Contact Message</h2>
                </div>
                <div class="content">
                    <p><strong>From:</strong> ' . htmlspecialchars($sender_name) . ' (' . htmlspecialchars($sender_email) . ')</p>
                    <p><strong>Subject:</strong> ' . htmlspecialchars($subject) . '</p>
                    <p><strong>Message:</strong><br>' . nl2br(htmlspecialchars($content)) . '</p>
                </div>
                <div class="footer">
                    <p>Sent via MUST CU System</p>
                </div>
            </div>
        </body>
        </html>
    ';

    $confirm_body = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
                .header { background-color: #f8f9fa; padding: 10px; text-align: center; border-bottom: 2px solid #28a745; }
                .content { padding: 20px; }
                .footer { font-size: 12px; color: #777; text-align: center; padding-top: 10px; }
                h2 { color: #28a745; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Message Received</h2>
                </div>
                <div class="content">
                    <p>Dear ' . htmlspecialchars($sender_name) . ',</p>
                    <p>Your message with subject <strong>"' . htmlspecialchars($subject) . '"</strong> has been successfully sent to the admin.</p>
                    <p><strong>Message Content:</strong><br>' . nl2br(htmlspecialchars($content)) . '</p>
                    <p>Thank you for contacting us!</p>
                </div>
                <div class="footer">
                    <p>Best regards,<br>MUST CU Team</p>
                </div>
            </div>
        </body>
        </html>
    ';

    $mail = new PHPMailer(true);
    $email_errors = [];

    try {
        // Configure single SMTP
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function($str, $level) {
            error_log("PHPMailer [Level $level]: $str");
        };

        $mail->isSMTP();
        $mail->Host = 'mail.mustcu.or.ke'; // Replace with your SMTP host (e.g., 'smtp.gmail.com')
        $mail->SMTPAuth = true;
        $mail->Username = 'secretary@mustcu.or.ke'; // Replace with your SMTP username
        $mail->Password = '3GF3W3ebeSa5vgZhbuDp'; // Replace with your SMTP password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Send admin email
        $mail->setFrom('secretary@mustcu.or.ke', 'MUST CU');
        $mail->addAddress($admin_email);
        $mail->isHTML(true);
        $mail->Subject = 'New Contact Message';
        $mail->Body = $admin_body;
        $mail->send();

        // Send confirmation email to sender
        $mail->clearAddresses();
        $mail->addAddress($sender_email);
        $mail->Subject = 'Confirmation: Your Message Has Been Received';
        $mail->Body = $confirm_body;
        $mail->send();

        error_log("Email sent successfully using SMTP");
        return [];
    } catch (Exception $e) {
        $error_message = "Failed to send email: {$mail->ErrorInfo}";
        $email_errors[] = $error_message;
        error_log($error_message);
        return $email_errors;
    }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
    $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING);
    $admin_id = filter_input(INPUT_POST, 'admin_id', FILTER_SANITIZE_NUMBER_INT);
    $csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_STRING);

    if (empty($subject) || empty($content) || empty($admin_id)) {
        $errors[] = 'All fields are required.';
    } elseif ($csrf_token !== $_SESSION['csrf_token']) {
        $errors[] = 'Invalid CSRF token. Please try submitting again.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT name, email FROM members WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $sender = $stmt->fetch();

            if (!$sender) {
                $errors[] = 'Sender information not found.';
            } else {
                $stmt = $pdo->prepare("SELECT email FROM admins WHERE id = ? AND status = 'active'");
                $stmt->execute([$admin_id]);
                $admin = $stmt->fetch();

                if ($admin) {
                    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, sender_type, sender_name, admin_id, subject, content, attachment, status, sent_at, replied_at, reply_content) VALUES (?, 'member', ?, ?, ?, ?, NULL, 'sent', NOW(), NULL, NULL)");
                    $stmt->execute([$_SESSION['user_id'], $sender['name'], $admin_id, $subject, $content]);
                    
                    $email_errors = sendContactMessage($admin['email'], $sender['name'], $sender['email'], $subject, $content);
                    if (empty($email_errors)) {
                        $_SESSION['success_message'] = 'Message sent successfully.';
                        // Regenerate CSRF token to prevent reuse
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        header('Location: contact_admin.php?success=1');
                        exit;
                    } else {
                        $errors[] = 'Failed to send email. Please try again or contact support.';
                        foreach ($email_errors as $email_error) {
                            $errors[] = htmlspecialchars($email_error);
                        }
                    }
                } else {
                    $errors[] = 'Invalid admin selected.';
                }
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error: Unable to process your request. Please try again later.';
            error_log("PDOException in POST handling: {$e->getMessage()}");
        }
    }
}

$per_page_options = [20, 40, 80, 100];
$per_page = isset($_GET['per_page']) && in_array((int)$_GET['per_page'], $per_page_options) ? (int)$_GET['per_page'] : 20;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $per_page;

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE sender_id = ? AND sender_type = 'member'");
    $stmt->execute([$_SESSION['user_id']]);
    $total_messages = $stmt->fetchColumn();
    $total_pages = ceil($total_messages / $per_page);

    $stmt = $pdo->prepare("
        SELECT m.*, a.name AS admin_name, a.position AS admin_position 
        FROM messages m 
        JOIN admins a ON m.admin_id = a.id 
        WHERE m.sender_id = ? AND m.sender_type = 'member' 
        ORDER BY m.sent_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->bindValue(2, $per_page, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $messages = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = 'Database error: Unable to fetch messages. Please try again later.';
    error_log("PDOException in messages fetch: {$e->getMessage()}");
    $messages = [];
    $total_pages = 0;
}

try {
    $stmt = $pdo->prepare("SELECT id, name, position FROM admins WHERE status = 'active'");
    $stmt->execute();
    $admins = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = 'Database error: Unable to fetch admins. Please try again later.';
    error_log("PDOException in admins fetch: {$e->getMessage()}");
    $admins = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Admin - MUST CU</title>
    <link rel="stylesheet" href="css/style.css">
    
    <style>
        .card-body p { margin-bottom: 1rem; }
        .toggle-more { color: #007bff; cursor: pointer; }
        .toggle-more:hover { text-decoration: underline; }
        .short-text, .full-text { display: inline; }
        .response-section { border-top: 1px solid #ddd; padding-top: 1rem; margin-top: 1rem; }
        @media (max-width: 576px) {
            .card-header { font-size: 0.9rem; }
            .card-body { font-size: 0.85rem; }
            .pagination { flex-wrap: wrap; }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6">
                <h2 class="text-center mb-4"><i class="fas fa-envelope"></i> Contact Admin</h2>
                <?php if ($errors): ?>
                    <div class="alert alert-danger fade-in">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success fade-in"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                <form method="POST" class="card p-4 slide-in">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <div class="mb-3">
                        <label for="admin_id" class="form-label"><i class="fas fa-user"></i> Select Admin</label>
                        <select name="admin_id" id="admin_id" class="form-control" required>
                            <option value="">Select an admin</option>
                            <?php foreach ($admins as $admin): ?>
                                <option value="<?php echo $admin['id']; ?>"><?php echo htmlspecialchars($admin['name'] . ' (' . $admin['position'] . ')'); ?></option>
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

        <div class="row justify-content-center mt-5">
            <div class="col-12 col-md-8">
                <h2 class="text-center mb-4">Your Recent Messages</h2>
                <form method="GET" class="mb-3 text-center">
                    <label for="per_page" class="form-label">Items per page:</label>
                    <select name="per_page" id="per_page" onchange="this.form.submit()" class="form-select d-inline-block w-auto">
                        <?php foreach ($per_page_options as $option): ?>
                            <option value="<?php echo $option; ?>" <?php echo $per_page == $option ? 'selected' : ''; ?>><?php echo $option; ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <?php if (empty($messages)): ?>
                    <p class="text-center">No messages sent yet.</p>
                <?php else: ?>
                    <?php foreach ($messages as $message): ?>
                        <div class="card mb-3">
                            <div class="card-header">
                                To: <?php echo htmlspecialchars($message['admin_name'] . ' (' . $message['admin_position'] . ')'); ?> - 
                                Subject: <?php echo htmlspecialchars($message['subject']); ?> - 
                                Sent: <?php echo htmlspecialchars($message['sent_at']); ?>
                            </div>
                            <div class="card-body">
                                <p><strong>Message:</strong></p>
                                <?php
                                $msg_content = htmlspecialchars($message['content']);
                                if (strlen($msg_content) > 200) {
                                    echo '<span class="short-text">' . substr($msg_content, 0, 200) . '...</span>';
                                    echo '<span class="full-text d-none">' . $msg_content . '</span>';
                                    echo ' <a href="#" class="toggle-more">Show More</a>';
                                } else {
                                    echo $msg_content;
                                }
                                ?>
                                <?php if (!empty($message['reply_content']) && !empty($message['replied_at'])): ?>
                                    <div class="response-section">
                                        <p><strong>Admin Response (Replied at: <?php echo htmlspecialchars($message['replied_at']); ?>):</strong></p>
                                        <?php
                                        $reply_content = htmlspecialchars($message['reply_content']);
                                        if (strlen($reply_content) > 200) {
                                            echo '<span class="short-text">' . substr($reply_content, 0, 200) . '...</span>';
                                            echo '<span class="full-text d-none">' . $reply_content . '</span>';
                                            echo ' <a href="#" class="toggle-more">Show More</a>';
                                        } else {
                                            echo $reply_content;
                                        }
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <nav aria-label="Pagination">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&per_page=<?php echo $per_page; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.toggle-more').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const parent = this.parentElement;
                    const shortText = parent.querySelector('.short-text');
                    const fullText = parent.querySelector('.full-text');
                    if (fullText.classList.contains('d-none')) {
                        shortText.classList.add('d-none');
                        fullText.classList.remove('d-none');
                        this.textContent = 'Show Less';
                    } else {
                        shortText.classList.remove('d-none');
                        fullText.classList.add('d-none');
                        this.textContent = 'Show More';
                    }
                });
            });
        });
    </script>
</body>
</html>