<?php
require_once '../shared/config/config.php';
require_once '../vendor/autoload.php'; // Assuming PHPMailer is included via Composer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'leader') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';
$password_error = '';
$password_success = '';

try {
    // Fetch user details
    $stmt = $pdo->prepare("SELECT name, email, phone, year, course, position, docket FROM leaders WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        throw new Exception("User not found.");
    }

    // Function to send email notification
    function sendEmailNotification($toEmail, $toName, $subject, $body) {
        $mail = new PHPMailer(true);
        try {
            // SMTP configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'mulwaisaac851@gmail.com';
            $mail->Password = 'uiby qfze lwhn pvsz';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Sender and recipient
            $mail->setFrom('no-reply@yourdomain.com', 'Your System Name');
            $mail->addAddress($toEmail, $toName);

            // Email content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = strip_tags($body);

            $mail->send();
        } catch (Exception $e) {
            throw new Exception("Failed to send email notification: {$mail->ErrorInfo}");
        }
    }

    // Handle profile update submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $year = trim($_POST['year']);
        $course = trim($_POST['course']);
        $position = trim($_POST['position']);
        $docket = trim($_POST['docket']);

        // Validate inputs
        if (empty($name) || empty($email) || empty($phone) || empty($year) || empty($course) || empty($position) || empty($docket)) {
            throw new Exception("All profile fields are required.");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }

        // Update user details
        $stmt = $pdo->prepare("UPDATE leaders SET name = ?, email = ?, phone = ?, year = ?, course = ?, position = ?, docket = ? WHERE id = ?");
        $stmt->execute([$name, $email, $phone, $year, $course, $position, $docket, $user_id]);

        // Send email notification for profile update
        $emailBody = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f4f7fa; border-radius: 10px;">
                <div style="background-color: #0207ba; padding: 15px; text-align: center; border-radius: 10px 10px 0 0;">
                    <h2 style="color: #ffffff; margin: 0; font-size: 24px;">Profile Update Notification</h2>
                </div>
                <div style="padding: 20px; background-color: #ffffff; border-radius: 0 0 10px 10px;">
                    <p style="color: #333333; font-size: 16px;">Dear ' . htmlspecialchars($name) . ',</p>
                    <p style="color: #333333; font-size: 16px;">Your profile details have been updated successfully. Below are the updated details:</p>
                    <ul style="list-style: none; padding: 0; color: #333333; font-size: 15px;">
                        <li style="margin-bottom: 10px;"><strong style="color: #0207ba;">Name:</strong> ' . htmlspecialchars($name) . '</li>
                        <li style="margin-bottom: 10px;"><strong style="color: #0207ba;">Email:</strong> ' . htmlspecialchars($email) . '</li>
                        <li style="margin-bottom: 10px;"><strong style="color: #0207ba;">Phone:</strong> ' . htmlspecialchars($phone) . '</li>
                        <li style="margin-bottom: 10px;"><strong style="color: #0207ba;">Year of Study:</strong> ' . htmlspecialchars($year) . '</li>
                        <li style="margin-bottom: 10px;"><strong style="color: #0207ba;">Course:</strong> ' . htmlspecialchars($course) . '</li>
                        <li style="margin-bottom: 10px;"><strong style="color: #0207ba;">Position:</strong> ' . htmlspecialchars($position) . '</li>
                        <li style="margin-bottom: 10px;"><strong style="color: #0207ba;">Docket:</strong> ' . htmlspecialchars($docket) . '</li>
                    </ul>
                    <p style="color: #ff7900; font-size: 16px; font-weight: bold;">If you did not make these changes, please contact our support team immediately at <a href="mailto:support@yourdomain.com" style="color: #fff000; text-decoration: none;">support@yourdomain.com</a>.</p>
                    <p style="color: #333333; font-size: 16px;">Thank you,<br><span style="color: #0207ba; font-weight: bold;">Your System Name</span></p>
                </div>
                <div style="text-align: center; padding: 10px; font-size: 12px; color: #666666;">
                    <p>© ' . date('Y') . ' Your System Name. All rights reserved.</p>
                </div>
            </div>
        ';
        sendEmailNotification($email, $name, "Profile Updated Successfully", $emailBody);

        $success = "Profile updated successfully. A confirmation email has been sent.";
    }

    // Handle password update submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_password'])) {
        $current_password = trim($_POST['current_password']);
        $new_password = trim($_POST['new_password']);
        $confirm_password = trim($_POST['confirm_password']);

        // Validate password inputs
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            throw new Exception("All password fields are required.");
        }
        if ($new_password !== $confirm_password) {
            throw new Exception("New password and confirmation do not match.");
        }
        if (strlen($new_password) < 8) {
            throw new Exception("New password must be at least 8 characters long.");
        }

        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM leaders WHERE id = ?");
        $stmt->execute([$user_id]);
        $stored_password = $stmt->fetchColumn();

        if (!password_verify($current_password, $stored_password)) {
            throw new Exception("Current password is incorrect.");
        }

        // Update password
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE leaders SET password = ? WHERE id = ?");
        $stmt->execute([$new_password_hash, $user_id]);

        // Send email notification for password change
        $emailBody = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f4f7fa; border-radius: 10px;">
                <div style="background-color: #0207ba; padding: 15px; text-align: center; border-radius: 10px 10px 0 0;">
                    <h2 style="color: #ffffff; margin: 0; font-size: 24px;">Password Change Notification</h2>
                </div>
                <div style="padding: 20px; background-color: #ffffff; border-radius: 0 0 10px 10px;">
                    <p style="color: #333333; font-size: 16px;">Dear ' . htmlspecialchars($user['name']) . ',</p>
                    <p style="color: #333333; font-size: 16px;">Your password has been changed successfully on ' . date('F j, Y, g:i a') . '.</p>
                    <p style="color: #ff7900; font-size: 16px; font-weight: bold;">If you did not initiate this change, please contact our support team immediately at <a href="mailto:support@yourdomain.com" style="color: #fff000; text-decoration: none;">support@yourdomain.com</a> to secure your account.</p>
                    <p style="color: #333333; font-size: 16px;">Thank you,<br><span style="color: #0207ba; font-weight: bold;">Your System Name</span></p>
                </div>
                <div style="text-align: center; padding: 10px; font-size: 12px; color: #666666;">
                    <p>© ' . date('Y') . ' Your System Name. All rights reserved.</p>
                </div>
            </div>
        ';
        sendEmailNotification($user['email'], $user['name'], "Password Changed Successfully", $emailBody);

        $password_success = "Password updated successfully. A confirmation email has been sent.";
    }
} catch (Exception $e) {
    if (isset($_POST['update_password'])) {
        $password_error = "Error: " . $e->getMessage();
    } else {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<?php include 'includes/header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .content-wrapper {
            padding: 40px 0;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .profile-image-container {
            position: relative;
            margin-bottom: 20px;
        }
        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #ff7900;
            transition: transform 0.3s;
        }
        .profile-image:hover {
            transform: scale(1.05);
        }
        .form-label {
            font-weight: 500;
            color: #333;
        }
        .form-control {
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 10px;
        }
        .form-control:focus {
            border-color: #0207ba;
            box-shadow: 0 0 5px rgba(2, 7, 186, 0.5);
        }
        .btn-primary {
            background-color: #0207ba;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
        }
        .btn-primary:hover {
            background-color: #ff7900;
        }
        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .section-title {
            color: #0207ba;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .password-toggle {
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #0207ba;
            font-size: 14px;
        }
        .password-toggle:hover {
            color: #ff7900;
        }
        .input-group {
            position: relative;
        }
        /* Preloader styles */
        .preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .spinner {
            width: 60px;
            height: 60px;
            border: 6px solid #f4f7fa;
            border-top: 6px solid #0207ba;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @media (max-width: 768px) {
            .profile-image {
                width: 120px;
                height: 120px;
            }
            .spinner {
                width: 40px;
                height: 40px;
                border: 4px solid #f4f7fa;
                border-top: 4px solid #0207ba;
            }
        }
    </style>
</head>
<body>
    <!-- Preloader -->
    <div class="preloader" id="preloader">
        <div class="spinner"></div>
    </div>

    
        <div class="container">
            <h2 class="section-title text-center mb-5">My Profile</h2>
            
            <!-- Profile Update Feedback -->
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if ($password_error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($password_error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if ($password_success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($password_success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Profile Image and Name -->
                <div class="col-md-4 text-center">
                    <div class="card p-4">
                        <div class="profile-image-container">
                            <label for="clickable_image">
                                <img src="https://img.icons8.com/?size=100&id=bzanxGcmX3R8&format=png&color=000000" alt="Profile Image" class="profile-image">
                            </label>
                            <input type="file" id="clickable_image" style="display: none;" accept="image/*">
                        </div>
                        <h4 class="mt-3"><?php echo htmlspecialchars($user['name']); ?></h4>
                        <p class="text-muted"><?php echo htmlspecialchars($user['position']); ?></p>
                    </div>
                </div>

                <!-- Profile Update Form -->
                <div class="col-md-8">
                    <div class="card p-4">
                        <h4 class="section-title">Update Profile</h4>
                        <form action="profile.php" method="POST" enctype="multipart/form-data" id="profileForm">
                            <input type="hidden" name="update_profile" value="1">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" name="phone" id="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="year" class="form-label">Year of Study</label>
                                <input type="text" name="year" id="year" class="form-control" value="<?php echo htmlspecialchars($user['year']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="course" class="form-label">Course</label>
                                <input type="text" name="course" id="course" class="form-control" value="<?php echo htmlspecialchars($user['course']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="position" class="form-label">Position</label>
                                <input type="text" name="position" id="position" class="form-control" value="<?php echo htmlspecialchars($user['position']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="docket" class="form-label">Docket</label>
                                <input type="text" name="docket" id="docket" class="form-control" value="<?php echo htmlspecialchars($user['docket']); ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Update Profile</button>
                        </form>
                    </div>

                    <!-- Password Update Form -->
                    <div class="card p-4 mt-4">
                        <h4 class="section-title">Change Password</h4>
                        <form action="profile.php" method="POST" id="passwordForm">
                            <input type="hidden" name="update_password" value="1">
                            <div class="mb-3 input-group">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" name="current_password" id="current_password" class="form-control" required>
                                <span class="password-toggle" onclick="togglePassword('current_password', this)">Show</span>
                            </div>
                            <div class="mb-3 input-group">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" name="new_password" id="new_password" class="form-control" required>
                                <span class="password-toggle" onclick="togglePassword('new_password', this)">Show</span>
                            </div>
                            <div class="mb-3 input-group">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                                <span class="password-toggle" onclick="togglePassword('confirm_password', this)">Show</span>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Change Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(inputId, toggleElement) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                toggleElement.textContent = 'Hide';
            } else {
                input.type = 'password';
                toggleElement.textContent = 'Show';
            }
        }

        // Show preloader on form submission
        document.getElementById('profileForm').addEventListener('submit', function() {
            document.getElementById('preloader').style.display = 'flex';
        });

        document.getElementById('passwordForm').addEventListener('submit', function() {
            document.getElementById('preloader').style.display = 'flex';
        });

        // Hide preloader when page loads (in case of server response)
        window.addEventListener('load', function() {
            document.getElementById('preloader').style.display = 'none';
        });
    </script>
</body>
</html>

<?php include 'includes/footer.php'; ?>