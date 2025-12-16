<?php
// Include database configuration file
require_once 'config.php';




// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize inputs
    $name = htmlspecialchars(trim($_POST['name']));
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_NUMBER_INT);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $year = intval($_POST['year']);
    $course = htmlspecialchars(trim($_POST['course']));
    $completion_year = intval($_POST['completion_year']);
    $completion_month = sprintf("%02d", intval($_POST['completion_month']));
    $completion_date = "$completion_year-$completion_month";
    $ministry = htmlspecialchars(trim($_POST['ministry'] ?? ''));
    $submitted_at = date('Y-m-d H:i:s');

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        exit(json_encode(['success' => false, 'message' => 'Invalid email format.']));
    }

    // Check for existing user
    $checkSql = "SELECT 'member' AS type FROM members WHERE email = ?
                 UNION 
                 SELECT 'leader' AS type FROM leaders WHERE email = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param('ss', $email, $email);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $userType = ucfirst($row['type']);
        exit(json_encode(['success' => false, 'message' => "You are already registered as a $userType of MUSTCU with this email."]));
    }
       // Check for existing phone number
$checkPhoneSql = "SELECT 'member' AS type FROM members WHERE phone = ?
                 UNION 
                 SELECT 'leader' AS type FROM leaders WHERE phone = ?";
$checkPhoneStmt = $conn->prepare($checkPhoneSql);
$checkPhoneStmt->bind_param('ss', $phone, $phone);
$checkPhoneStmt->execute();
$phoneResult = $checkPhoneStmt->get_result();

if ($phoneResult->num_rows > 0) {
    $row = $phoneResult->fetch_assoc();
    $userType = ucfirst($row['type']);
    exit(json_encode(['success' => false, 'message' => "You are already registered as a $userType of MUSTCU with this phone number."]));
}

    // Determine registration type and insert
    if (isset($_POST['position']) && isset($_POST['docket'])) {
        $position = htmlspecialchars(trim($_POST['position']));
        $docket = htmlspecialchars(trim($_POST['docket']));
        $sql = "INSERT INTO leaders (name, email, phone, year, course, completion_year, ministry, position, docket, submitted_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssissssss', $name, $email, $phone, $year, $course, $completion_date, $ministry, $position, $docket, $submitted_at);
    } else {
        $sql = "INSERT INTO members (name, email, phone, year, course, completion_year, ministry, submitted_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssissss', $name, $email, $phone, $year, $course, $completion_date, $ministry, $submitted_at);
    }

    // Execute database insertion
    if ($stmt->execute()) {
        // PHPMailer setup
        $mail = new PHPMailer(true);
        try {
            // Enable SMTP debugging (remove or set to 0 in production)
            $mail->SMTPDebug = 2; // 2 = client/server messages
            $mail->Debugoutput = function($str, $level) { error_log("PHPMailer: $str"); };

            $mail->isSMTP();
            $mail->Host = 'mail.mustcu.or.ke'; // Fixed whitespace
            $mail->SMTPAuth = true;
            $mail->Username = 'info@mustcu.or.ke';
            $mail->Password = '6)kgnGba%(nK'; // Move to config.php for security
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Sender & Recipient
            $mail->setFrom('info@mustcu.or.ke', 'MUSTCU Registration');
            $mail->addAddress($email, $name);

            // Email content
            $logoUrl = 'https://mustcu.or.ke/images/mustculogoemail.jpeg';
            $mail->isHTML(true);
            $mail->Subject = 'Welcome to Meru University Christian Union!';
            $mail->Body = '
            <html>
            <head>
                <style>
                    body {
                        font-family: "Arial", sans-serif;
                        background-color: #f0f4f8;
                        color: #333;
                        padding: 20px;
                        margin: 0;
                    }
                    .card {
                        background: linear-gradient(135deg, #0207ba, #4364f7, #ff7900);
                        padding: 30px;
                        border-radius: 12px;
                        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
                        text-align: center;
                        max-width: 650px;
                        margin: 0 auto;
                        color: #fff;
                    }
                    .logo {
                        width: 130px;
                        margin-bottom: 20px;
                        border-radius: 50%;
                    }
                    .header {
                        font-size: 30px;
                        font-weight: bold;
                        margin-bottom: 20px;
                        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
                    }
                    .message {
                        font-size: 18px;
                        line-height: 1.6;
                        padding: 25px;
                        background: rgba(255, 255, 255, 0.95);
                        border-radius: 10px;
                        margin-top: 20px;
                        color: #333;
                    }
                    .benefits {
                        background-color: #f8f9fa;
                        padding: 20px;
                        border-radius: 8px;
                        margin: 20px 0;
                        text-align: left;
                    }
                    .benefits h2 {
                        color: #ff7900;
                        font-size: 20px;
                        margin-bottom: 15px;
                    }
                    .benefits ul {
                        list-style: none;
                        padding: 0;
                        margin: 0;
                    }
                    .benefits li {
                        margin-bottom: 15px;
                        display: flex;
                        align-items: center;
                    }
                    .benefits li span:first-child {
                        color: #0207ba;
                        margin-right: 10px;
                        font-size: 18px;
                    }
                    .footer {
                        margin-top: 25px;
                        font-size: 14px;
                        color: #e0e0e0;
                        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
                    }
                    .footer2 {
                        margin-top: 25px;
                        font-size: 14px;
                        color: #e0e0e0;
                        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
                    }
                </style>
            </head>
            <body>
                <div class="card">
                    <img class="logo" src="' . $logoUrl . '" alt="MUSTCU Logo">
                    <div class="header">Welcome to MUSTCU, ' . htmlspecialchars($name) . '! 🌟</div>
                    <div class="message">
                        We’re thrilled to confirm your successful registration with 
                        <strong>Meru University Christian Union (MUSTCU)</strong>!
                        <br><br>
                        You’re now part of a vibrant community dedicated to 
                        <strong>faith, fellowship, and service</strong>.
                        <div class="benefits">
                            <h2>As a member, you’ll enjoy:</h2>
                            <ul>
                                <li><span>✓</span> Engaging fellowship programs</li>
                                <li><span>✓</span> Ministry groups to serve in your unique way</li>
                                <li><span>✓</span> Bible studies and prayer for spiritual growth</li>
                                <li><span>✓</span> Lasting friendships with fellow believers</li>
                            </ul>
                        </div>
                        We look forward to growing together in Christ! 🙏
                    </div>
                    <div class="footer">MUSTCU MEDIA TEAM</div>
                    <div class="footer2">MUSTCU © 2025 - All Rights Reserved</div>
                </div>
            </body>
            </html>';

            $mail->AltBody = "Greetings in Christ, $name!\n\nYou have successfully registered as a member of Meru University Christian Union (MUSTCU).\n\nAs a member, you’ll enjoy:\n- Engaging fellowship programs\n- Ministry groups to serve in your unique way\n- Bible studies and prayer for spiritual growth\n- Lasting friendships with fellow believers\n\nGod bless you!";

            // Send email
            $mail->send();
            echo json_encode(['success' => true, 'message' => 'Registration successful. Confirmation email sent.']);
        } catch (Exception $e) {
            error_log("Email sending failed: " . $mail->ErrorInfo);
            echo json_encode(['success' => true, 'message' => 'Registration successful, but email failed to send: ' . $mail->ErrorInfo]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }

    $stmt->close();
    $checkStmt->close();
    $conn->close();
}
?>