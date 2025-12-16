<?php

// Turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'includes/db_connect.php';
require_once '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$errors = [];
$success = '';

// Function to send reply notification using PHPMailer
function sendReplyNotification($recipient_email, $response, $sender_name, $subject) {
    $mail = new PHPMailer(true);
    try {
        // SMTP configuration (update with your actual settings)
        $mail->isSMTP();
        $mail->Host = 'smtp.example.com'; // e.g., smtp.gmail.com
        $mail->SMTPAuth = true;
        $mail->Username = 'your-email@example.com'; // Your SMTP username
        $mail->Password = 'your-password'; // Your SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Sender and recipient
        $mail->setFrom('no-reply@mustcu.or.ke', 'MUST CU Admin');
        $mail->addAddress($recipient_email);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Response to Your Message: ' . htmlspecialchars($subject);
        $mail->Body = '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 12px; box-shadow: 0 4px 8px rgba(0,0,0,0.15); }
                .header { background: linear-gradient(135deg, #0207ba, #001f7a); color: #fff000; padding: 20px; text-align: center; border-top-left-radius: 12px; border-top-right-radius: 12px; }
                .header h1 { margin: 0; font-size: 24px; }
                .content { padding: 20px; color: #333333; }
                .content p { font-size: 16px; line-height: 1.6; }
                .response { background: #f8f9fa; border-left: 4px solid #ff7900; padding: 15px; margin: 15px 0; border-radius: 8px; }
                .footer { background: #0207ba; color: #fff000; text-align: center; padding: 10px; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px; }
                .footer p { margin: 0; font-size: 14px; }
                .button { display: inline-block; padding: 10px 20px; background: linear-gradient(135deg, #0207ba, #001f7a); color: #fff000; text-decoration: none; border-radius: 8px; margin: 10px 0; }
                .button:hover { background: linear-gradient(135deg, #ff7900, #e66900); }
                @media only screen and (max-width: 600px) {
                    .container { width: 100%; margin: 10px; }
                    .header h1 { font-size: 20px; }
                    .content p { font-size: 14px; }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>MUST CU Message Response</h1>
                </div>
                <div class="content">
                    <p>Dear ' . htmlspecialchars($sender_name) . ',</p>
                    <p>Thank you for your message. We have reviewed your submission and here is our response:</p>
                    <div class="response">
                        <p>' . htmlspecialchars($response) . '</p>
                    </div>
                    <p>If you have further questions, please reply to this email or contact us through the MUST CU platform.</p>
                    <a href="https://mustcu.or.ke" class="button">Visit MUST CU</a>
                </div>
                <div class="footer">
                    <p>&copy; ' . date('Y') . ' MUST Christian Union. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>';

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('PHPMailer error: ' . $mail->ErrorInfo);
        return false;
    }
}

// Handle message response
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message_id = filter_input(INPUT_POST, 'message_id', FILTER_SANITIZE_NUMBER_INT);
    $response = filter_input(INPUT_POST, 'response', FILTER_SANITIZE_STRING);

    if (empty($message_id) || empty($response)) {
        $errors[] = 'Message ID and response are required.';
    } else {
        try {
            // Fetch the message to get sender details
            $stmt = $pdo->prepare("SELECT sender_name, sender_type, sender_id, subject, content FROM messages WHERE id = ? AND admin_id = ?");
            $stmt->execute([$message_id, $_SESSION['user_id']]);
            $message = $stmt->fetch();

            if ($message) {
                $stmt = $pdo->prepare("UPDATE messages SET reply_content = ?, replied_at = NOW(), admin_id = ? WHERE id = ?");
                $stmt->execute([$response, $_SESSION['user_id'], $message_id]);

                // Get sender email from members or admins table based on sender_type
                $sender_email = '';
                if ($message['sender_type'] === 'member') {
                    $stmt = $pdo->prepare("SELECT email FROM members WHERE id = ?");
                    $stmt->execute([$message['sender_id']]);
                    $sender = $stmt->fetch();
                    $sender_email = $sender['email'] ?? '';
                } elseif ($message['sender_type'] === 'admin') {
                    $stmt = $pdo->prepare("SELECT email FROM admins WHERE id = ?");
                    $stmt->execute([$message['sender_id']]);
                    $sender = $stmt->fetch();
                    $sender_email = $sender['email'] ?? '';
                }

                if ($sender_email && sendReplyNotification($sender_email, $response, $message['sender_name'], $message['subject'])) {
                    $success = 'Response sent successfully.';
                } else {
                    $errors[] = 'Failed to send email notification.';
                }
            } else {
                $errors[] = 'Message not found or not assigned to you.';
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
            error_log('Message response error: ' . $e->getMessage());
        }
    }
}

// Pagination settings for unreplied messages
$unreplied_per_page = isset($_GET['unreplied_per_page']) ? (int)$_GET['unreplied_per_page'] : 20;
if (!in_array($unreplied_per_page, [20, 50, 100, 200, 300, 500])) {
    $unreplied_per_page = 20;
}
$unreplied_page = isset($_GET['unreplied_page']) ? max(1, (int)$_GET['unreplied_page']) : 1;
$unreplied_offset = ($unreplied_page - 1) * $unreplied_per_page;

// Fetch total count of unreplied messages for this admin
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE reply_content IS NULL AND admin_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $total_unreplied = $stmt->fetchColumn();
    $unreplied_total_pages = ceil($total_unreplied / $unreplied_per_page);
} catch (PDOException $e) {
    $errors[] = 'Failed to fetch unreplied messages count: ' . $e->getMessage();
    error_log('Unreplied messages count error: ' . $e->getMessage());
    $total_unreplied = 0;
    $unreplied_total_pages = 1;
}

// Fetch unreplied messages for this admin
try {
    $stmt = $pdo->prepare("
        SELECT m.id, m.sender_name, m.sender_type, m.subject, m.content, m.sent_at
        FROM messages m
        WHERE m.reply_content IS NULL AND m.admin_id = ?
        ORDER BY m.sent_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$_SESSION['user_id'], $unreplied_per_page, $unreplied_offset]);
    $unreplied_messages = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = 'Failed to fetch unreplied messages: ' . $e->getMessage();
    error_log('Unreplied messages fetch error: ' . $e->getMessage());
    $unreplied_messages = [];
}

// Pagination settings for replied messages
$messages_per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
if (!in_array($messages_per_page, [20, 50, 100, 200, 300, 500])) {
    $messages_per_page = 20;
}
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $messages_per_page;

// Fetch total count of replied messages by this admin
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE reply_content IS NOT NULL AND admin_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $total_messages = $stmt->fetchColumn();
    $total_pages = ceil($total_messages / $messages_per_page);
} catch (PDOException $e) {
    $errors[] = 'Failed to fetch replied messages count: ' . $e->getMessage();
    error_log('Replied messages count error: ' . $e->getMessage());
    $total_messages = 0;
    $total_pages = 1;
}

// Fetch replied messages by this admin
try {
    $stmt = $pdo->prepare("
        SELECT m.id, m.sender_name, m.sender_type, m.subject, m.content, m.sent_at, m.reply_content, m.replied_at, 
               a.name AS replier_name 
        FROM messages m 
        LEFT JOIN admins a ON m.admin_id = a.id 
        WHERE m.reply_content IS NOT NULL AND m.admin_id = ?
        ORDER BY m.sent_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$_SESSION['user_id'], $messages_per_page, $offset]);
    $messages = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = 'Failed to fetch replied messages: ' . $e->getMessage();
    error_log('Replied messages fetch error: ' . $e->getMessage());
    $messages = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messaging Center - MUST CU</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f4f4f4, #e0e0e0);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            margin: 0;
        }
      
        h2, h3 {
            color: #0207ba;
            text-align: center;
            font-weight: 600;
            margin-bottom: 1.5rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .alert {
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 1.1rem;
            padding: 1.2rem;
            text-align: center;
            animation: slideIn 0.5s ease-in forwards, fadeOut 0.5s ease-out 4.5s forwards;
            opacity: 0;
            transform: translateY(-20px);
            max-width: 600px;
            width: 100%;
            margin-left: auto;
            margin-right: auto;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
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
        .table {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }
        .table th, .table td {
            vertical-align: middle;
            text-align: center;
            padding: 0.85rem;
            font-size: 0.95rem;
        }
        .table thead {
            background: linear-gradient(135deg, #0207ba, #001f7a);
            color: #fff000;
        }
        .table tbody tr {
            transition: background 0.3s ease;
        }
        .table tbody tr:hover {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        }
        .message-content {
            max-width: 300px;
            white-space: normal;
            word-break: break-word;
        }
        .message-full {
            display: none;
        }
        .message-truncated::after {
            content: "...";
        }
        .toggle-message {
            cursor: pointer;
            color: #ff7900;
            text-decoration: underline;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }
        .toggle-message:hover {
            color: #e66900;
        }
        .form-control {
            border: 2px solid #0207ba;
            border-radius: 8px;
            padding: 0.75rem;
            font-size: 0.95rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .form-control:focus {
            border-color: #ff7900;
            box-shadow: 0 0 0 3px rgba(255, 121, 0, 0.3);
            outline: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #0207ba, #001f7a);
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: 8px;
            color: #fff000;
            transition: background 0.3s ease, transform 0.2s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #ff7900, #e66900);
            transform: scale(1.05);
        }
        .pagination {
            justify-content: center;
            margin-bottom: 20px;
        }
        .pagination .page-link {
            color: #0207ba;
            border-radius: 8px;
            margin: 0 5px;
            transition: all 0.3s ease;
        }
        .pagination .page-link:hover {
            background: #ff7900;
            color: #ffffff;
        }
        .pagination .active .page-link {
            background: #0207ba;
            color: #fff000;
            border-color: #0207ba;
        }
        .pagination-select {
            margin-left: 20px;
            display: inline-flex;
            align-items: center;
        }
        .pagination-select label {
            margin-right: 10px;
            font-weight: 600;
            color: #0207ba;
        }
        .pagination-select select {
            border: 2px solid #0207ba;
            border-radius: 8px;
            padding: 5px;
            font-size: 0.95rem;
            transition: border-color 0.3s ease;
        }
        .pagination-select select:focus {
            border-color: #ff7900;
            outline: none;
        }
        .fa {
            color: #fff000;
            margin-right: 0.5rem;
        }
        @media (max-width: 768px) {
            .table th, .table td {
                font-size: 0.9rem;
                padding: 0.5rem;
            }
            .message-content {
                max-width: 200px;
            }
        }
        @media (max-width: 576px) {
            .table th, .table td {
                font-size: 0.85rem;
                padding: 0.4rem;
            }
            .message-content {
                max-width: 150px;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container mt-5">
        <h2 class="text-center mb-4"><i class="fas fa-comment"></i> Messaging Center</h2>
        <?php if ($errors): ?>
            <?php foreach ($errors as $error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <p><?php echo htmlspecialchars($success); ?></p>
            </div>
        <?php endif; ?>

        <!-- Unreplied Messages Section -->
        <h3 class="text-center mb-4"><i class="fas fa-envelope-open"></i> Recently Sent Unreplied Messages</h3>
        <nav aria-label="Unreplied messages pagination" class="pagination">
            <ul class="pagination">
                <li class="page-item <?php echo $unreplied_page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?unreplied_page=<?php echo $unreplied_page - 1; ?>&unreplied_per_page=<?php echo $unreplied_per_page; ?>" aria-label="Previous">
                        <span aria-hidden="true">«</span>
                    </a>
                </li>
                <?php for ($i = 1; $i <= $unreplied_total_pages; $i++): ?>
                    <li class="page-item <?php echo $i === $unreplied_page ? 'active' : ''; ?>">
                        <a class="page-link" href="?unreplied_page=<?php echo $i; ?>&unreplied_per_page=<?php echo $unreplied_per_page; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php echo $unreplied_page >= $unreplied_total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?unreplied_page=<?php echo $unreplied_page + 1; ?>&unreplied_per_page=<?php echo $unreplied_per_page; ?>" aria-label="Next">
                        <span aria-hidden="true">»</span>
                    </a>
                </li>
            </ul>
            <div class="pagination-select">
                <label for="unreplied_per_page">Messages per page:</label>
                <select id="unreplied_per_page" onchange="window.location.href='?unreplied_page=1&unreplied_per_page=' + this.value">
                    <option value="20" <?php echo $unreplied_per_page == 20 ? 'selected' : ''; ?>>20</option>
                    <option value="50" <?php echo $unreplied_per_page == 50 ? 'selected' : ''; ?>>50</option>
                    <option value="100" <?php echo $unreplied_per_page == 100 ? 'selected' : ''; ?>>100</option>
                    <option value="200" <?php echo $unreplied_per_page == 200 ? 'selected' : ''; ?>>200</option>
                    <option value="300" <?php echo $unreplied_per_page == 300 ? 'selected' : ''; ?>>300</option>
                    <option value="500" <?php echo $unreplied_per_page == 500 ? 'selected' : ''; ?>>500</option>
                </select>
            </div>
        </nav>
        <div class="row">
            <div class="col-md-12">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Sender</th>
                            <th>Type</th>
                            <th>Subject</th>
                            <th>Content</th>
                            <th>Sent At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($unreplied_messages)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No unreplied messages assigned to you.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($unreplied_messages as $message): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($message['sender_name'] ?: 'Anonymous'); ?></td>
                                    <td><?php echo ucfirst($message['sender_type']); ?></td>
                                    <td><?php echo htmlspecialchars($message['subject'] ?: 'No Subject'); ?></td>
                                    <td class="message-content">
                                        <span class="message-truncated" id="unreplied-truncated-<?php echo $message['id']; ?>">
                                            <?php echo htmlspecialchars(substr($message['content'], 0, 100)); ?>
                                        </span>
                                        <span class="message-full" id="unreplied-full-<?php echo $message['id']; ?>">
                                            <?php echo htmlspecialchars($message['content']); ?>
                                        </span>
                                        <?php if (strlen($message['content']) > 100): ?>
                                            <br>
                                            <span class="toggle-message" onclick="toggleMessage('unreplied', <?php echo $message['id']; ?>)">
                                                Show More
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('Y-m-d h:i A', strtotime($message['sent_at'])); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#replyModal" 
                                            data-message-id="<?php echo $message['id']; ?>"
                                            data-sender-name="<?php echo htmlspecialchars($message['sender_name'] ?: 'Anonymous'); ?>"
                                            data-subject="<?php echo htmlspecialchars($message['subject'] ?: 'No Subject'); ?>"
                                            data-content="<?php echo htmlspecialchars($message['content']); ?>"
                                            data-sent-at="<?php echo date('Y-m-d h:i A', strtotime($message['sent_at'])); ?>">
                                            <i class="fas fa-reply"></i> Respond
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Replied Messages Section -->
        <h3 class="text-center mb-4 mt-5"><i class="fas fa-envelope"></i> Replied Messages</h3>
        <nav aria-label="Messages pagination" class="pagination">
            <ul class="pagination">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&per_page=<?php echo $messages_per_page; ?>" aria-label="Previous">
                        <span aria-hidden="true">«</span>
                    </a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&per_page=<?php echo $messages_per_page; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&per_page=<?php echo $messages_per_page; ?>" aria-label="Next">
                        <span aria-hidden="true">»</span>
                    </a>
                </li>
            </ul>
            <div class="pagination-select">
                <label for="messages_per_page">Messages per page:</label>
                <select id="messages_per_page" onchange="window.location.href='?page=1&per_page=' + this.value">
                    <option value="20" <?php echo $messages_per_page == 20 ? 'selected' : ''; ?>>20</option>
                    <option value="50" <?php echo $messages_per_page == 50 ? 'selected' : ''; ?>>50</option>
                    <option value="100" <?php echo $messages_per_page == 100 ? 'selected' : ''; ?>>100</option>
                    <option value="200" <?php echo $messages_per_page == 200 ? 'selected' : ''; ?>>200</option>
                    <option value="300" <?php echo $messages_per_page == 300 ? 'selected' : ''; ?>>300</option>
                    <option value="500" <?php echo $messages_per_page == 500 ? 'selected' : ''; ?>>500</option>
                </select>
            </div>
        </nav>
        <div class="row">
            <div class="col-md-12">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Sender</th>
                            <th>Type</th>
                            <th>Subject</th>
                            <th>Content</th>
                            <th>Sent At</th>
                            <th>Reply Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($messages)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No replied messages by you.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($messages as $message): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($message['sender_name'] ?: 'Anonymous'); ?></td>
                                    <td><?php echo ucfirst($message['sender_type']); ?></td>
                                    <td><?php echo htmlspecialchars($message['subject'] ?: 'No Subject'); ?></td>
                                    <td class="message-content">
                                        <span class="message-truncated" id="all-truncated-<?php echo $message['id']; ?>">
                                            <?php echo htmlspecialchars(substr($message['content'], 0, 100)); ?>
                                        </span>
                                        <span class="message-full" id="all-full-<?php echo $message['id']; ?>">
                                            <?php echo htmlspecialchars($message['content']); ?>
                                        </span>
                                        <?php if (strlen($message['content']) > 100): ?>
                                            <br>
                                            <span class="toggle-message" onclick="toggleMessage('all', <?php echo $message['id']; ?>)">
                                                Show More
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('Y-m-d h:i A', strtotime($message['sent_at'])); ?></td>
                                    <td>
                                        <strong>Reply:</strong> <?php echo htmlspecialchars($message['reply_content']); ?><br>
                                        <small><i>Replied by:</i> <?php echo htmlspecialchars($message['replier_name'] ?: 'N/A'); ?><br>
                                        <i>Replied at:</i> <?php echo date('Y-m-d h:i A', strtotime($message['replied_at'])); ?></small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Reply Modal -->
    <div class="modal fade" id="replyModal" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="replyModalLabel">Reply to Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="replyForm">
                        <input type="hidden" name="message_id" id="modal-message-id">
                        <div class="mb-3">
                            <label for="modal-sender-name" class="form-label">Sender:</label>
                            <input type="text" class="form-control" id="modal-sender-name" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="modal-subject" class="form-label">Subject:</label>
                            <input type="text" class="form-control" id="modal-subject" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="modal-sent-at" class="form-label">Sent At:</label>
                            <input type="text" class="form-control" id="modal-sent-at" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="modal-content" class="form-label">Message Content:</label>
                            <textarea class="form-control" id="modal-content" rows="5" readonly></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="response" class="form-label">Your Response:</label>
                            <textarea name="response" class="form-control" id="response" rows="5" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" form="replyForm" class="btn btn-primary"><i class="fas fa-reply"></i> Send Response</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
    <script>
        function toggleMessage(prefix, messageId) {
            const truncated = document.getElementById(`${prefix}-truncated-${messageId}`);
            const full = document.getElementById(`${prefix}-full-${messageId}`);
            const toggleLink = truncated.parentElement.querySelector('.toggle-message');

            if (truncated.style.display === 'none') {
                truncated.style.display = 'inline';
                full.style.display = 'none';
                toggleLink.textContent = 'Show More';
            } else {
                truncated.style.display = 'none';
                full.style.display = 'inline';
                toggleLink.textContent = 'Show Less';
            }
        }

        // Auto-dismiss alerts after 4.5 seconds
        document.addEventListener('DOMContentLoaded', () => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    if (alert && alert.parentNode) {
                        alert.style.animation = 'fadeOut 0.5s ease-out forwards';
                        setTimeout(() => {
                            alert.remove();
                        }, 500);
                    }
                }, 4000);
            });
        });

        // Populate modal with message data
        const replyModal = document.getElementById('replyModal');
        replyModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const messageId = button.getAttribute('data-message-id');
            const senderName = button.getAttribute('data-sender-name');
            const subject = button.getAttribute('data-subject');
            const content = button.getAttribute('data-content');
            const sentAt = button.getAttribute('data-sent-at');

            const modalMessageId = replyModal.querySelector('#modal-message-id');
            const modalSenderName = replyModal.querySelector('#modal-sender-name');
            const modalSubject = replyModal.querySelector('#modal-subject');
            const modalSentAt = replyModal.querySelector('#modal-sent-at');
            const modalContent = replyModal.querySelector('#modal-content');
            const responseTextarea = replyModal.querySelector('#response');

            modalMessageId.value = messageId;
            modalSenderName.value = senderName;
            modalSubject.value = subject;
            modalSentAt.value = sentAt;
            modalContent.value = content;
            responseTextarea.value = ''; // Clear previous response
        });
    </script>
</body>
</html>