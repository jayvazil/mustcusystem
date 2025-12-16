<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'includes/db_connect.php';
require_once '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Generate a CSRF token if one doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// SMTP Configuration
function sendPositionEmail($to, $subject, $message) {
    $mail = new PHPMailer(true);
   
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Change to your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'mustcu20@gmail.com'; // Your SMTP username
        $mail->Password = 'xebp hmpt cfhh inrv'; // Your SMTP password (use app password for Gmail)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
       
        // Recipients
        $mail->setFrom('noreply@mustcu.or.ke', 'MUST CU');
        $mail->addAddress($to);
       
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;
       
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

 $errors = [];
 $success = '';

// Handle add new leader
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_leader'])) {
    // CSRF validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $member_id = filter_input(INPUT_POST, 'new_leader_id', FILTER_VALIDATE_INT);
        $position = filter_input(INPUT_POST, 'new_position', FILTER_SANITIZE_STRING);
        $docket = filter_input(INPUT_POST, 'new_docket', FILTER_SANITIZE_STRING);
        if (empty($member_id) || empty($position) || empty($docket)) {
            $errors[] = 'All fields are required to add a new leader.';
        } else {
            try {
                // Fetch all required member details from the members table
                $stmt = $pdo->prepare("SELECT id, name, email, phone, year, course, completion_year, ministry, password, reset_token, reset_expiry, submitted_at FROM members WHERE id = ?");
                $stmt->execute([$member_id]);
                $member = $stmt->fetch();
                if ($member) {
                    // Check if leader already exists BUT ONLY IF ACTIVE
                    $stmt = $pdo->prepare("SELECT id, position FROM leaders WHERE id = ? AND status = 'active'");
                    $stmt->execute([$member_id]);
                    $existing_leader = $stmt->fetch();
                    if ($existing_leader) {
                        $errors[] = "This member is already an active leader ({$existing_leader['position']}).";
                    } else {
                        // Insert into leaders with all the additional fields
                        $stmt = $pdo->prepare("INSERT INTO leaders (id, name, email, phone, year, course, completion_year, ministry, position, docket, status, start_date, password, reset_token, reset_expiry, submitted_at, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW(), ?, ?, ?, ?, 'leader')");
                        $stmt->execute([
                            $member['id'], 
                            $member['name'], 
                            $member['email'], 
                            $member['phone'], 
                            $member['year'], 
                            $member['course'], 
                            $member['completion_year'], 
                            $member['ministry'], 
                            $position, 
                            $docket,
                            $member['password'],
                            $member['reset_token'],
                            $member['reset_expiry'],
                            $member['submitted_at']
                        ]);
                        
                        // Insert into position_history
                        $stmt = $pdo->prepare("INSERT INTO position_history (user_id, position, docket, start_date, status, inherited_from) VALUES (?, ?, ?, NOW(), 'active', NULL)");
                        $stmt->execute([$member_id, $position, $docket]);
                       
                        // Send welcome email
                        $subject = "Congratulations! You've Been Appointed as " . $position;
                        $message = "
                        <html>
                        <head>
                            <style>
                                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                                .header { background: #0207ba; color: #fff000; padding: 20px; text-align: center; }
                                .content { background: #f9f9f9; padding: 20px; }
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <div class='header'>
                                    <h1>Leadership Appointment</h1>
                                </div>
                                <div class='content'>
                                    <h2>Dear " . htmlspecialchars($member['name']) . ",</h2>
                                    <p>You have been appointed as " . htmlspecialchars($position) . " at MUST Christian Union.</p>
                                    <p><strong>Docket:</strong> " . htmlspecialchars($docket) . "</p>
                                    <p><strong>Course:</strong> " . htmlspecialchars($member['course']) . "</p>
                                    <p><strong>Year:</strong> " . htmlspecialchars($member['year']) . "</p>
                                    <p><strong>Ministry:</strong> " . htmlspecialchars($member['ministry']) . "</p>
                                    <p>Welcome to the leadership team!</p>
                                    <p><strong>MUST CU Administration</strong></p>
                                </div>
                            </div>
                        </body>
                        </html>
                        ";
                       
                        if (sendPositionEmail($member['email'], $subject, $message)) {
                            $success = 'New leader added successfully. Welcome email sent.';
                        } else {
                            $success = 'New leader added successfully. (Email notification failed)';
                        }
                    }
                } else {
                    $errors[] = 'Member not found.';
                }
            } catch (PDOException $e) {
                $errors[] = 'Failed to add new leader: ' . $e->getMessage();
                error_log('Add leader error: ' . $e->getMessage());
            }
        }
    }
}

// Handle admin succession
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_id'])) {
    // CSRF validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $admin_id = filter_input(INPUT_POST, 'admin_id', FILTER_VALIDATE_INT);
        $successor_id = filter_input(INPUT_POST, 'admin_successor_id', FILTER_VALIDATE_INT);
        $position = filter_input(INPUT_POST, 'admin_position', FILTER_SANITIZE_STRING);
        if (empty($admin_id) || empty($successor_id) || empty($position)) {
            $errors[] = 'All fields are required for admin succession.';
        } else {
            try {
                // Get successor details from members table
                $stmt = $pdo->prepare("SELECT id, name, email, phone, year, course, completion_year, ministry, password, reset_token, reset_expiry, submitted_at FROM members WHERE id = ?");
                $stmt->execute([$successor_id]);
                $successor = $stmt->fetch();
                if ($successor) {
                    // Get current admin details
                    $stmt = $pdo->prepare("SELECT username, password, name, email FROM admins WHERE id = ?");
                    $stmt->execute([$admin_id]);
                    $current_admin = $stmt->fetch();
                    if ($current_admin) {
                        // Update the admin record with the new person's information
                        // BUT keep the same username and password
                        $stmt = $pdo->prepare("UPDATE admins SET name = ?, email = ?, position = ? WHERE id = ?");
                        $stmt->execute([
                            $successor['name'],
                            $successor['email'],
                            $position,
                            $admin_id
                        ]);
                        
                        // Add the successor to the leaders table with all the additional fields
                        $stmt = $pdo->prepare("INSERT INTO leaders (id, name, email, phone, year, course, completion_year, ministry, position, docket, status, start_date, password, reset_token, reset_expiry, submitted_at, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW(), ?, ?, ?, ?, 'leader')");
                        $stmt->execute([
                            $successor['id'], 
                            $successor['name'], 
                            $successor['email'], 
                            $successor['phone'], 
                            $successor['year'], 
                            $successor['course'], 
                            $successor['completion_year'], 
                            $successor['ministry'], 
                            $position, 
                            'Admin Position',
                            $successor['password'],
                            $successor['reset_token'],
                            $successor['reset_expiry'],
                            $successor['submitted_at']
                        ]);
                        
                        // End any active position history for the successor
                        $stmt = $pdo->prepare("UPDATE position_history SET end_date = NOW(), status = 'inactive' WHERE user_id = ? AND status = 'active'");
                        $stmt->execute([$successor_id]);
                        
                        // Insert new position history record
                        $stmt = $pdo->prepare("INSERT INTO position_history (user_id, position, docket, start_date, status, inherited_from) VALUES (?, ?, ?, NOW(), 'active', ?)");
                        $stmt->execute([$successor_id, $position, 'Admin Position', $admin_id]);
                       
                        // Send notification email
                        $subject = "Congratulations! You've Been Appointed as Admin";
                        $message = "
                        <html>
                        <head>
                            <style>
                                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                                .header { background: #0207ba; color: #fff000; padding: 20px; text-align: center; }
                                .content { background: #f9f9f9; padding: 20px; }
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <div class='header'>
                                    <h1>Admin Appointment</h1>
                                </div>
                                <div class='content'>
                                    <h2>Dear " . htmlspecialchars($successor['name']) . ",</h2>
                                    <p>You have been appointed as " . htmlspecialchars($position) . " at MUST Christian Union.</p>
                                    <p><strong>Course:</strong> " . htmlspecialchars($successor['course']) . "</p>
                                    <p><strong>Year:</strong> " . htmlspecialchars($successor['year']) . "</p>
                                    <p><strong>Ministry:</strong> " . htmlspecialchars($successor['ministry']) . "</p>
                                    <p>Your login credentials remain the same. Please use your existing username and password to access the admin panel.</p>
                                    <p><strong>MUST CU Administration</strong></p>
                                </div>
                            </div>
                        </body>
                        </html>
                        ";
                       
                        if (sendPositionEmail($successor['email'], $subject, $message)) {
                            $success = 'Admin succession completed successfully. ' . $successor['name'] . ' is now the admin. Login credentials remain unchanged. Notification email sent.';
                        } else {
                            $success = 'Admin succession completed successfully. ' . $successor['name'] . ' is now the admin. Login credentials remain unchanged. (Email notification failed)';
                        }
                    } else {
                        $errors[] = 'Admin to replace not found.';
                    }
                } else {
                    $errors[] = 'Successor not found for admin succession.';
                }
            } catch (PDOException $e) {
                $errors[] = 'Failed to complete admin succession: ' . $e->getMessage();
                error_log('Admin succession error: ' . $e->getMessage());
            }
        }
    }
}

// Pagination settings
 $rows_per_page_options = [50, 100, 250, 300];
 $rows_per_page = isset($_GET['rows']) && in_array((int)$_GET['rows'], $rows_per_page_options) ? (int)$_GET['rows'] : 50;
 $page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
 $offset = ($page - 1) * $rows_per_page;

// Fetch total number of succession records
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM position_history");
    $total_rows = $stmt->fetchColumn();
    $total_pages = ceil($total_rows / $rows_per_page);
} catch (PDOException $e) {
    $errors[] = 'Failed to fetch succession count: ' . $e->getMessage();
    error_log('Succession count error: ' . $e->getMessage());
    $total_rows = 0;
    $total_pages = 1;
}

// Fetch succession history with pagination
try {
    $stmt = $pdo->prepare("
        SELECT ph.user_id, ph.position, ph.docket, ph.start_date, ph.end_date, ph.status, ph.inherited_from, m.name AS successor_name
        FROM position_history ph
        JOIN members m ON ph.user_id = m.id
        ORDER BY ph.start_date DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$rows_per_page, $offset]);
    $successions = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = 'Failed to fetch succession history: ' . $e->getMessage();
    error_log('Succession history error: ' . $e->getMessage());
    $successions = [];
}

// Fetch admins for the admin selection dropdown
try {
    $stmt = $pdo->prepare("SELECT id, username, position FROM admins WHERE role = 'admin' AND status = 'active'");
    $stmt->execute();
    $admins = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = 'Failed to fetch admins: ' . $e->getMessage();
    error_log('Admins fetch error: ' . $e->getMessage());
    $admins = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Hereditary Positions - MUST CU</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
            border-radius: 10px;
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
        .centered-form {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
            padding: 2rem;
            max-width: 600px;
            height:500px;
            width: 100%;
            margin: 0 auto 2rem auto;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .centered-form::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #0207ba, #ff7900, #fff000);
        }
        .centered-form:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.25);
        }
        .centered-form .form-control,
        .centered-form .form-select {
            max-width: 500px;
            width: 100%;
            border: 2px solid #0207ba;
            border-radius: 8px;
            padding: 0.75rem;
            font-size: 0.95rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .centered-form .form-control:focus,
        .centered-form .form-select:focus {
            border-color: #ff7900;
            box-shadow: 0 0 0 3px rgba(255, 121, 0, 0.3);
            outline: none;
        }
        .centered-form .form-label {
            font-size: 0.95rem;
            font-weight: 600;
            color: #0207ba;
            margin-bottom: 0.5rem;
        }
        .centered-form .btn-primary {
            background: linear-gradient(135deg, #0207ba, #001f7a);
            border: none;
            padding: 0.85rem 1.5rem;
            font-weight: 600;
            border-radius: 8px;
            color: #fff000;
            transition: background 0.3s ease, transform 0.2s ease;
        }
        .centered-form .btn-primary:hover {
            background: linear-gradient(135deg, #ff7900, #e66900);
            transform: scale(1.05);
        }
        .centered-form .btn-success {
            background: linear-gradient(135deg, #198754, #157347);
            border: none;
            padding: 0.85rem 1.5rem;
            font-weight: 600;
            border-radius: 8px;
            color: white;
            transition: background 0.3s ease, transform 0.2s ease;
        }
        .centered-form .btn-success:hover {
            background: linear-gradient(135deg, #146c43, #0f5132);
            transform: scale(1.05);
        }
        .centered-form .fa {
            margin-right: 0.5rem;
        }
        .centered-table-container {
            display: flex;
            justify-content: center;
            width: 100%;
        }
        .centered-table {
            max-width: 1000px;
            width: 100%;
            text-align: center;
            border-collapse: collapse;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }
        .centered-table::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #0207ba, #ff7900, #fff000);
        }
        .centered-table th,
        .centered-table td {
            text-align: center;
            padding: 0.85rem;
            font-size: 0.95rem;
        }
        .centered-table thead {
            background: linear-gradient(135deg, #0207ba, #001f7a);
            color: #fff000;
        }
        .centered-table tbody tr {
            transition: background 0.3s ease;
        }
        .centered-table tbody tr:hover {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        }
        .form-label {
            font-size: 0.9rem;
            font-weight: 500;
            color: #374151;
        }
        .form-control, .form-select {
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.75rem;
            font-size: 0.9rem;
        }
        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 1.5rem;
        }
        .pagination .page-link {
            color: #0207ba;
            border-radius: 6px;
            margin: 0 3px;
            transition: background 0.3s ease, color 0.3s ease;
        }
        .pagination .page-link:hover {
            background: #ff7900;
            color: #ffffff;
        }
        .pagination .page-item.active .page-link {
            background: #0207ba;
            border-color: #0207ba;
            color: #fff000;
        }
        .select2-container .select2-selection--single {
            border: 2px solid #0207ba;
            border-radius: 8px;
            height: 38px;
            padding: 0.5rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #0207ba;
            line-height: 28px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 34px;
        }
        .section-header {
            background: linear-gradient(135deg, #0207ba, #001f7a);
            color: #fff000;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .form-section {
            margin-bottom: 3rem;
        }
        .admin-info {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            border: 2px solid #0207ba;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            display: none;
        }
        .admin-info h5 {
            color: #0207ba;
            margin-bottom: 0.5rem;
        }
        .status-active {
            color: #198754;
            font-weight: bold;
        }
        .status-inactive {
            color: #dc3545;
            font-weight: bold;
        }
        @media (max-width: 768px) {
            .centered-form {
                padding: 1.5rem;
            }
            .centered-table {
                max-width: 100%;
            }
        }
        @media (max-width: 576px) {
            .centered-form {
                padding: 1rem;
            }
            .centered-table th,
            .centered-table td {
                font-size: 0.85rem;
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container mt-5">
        <h2 class="mb-4"><i class="fas fa-briefcase"></i> Manage Positions</h2>
        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <!-- Add New Leader Form -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="mb-0"><i class="fas fa-user-plus"></i> Add New Leader</h3>
            </div>
            <form method="POST" class="card p-4 centered-form">
                <input type="hidden" name="add_leader" value="1">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="mb-3">
                    <label for="new_leader_id" class="form-label"><i class="fas fa-search"></i> Search Member (Name, Email or Phone)</label>
                    <select name="new_leader_id" id="new_leader_id" class="form-control select2" required>
                        <option value="">Search by name, email or phone</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Leader Position</label>
                        <select name="new_position" class="form-control" required>
                            <option value="" disabled selected>Select Position</option>
                            <option value="Chairperson">Chairperson</option>
                            <option value="Vice Chairperson">Vice Chairperson</option>
                            <option value="Secretary">Secretary</option>
                            <option value="Vice Secretary">Vice Secretary</option>
                            <option value="General Secretary">General Secretary</option>
                            <option value="Treasurer">Treasurer</option>
                            <option value="Coordinator">Coordinator</option>
                            <option value="Facilitator">Facilitator</option>
                            <option value="Committee Member">Committee Member</option>
                            <option value="Sub-Committee Member">Sub-Committee Member</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Leader Docket</label>
                        <select name="new_docket" class="form-control" required>
                            <option value="" disabled selected>Select Docket</option>
                            <option value="Executive">Executive</option>
                            <option value="Arts and Media">Arts and Media</option>
                            <option value="Creative">Creative</option>
                            <option value="Music Ministry">Music Ministry</option>
                            <option value="Sunday School">Sunday School</option>
                            <option value="Bible Study">Bible Study</option>
                            <option value="Non-residents">Non-residents</option>
                            <option value="Hospitality">Hospitality</option>
                            <option value="Missions and Evangelism">Missions and Evangelism</option>
                            <option value="Discipleship">Discipleship</option>
                            <option value="Treasury">Treasury</option>
                            <option value="Prayer">Prayer</option>
                            <option value="Best-p">Best-p</option>
                            <option value="FFC">FFC</option>
                            <option value="Choir">Choir</option>
                            <option value="IT and Publicity">IT and Publicity</option>
                            <option value="Praise and Worship">Praise and Worship</option>
                            <option value="Instrumentalist">Instrumentalist</option>
                            <option value="Sports">Sports</option>
                            <option value="Ushering">Ushering</option>
                            <option value="Decor">Decor</option>
                            <option value="Catering">Catering</option>
                            <option value="Editorial">Editorial</option>
                            <option value="Advocacy">Advocacy</option>
                            <option value="Welfare">Welfare</option>
                            <option value="Edeleafty">Edeleafty</option>
                            <option value="Vukafty">Vukafty</option>
                            <option value="Brothers">Brothers</option>
                            <option value="Sisters">Sisters</option>
                            <option value="Anzafty">Anzafty</option>
                            <option value="Outreach">Outreach</option>
                            <option value="Inreach">Inreach</option>
                            <option value="High School">High School</option>
                            <option value="Annual Mission Committee">Annual Mission Committee</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-success"><i class="fas fa-user-plus"></i> Add New Leader</button>
            </form>
        </div>
        <!-- 
        <div class="form-section">
            <div class="section-header">
                <h3 class="mb-0"><i class="fas fa-user-shield"></i> Succeed Admin</h3>
            </div>
            <form method="POST" class="card p-4 centered-form">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="mb-3">
                    <label for="admin_id" class="form-label"><i class="fas fa-user-shield"></i> Select Admin to Replace</label>
                    <select name="admin_id" id="admin_id" class="form-control" required onchange="fetchAdminDetails(this.value)">
                        <option value="">Select an admin</option>
                        <?php foreach ($admins as $admin): ?>
                            <option value="<?php echo $admin['id']; ?>" data-position="<?php echo htmlspecialchars($admin['position']); ?>">
                                <?php echo htmlspecialchars($admin['username'] . ' - ' . $admin['position']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="admin-info" id="adminInfo">
                    <h5><i class="fas fa-info-circle"></i> Current Admin Information</h5>
                    <p><strong>Position:</strong> <span id="currentAdminPosition"></span></p>
                    <p><strong>Username:</strong> <span id="currentAdminUsername"></span></p>
                </div>
                <div class="mb-3">
                    <label for="admin_successor_id" class="form-label"><i class="fas fa-user"></i> Search Successor (Name or Email)</label>
                    <select name="admin_successor_id" id="admin_successor_id" class="form-control select2" required>
                        <option value="">Search by name or email</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="admin_position" class="form-label"><i class="fas fa-briefcase"></i> Position</label>
                    <input type="text" name="admin_position" id="admin_position" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Succeed Admin</button>
            </form>
        </div>Admin Succession Form -->
        <!-- Succession History Table -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="mb-0"><i class="fas fa-history"></i> Position History</h3>
            </div>
            <div class="mb-3 text-center">
                <label for="rows_per_page" class="form-label">Rows per page:</label>
                <select id="rows_per_page" class="form-select w-auto d-inline-block" onchange="window.location.href='?page=1&rows=' + this.value">
                    <?php foreach ($rows_per_page_options as $option): ?>
                        <option value="<?php echo $option; ?>" <?php echo $rows_per_page == $option ? 'selected' : ''; ?>><?php echo $option; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="centered-table-container">
                <div class="table-responsive">
                    <table class="table table-bordered centered-table">
                        <thead>
                            <tr>
                                <th>Successor</th>
                                <th>Position</th>
                                <th>Docket</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($successions)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No succession records found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($successions as $succession): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($succession['successor_name']); ?></td>
                                        <td><?php echo htmlspecialchars($succession['position']); ?></td>
                                        <td><?php echo htmlspecialchars($succession['docket'] ?? 'N/A'); ?></td>
                                        <td><?php echo date('Y-m-d h:i A', strtotime($succession['start_date'])); ?></td>
                                        <td>
                                            <?php if ($succession['end_date']): ?>
                                                <?php echo date('Y-m-d h:i A', strtotime($succession['end_date'])); ?>
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="<?php echo $succession['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>">
                                                <?php echo htmlspecialchars(ucfirst($succession['status'])); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination-container">
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&rows=<?php echo $rows_per_page; ?>">Previous</a>
                            </li>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&rows=<?php echo $rows_per_page; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&rows=<?php echo $rows_per_page; ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="js/script.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2 for all search fields
            $('#admin_successor_id').select2({
                placeholder: "Search by name or email",
                allowClear: true,
                minimumInputLength: 2,
                ajax: {
                    url: 'search_members.php',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            search: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(function(member) {
                                return {
                                    id: member.id,
                                    text: member.name + ' (' + member.email + ')'
                                };
                            })
                        };
                    },
                    cache: true
                }
            });
            $('#new_leader_id').select2({
                placeholder: "Search by name, email or phone",
                allowClear: true,
                minimumInputLength: 2,
                ajax: {
                    url: 'search_members.php?include_phone=1',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            search: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(function(member) {
                                return {
                                    id: member.id,
                                    text: member.name + ' (' + member.email + ') - ' + (member.phone || 'No phone')
                                };
                            })
                        };
                    },
                    cache: true
                }
            });
            // Auto-dismiss alerts after 4 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 4000);
        });
        function fetchAdminDetails(adminId) {
            if (adminId) {
                // Get the selected option
                const selectedOption = document.querySelector('#admin_id option:checked');
                if (selectedOption) {
                    const position = selectedOption.getAttribute('data-position');
                    const username = selectedOption.text.split(' - ')[0];
                   
                    document.getElementById('admin_position').value = position || '';
                    document.getElementById('currentAdminPosition').textContent = position || 'Not specified';
                    document.getElementById('currentAdminUsername').textContent = username || 'Not specified';
                   
                    // Show admin info
                    document.getElementById('adminInfo').style.display = 'block';
                }
            } else {
                document.getElementById('admin_position').value = '';
                document.getElementById('adminInfo').style.display = 'none';
            }
        }
    </script>
</body>
</html>