<?php

require_once '../../shared/config/config.php';

require_once '../../vendor/autoload.php'; // Ensure PHPMailer is installed via Composer

// Ensure current_page is set to prevent redirect loop
$user_id = (int)($_SESSION['user_id'] ?? null);
$current_page = 1;

if (!$user_id || $_SESSION['role'] !== 'member') {
    header('Location: index.php');
    exit;
}

global $pdo;
$stmt = $pdo->prepare("INSERT INTO orientation_progress (user_id, page_number, completed) VALUES (:user_id, :page_number, :completed) ON DUPLICATE KEY UPDATE completed = :completed");
$stmt->execute([
    ':user_id' => $user_id,
    ':page_number' => $current_page,
    ':completed' => false
]);

$errors = [];
$success = '';
$showCelebration = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finish'])) {
    $start_time = $_SESSION['page_start_time'] ?? 0;
    if ((time() - $start_time) < 10) {
        $errors[] = 'Please wait at least 10 seconds before proceeding.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE orientation_progress SET completed = TRUE, completed_at = NOW() WHERE user_id = :user_id AND page_number = :page_number");
            $stmt->execute([
                ':user_id' => $user_id,
                ':page_number' => $current_page
            ]);

            $stmt = $pdo->prepare("SELECT email, phone, name FROM members WHERE id = :user_id");
            $stmt->execute([':user_id' => $user_id]);
            $member = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$member) {
                throw new Exception('Member data not found');
            }

            $email = $member['email'];
            $name = $member['name'];

            // Send styled congratulatory email
            $subject = "Congratulations on Your Membership!";
            $message = "
                <html>
<head>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f4f4; color: #0207ba; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #fff; padding: 20px; border: 5px double #0207ba; border-radius: 15px; position: relative; overflow: hidden; }
        .header { text-align: center; padding: 15px; border-bottom: 3px solid #fff000; }
        .header h1 { margin: 0; font-size: 2.5rem; color: #0207ba; text-transform: uppercase; letter-spacing: 2px; position: relative; }
       
        .content { padding: 20px; line-height: 1.6; }
        .content h2 { color: #0207ba; border-bottom: 2px dashed #fff000; padding-bottom: 5px; }
        .content p { margin: 15px 0; position: relative; }
       
        .footer { text-align: center; font-size: 12px; color: #0207ba; padding-top: 15px; border-top: 2px dotted #fff000; margin-top: 20px; }
        .decorative-line { border-top: 1px dashed #fff000; margin: 20px 0; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>MUST CU ORIENTATION!!!</h1>
        </div>
        <div class='content'>
            <h2>Dear $name,</h2>
            <div class='decorative-line'></div>
            <p>Congratulations! You are already a valued member of CU Ministries. 
            We are thrilled to have you in our community and look forward to your participation in our ministries
            and other activities of the Christian.</p>
            <p>If you have any questions or need assistance, feel free to reach out to us.</p>
            <p>Best regards,<br>The MUST CU Media Team</p>
        </div>
        <div class='footer'>
            &copy; 2025 CU Ministries. All rights reserved.
        </div>
    </div>
</body>
</html>
            ";
      function sendEmail($to, $subject, $htmlMessage, $smtpConfig = [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_username' => 'mulwaisaac851@gmail.com',
    'smtp_password' => 'uiby qfze lwhn pvsz',
    'smtp_secure' => 'tls'
]) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $smtpConfig['smtp_host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpConfig['smtp_username'];
        $mail->Password   = $smtpConfig['smtp_password'];
        $mail->SMTPSecure = $smtpConfig['smtp_secure'];
        $mail->Port       = $smtpConfig['smtp_port'];

        // Recipients
        $mail->setFrom($smtpConfig['smtp_username'], 'CU Ministries');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlMessage;
        $mail->AltBody = strip_tags($htmlMessage);

        $mail->send();
    } catch (Exception $e) {
        error_log('Email sending failed: ' . $mail->ErrorInfo);
        throw new Exception('Email sending failed: ' . $mail->ErrorInfo);
    }
}
            $success = 'Congratulations! A congratulatory email has been sent to ' . $email . '.';
            $showCelebration = true;
            unset($_SESSION['current_page']);
        } catch (Exception $e) {
            $errors[] = 'Failed to complete orientation: ' . $e->getMessage();
            error_log('Email Error: ' . $e->getMessage());
        }
    }
}
$_SESSION['page_start_time'] = time();
?>

<?php include '../includes/header.php'; ?>

<div class="content-container">
    <h1 class="mb-4" style="color: var(--primary-blue); text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3); background: linear-gradient(90deg, var(--primary-blue), var(--accent-orange)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">CU Ministries</h1>
    <?php if ($errors): ?>
        <div class="alert alert-danger" style="animation: fadeIn 0.5s; border-left: 5px solid var(--accent-orange); box-shadow: 0 4px 12px rgba(255, 0, 0, 0.2);"><?php echo implode('<br>', $errors); ?></div>
    <?php endif; ?>
    <?php if ($success && !$showCelebration): ?>
        <div class="alert alert-success" style="animation: fadeIn 0.5s; border-left: 5px solid var(--accent-orange); box-shadow: 0 4px 12px rgba(0, 255, 0, 0.2);"><?php echo $success; ?></div>
    <?php endif; ?>
    <div class="section-card">
        <h3 style="color: var(--accent-orange); text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.2);">Ushering Ministry</h3>
        <p style="color: #333; line-height: 1.8; font-size: 1.1rem;">The Ushering Ministry is the face of the Christian Union during fellowships and services. 
        Ushers serve with humility, love, and hospitality by welcoming members, guiding visitors, organizing seating, and maintaining order during gatherings. 
        They reflect Christ’s heart of service by creating an environment of peace, respect, and reverence in the house of God.
        Ushers model servanthood (Mark 10:45), help eliminate distractions in worship, and set a tone of reverence and unity.</p>
    </div>
    <div class="section-card">
        <h3 style="color: var(--accent-orange); text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.2);">Catering Ministry</h3>
        <p style="color: #333; line-height: 1.8; font-size: 1.1rem;">The Catering Ministry serves God by preparing meals and refreshments during events like missions, Sunday fellowships, meetings, and retreats. 
        They ensure that the physical needs of members and guests are met in a clean, loving, and excellent manner. 
        Their hands bring nourishment with prayer, joy, and sacrifice.</p>
    </div>
    <div class="section-card">
        <h3 style="color: var(--accent-orange); text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.2);">Decor Ministry</h3>
        <p style="color: #333; line-height: 1.8; font-size: 1.1rem;">The Decor Ministry beautifies worship spaces using godly creativity. 
        Through colors, designs, and thematic setups, they create an atmosphere that glorifies God and prepares hearts for worship. 
        Their goal is not just to impress visually, but to reflect the beauty and order of heaven (1 Corinthians 14:40).
        They serve through art and contribute to a sacred, welcoming environment that honors God's presence.</p>
    </div>
    <div class="section-card">
        <h3 style="color: var(--accent-orange); text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.2);">IT and Publicity Ministry</h3>
        <p style="color: #333; line-height: 1.8; font-size: 1.1rem;">This ministry leverages technology to enhance communication, 
        reach more people, and glorify God through digital means. It includes:
Poster design, Video editing, Projection during services, Livestreaming fellowships and events 
They help share the Gospel visually and digitally, manage the C.U. social media pages and the CU website,  ensuring smooth flow of information across Union.</p>
    </div>
    <div class="section-card">
        <h3 style="color: var(--accent-orange); text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.2);">Creative Ministry</h3>
        <p style="color: #333; line-height: 1.8; font-size: 1.1rem;">The Creative Ministry uses drama, spoken word, poetry, dance, and skits to teach, encourage, and evangelize. 
        They communicate powerful Biblical truths in relatable and engaging ways. Whether on stage or in the streets, they carry God’s message with boldness and artistic excellence.
        They stir hearts, open eyes, and challenge minds using God-given talents for Kingdom purposes</p>
    </div>
    <div class="section-card">
        <h3 style="color: var(--accent-orange); text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.2);">Praise and Worship Ministry</h3>
        <p style="color: #333; line-height: 1.8; font-size: 1.1rem;">This ministry leads the congregation into the presence of God through music, singing, and spiritual songs. 
        They prepare the hearts of the fellowship for the Word and cultivate a dwelling place for God's presence through anointed, Spirit-led worship.
        They offer spiritual sacrifices of praise (Hebrews 13:15) and help lead others into communion with the Holy Spirit during our services.</p>
    </div>
    <div class="section-card">
        <h3 style="color: var(--accent-orange); text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.2);">Choir Ministry</h3>
        <p style="color: #333; line-height: 1.8; font-size: 1.1rem;">The Choir Ministry is a group of dedicated vocalists who minister through powerful, well-rehearsed songs—often incorporating harmonies, 
        traditional music, and spirituals. They serve during major services, rallies, and missions, bringing depth and variety to musical worship.
 Choir members declare God’s greatness and edify the body through excellence in vocal ministry (Psalm 149:1).</p>
    </div>
    <div class="section-card">
        <h3 style="color: var(--accent-orange); text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.2);">Sunday School Ministry</h3>
        <p style="color: #333; line-height: 1.8; font-size: 1.1rem;">This outreach ministry involves visiting nearby local churches to teach young children the Word of God in age-appropriate ways.
         Sunday school teachers use storytelling, Bible verses, songs, and illustrations to build a godly foundation in the next generation.
         They guide  the little children  to know God and Obey Him (Mark 10:14), planting eternal seeds of faith in young hearts.</p>
    </div>
    
    <div class="section-card">
        <h3 style="color: var(--accent-orange); text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.2);">Thank You!</h3>
        <p style="color: #333; line-height: 1.8; font-size: 1.1rem;">Thank you for completing our online orientation! Click the button below to finish.</p>
    </div>
    <div id="countdown" style="margin: 20px 0; font-size: 1.2rem; color: var(--primary-blue); text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);">Time remaining: <span id="countdown-timer">10</span> seconds</div>
    <form method="POST">
        <button type="submit" name="finish" id="finishButton" class="btn btn-primary" disabled>Finish Orientation</button>
    </form>
    <?php if ($showCelebration): ?>
        <div id="celebration-container" style="display: block; position: relative; z-index: 10000;">
            <div id="congratMessage" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 2.5rem; color: #fff; text-shadow: 2px 2px 10px #ff7900; text-align: center; animation: fadeInOut 10s ease-out;"></div>
            <div id="countdown-celebration" style="position: fixed; top: 60%; left: 50%; transform: translateX(-50%); font-size: 1.5rem; color: #fff; text-shadow: 1px 1px 5px #000;"></div>
            <div id="navigation-buttons" style="position: fixed; top: 70%; left: 50%; transform: translateX(-50%); display: none; z-index: 10001;">
                <a href="../dashboard.php" class="btn btn-success" style="margin-right: 10px; padding: 10px 20px; background-color: #0207ba; color: #fff; border-radius: 5px; text-decoration: none;">Back to Dashboard</a>
                <a href="orientation1.php" class="btn btn-warning" style="padding: 10px 20px; background-color: #fff000; color: #fff; border-radius: 5px; text-decoration: none;">Retake Orientation</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
    let startTime = <?php echo $_SESSION['page_start_time'] ?? time(); ?>;
    let timeLeft = 10;
    const timer = document.getElementById('countdown-timer');
    const finishButton = document.getElementById('finishButton');

    function updateTimer() {
        timeLeft = 10 - Math.floor((Date.now() - startTime * 1000) / 1000);
        if (timeLeft <= 0) {
            timer.textContent = '0';
            finishButton.disabled = false;
            clearInterval(timerInterval);
        } else {
            timer.textContent = timeLeft;
        }
    }
    const timerInterval = setInterval(updateTimer, 1000);
    updateTimer();

    // Celebration logic
    document.addEventListener('DOMContentLoaded', () => {
        const celebrationContainer = document.getElementById('celebration-container');
        if (celebrationContainer && <?php echo json_encode($showCelebration); ?>) {
            startCelebration();
        }
    });

    function startCelebration() {
        const congratMessage = document.getElementById('congratMessage');
        const countdownCelebration = document.getElementById('countdown-celebration');
        const navigationButtons = document.getElementById('navigation-buttons');

        congratMessage.innerHTML = 'Congratulations!<br>You have completed the orientation!';
        congratMessage.style.opacity = 0;
        let countdownValue = 10;

        function updateCelebration() {
            countdownCelebration.textContent = `Countdown: ${countdownValue}`;
            if (countdownValue > 0) {
                // Animate balloons
                const balloon = document.createElement('div');
                balloon.className = 'balloon';
                balloon.style.left = Math.random() * 100 + 'vw';
                balloon.style.animationDuration = (Math.random() * 5 + 5) + 's';
                balloon.style.background = `hsl(${Math.random() * 360}, 70%, 50%)`;
                document.body.appendChild(balloon);

                // Fade in congratulation message
                congratMessage.style.transition = 'opacity 1s';
                congratMessage.style.opacity = 1;

                countdownValue--;
            } else {
                countdownCelebration.style.display = 'none';
                navigationButtons.style.display = 'block';
                clearInterval(celebrationInterval);
            }
        }

        const celebrationInterval = setInterval(updateCelebration, 1000);
        updateCelebration(); // Initial call
    }

    // Balloon animation styles
    const style = document.createElement('style');
    style.textContent = `
        .balloon {
            position: fixed;
            bottom: -100px;
            width: 50px;
            height: 75px;
            background: #ff0;
            border-radius: 50% 50% 20% 20%;
            animation: floatUp 5s infinite;
            z-index: 10000;
        }
        @keyframes floatUp {
            0% { bottom: -100px; transform: scale(0.5); }
            50% { transform: scale(1); }
            100% { bottom: 100vh; transform: scale(0.5); }
        }
        @keyframes fadeInOut {
            0% { opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { opacity: 0; }
        }
    `;
    document.head.appendChild(style);
</script>
<style>
    :root { --primary-blue: #0207ba; --accent-orange: #ff7900; --white: #ffffff; --shadow: rgba(2, 7, 186, 0.15); }
    body { background: linear-gradient(135deg, var(--primary-blue), #1e3c72); display: flex; flex-direction: column; min-height: 100vh; margin: 0; font-family: 'Segoe UI', sans-serif; position: relative; overflow-x: hidden; }
    .content-container { 
        flex: 1; 
        background: rgba(255, 255, 255, 0.95); 
        border-radius: 20px; 
        box-shadow: 0 20px 40px var(--shadow); 
        padding: 3.5rem; 
        max-width: 900px; 
        margin: 0 auto; 
        text-align: center; 
        animation: slideUp 1s; 
        border: 2px solid var(--accent-orange); 
        position: relative; 
        overflow: hidden; 
    }
    .content-container::before { 
        content: ''; 
        position: absolute; 
        top: -50%; 
        left: -50%; 
        width: 200%; 
        height: 200%; 
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%); 
        z-index: 0; 
        animation: rotate 20s linear infinite; 
    }
    .content-container > * { position: relative; z-index: 1; }
    .section-card { 
        margin: 20px 0; 
        padding: 20px; 
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.7)); 
        border-left: 5px solid var(--accent-orange); 
        border-radius: 15px; 
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1); 
        transition: transform 0.3s ease, box-shadow 0.3s ease; 
        animation: fadeIn 0.5s; 
    }
    .section-card:hover { 
        transform: translateY(-5px); 
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2); 
    }
    .btn-primary { 
        background: linear-gradient(135deg, var(--primary-blue), var(--accent-orange)); 
        color: #fff000; 
        padding: 1.5rem 3rem; 
        border-radius: 15px; 
        font-size: 1.3rem; 
        transition: transform 0.3s, box-shadow 0.3s, filter 0.3s; 
        border: 2px solid #fff000; 
        position: relative; 
        overflow: hidden; 
    }
    .btn-primary::after { 
        content: ''; 
        position: absolute; 
        top: 50%; 
        left: 50%; 
        width: 0; 
        height: 0; 
        background: rgba(255, 240, 0, 0.3); 
        border-radius: 50%; 
        transform: translate(-50%, -50%); 
        transition: width 0.6s ease, height 0.6s ease; 
    }
    .btn-primary:hover::after { 
        width: 200%; 
        height: 200%; 
    }
    .btn-primary:hover { 
        transform: translateY(-5px); 
        box-shadow: 0 15px 30px rgba(2, 7, 186, 0.5); 
        filter: brightness(1.1); 
    }
    .btn-primary:disabled { background: #ccc; }
    .btn-success:hover { background-color: #218838; }
    .btn-warning:hover { background-color: #e0a800; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideUp { from { transform: translateY(50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    @keyframes rotate { 
        0% { transform: rotate(0deg); } 
        100% { transform: rotate(360deg); } 
    }
    @media (max-width: 768px) { 
        .content-container { padding: 2rem; max-width: 90%; } 
        .section-card { padding: 15px; }
    }
</style>