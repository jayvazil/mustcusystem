<?php
require_once '../../shared/config/config.php';

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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['next'])) {
    $start_time = $_SESSION['page_start_time'] ?? 0;
    if ((time() - $start_time) < 10) {
        $errors[] = 'Please wait at least 30 seconds before proceeding.';
    } else {
        $stmt = $pdo->prepare("UPDATE orientation_progress SET completed = TRUE, completed_at = NOW() WHERE user_id = :user_id AND page_number = :page_number");
        $stmt->execute([
            ':user_id' => $user_id,
            ':page_number' => $current_page
        ]);
        header('Location: orientation5.php');
        exit;
    }
}
$_SESSION['page_start_time'] = time();
?>

<?php include '../includes/header.php'; ?>

<div class="content-container">
    <h1 class="mb-4" style="color: var(--primary-blue); text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3); background: linear-gradient(90deg, var(--primary-blue), var(--accent-orange)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Who is a CU Member?</h1>
    <?php if ($errors): ?>
        <div class="alert alert-danger" style="animation: fadeIn 0.5s; border-left: 5px solid var(--accent-orange); box-shadow: 0 4px 12px rgba(255, 0, 0, 0.2);"><?php echo implode('<br>', $errors); ?></div>
    <?php endif; ?>
    <div class="section-card">
        <h3 style="color: var(--accent-orange); text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.2);">Who is a CU Member?</h3>
        <p style="color: #333; line-height: 1.2; font-size: 1.1rem;">A Christian Union (C.U.) member is a student who has personally encountered the saving grace of Jesus Christ and made a conscious decision to follow Him as Lord and Savior. This individual has experienced spiritual rebirth through faith and now identifies as a child of God, walking daily in fellowship with the Holy Spirit. As part of their journey of faith, the C.U. member joins with like-minded believers on campus to grow spiritually, to serve others, and to fulfill the Great Commission within the university and beyond</p>

<p>Being a member of the C.U. is not simply about attending meetings or joining a religious club—it is about becoming part of a vibrant, Spirit-filled spiritual family where Christ is the center of everything. A C.U. member embraces the values of the Kingdom of God and reflects those values through character, conduct, and commitment, both in private and in public. They understand that university life is not only an academic journey but also a spiritual one, and they are intentional about cultivating a life that pleases God</p>

<p>At the heart of a C.U. member’s life is a desire to know God more deeply through consistent Bible reading, prayer, worship, and fellowship. They engage in daily devotion, attend Bible study groups, and make time for both personal and corporate prayer. Their life is rooted in the Word of God, which they uphold as the ultimate authority for faith and practice. They hunger for truth and wisdom, not just in academic matters, but in the things of God</p>

<p>A C.U. member lives out their faith with humility and courage. They do not hide their identity in Christ, but shine as a light on campus—whether in lecture halls, hostels, dining halls, or during extracurricular activities. Their lifestyle speaks louder than their words: they pursue holiness, practice integrity, extend kindness, and walk in love. When they fall short, they are quick to repent, seek accountability, and rise again in the grace of God. They are not perfect, but they are being perfected</p>

<p>They are also active participants in the life of the Christian Union. They attend Sunday fellowships, weekly prayer meetings, missions, keshas (overnight prayers), and outreach events. But beyond attendance, they serve. They use their talents and gifts for the glory of God—whether in singing, ushering, preaching, counseling, organizing events, or evangelizing. Every C.U. member is seen as a minister in their own right, called to serve the body and to reach the lost with the Gospel</p>

    </div>
    <div class="section-card">
        <h3 style="color: var(--accent-orange); text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.2);">Membership rights and responsibilities</h3>
        <ul style="text-align: left; color: #444; font-size: 1rem;">
           <p>All members of the union shall be entitled to:</p>
            <li> Participate in all activities.</li>
            <li>Vote in all general meetings.</li>
            <li>Eligible to propose amendments.</li>
            <li>Uphold the doctrinal basis, core values and actively support the union in achieving its
objectives.</li>
            <li>Shall be entitled to pass a vote of no confidence in the executive committee.</li>
            <li>Shall be eligible for consideration for assistance from the welfare kitty if and when available
as per the guidelines in the welfare policy document. </li>
            <li> Shall be eligible to be nominated in the union leadership subject to chapter 8 of this
constitution</li>
        </ul>
    </div>
    <div class="section-card">
        <h3 style="color: var(--accent-orange); text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.2);">Advantages of being a CU Member</h3>
        <ul style="text-align: left; color: #444; font-size: 1rem;">
            <li>You receive godly mentorship – Be guided and corrected in love by mature Christians.</li>
            <li>You build a strong prayer life – Learn to pray with power, consistency, and faith.</li>
            <li>You grow spiritually – Through prayer, Bible study, and fellowship with other believers.</li>
            <li>You find true Christian friends – Form lasting, godly friendships that encourage your walk with God.</li>
            <li>You stay focused and disciplined – Learn to balance your faith, studies, and personal life.</li>
            <li>You develop leadership skills – Serve in various ministries and grow in confidence and responsibility.</li>
            <li>You discover your spiritual gifts – Identify and use your God-given talents to serve others.</li>
            <li>You learn to share the Gospel – Get trained and empowered to evangelize and go for missions.</li>
            <li>You stand firm in your morals – Be rooted in purity, integrity, and godly values.</li>
            <li>You get support in hard times – A spiritual family that prays for you and helps you when you’re down.</li>
            <li>You enjoy vibrant worship – Experience deep, Spirit-filled praise and worship regularly.</li>
            <li>You become purpose-driven – Learn to live intentionally for Christ in everything you do.</li>
            <li>You prepare for life after campus – Get equipped spiritually, emotionally, and socially for the future.</li>
            <li>You find a home away from home – C.U. becomes your spiritual shelter during your campus years.</li>
            <li>You become a light on campus – Represent Jesus boldly through your words and actions.</li>   
        </ul>
    </div>
    <div id="countdown" style="margin: 20px 0; font-size: 1.2rem; color: var(--primary-blue); text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);">Time remaining: <span id="countdown-timer">10</span> seconds</div>
    <form method="POST">
        <button type="submit" name="next" id="nextButton" class="btn btn-primary" disabled>Next</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
    let startTime = <?php echo $_SESSION['page_start_time'] ?? time(); ?>;
    let timeLeft = 10;
    const timer = document.getElementById('countdown-timer');
    const nextButton = document.getElementById('nextButton');

    function updateTimer() {
        timeLeft = 10 - Math.floor((Date.now() - startTime * 1000) / 1000);
        if (timeLeft <= 0) {
            timer.textContent = '0';
            nextButton.disabled = false;
            clearInterval(timerInterval);
        } else {
            timer.textContent = timeLeft;
        }
    }
    const timerInterval = setInterval(updateTimer, 1000);
    updateTimer();
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