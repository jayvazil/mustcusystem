<?php
require_once '../shared/config/config.php';

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Session expiration logic
if (isset($_SESSION['user_id']) && isset($_SESSION['login_time'])) {
    $session_duration = time() - $_SESSION['login_time'];
    if ($session_duration > 3600) { // 1 hour = 3600 seconds
        $_SESSION = [];
        session_destroy();
        $_SESSION['error'] = "Your session has expired. Please log in again.";
        header("Location: index.php");
        exit();
    }
}


require_once __DIR__ . '/../../PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../../PHPMailer/SMTP.php';
require_once __DIR__ . '/../../PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;



function generateEmailTemplate($subject, $content) {
    $html = <<<EOD
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>$subject</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 600px; margin: 20px auto; background-color: #ffffff; border: 1px solid #e0e0e0; border-radius: 8px;">
        <tr>
            <td style="background-color: #003087; padding: 20px; text-align: center; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                <h1 style="color: #ffffff; margin: 0; font-size: 24px;">MUST CU System</h1>
            </td>
        </tr>
        <tr>
            <td style="padding: 30px; color: #333333;">
                <h2 style="font-size: 20px; margin-top: 0; color: #003087;">$subject</h2>
                <div style="font-size: 16px; line-height: 1.6;">
                    $content
                </div>
                <hr style="border: 0; border-top: 1px solid #e0e0e0; margin: 20px 0;">
                <p style="font-size: 14px; color: #666666; text-align: center;">
                    If you have any questions, contact us at <a href="mailto:support@mustcu.org" style="color: #003087; text-decoration: none;">support@mustcu.org</a>.
                </p>
            </td>
        </tr>
        <tr>
            <td style="background-color: #f4f4f4; padding: 15px; text-align: center; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                <p style="font-size: 12px; color: #666666; margin: 0;">
                    &copy; 2025 MUST Christian Union. All rights reserved.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
EOD;
    return $html;
}

function sendEmail($to, $subject, $message, $pdo) {
    $mail = new PHPMailer(true);
    $sender = 'mulwaisaac851@gmail.com';
    $status = 'failed';
    $error_message = null;

    // Wrap the message in the styled template
    $styled_message = generateEmailTemplate($subject, $message);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Update with your SMTP host
        $mail->SMTPAuth = true;
        $mail->Username = 'mulwaisaac851@gmail.com'; // Update with your SMTP username
        $mail->Password = 'fyra dtzw jvmj jots'; // Update with your SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->setFrom($sender, 'MUST Christian Union');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $styled_message;
        $mail->send();
        $status = 'sent';
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }

   
}

function sendBulkEmail($recipients, $subject, $message, $pdo) {
    $success = true;
    $styled_message = generateEmailTemplate($subject, $message);
    foreach ($recipients as $recipient) {
        if (!sendEmail($recipient['email'], $subject, $styled_message, $pdo)) {
            $success = false;
        }
    }
    return $success;
}
?>