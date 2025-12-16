<?php
// approve_email.php
require_once '../shared/config/config.php';
require_once '../vendor/autoload.php';
session_start();

use PHPMailer\PHPMailer\PHPMailer;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access denied.");
}

$id = $_GET['id'] ?? 0;
$action = $_GET['action'] ?? '';

if (!$id || !in_array($action, ['approve', 'reject'])) {
    die("Invalid request.");
}

$stmt = $pdo->prepare("SELECT * FROM emails WHERE id = ? AND status = 'pending'");
$stmt->execute([$id]);
$email = $stmt->fetch();

if (!$email) {
    die("Email not found or already processed.");
}

// --- APPROVE: SEND EMAIL ---
if ($action === 'approve') {
    preg_match('/Ministry: ([^ ]+)/', $email['audience'], $m1);
    preg_match('/Year: (\d+)/', $email['audience'], $m2);
    $ministry = $m1[1] ?? '';
    $year = $m2[1] ?? '';

    $query = "SELECT DISTINCT email FROM leaders WHERE email IS NOT NULL AND email != '' ";
    $params = []; $conds = [];
    if ($ministry) { $conds[] = "ministry = ?"; $params[] = $ministry; }
    if ($year)     { $conds[] = "year = ?";     $params[] = $year; }
    if ($conds) $query .= "AND " . implode(' AND ', $conds);

    $recipients = $pdo->prepare($query)->execute($params) ? $pdo->query($query)->fetchAll(PDO::FETCH_COLUMN) : [];

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'mustcu20@gmail.com';
    $mail->Password = 'xebp hmpt cfhh inrv';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->setFrom('mustcu20@gmail.com', 'MUST CU');
    $mail->isHTML(true);
    $mail->Subject = $email['subject'];

    $mail->Body = "
    <!DOCTYPE html>
    <html>
    <head><meta charset='UTF-8'>
    <style>
        body { font-family: Arial; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .header { background: #1a5fb4; color: white; padding: 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .body { padding: 30px; color: #333; line-height: 1.6; }
        .footer { background: #f0f0f0; padding: 15px; text-align: center; font-size: 12px; color: #666; }
    </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'><h1>MUST Christian Union</h1></div>
            <div class='body'>
                <p>Dear Member,</p>
                <p>" . nl2br(htmlspecialchars($email['content'])) . "</p>
                <p><strong>Ministry:</strong> " . ($ministry ?: 'All') . "<br>
                   <strong>Year:</strong> " . ($year ?: 'All') . "</p>
                <hr>
                <p>Stay blessed,<br><strong>MUST CU Leadership</strong></p>
            </div>
            <div class='footer'>© " . date('Y') . " MUST Christian Union | <a href='https://mustcu.or.ke'>mustcu.or.ke</a></div>
        </div>
    </body>
    </html>";

    foreach ($recipients as $to) {
        $mail->addAddress($to);
        $mail->send();
        $mail->clearAddresses();
    }

    $pdo->prepare("UPDATE emails SET status = 'sent', approved_by = ?, approved_at = NOW() WHERE id = ?")
        ->execute([$_SESSION['user_id'], $id]);

    $msg = "Email sent to " . count($recipients) . " recipients.";

// --- REJECT ---
} else {
    $pdo->prepare("UPDATE emails SET status = 'rejected', approved_by = ?, approved_at = NOW() WHERE id = ?")
        ->execute([$_SESSION['user_id'], $id]);
    $msg = "Email rejected.";
}

echo "<div class='alert alert-success'>$msg</div>";
echo "<a href='admin_emails.php' class='btn btn-primary'>Back to Emails</a>";