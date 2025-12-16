<?php
require_once '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;


require_once '../vendor/autoload.php'; // adjust path if needed

function sendResetEmail($email, $token, $role) {
    $template = file_get_contents(__DIR__ . '/templates/reset_link.html');
    $url = "https://$role.mustcu.or.ke/reset_password.php?token=$token&role=$role";
    $template = str_replace(['{{email}}', '{{url}}', '{{role}}'], [htmlspecialchars($email), $url, ucfirst($role)], $template);

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'mulwaisaac851@gmail.com'; // Replace with actual email
        $mail->Password = 'uiby qfze lwhn pvsz'; // Replace with actual password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('notifications@mustcu.or.ke', 'MUST CU');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $mail->Body = $template;
        $mail->send();
    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
    }
}

function sendResetConfirmationEmail($email, $role, $new_password) {
    $template = file_get_contents(__DIR__ . '/templates/reset_confirmation.html');
    $timestamp = date('Y-m-d h:i A T');
    $template = str_replace(
        ['{{email}}', '{{role}}', '{{new_password}}', '{{timestamp}}'],
        [htmlspecialchars($email), ucfirst($role), htmlspecialchars($new_password), $timestamp],
        $template
    );

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'notifications@mustcu.or.ke';
        $mail->Password = 'your_smtp_password';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('notifications@mustcu.or.ke', 'MUST CU');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Confirmation';
        $mail->Body = $template;
        $mail->send();
    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
    }
}

function sendApprovalNotification($email, $type, $title) {
    $template = file_get_contents(__DIR__ . '/templates/approval_notification.html');
    $template = str_replace(['{{email}}', '{{type}}', '{{title}}'], [htmlspecialchars($email), $type, htmlspecialchars($title)], $template);

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'notifications@mustcu.or.ke';
        $mail->Password = 'your_smtp_password';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('notifications@mustcu.or.ke', 'MUST CU');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = ucfirst($type) . ' Approval Required';
        $mail->Body = $template;
        $mail->send();
    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
    }
}

function sendContactMessage($email, $sender, $subject, $content) {
    $template = file_get_contents(__DIR__ . '/templates/contact_message.html');
    $template = str_replace(
        ['{{email}}', '{{sender}}', '{{subject}}', '{{content}}'],
        [htmlspecialchars($email), htmlspecialchars($sender), htmlspecialchars($subject), htmlspecialchars($content)],
        $template
    );

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'notifications@mustcu.or.ke';
        $mail->Password = 'your_smtp_password';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('notifications@mustcu.or.ke', 'MUST CU');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'New Contact Message';
        $mail->Body = $template;
        $mail->send();
    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
    }
}

function sendPositionTransitionNotification($email, $position, $docket, $days) {
    $template = file_get_contents(__DIR__ . '/templates/position_transition.html');
    $template = str_replace(
        ['{{email}}', '{{position}}', '{{docket}}', '{{days}}'],
        [htmlspecialchars($email), htmlspecialchars($position), htmlspecialchars($docket), $days],
        $template
    );

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'notifications@mustcu.or.ke';
        $mail->Password = 'your_smtp_password';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('notifications@mustcu.or.ke', 'MUST CU');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Position Transition Required';
        $mail->Body = $template;
        $mail->send();
    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
    }
}

function sendMessageResponse($recipientEmail, $responseContent, $recipientType) {
    $mailer = new PHPMailer(true);

    try {
        // Server settings
        $mailer->isSMTP();
        $mailer->Host = 'smtp.gmail.com'; // Gmail SMTP server
        $mailer->SMTPAuth = true;
        $mailer->Username = 'your_email@gmail.com'; // Your Gmail address
        $mailer->Password = 'your_app_password'; // Your Gmail App Password (see Step 3)
        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->Port = 587; // TCP port for Gmail

        // Recipients
        $mailer->setFrom('your_email@gmail.com', 'MUST CU Admin Team');
        $mailer->addAddress($recipientEmail);

        // Content
        $subject = "Response to Your Message - MUST CU";
        $body = "Hello,\n\n";
        $body .= "Your message has been responded to by an administrator of MUST CU.\n\n";
        $body .= "Response:\n" . htmlspecialchars($responseContent) . "\n\n";
        $body .= "Regards,\nMUST CU Admin Team";

        $mailer->isHTML(false); // Set to false for plain text email
        $mailer->Subject = $subject;
        $mailer->Body = $body;

        // Send email
        $mailer->send();
        return true;
    } catch (Exception $e) {
        // Log error or handle it as needed
        error_log("Failed to send email: {$mailer->ErrorInfo}");
        return false;
    }
}

function sendEmail($to, $subject, $message, $smtpSettings) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $smtpSettings['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $smtpSettings['smtp_username'];
        $mail->Password = $smtpSettings['smtp_password'];
        $mail->SMTPSecure = $smtpSettings['smtp_secure'];
        $mail->Port = $smtpSettings['smtp_port'];

        // Recipients
        $mail->setFrom('no-reply@cumministries.org', 'CU Ministries');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->AltBody = strip_tags($message);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
        return false;
    }
}
?>