<?php
// Attendance.php – 100% WORKING, NO NOTICES
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// --- INCLUDE CONFIG & PHPMailer ---
require_once '../shared/config/config.php';
require_once '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

// --- 1. SESSION CHECK (using your existing session keys) ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'leader') {
    header('Location: index.php');
    exit;
}

// --- 2. GET LEADER FROM DATABASE (using user_id) ---
$leader_id = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT name, email FROM leaders WHERE id = ?");
$stmt->execute([$leader_id]);
$leader = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$leader) {
    die("Leader not found. Please contact admin.");
}

// --- 3. SMTP SETTINGS ---
$smtp_host = 'smtp.gmail.com';
$smtp_port = 587;
$smtp_user = 'mustcu20@gmail.com';
$smtp_pass = 'xebp hmpt cfhh inrv';   // CHANGE THIS!
$smtp_from = 'support@mustcu.or.ke';
$smtp_name = 'MUST CU Attendance';

// --- 4. GET GROUP CATEGORIES ---
$types = $pdo->query("SELECT DISTINCT type FROM groups ORDER BY type")
             ->fetchAll(PDO::FETCH_COLUMN);

// --- 5. FORM PROCESSING ---
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $group_id        = (int)($_POST['group_id'] ?? 0);
    $event_name      = trim($_POST['event_name'] ?? '');
    $activity_date   = $_POST['activity_date'] ?? '';
    $starting_time   = $_POST['starting_time'] ?? '';
    $ending_time     = !empty($_POST['ending_time']) ? $_POST['ending_time'] : null;
    $total_attendees = (int)($_POST['total_attendees'] ?? 0);
    $comments        = trim($_POST['comments'] ?? '');

    // Prevent duplicate
    $check = $pdo->prepare("SELECT id FROM attendance WHERE group_id = ? AND activity_date = ?");
    $check->execute([$group_id, $activity_date]);
    if ($check->fetchColumn()) {
        $error = 'Attendance already submitted for this group on this date!';
    } else {
        try {
            // Insert attendance
            $ins = $pdo->prepare("
                INSERT INTO attendance 
                (group_id, event_name, activity_date, starting_time, ending_time,
                 total_attendees, leader_name, leader_email, comments)
                VALUES (?,?,?,?,?,?,?,?,?)
            ");
            $ins->execute([
                $group_id, $event_name, $activity_date, $starting_time,
                $ending_time, $total_attendees,
                $leader['name'], $leader['email'], $comments
            ]);

            // Get group name
            $grpStmt = $pdo->prepare("SELECT name FROM groups WHERE id = ?");
            $grpStmt->execute([$group_id]);
            $group_name = $grpStmt->fetchColumn() ?: 'Unknown Group';

            // SEND EMAIL
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $smtp_host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtp_user;
            $mail->Password   = $smtp_pass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $smtp_port;

            $mail->setFrom($smtp_from, $smtp_name);
            $mail->addAddress($leader['email'], $leader['name']);
            $mail->Subject = "Attendance Submitted: $group_name – $activity_date";
            $mail->isHTML(true);

           $mail->Body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
        <div style='background-color: #0207ba; padding: 20px; text-align: center;'>
            <h2 style='color: white; margin: 0;'>Attendance Confirmed</h2>
        </div>
        
        <div style='padding: 25px; background-color: #f8f9fa;'>
            <p>Dear <strong style='color: #0207ba;'>{$leader['name']}</strong>,</p>
            <p>Your attendance record has been successfully saved:</p>
            
            <div style='background: white; border-radius: 8px; padding: 20px; margin: 20px 0; border-left: 4px solid #ff7900;'>
                <table width='100%' cellpadding='10' cellspacing='0' style='border-collapse: collapse;'>
                    <tr style='background-color: #f0f2ff;'>
                        <td width='30%' style='border-bottom: 2px solid #0207ba;'><strong>Group</strong></td>
                        <td style='border-bottom: 2px solid #0207ba;'>" . htmlspecialchars($group_name) . "</td>
                    </tr>
                    <tr>
                        <td style='border-bottom: 1px solid #eee;'><strong>Event</strong></td>
                        <td style='border-bottom: 1px solid #eee;'>" . htmlspecialchars($event_name) . "</td>
                    </tr>
                    <tr style='background-color: #f0f2ff;'>
                        <td style='border-bottom: 1px solid #eee;'><strong>Date</strong></td>
                        <td style='border-bottom: 1px solid #eee;'>$activity_date</td>
                    </tr>
                    <tr>
                        <td style='border-bottom: 1px solid #eee;'><strong>Start Time</strong></td>
                        <td style='border-bottom: 1px solid #eee;'>$starting_time</td>
                    </tr>
                    <tr style='background-color: #f0f2ff;'>
                        <td style='border-bottom: 1px solid #eee;'><strong>End Time</strong></td>
                        <td style='border-bottom: 1px solid #eee;'>" . ($ending_time ?: '—') . "</td>
                    </tr>
                    <tr>
                        <td><strong>Total Attendees</strong></td>
                        <td>
                            <span style='color: #ff7900; font-size: 18px; font-weight: bold;'>
                                $total_attendees
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div style='text-align: center; margin: 25px 0; padding: 15px; background-color: #ff7900; border-radius: 5px;'>
                <p style='color: #0207ba; font-size: 16px; margin: 0;'>
                    <strong>Thank you for your excellent leadership!</strong>
                </p>
            </div>
        </div>
        
        <div style='background-color: #0207ba; padding: 15px; text-align: center;'>
            <small style='color: white;'>
                MUST Christian Union | 
                <a href='https://mustcu.or.ke' style='color: #ff7900; text-decoration: none;'>mustcu.or.ke</a>
            </small>
        </div>
    </div>
";

            $mail->send();
            $success = "Submitted! Email sent to <strong>{$leader['email']}</strong>";

        } catch (Exception $e) {
            $error = "Submitted, but email failed: " . ($mail->ErrorInfo ?? 'Unknown error');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CU Attendance</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>.container{max-width:650px;margin-top:2rem;}</style>
</head>
<body>
<?php include 'includes/header.php'; ?>
<div class="container card p-4">

  <h3 class="text-center mb-4 text-primary">Submit Attendance</h3>
  <p class="text-end text-muted">Logged in: <strong><?= htmlspecialchars($leader['name']) ?></strong></p>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Category *</label>
      <select id="type" class="form-select" onchange="loadGroups()" required>
        <option value="">-- Select Category --</option>
        <?php foreach ($types as $t): ?>
          <option value="<?= htmlspecialchars($t) ?>"><?= ucfirst($t) ?>s</option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Group *</label>
      <select id="group_id" name="group_id" class="form-select" required>
        <option value="">-- Select category first --</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Event / Meeting Name *</label>
      <input type="text" name="event_name" class="form-control" required>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Date *</label>
        <input type="date" name="activity_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
      </div>
      <div class="col-md-3 mb-3">
        <label class="form-label">Starting Time *</label>
        <input type="time" name="starting_time" class="form-control" required>
      </div>
      <div class="col-md-3 mb-3">
        <label class="form-label">Ending Time</label>
        <input type="time" name="ending_time" class="form-control">
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Total Attendees *</label>
      <input type="number" name="total_attendees" class="form-control" min="1" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Comments (optional)</label>
      <textarea name="comments" class="form-control" rows="2"></textarea>
    </div>

    <button type="submit" class="btn btn-primary w-100">Submit & Send Email</button>
  </form>

</div>

<script>
function loadGroups() {
  const type = document.getElementById('type').value;
  const sel = document.getElementById('group_id');
  sel.innerHTML = '<option>Loading...</option>';

  fetch('api.php?type=' + encodeURIComponent(type))
    .then(r => r.json())
    .then(data => {
      sel.innerHTML = '<option value="">-- Select Group --</option>';
      data.forEach(g => sel.add(new Option(g.name, g.id)));
    })
    .catch(() => sel.innerHTML = '<option>Error loading groups</option>');
}
</script>
</body>
</html>