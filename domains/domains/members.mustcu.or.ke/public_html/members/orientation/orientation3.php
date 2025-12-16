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
        header('Location: orientation4.php');
        exit;
    }
}
$_SESSION['page_start_time'] = time();
?>

<?php include '../includes/header.php'; ?>

<div class="fixed-marquee">
    <div class="marquee-text">Welcome to MUST CU! Join our vibrant community of faith, fellowship, and growth! 🎉</div>
</div>

<div class="content-container">
    <h1 class="mb-4" style="color: var(--primary-blue); text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);">Activities and Events</h1>
    <?php if ($errors): ?>
        <div class="alert alert-danger" style="animation: fadeIn 0.5s; border-left: 5px solid var(--accent-orange);"><?php echo implode('<br>', $errors); ?></div>
    <?php endif; ?>
    <div class="event-description-section">
        <h2 style="color: var(--accent-orange);">About Our Events And Activities</h2>
        <p style="color: #333; line-height: 1.6;">MUST CU hosts a variety of enriching events to foster spiritual growth and community engagement. 
        These include weekly worship services with inspiring sermons, interactive Bible study sessions held in small groups, 
        outreach programs to serve the University Fraternity and leadership training workshops to empower members. 
        Events are conducted with a blend of in-person gatherings and virtual sessions, ensuring accessibility for all members. 
        Participation is encouraged through registration and active involvement!</p>
        <p>All these activities are dictated by the CU calender of events which is a summary of all activities that will be carried out by 
        the Christian Union in a given Semester</p>
    </div>
    <div class="calendar-section">
        <h2 style="color: var(--accent-orange);">Here is the calender of events for the August to December Semester</h2>
        <img src="Images/calender of events 2025 2026.png" alt="CU Event Calendar" style="border: 3px solid var(--primary-blue);" onclick="zoomImage(this)">
    </div>
    <h3 style="color: var(--accent-orange); text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);">Upcoming Major Events</h3>
    <div class="events-slider" id="eventsSlider">
        <div class="event">
            <h4>Annual Conference - Aug 15, 2025</h4>
            <p>A two-day spiritual retreat featuring keynote speakers, workshops, and fellowship activities to inspire and unite the CU community.</p>
        </div>
        <div class="event">
            <h4>Leadership Summit - Sep 10, 2025</h4>
            <p>An intensive one-day event with leadership training, team-building exercises, and networking opportunities for emerging leaders.</p>
        </div>
        <div class="event">
            <h4>Community Outreach - Oct 5, 2025</h4>
            <p>A day of service including food drives, health check-ups, and educational sessions to support the local community.</p>
        </div>
    </div>
    <div class="section">
        <h3 style="color: var(--primary-blue);">Activities of the CU</h3>
        <p style="color: #444; font-style: italic;">Includes worship services, Bible studies, outreach programs, and leadership training.</p>
    </div>
    <div id="countdown">Time remaining: <span id="countdown-timer">10</span> seconds</div>
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

    // Marquee with JavaScript
    const marqueeText = document.querySelector('.marquee-text');
    let marqueePosition = 100;
    function moveMarquee() {
        marqueePosition -= 0.5;
        if (marqueePosition <= -marqueeText.offsetWidth) marqueePosition = 100;
        marqueeText.style.transform = `translateX(${marqueePosition}%)`;
        requestAnimationFrame(moveMarquee);
    }
    moveMarquee();

    let slideIndex = 0;
    const slides = document.querySelectorAll('.event');
    const slider = document.getElementById('eventsSlider');
    slider.addEventListener('click', () => showModal());

    function showSlides() {
        slides.forEach(slide => {
            slide.style.opacity = '0';
            slide.style.transform = 'translateY(100%)';
        });
        slideIndex++;
        if (slideIndex > slides.length) slideIndex = 1;
        const currentSlide = slides[slideIndex - 1];
        currentSlide.style.transition = 'transform 2s ease-out, opacity 2s ease-out';
        currentSlide.style.opacity = '1';
        currentSlide.style.transform = 'translateY(0)';
        setTimeout(() => {
            currentSlide.style.transition = 'transform 1s ease-in, opacity 1s ease-in';
            currentSlide.style.opacity = '1';
            currentSlide.style.transform = 'translateY(-100%)';
            setTimeout(() => {
                showSlides();
            }, 5000); // Slide out over 2 seconds
        }, 5000); // Display for 3 seconds
    }
    showSlides();

    function showModal() {
        const modal = document.createElement('div');
        modal.className = 'modal-overlay';
        modal.innerHTML = `
            <div class="modal-content">
                <button class="modal-close">X</button>
                ${Array.from(slides).map(slide => `
                    <div class="modal-event">
                        <h4>${slide.querySelector('h4').textContent}</h4>
                        <p>${slide.querySelector('p').textContent}</p>
                    </div>
                `).join('')}
            </div>
        `;
        document.body.appendChild(modal);

        modal.querySelector('.modal-close').addEventListener('click', () => modal.remove());
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') modal.remove();
        });
    }

    function zoomImage(img) {
        const modal = document.createElement('div');
        modal.className = 'image-modal-overlay';
        modal.innerHTML = `
            <div class="image-modal-content">
                <img src="${img.src}" alt="${img.alt}" class="zoomed-image">
                <button class="modal-close">X</button>
            </div>
        `;
        document.body.appendChild(modal);

        const zoomedImage = modal.querySelector('.zoomed-image');
        let isDragging = false;
        let startX = 0, startY = 0;
        let translateX = 0, translateY = 0;

        // Calculate boundaries based on image and viewport size
        function updateBoundaries() {
            const imgRect = zoomedImage.getBoundingClientRect();
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            const maxTranslateX = Math.max(0, (imgRect.width - viewportWidth) / 2);
            const maxTranslateY = Math.max(0, (imgRect.height - viewportHeight) / 2);
            return { maxTranslateX, maxTranslateY };
        }

        zoomedImage.addEventListener('mousedown', (e) => {
            isDragging = true;
            startX = e.clientX - translateX;
            startY = e.clientY - translateY;
            zoomedImage.style.cursor = 'grabbing';
        });

        zoomedImage.addEventListener('mousemove', (e) => {
            if (isDragging) {
                const { maxTranslateX, maxTranslateY } = updateBoundaries();
                translateX = Math.min(maxTranslateX, Math.max(-maxTranslateX, e.clientX - startX));
                translateY = Math.min(maxTranslateY, Math.max(-maxTranslateY, e.clientY - startY));
                zoomedImage.style.transform = `translate(${translateX}px, ${translateY}px) scale(1.5)`;
            }
        });

        zoomedImage.addEventListener('mouseup', () => {
            isDragging = false;
            zoomedImage.style.cursor = 'grab';
        });

        zoomedImage.addEventListener('mouseleave', () => {
            isDragging = false;
            zoomedImage.style.cursor = 'grab';
        });

        zoomedImage.addEventListener('touchstart', (e) => {
            isDragging = true;
            const touch = e.touches[0];
            startX = touch.clientX - translateX;
            startY = touch.clientY - translateY;
        });

        zoomedImage.addEventListener('touchmove', (e) => {
            if (isDragging) {
                const { maxTranslateX, maxTranslateY } = updateBoundaries();
                const touch = e.touches[0];
                translateX = Math.min(maxTranslateX, Math.max(-maxTranslateX, touch.clientX - startX));
                translateY = Math.min(maxTranslateY, Math.max(-maxTranslateY, touch.clientY - startY));
                zoomedImage.style.transform = `translate(${translateX}px, ${translateY}px) scale(1.5)`;
                e.preventDefault();
            }
        });

        zoomedImage.addEventListener('touchend', () => {
            isDragging = false;
        });

        modal.querySelector('.modal-close').addEventListener('click', () => modal.remove());
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') modal.remove();
        });

        // Update boundaries on window resize
        window.addEventListener('resize', () => {
            const { maxTranslateX, maxTranslateY } = updateBoundaries();
            translateX = Math.min(maxTranslateX, Math.max(-maxTranslateX, translateX));
            translateY = Math.min(maxTranslateY, Math.max(-maxTranslateY, translateY));
            zoomedImage.style.transform = `translate(${translateX}px, ${translateY}px) scale(1.0)`;
        });
    }
</script>
<style>
    :root { --primary-blue: #0207ba; --accent-orange: #ff7900; --white: #ffffff; --shadow: rgba(2, 7, 186, 0.15); }
    body { background: linear-gradient(135deg, var(--primary-blue), #1e3c72); display: flex; flex-direction: column; min-height: 100vh; margin: 0; font-family: 'Segoe UI', sans-serif; }
    .fixed-marquee { 
        position: fixed; 
        top: 0; 
        width: 100%; 
        background: linear-gradient(90deg, var(--primary-blue), var(--accent-orange)); 
        color: #fff000; 
        padding: 10px 0; 
        z-index: 1000; 
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3); 
        overflow: hidden; 
    }
    .marquee-text { 
        display: inline-block; 
        white-space: nowrap; 
        position: relative; 
    }
    .content-container { 
        flex: 1; 
        background: rgba(255, 255, 255, 0.95); 
        border-radius: 20px; 
        box-shadow: 0 20px 40px var(--shadow); 
        padding: 4rem 3.5rem; 
        max-width: 900px; 
        margin: 60px auto 0; 
        text-align: center; 
        animation: slideUp 1s; 
        border: 2px solid var(--accent-orange); 
    }
    .calendar-section { margin: 30px 0; animation: zoomIn 1s; }
    .calendar-section img { width: 100%; border-radius: 15px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2); cursor: pointer; }
    .events-slider { 
        position: relative; 
        height: 150px; 
        overflow: hidden; 
        margin-top: 20px; 
        background: rgba(2, 7, 186, 0.1); 
        border-radius: 10px; 
        padding: 10px; 
        cursor: pointer; 
    }
    .event { 
        width: 90%; 
        text-align: center; 
        color: var(--primary-blue); 
        font-size: 1.2rem; 
        background: #fff; 
        padding: 15px; 
        border-radius: 10px; 
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); 
    }
    .event h4 { color: var(--accent-orange); margin-bottom: 5px; }
    .event p { color: #555; font-size: 0.9rem; }
    .btn-primary { 
        background: linear-gradient(135deg, var(--primary-blue), var(--accent-orange)); 
        color: #fff000; 
        padding: 1.5rem 3rem; 
        border-radius: 15px; 
        font-size: 1.3rem; 
        transition: transform 0.3s, box-shadow 0.3s; 
        border: 2px solid #fff000; 
    }
    .btn-primary:hover { 
        transform: translateY(-5px); 
        box-shadow: 0 10px 20px rgba(2, 7, 186, 0.4); 
    }
    .btn-primary:disabled { background: #ccc; }
    .event-description-section { 
        background: rgba(255, 255, 255, 0.8); 
        padding: 20px; 
        border-radius: 10px; 
        margin-bottom: 30px; 
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1); 
    }
    .section { margin-top: 30px; }
    #countdown { margin: 20px 0; font-size: 1.2rem; color: var(--primary-blue); }
    .modal-overlay { 
        position: fixed; 
        top: 0; 
        left: 0; 
        width: 100%; 
        height: 100%; 
        background: rgba(0, 0, 0, 0.85); 
        z-index: 2000; 
        display: flex; 
        justify-content: center; 
        align-items: center; 
    }
    .modal-content { 
        position: relative; 
        background: #fff; 
        padding: 20px; 
        border-radius: 15px; 
        max-width: 90%; 
        max-height: 90vh; 
        overflow-y: auto; 
        text-align: center; 
        animation: fadeIn 0.5s; 
    }
    .modal-event { margin-bottom: 20px; }
    .modal-event h4 { color: var(--accent-orange); }
    .modal-event p { color: #333; font-size: 1rem; }
    .modal-close { 
        position: absolute; 
        top: 10px; 
        right: 10px; 
        background: var(--primary-blue); 
        color: #fff000; 
        border: none; 
        padding: 8px 12px; 
        border-radius: 50%; 
        cursor: pointer; 
        font-size: 1.5rem; 
        font-weight: bold; 
        z-index: 2001; 
    }
    .modal-close:hover { background: #fff000; color: var(--primary-blue); }
    .image-modal-overlay { 
        position: fixed; 
        top: 0; 
        left: 0; 
        width: 100%; 
        height: 100%; 
        background: rgba(0, 0, 0, 0.85); 
        z-index: 2000; 
        display: flex; 
        justify-content: center; 
        align-items: center; 
        overflow: auto; 
        overscroll-behavior: none; 
    }
    .image-modal-content { 
        position: relative; 
        width: 100%; 
        height: 100%; 
        display: flex; 
        justify-content: center; 
        align-items: center; 
        overflow: visible; 
    }
    .zoomed-image { 
        width: auto; 
        height: auto; 
        max-width: 90vw; 
        max-height: 90vh; 
        cursor: grab; 
        user-select: none; 
        animation: zoomOut 0.5s forwards; 
        transform-origin: center; 
        touch-action: none; 
    }
    .zoomed-image:active { cursor: grabbing; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideUp { from { transform: translateY(100%); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    @keyframes zoomIn { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    @keyframes zoomOut { from { transform: scale(0.8); } to { transform: scale(1.0); } }
    @media (max-width: 768px) { 
        .content-container { padding: 2rem; max-width: 90%; margin-top: 80px; } 
        .events-slider { height: 200px; } 
        .event { font-size: 1rem; padding: 10px; } 
        .modal-content { max-width: 95%; padding: 15px; }
        .zoomed-image { 
            max-width: 95vw; 
            max-height: 85vh; 
        }
    }
    @media (max-width: 480px) { 
        .content-container { padding: 1.5rem; } 
        .events-slider { height: 250px; } 
        .event { font-size: 0.9rem; } 
        .btn-primary { padding: 1rem 2rem; font-size: 1.1rem; }
        .zoomed-image { 
            max-width: 98vw; 
            max-height: 80vh; 
        }
    }
</style>