<?php
require_once '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;


require_once '../vendor/autoload.php'; // adjust path if needed


function sendNewLoginEmail($email, $role, $password) {
    error_log("Starting sendNewLoginEmail: Email=$email, Role=$role at " . date('Y-m-d H:i:s'), 3, __DIR__ . '/../reset_debug.log');
    $template = file_get_contents(__DIR__ . '/templates/new_login.html');
    if ($template === false) {
        $error = "Template file not found: " . __DIR__ . '/templates/new_login.html';
        error_log("Error: $error at " . date('Y-m-d H:i:s'), 3, __DIR__ . '/../reset_debug.log');
        return $error;
    }
    $template = str_replace(['{{email}}', '{{role}}', '{{password}}'], [htmlspecialchars($email), ucfirst($role), htmlspecialchars($password)], $template);
    error_log("New login template processed at " . date('Y-m-d H:i:s'), 3, __DIR__ . '/../reset_debug.log');

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'mail.mustcu.or.ke';
        $mail->SMTPAuth = true;
        $mail->Username = 'notifications@mustcu.or.ke';
        $mail->Password = 'jkTD4SYzfRQuyx9JmV9u';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->SMTPDebug = 2; // Temporary debug
        $mail->Debugoutput = function($str, $level) {
            error_log("SMTP Debug: $str at " . date('Y-m-d H:i:s'), 3, __DIR__ . '/../reset_debug.log');
        };

        $mail->setFrom('notifications@mustcu.or.ke', 'MUST CU');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'New Login Credentials';
        $mail->Body = $template;
        $mail->send();
        error_log("Success: New login email sent to $email at " . date('Y-m-d H:i:s'), 3, __DIR__ . '/../reset_debug.log');
        return true;
    } catch (Exception $e) {
        $error = "Email sending failed: {$mail->ErrorInfo}";
        error_log("Error: $error at " . date('Y-m-d H:i:s'), 3, __DIR__ . '/../reset_debug.log');
        return $error;
    }
}

function sendResetEmail($email, $token, $role) {
    $template = file_get_contents(__DIR__ . '/templates/reset_link.html');
    if ($template === false) {
        error_log("Template file not found: " . __DIR__ . '/templates/reset_link.html at ' . date('Y-m-d H:i:s'));
        return "Template file not found";
    }
    $url = "https://$role.mustcu.or.ke/reset_password.php?token=$token&role=$role";
    $template = str_replace(['{{email}}', '{{url}}', '{{role}}'], [htmlspecialchars($email), $url, ucfirst($role)], $template);

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'mail.mustcu.or.ke';
        $mail->SMTPAuth = true;
        $mail->Username = 'notifications@mustcu.or.ke';
        $mail->Password = 'jkTD4SYzfRQuyx9JmV9u';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->SMTPDebug = 2; // Enable debug output (temporary for testing)
        $mail->Debugoutput = function($str, $level) {
            error_log("SMTP Debug: $str at " . date('Y-m-d H:i:s'));
        };

        $mail->setFrom('notifications@mustcu.or.ke', 'MUST CU');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $mail->Body = $template;
        $mail->send();
        error_log("Email sent successfully to $email at " . date('Y-m-d H:i:s'));
        return true;
    } catch (Exception $e) {
        $error = "Email sending failed: {$mail->ErrorInfo}";
        error_log($error . " at " . date('Y-m-d H:i:s'));
        return $error;
    }
}

function sendResetConfirmationEmail($email, $role, $new_password) {
    $template = file_get_contents(__DIR__ . '/templates/reset_confirmation.html');
    if ($template === false) {
        error_log("Template file not found: " . __DIR__ . '/templates/reset_confirmation.html at ' . date('Y-m-d H:i:s'));
        return "Template file not found";
    }
    $timestamp = date('Y-m-d h:i A T');
    $template = str_replace(
        ['{{email}}', '{{role}}', '{{new_password}}', '{{timestamp}}'],
        [htmlspecialchars($email), ucfirst($role), htmlspecialchars($new_password), $timestamp],
        $template
    );

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'mail.mustcu.or.ke';
        $mail->SMTPAuth = true;
        $mail->Username = 'notifications@mustcu.or.ke';
        $mail->Password = 'jkTD4SYzfRQuyx9JmV9u';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->SMTPDebug = 2; // Enable debug output (temporary for testing)
        $mail->Debugoutput = function($str, $level) {
            error_log("SMTP Debug: $str at " . date('Y-m-d H:i:s'));
        };

        $mail->setFrom('notifications@mustcu.or.ke', 'MUST CU');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Confirmation';
        $mail->Body = $template;
        $mail->send();
        error_log("Confirmation email sent successfully to $email at " . date('Y-m-d H:i:s'));
        return true;
    } catch (Exception $e) {
        $error = "Email sending failed: {$mail->ErrorInfo}";
        error_log($error . " at " . date('Y-m-d H:i:s'));
        return $error;
    }
}
function sendApprovalNotification($email, $type, $title) {
    $template = file_get_contents(__DIR__ . '/templates/approval_notification.html');
    $template = str_replace(['{{email}}', '{{type}}', '{{title}}'], [htmlspecialchars($email), $type, htmlspecialchars($title)], $template);

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'mail.mustcu.or.ke';
        $mail->SMTPAuth = true;
        $mail->Username = 'notifications@mustcu.or.ke';
        $mail->Password = 'jkTD4SYzfRQuyx9JmV9u';
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

function sendContactMessage($admin_email, $sender_name, $sender_email, $subject, $content) {
    $template = file_get_contents(__DIR__ . '/templates/contact_message.html');
    $template = str_replace(
        ['{{email}}', '{{sender}}', '{{subject}}', '{{content}}'],
        [htmlspecialchars($sender_email), htmlspecialchars($sender_name), htmlspecialchars($subject), htmlspecialchars($content)],
        $template
    );

    $mail = new PHPMailer(true);
    try {
        $mail->SMTPDebug = 2; // Enable debug output (0 = off, 1 = client, 2 = client+server)
        $mail->Debugoutput = function($str, $level) {
            error_log("PHPMailer [$level]: $str");
        };

        $mail->isSMTP();
        $mail->Host = 'mail.mustcu.or.ke';
        $mail->SMTPAuth = true;
        $mail->Username = 'secretary@mustcu.or.ke';
        $mail->Password = '3GF3W3ebeSa5vgZhbuDp';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('secretary@mustcu.or.ke', 'MUST CU');

        // Send to admin
        $mail->addAddress($admin_email);
        $mail->isHTML(true);
        $mail->Subject = 'New Contact Message';
        $mail->Body = $template;
        $mail->send();

        // Clear addresses for the next email
        $mail->clearAddresses();

        // Send confirmation to sender
        $confirm_body = "
            <html>
            <body>
                <p>Dear " . htmlspecialchars($sender_name) . ",</p>
                <p>Your message with subject '<strong>" . htmlspecialchars($subject) . "</strong>' has been successfully sent to the admin.</p>
                <p>Content:<br>" . htmlspecialchars($content) . "</p>
                <p>Thank you for contacting us!</p>
                <p>Best regards,<br>MUST CU Team</p>
            </body>
            </html>
        ";
        $mail->addAddress($sender_email);
        $mail->Subject = 'Confirmation: Your Message Has Been Received';
        $mail->Body = $confirm_body;
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
        $mail->Host = 'mail.mustcu.or.ke';
        $mail->SMTPAuth = true;
        $mail->Username = 'reminders@mustcu.or.ke';
        $mail->Password = '3UKYrhxJscV8WV9ytWUG';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('reminders@mustcu.or.ke', 'MUST CU');
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
        $mailer->Host = 'mail.mustcu.or.ke'; // Gmail SMTP server
        $mailer->SMTPAuth = true;
        $mailer->Username = 'secretary@mustcu.or.ke'; // Your Gmail address
        $mailer->Password = '3GF3W3ebeSa5vgZhbuDp'; // Your Gmail App Password (see Step 3)
        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->Port = 587; // TCP port for Gmail

        // Recipients
        $mailer->setFrom('secretary@mustcu.or.ke', 'MUST CU Admin Team');
        $mailer->addAddress($recipientEmail);

        // Content
        $subject = "Response to Your Message - MUST CU";
      

      $mailer->isHTML(true);
$mailer->Subject = $subject;

$body = '
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>' . htmlspecialchars($subject) . '</title>
</head>
<body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color:#f4f4f4;">

  <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 0 10px rgba(0,0,0,0.1);">
          
          <!-- Header -->
          <tr>
            <td style="background-color:#0207ba; padding:20px; color:white; text-align:center;">
              <h2 style="margin:0; font-size:20px;">Response to Your Message - MUST CU</h2>
            </td>
          </tr>

          <!-- Decorative Separator -->
          <tr>
            <td style="background: linear-gradient(to right, #ff7900, #fff000); height: 6px;"></td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:30px 20px; color:#333; font-size:16px; line-height:1.6;">
              <p>Hello,</p>
              <p>Your message has been responded to by an administrator of <strong>MUST CU</strong>.</p>
              <p><strong>Response:</strong><br>' . nl2br(htmlspecialchars($responseContent)) . '</p>
              <p>Regards,<br>MUST CU Admin Team</p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background-color:#f4f4f4; padding:20px; text-align:center; font-size:13px; color:#555;">
              <p style="margin:0;">&copy; ' . date('Y') . ' MUST CU. All rights reserved.<br>
              <a href="https://mustcu.or.ke" style="color:#0207ba; text-decoration:none;">mustcu.or.ke</a></p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>';

$mailer->Body = $body;
$mailer->AltBody = "Hello,\n\nYour message has been responded to by an administrator of MUST CU.\n\nResponse:\n" . strip_tags($responseContent) . "\n\nRegards,\nMUST CU Admin Team";


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

$messageContent = $message; // Original plain message you passed to the function

$message = '
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>' . htmlspecialchars($subject) . '</title>
</head>
<body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color:#f4f4f4;">

  <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 0 10px rgba(0,0,0,0.1);">
          
          <!-- Header -->
          <tr>
            <td style="background-color:#0207ba; padding:20px; color:white; text-align:center;">
              <h1 style="margin:0; font-size:24px;">' . htmlspecialchars($subject) . '</h1>
            </td>
          </tr>

          <!-- Decorative Separator -->
          <tr>
            <td style="background: linear-gradient(to right, #ff7900, #fff000); height: 6px;"></td>
          </tr>

          <!-- Message Body -->
          <tr>
            <td style="padding:30px 20px; color:#333; font-size:16px; line-height:1.6;">
              ' . $messageContent . '
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background-color:#f4f4f4; padding:20px; text-align:center; font-size:13px; color:#555;">
              <p style="margin:0;">
                &copy; ' . date('Y') . ' CU Ministries. All rights reserved.<br>
                <a href="https://cumministries.org" style="color:#0207ba; text-decoration:none;">cumministries.org</a>
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>';

$mail->Body = $message;
$mail->AltBody = strip_tags($messageContent);


        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
        return false;
    }
}

$smtpAccounts = [
    [
        'smtp_host' => 'mail.mustcu.or.ke',
        'smtp_username' => 'artsandmedia@mustcu.or.ke',
        'smtp_password' => 'ZMWRc93TUZxubcHq94vk',
        'smtp_secure' => 'tls',
        'smtp_port' => 587
    ],
    [
        'smtp_host' => 'mail.mustcu.or.ke',
        'smtp_username' => 'music@mustcu.or.ke',
        'smtp_password' => 'n9QapMafGjJ3UPnunePW',
        'smtp_secure' => 'tls',
        'smtp_port' => 587
    ],
    [
        'smtp_host' => 'mail.mustcu.or.ke',
        'smtp_username' => 'support@mustcu.or.ke',
        'smtp_password' => 'Z2bChFkXDqfrMUhkKAGG',
        'smtp_secure' => 'tls',
        'smtp_port' => 587
    ],
    [
        'smtp_host' => 'mail.mustcu.or.ke',
        'smtp_username' => 'hospitality@mustcu.or.ke',
        'smtp_password' => 'jte3BmBTLTScsWsfA2us',
        'smtp_secure' => 'tls',
        'smtp_port' => 587
    ],
    // Add more SMTP accounts if needed
];
?>