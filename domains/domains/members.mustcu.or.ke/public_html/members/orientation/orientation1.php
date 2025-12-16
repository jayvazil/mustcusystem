<?php


require_once '../../shared/config/config.php';

$user_id = (int)($_SESSION['user_id'] ?? null);
$current_page = 1;

if (!$user_id || $_SESSION['role'] !== 'member') {
    header('Location: index.php');
    exit;
}

global $pdo; // Changed from $conn to $pdo for PDO compatibility
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
        $errors[] = 'Please wait at least 10 seconds before proceeding.';
    } else {
        $stmt = $pdo->prepare("UPDATE orientation_progress SET completed = TRUE, completed_at = NOW() WHERE user_id = :user_id AND page_number = :page_number");
        $stmt->execute([
            ':user_id' => $user_id,
            ':page_number' => $current_page
        ]);
        $_SESSION['current_page'] = 1;
        header('Location: orientation2.php');
        exit;
    }
}
$_SESSION['page_start_time'] = time();
?>

<?php include '../includes/header.php'; ?>

<div class="content-container">
    <h1 class="mb-4" style="color: var(--primary-blue);">Welcome to MUST CU Orientation</h1>
    <?php if ($errors): ?>
        <div class="alert alert-danger" style="animation: fadeIn 0.5s;"><?php echo implode('<br>', $errors); ?></div>
    <?php endif; ?>
    <div class="marquee">
        <span class="marquee-text">Welcome to MUST CU! Join us on this journey of faith and community...</span>
    </div>
    <p class="typed-text" id="typed"></p>
    <div class="section">
        <h3>What is MUST CU?</h3>
        <p>MUST C.U. (Meru University of Science and Technology Christian Union) is a non-denominational, 
        student-led evangelical Christian fellowship rooted in the life and mission of Jesus Christ.</p>
        <p>It is a vibrant community of believers dedicated to spiritual growth 
        and service at Meru  University Of Science and Technology.</p>
        <p>Grounded in the authority of Scripture and the power of the Holy Spirit, MUST C.U. is committed to fostering an atmosphere where students can:
<h6>Grow in their faith</h6>
<h6>Worship God in spirit and truth</h6>
<h6>Receive Biblical teaching</h6>
<h6>Build godly friendships and accountability that last beyond campus life</h6>
As a Christ-centered fellowship, we uphold the unity of the Body of Christ and welcomes 
all who profess faith in Jesus or who are curious to know Him more—into a community 
that prays together, studies the Word together, serves together, and evangelizes together</p>
    </div>
    <div class="section vmo-container">
        <div class="vmo-box">
            <h4>Vision</h4>
            <p>To be faithful disciples and witnesses of Christ in and out of campus. </p>
        </div>
        <div class="vmo-box">
            <h4>Mission</h4>
            <p>To equip every student with Discipleship and Evangelism so as to live a Christ-like life. </p>
        </div>
        <div class="vmo-box">
            <h4>Objectives</h4>
            <p>Evangelism: To train, encourage and commit its members to preach Jesus Christ in and
out of campus with an aim of leading people to a personal commitment to Him. </p>
            <p>Discipleship: To foster maturity of its members through Bible Study, prayer and fellowship.</p>
            <p>Mission and Compassion: To mobilize members into mission work, compassion activity and
societal transformation in every sphere of life as God leads them.</p>
            <p>Leadership development: To identify, develop and enhance leadership skills of its members
through training, mentorship and experience</p>
            
        </div>
        <div class="vmo-box">
            <h4> Core values</h4>
            <p>Unity - we regard unity as seeking oneness in the midst of our diversity as we value each
other and work together to build up the body of Christ </p>
            <p>Integrity we regard integrity as a faithful adherence to a moral code in diverse areas of life</p>
            <p> Accountability – we regard accountability as the state of being responsible for oneself and
others in all aspects that is conduct, statement, time and finances.</p>
            <p>Stewardship – we regard stewardship as faithful management of resources, abilities and
people entrusted to our care by God and knowing that we shall give an account.</p>
             <p> Excellence - we regard excellence as the quality of being outstanding and delivering quality
results in all aspects of life for the glory of God</p>
        </div>
    </div>
    <div class="section">
        <h3>Doctrinal Basis</h3>
        <p>Membership shall be open only to those in agreement with the fundamental biblical truths as enumerated
below:</p>
        <div class="doctrinal-container">
            <?php
            $doctrinal_points = [
                "The unity of the Father, Son and the Holy Spirit in the God head",
                "The sovereignty of God in the creation, revelation, redemption and final judgment. ",
                "The divine inspiration and entire trustworthiness of the Holy scripture as originally given and
 its supreme authority in all matters of",
                "The universal sinfulness and guilt of all men since the fall rendering them subject to God's
 wrath and condemna",
                "The divine birth of Jesus Christ ",
                "Redemption from guilt, penalty, dominion and pollution of sin, solely through the sacrificial
 and substitution death of",
                "The bodily resurrection of Jesus Christ from the dead and his ascension to the right hand of
 God the father",
                "The justification of the sinner by the grace of God alone through faith. ",
                "The presence and the power of the Holy Spirit in a believer’s life. ",
                "The in-dwelling and the working of the Holy Spirit in a believer’s life. ,",
                "The expectation of the personal return of the lord Jesus Christ. ",
                "The one Holy universal Church which is the body of the Christ to which all believers belong. ",
                "The unity of believers in the body of Christ without discrimination"
            ];
            foreach ($doctrinal_points as $point) {
                echo "<div class='doctrinal-box'>$point</div>";
            }
            ?>
        </div>
    </div>
    <div class="section">
        <h3> Guiding Principles</h3>
        <h5>The Supreme Word Of God</h5>
        <h5>The CU Costitiution</h5>
        <h5>The CU Policy Documents</h5>
    </div>
    <div class="section">
        <h3> Governance structure</h3>
        <h5>1. The Annual General meeting</h5>
<p>This is the supreme governing body in the union.</p>
        <h5>2. The Advisory Committee<h5>
<p>This is the main advisory body to the union.</p>
        <h5>3. The Executive Committee</h5>
<p>This is the main policy making and administrative body of the union.</p>
        <h5>4. The Committees and subcommittees</h5>
    </div>
    <form method="POST">
        <button type="submit" name="next" id="nextButton" class="btn btn-primary" disabled>Next</button>
        <div id="countdown" class="countdown-timer"></div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
    // Typing animation
    let i = 0, text = "Hello and welcome to the MUST Christian Union!";
    const typed = document.getElementById('typed');
    function typeWriter() {
        if (i < text.length) {
            typed.textContent += text.charAt(i);
            i++;
            setTimeout(typeWriter, 100);
        }
    }
    typeWriter();

    // Countdown timer for Next button
    const nextButton = document.getElementById('nextButton');
    const countdownDisplay = document.getElementById('countdown');
    const startTime = Date.now(); // Use client-side time for reliability
    const minWaitTime = 10000; // 10 seconds in milliseconds

    function updateCountdown() {
        const elapsed = Date.now() - startTime;
        const remainingTime = Math.max(0, minWaitTime - elapsed);

        if (remainingTime > 0) {
            const seconds = Math.ceil(remainingTime / 1000);
            countdownDisplay.textContent = `Please wait ${seconds} second${seconds !== 1 ? 's' : ''} to proceed`;
            countdownDisplay.style.opacity = '1';
            countdownDisplay.style.transition = 'opacity 0.5s ease';
            nextButton.disabled = true; // Ensure button stays disabled
            requestAnimationFrame(updateCountdown); // Smooth updates
        } else {
            nextButton.disabled = false; // Enable button exactly after 10 seconds
            countdownDisplay.style.opacity = '0';
            setTimeout(() => {
                countdownDisplay.style.display = 'none';
            }, 500); // Match transition duration
        }
    }

    // Initialize countdown
    nextButton.disabled = true; // Explicitly disable button on load
    updateCountdown();
</script>
<style>
    :root { 
        --primary-blue: #0207ba; 
        --accent-orange: #ff7900; 
        --white: #ffffff; 
        --shadow: rgba(2, 7, 186, 0.15); 
    }
    body { 
        background: linear-gradient(135deg, var(--primary-blue), #1e3c72); 
        display: flex; 
        flex-direction: column; 
        min-height: 100vh; 
        margin: 0; 
        font-family: 'Segoe UI', sans-serif; 
    }
    .content-container { 
        flex: 1; 
        background: var(--white); 
        border-radius: 20px; 
        box-shadow: 0 20px 40px var(--shadow); 
        padding: 3.5rem; 
        max-width: 800px; 
        margin: 0 auto; 
        text-align: center; 
        animation: slideUp 1s; 
    }
    .marquee { 
        background: var(--primary-blue); 
        color: #fff000; 
        padding: 10px; 
        overflow: hidden; 
        font-size: 1.2rem; 
        position: relative;
    }
    .marquee-text {
        display: inline-block;
        white-space: nowrap;
        animation: scroll 10s infinite linear;
    }
    .typed-text { 
        font-size: 1.8rem; 
        color: var(--accent-orange); 
        margin: 20px 0; 
    }
    .section { 
        margin: 20px 0; 
        padding: 15px; 
        background: rgba(255, 255, 255, 0.9); 
        border-left: 5px solid var(--accent-orange); 
        border-radius: 10px; 
    }
    .vmo-container {
        display: flex;
        justify-content: space-between;
        gap: 15px;
        flex-wrap: wrap;
    }
    .vmo-box {
        flex: 1;
        min-width: 200px;
        padding: 15px;
        background: rgba(255, 121, 0, 0.1);
        border-radius: 8px;
        border: 1px solid var(--accent-orange);
    }
    .doctrinal-container {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 10px;
        align-items: center;
    }
    .doctrinal-box {
        background: rgba(2, 7, 186, 0.1);
        border: 1px solid var(--primary-blue);
        border-radius: 5px;
        padding: 10px;
        text-align: center;
        font-size: 0.9rem;
        width: 80%;
        max-width: 300px;
    }
    .btn-primary { 
        background: linear-gradient(135deg, var(--primary-blue), var(--accent-orange)); 
        color: #fff000; 
        padding: 1.2rem 2.5rem; 
        border-radius: 12px; 
        font-size: 1.2rem; 
        transition: transform 0.3s, box-shadow 0.3s; 
    }
    .btn-primary:hover { 
        transform: translateY(-5px); 
        box-shadow: 0 10px 20px rgba(2, 7, 186, 0.4); 
    }
    .btn-primary:disabled { 
        background: #ccc; 
        cursor: not-allowed;
    }
    .countdown-timer {
        margin-top: 10px;
        color: var(--primary-blue);
        font-size: 1rem;
        transition: opacity 0.5s ease;
    }
    @keyframes fadeIn { 
        from { opacity: 0; } 
        to { opacity: 1; } 
    }
    @keyframes slideUp { 
        from { transform: translateY(50px); opacity: 0; } 
        to { transform: translateY(0); opacity: 1; } 
    }
    @keyframes scroll { 
        0% { transform: translateX(100%); } 
        100% { transform: translateX(-100%); } 
    }
    @media (max-width: 768px) { 
        .content-container { padding: 2rem; max-width: 90%; } 
        .vmo-container { flex-direction: column; }
        .doctrinal-box { width: 90%; }
    }
</style>