<?php
require_once '../shared/config/config.php';
require_once '../vendor/autoload.php';
session_start();

use PHPMailer\PHPMailer\PHPMailer;

// --- AUTH ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'leader') {
    header('Location: index.php');
    exit;
}

$errors = [];
$success = '';

// --- SMTP ---
$gmail_user = 'mustcu20@gmail.com';
$gmail_pass = 'xebp hmpt cfhh inrv';
$from_name  = 'MUST CU Updates';

// --- GET FILTERS ---
$ministries = $pdo->query("SELECT DISTINCT name FROM groups WHERE type = 'Ministry' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
$years = $pdo->query("SELECT DISTINCT year FROM leaders WHERE year IS NOT NULL ORDER BY year DESC")->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject  = trim($_POST['subject'] ?? '');
    $content  = trim($_POST['content'] ?? '');
    $ministry = $_POST['ministry'] ?? '';
    $year     = $_POST['year'] ?? '';

    if (empty($subject) || empty($content)) {
        $errors[] = 'Subject and content are required.';
    } elseif ($ministry === '' && $year === '') {
        $errors[] = 'Select at least one Ministry or Year.';
    } else {
        // --- COUNT RECIPIENTS ---
        $query = "SELECT COUNT(DISTINCT email) FROM leaders WHERE email IS NOT NULL AND email != '' ";
        $params = []; $conds = [];
        if ($ministry) { $conds[] = "ministry = ?"; $params[] = $ministry; }
        if ($year)     { $conds[] = "year = ?";     $params[] = $year; }
        if ($conds) $query .= "AND " . implode(' AND ', $conds);

        $count = $pdo->prepare($query)->execute($params) ? $pdo->query("SELECT FOUND_ROWS()")->fetchColumn() : 0;
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $recipient_count = $stmt->fetchColumn();

        if ($recipient_count == 0) {
            $errors[] = "No recipients found.";
        } else {
            $audience = trim(($ministry ? "Ministry: $ministry" : "") . " " . ($year ? "Year: $year" : ""));

            // --- SAVE TO DB (pending) ---
            $stmt = $pdo->prepare("
                INSERT INTO emails 
                (creator_id, creator_type, subject, content, audience, status, submitted_at)
                VALUES (?, 'leader', ?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([$_SESSION['user_id'], $subject, $content, $audience]);

            $email_id = $pdo->lastInsertId();

            // --- SEND APPROVAL EMAIL TO ADMINS ---
            $admins = $pdo->query("SELECT email FROM admins WHERE position IN ('secretary', 'vice secretary') AND status = 'active'")->fetchAll(PDO::FETCH_COLUMN);

            $approval_url = "https://leaders.mustcu.or.ke/leaders/approve_email.php?id=$email_id";

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = $gmail_user;
                $mail->Password = $gmail_pass;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                $mail->setFrom($gmail_user, $from_name);
                $mail->isHTML(true);
                $mail->Subject = "Approve Email: $subject";

                $mail->Body = "
                <h3>Email Approval Needed</h3>
                <p><strong>Leader:</strong> {$_SESSION['user_id']}</p>
                <p><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>
                <p><strong>Recipients:</strong> $recipient_count</p>
                <p><strong>Audience:</strong> $audience</p>
                <hr>
                <p><strong>Message:</strong></p>
                <div style='background:#f9f9f9;padding:15px;border-left:4px solid #1a5fb4;'>
                    " . nl2br(htmlspecialchars($content)) . "
                </div>
                <hr>
                <p>
                    <a href='$approval_url&action=approve' style='background:#28a745;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Approve</a>
                    <a href='$approval_url&action=reject' style='background:#dc3545;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;margin-left:10px;'>Reject</a>
                </p>
                ";

                foreach ($admins as $admin) {
                    $mail->addAddress($admin);
                    $mail->send();
                    $mail->clearAddresses();
                }

                $success = "Submitted for approval. $recipient_count recipient(s) will be notified after approval.";

            } catch (Exception $e) {
                $errors[] = "Failed to send approval request: " . $mail->ErrorInfo;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Email - MUST CU</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <h2 class="text-center mb-4">Send Email Update</h2>

                <?php if ($errors): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $e): ?><p><?= htmlspecialchars($e) ?></p><?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>

                <form method="POST" class="card p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ministry</label>
                            <select name="ministry" class="form-select">
                                <option value="">All Ministries</option>
                                <?php foreach ($ministries as $m): ?>
                                    <option value="<?= htmlspecialchars($m) ?>" <?= ($_POST['ministry'] ?? '') === $m ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($m) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Year</label>
                            <select name="year" class="form-select">
                                <option value="">All Years</option>
                                <?php foreach ($years as $y): ?>
                                    <option value="<?= $y ?>" <?= ($_POST['year'] ?? '') == $y ? 'selected' : '' ?>>
                                        <?= $y ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Subject <span class="text-muted">(Recipients: <strong id="count">0</strong>)</span></label>
                        <input type="text" name="subject" class="form-control" value="<?= $_POST['subject'] ?? '' ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="content" class="form-control" rows="6" required><?= $_POST['content'] ?? '' ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-warning w-100">
                        Submit for Approval
                    </button>
                </form>

                <div class="mt-3 text-center text-muted small">
                    <p>Emails require admin approval before sending.</p>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('select[name="ministry"], select[name="year"]').forEach(sel => {
            sel.addEventListener('change', updateCount);
        });

        function updateCount() {
            const form = new FormData();
            form.append('ministry', document.querySelector('select[name="ministry"]').value);
            form.append('year', document.querySelector('select[name="year"]').value);

            fetch('api_recipients.php', {
                method: 'POST',
                body: form
            })
            .then(r => r.json())
            .then(d => document.getElementById('count').textContent = d.count)
            .catch(() => document.getElementById('count').textContent = '?');
        }
        updateCount();
    </script>
</body>
</html>