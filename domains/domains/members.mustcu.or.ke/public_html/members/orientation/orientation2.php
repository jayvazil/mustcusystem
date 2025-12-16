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
    if ((time() - $start_time) < 30) {
        $errors[] = 'Please wait at least 30 seconds before proceeding.';
    } else {
        $stmt = $pdo->prepare("UPDATE orientation_progress SET completed = TRUE, completed_at = NOW() WHERE user_id = :user_id AND page_number = :page_number");
        $stmt->execute([
            ':user_id' => $user_id,
            ':page_number' => $current_page
        ]);
        header('Location: orientation3.php');
        exit;
    }
}
$_SESSION['page_start_time'] = time();
?>

<?php include '../includes/header.php'; ?>

<div class="content-container">
    <h1 class="mb-4" style="color: var(--primary-blue);">
        MUST CU Leadership Structure
    </h1>
    <?php if ($errors): ?>
        <div class="alert alert-danger" style="animation: fadeIn 0.5s;"><?php echo implode('<br>', $errors); ?></div>
    <?php endif; ?>
    <div class="marquee">
        <span class="marquee-text">Welcome to MUST CU! Join us on this journey of faith and community...</span>
    </div>
    <p class="typed-text" id="typed"></p>
    <div class="leadership-grid">
        <div class="leadership-card">
            <img src="Images/Danie Kyalo.jpg" alt="Daniel Kyalo" onerror="this.src='https://via.placeholder.com/150'; console.log('Image failed to load:', this.src);" onclick="previewImage(this.src, 'Leader 1')">
            <input type="text" placeholder="Name" value="Daniel Kyalo" readonly>
            <input type="text" placeholder="Position" value="CU Chairperson" readonly>
            <textarea placeholder="Welcome message for first years..." class="message-text" readonly>Welcome to Meru university Christian Union family! We are delighted to have you onboard.We believe that in you, God has placed deep wells of wisdom and a burden for the students ministry. 
            We're excited to partner with you in spreading love, faith and truth. May God bless your ministry among us.</textarea>
            <button class="show-more-btn" onclick="showMore('1', this)">Show More</button>
        </div>
        <div class="leadership-card">
            <img src="Images/morrin 322.jpg" alt="Morrin Gichimu" onerror="this.src='https://via.placeholder.com/150'; console.log('Image failed to load:', this.src);" onclick="previewImage(this.src, 'Leader 2')">
            <input type="text" placeholder="Name" value="Morrin Gichimu" readonly>
            <input type="text" placeholder="Position" value="CU Vice Chairperson" readonly>
            <textarea placeholder="Welcome message for first years..." class="message-text" readonly>Dear Beloved First Years,
Grace and peace to you in the name of our Lord Jesus Christ!
On behalf of the Christian Union, I joyfully welcome you to campus—a place of growth, discovery, and divine purpose. As you begin this new chapter, know that you are not alone. You are part of a community that seeks to walk in faith, grow in grace, and serve in love.
Here at CU, we believe that your time on campus is not just about academic excellence, but also about deepening your relationship with God, building lasting friendships, and discovering your unique calling in Christ. Whether you're strong in faith or just beginning your spiritual journey, there's a place for you here.
We invite you to join us in fellowship, prayer, and service. Come as you are—curious, passionate, or even uncertain—and let God shape your heart and mind for His glory. Together, we will explore His Word, worship in unity, and grow into vessels of honor prepared for every good work.
Welcome home to a family that prays, encourages, and walks together in Christ. We’re excited to see the great things God will do in and through you!</textarea>
            <button class="show-more-btn" onclick="showMore('2', this)">Show More</button>
        </div>
        <div class="leadership-card">
            <img src="Images/tabitha 2.jpg" alt="Tabitha Naisiai" onerror="this.src='https://via.placeholder.com/150'; console.log('Image failed to load:', this.src);" onclick="previewImage(this.src, 'Leader 3')">
            <input type="text" placeholder="Name" value="Tabitha Naisiai" readonly>
            <input type="text" placeholder="Position" value="CU Secretary" readonly>
            <textarea placeholder="Welcome message for first years..." class="message-text" readonly>Hello dear freshers  .Welcome to must CU ,a place where you find  a new family 
in Christ and people to walk with in life and in salvation.
It is a place to grow your faith and the faith of others ,
a  place to allow yourself to be used by God to accomplish his mission of spreading the good news on earth.
Welcome once again</textarea>
            <button class="show-more-btn" onclick="showMore('3', this)">Show More</button>
        </div>
        <div class="leadership-card">
            <img src="Images/Nahashon Maina 2.jpg" alt="Nahashon Maina" onerror="this.src='https://via.placeholder.com/150'; console.log('Image failed to load:', this.src);" onclick="previewImage(this.src, 'Leader 4')">
            <input type="text" placeholder="Name" value="Nahashon Maina" readonly>
            <input type="text" placeholder="Position" value="CU Vice Secretary" readonly>
            <textarea placeholder="Welcome message for first years..." class="message-text" readonly>Praise The Lord Dear First Years
I take this opportunity to welcome the first years in the the prestigious and world class university and take this honor to 
Introduce you to the CU. This is a place you grow spiritually until you quite reach maturity. One of the doctrinal basis of the CU is the supremacy of the scriptures and through various programs and 
Services we are dedicated to ensure that the scriptures are well read and interpreted. Once again feel welcomed CU is home away from home</textarea>
            <button class="show-more-btn" onclick="showMore('4', this)">Show More</button>
        </div>
        <div class="leadership-card">
            <img src="Images/Flavian Arocho.jpg" alt="Flavian Arocho" onerror="this.src='https://via.placeholder.com/150'; console.log('Image failed to load:', this.src);" onclick="previewImage(this.src, 'Leader 5')">
            <input type="text" placeholder="Name" value="Flavian Arocho" readonly>
            <input type="text" placeholder="Position" value="CU Treasurer" readonly>
            <textarea placeholder="Welcome message for first years..." class="message-text" readonly>Hello and congratulations on beginning this exciting new chapter of your life! As you step into university life, I know it can feel like a whirlwind of new faces, places, and experiences, but you're not alone.
I’d love to invite you to be part of the Amazing MUST CHRISTIAN UNION. Whether you're strong in your faith, just curious, or simply looking for a place to belong, our doors and hearts are wide open.
A community that prays together, grows together, and supports one another; Uplifting worship, inspiring messages, and real conversations; Lifelong friendships and a deeper sense of purpose
Come as you are. Let’s journey together in faith, fellowship, and fun. Your story matters and we can’t wait to walk alongside you.</textarea>
            <button class="show-more-btn" onclick="showMore('5', this)">Show More</button>
        </div>
        <div class="leadership-card">
            <img src="https://members.mustcu.or.ke/Images/Leonard Karanja.jpg" alt="Leonard Karanja" onerror="this.src='https://via.placeholder.com/150'; console.log('Image failed to load:', this.src);" onclick="previewImage(this.src, 'Leader 6')">
            <input type="text" placeholder="Name" value="Leonard Karanja" readonly>
            <input type="text" placeholder="Position" value="Missions and Evangelism Coordinator" readonly>
            <textarea placeholder="Welcome message for first years..." class="message-text" readonly>Dear First Years',
Welcome to a new chapter of purpose, growth, and destiny! As the Mission and Evangelism team, we’re thrilled to journey with you as you discover God’s love and your kingdom assignment here at Meru University.
You’re not just joining a campus—you’re joining a family, a movement, and a mission. There’s room for you, your gifts, and your story in God’s plan.
Let’s light up this campus with Christ! 
With love,
Leonard Karanja
Mission & Evangelism Coordinator – MUSTCU</textarea>
            <button class="show-more-btn" onclick="showMore('6', this)">Show More</button>
        </div>
        <div class="leadership-card">
            <img src="Images/Elizabeth Mumo 2.jpg" alt="Elizabeth Mumo" onerror="this.src='https://via.placeholder.com/150'; console.log('Image failed to load:', this.src);" onclick="previewImage(this.src, 'Leader 7')">
            <input type="text" placeholder="Name" value="Elizabeth Mumo" readonly>
            <input type="text" placeholder="Position" value="Discipleship Coordinator" readonly>
            <textarea placeholder="Welcome message for first years..." class="message-text" readonly>Welcome first years to MUST CU !</textarea>
            <button class="show-more-btn" onclick="showMore('7', this)">Show More</button>
        </div>
        <div class="leadership-card">
            <img src="Images/sam lole.jpg" alt="Samuel Lole" onerror="this.src='https://via.placeholder.com/150'; console.log('Image failed to load:', this.src);" onclick="previewImage(this.src, 'Leader 8')">
            <input type="text" placeholder="Name" value="Samuel Lole" readonly>
            <input type="text" placeholder="Position" value="Music Coordinator" readonly>
            <textarea placeholder="Welcome message for first years..." class="message-text" readonly>Praise The Lord, 
Welcome to Meru University of Science and Technology! 
We’re excited to have you join this vibrant community where your academic journey begins and your God-given purpose unfolds. 
As the University Christian Union (CU), we warmly invite you to be part of a family of faith — a place where you can grow spiritually, 
build lasting friendships, and serve God with joy. The road ahead may have its challenges, but you're not alone; together we will seek God, 
encourage one another, and pursue excellence. As Psalm 119:9 reminds us, “How can a young person stay on the path of purity? By living according to your word.” 
You are not here by accident — God has a purpose for you here.
Karibu sana, and may this be a season of transformation and divine encounters!</textarea>
            <button class="show-more-btn" onclick="showMore('8', this)">Show More</button>
        </div>
        <div class="leadership-card">
            <img src="Images/Mitchell Wanjiku.jpg" alt="Mitchell Wajiku" onerror="this.src='https://via.placeholder.com/150'; console.log('Image failed to load:', this.src);" onclick="previewImage(this.src, 'Leader 9')">
            <input type="text" placeholder="Name" value="Mitchell Wajiku" readonly>
            <input type="text" placeholder="Position" value="Prayer Coordinator" readonly>
            <textarea placeholder="Welcome message for first years..." class="message-text" readonly>Hello and praise God, 
We joyfully welcome you to our christian union 
We believe that this will be a place where you will grow and also encounter God in a greater way
May God guide your steps throughout this journey 
May God bless you</textarea>
            <button class="show-more-btn" onclick="showMore('9', this)">Show More</button>
        </div>
        <div class="leadership-card">
            <img src="Images/Alex Muchiri (1).jpg" alt="Alex Muchiri" onerror="this.src='https://via.placeholder.com/150'; console.log('Image failed to load:', this.src);" onclick="previewImage(this.src, 'Leader 10')">
            <input type="text" placeholder="Name" value="Alex Muchiri" readonly>
            <input type="text" placeholder="Position" value="Organizing Secretary" readonly>
            <textarea placeholder="Welcome message for first years..." class="message-text" readonly>Welcome first years to MUST CU !</textarea>
            <button class="show-more-btn" onclick="showMore('10', this)">Show More</button>
        </div>
        <div class="leadership-card">
            <img src="Images/Dennis Muendo.jpg" alt="Dennis Muendo" onerror="this.src='https://via.placeholder.com/150'; console.log('Image failed to load:', this.src);" onclick="previewImage(this.src, 'Leader 11')">
            <input type="text" placeholder="Name" value="Dennis Muendo" readonly>
            <input type="text" placeholder="Position" value="Bible Study Coordinator" readonly>
            <textarea placeholder="Welcome message for first years..." class="message-text" readonly>"Welcome to the next chapter of your university journey! As you transition to your next level, remember that God's plans for you are bigger than your plans. 
            Trust in His guidance, hold on to your faith, and know that you are not alone. Let's walk this journey together, supporting and praying for one another. 
            As Jeremiah 29:11 reminds us, "For I know the plans I have for you,” declares the Lord, “plans to prosper you and not to harm you, plans to give you hope and a future." Congratulations on this milestone, and may God bless you abundantly!"</textarea>
            <button class="show-more-btn" onclick="showMore('11', this)">Show More</button>
        </div>
        <div class="leadership-card">
            <img src="Images/silas mwenfda 321.jpg" alt="Silas Mwenda" onerror="this.src='https://via.placeholder.com/150'; console.log('Image failed to load:', this.src);" onclick="previewImage(this.src, 'Leader 12')">
            <input type="text" placeholder="Name" value="Silas Mwenda" readonly>
            <input type="text" placeholder="Position" value="Non Resident Coordinator" readonly>
            <textarea placeholder="Welcome message for first years..." class="message-text" readonly>Helloo our dear younger brothers and sisters.
Let me express my great  joy for you as you join this new campus life .Welcome to MUST community ,a place to be.Just like any other ambitious rider be ready to 
face the slope with courage and boldness to enjoy the fruits of its steepness. 
Welcome to the MUST CU family,  a family every believer would wish to have. Always remember you are in the right place ,with the right people.Grace of the Lord Jesus be with you.Amen.</textarea>
            <button class="show-more-btn" onclick="showMore('12', this)">Show More</button>
        </div>
        <div class="leadership-card">
            <img src="Images/Isaac Mumo 2.jpg" alt="Isaac Mumo" onerror="this.src='https://via.placeholder.com/150'; console.log('Image failed to load:', this.src);" onclick="previewImage(this.src, 'Leader 13')">
            <input type="text" placeholder="Name" value="Isaac Mumo" readonly>
            <input type="text" placeholder="Position" value="Arts and Media Coordinator" readonly>
            <textarea placeholder="Welcome message for first years..." class="message-text" readonly>
Praise the Lord! 
Dear First Years', Congratulations on making it this far—God has indeed brought you here for a reason, and we celebrate your arrival with great anticipation and love!
University life is a new season, full of learning, growth, friendships, and discovery. It will shape your future—not only academically, but spiritually, emotionally, and personally. In this new journey, we encourage you to walk closely with God and stay grounded in your faith.
At MUST C.U., you will find:
A spiritual family that loves Jesus and desires to grow in Him,
A place where you can worship freely, study God’s Word, and pray with fellow believers,
And a platform where you can discover and use your gifts, serve others, and build lasting Christian friendships.
Whether you've known Christ for years, just got saved, or are still seeking to understand Him—we welcome you with open hearts. You belong here. 
We believe that you are not just here by chance, but by divine appointment. God has a special purpose for your life at this university (Jeremiah 29:11), and we are here to walk with you, encourage you, and see that purpose flourish.
Feel free to join our weekly fellowships, Bible studies, prayer meetings, and all the vibrant ministries we have in our Union. Don’t walk this journey alone—walk with God and with fellow believers.
Once again, welcome to campus, and welcome to MUST C.U.—a home away from home. May this be the beginning of something beautiful in your spiritual life.
Shalom and blessings Brethren!
Isaac Mumo</textarea>
            <button class="show-more-btn" onclick="showMore('13', this)">Show More</button>
        </div>
    </div>
    <div class="special-roles">
        <div class="role-card">
            <img src="Images/Stem Sheldon.jpg" alt="STEM Staff" onerror="this.src='https://via.placeholder.com/150'; console.log('Image failed to load:', this.src);" onclick="previewImage(this.src, 'STEM Staff')">
            <input type="text" placeholder="Name" value="Sheldon" readonly>
            <h4>Welcome Message</h4>
            <textarea placeholder="Enter welcome message for STEM Staff..." class="message-text" readonly>Dear First Years,
I am pleased to welcome you to Meru University and the Meru University Christian Union. As you embark on this exciting academic journey, 
we are here to support and journey with you every step of the way. Our goal is to foster spiritual growth, academic excellence, 
and a sense of community, helping you become the best version of yourself.
Warm regards,
Buyekane Sheldon,
STEM Staff
Fellowship Of Christian Unions (FOCUS KENYA)</textarea>
            <button class="show-more-btn" onclick="showMore('stem', this)">Show More</button>
        </div>
        <div class="role-card">
            <img src="Images/cupatron.jpg" alt="Patron" onerror="this.src='https://via.placeholder.com/150'; console.log('Image failed to load:', this.src);" onclick="previewImage(this.src, 'Patron')">
            <input type="text" placeholder="Name" value="John Thuita" readonly>
            <h4>Welcome Message</h4>
            <textarea placeholder="Enter welcome message for Patron..." class="message-text" readonly>Warm welcome from your Patron, first years!</textarea>
            <button class="show-more-btn" onclick="showMore('patron', this)">Show More</button>
        </div>
        <div class="role-card">
            <img src="Images/cmf (1).jpg" alt="CMF" onerror="this.src='https://via.placeholder.com/150'; console.log('Image failed to load:', this.src);" onclick="previewImage(this.src, 'CMF')">
            <input type="text" placeholder="Name" value="Joshua Maina" readonly>
            <h4>Welcome Message</h4>
            <textarea placeholder="Enter welcome message for CMF..." class="message-text" readonly>Welcome to CMF leadership, first years!</textarea>
            <button class="show-more-btn" onclick="showMore('cmf', this)">Show More</button>
        </div>
    </div>
    
    <div class="leadership-roles">
        <div class="role-description">
            <h4>Executive committee</h4>
            <p>The central governing body of the Christian Union and responsible for promoting and 
            implementing the objectives of the union.</p>
        </div>
        <div class="role-description">
            <h4>STEM Staff</h4>
            <p>Supporting the CU's growth and development through activities like training, mentorship, and discipleship.</p>
        </div>
        <div class="role-description">
            <h4>CU Patron</h4>
            <p>Upholding the CU's aims and doctrinal basis, acting as a convener for the advisory board,
             linking the CU to the university administration, and offering general advice and assistance.</p>
        </div>
        <div class="role-description">
            <h4>CMF Representative</h4>
            <p>Key bridge between FOCUS Kenya's mission and the students they serve, helping to cultivate a vibrant and impactful Christian community on campus.</p>
        </div>
    </div>
    <div id="countdown">Time remaining: <span id="countdown-timer">30</span> seconds</div>
    <form method="POST" id="nextForm">
        <button type="submit" name="next" id="nextButton" class="btn btn-primary" disabled>Next</button>
        <div id="countdown" class="countdown-timer"></div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
    let startTime = <?php echo $_SESSION['page_start_time'] ?? time(); ?>;
    let timeLeft = 30 - Math.floor((Date.now() - startTime * 1000) / 1000);
    if (timeLeft < 0) timeLeft = 0;
    const timer = document.getElementById('countdown-timer');
    const nextButton = document.getElementById('nextButton');
    const nextForm = document.getElementById('nextForm');

    function updateTimer() {
        timeLeft = 30 - Math.floor((Date.now() - startTime * 1000) / 1000);
        if (timeLeft <= 0) {
            timer.textContent = '0';
            nextButton.disabled = false;
        } else {
            timer.textContent = timeLeft;
        }
    }
    const timerInterval = setInterval(updateTimer, 1000);
    updateTimer();

    nextForm.addEventListener('submit', function(e) {
        if (nextButton.disabled) {
            e.preventDefault();
            return;
        }
        window.location.href = 'orientation3.php';
    });

    function previewImage(src, alt) {
        const modal = document.createElement('div');
        modal.className = 'modal-overlay';
        modal.innerHTML = `<div class="modal-content"><img src="${src}" alt="${alt}" class="modal-image"><button class="modal-close">X</button></div>`;
        document.body.appendChild(modal);
        modal.querySelector('.modal-close').addEventListener('click', () => modal.remove());
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.remove();
        });
    }

    function showMore(id, button) {
        const card = button.parentElement;
        const imgSrc = card.querySelector('img').src;
        const name = card.querySelector('input[placeholder="Name"]').value;
        const position = card.querySelector('input[placeholder="Position"]').value || card.querySelector('h4')?.textContent || '';
        const message = card.querySelector('.message-text').value || card.querySelector('.message-text').textContent;
        const modal = document.createElement('div');
        modal.className = 'modal-overlay';
        modal.innerHTML = `
            <div class="modal-content">
                <img src="${imgSrc}" alt="${id}" class="modal-image">
                <input type="text" value="${name}" readonly class="modal-input">
                <input type="text" value="${position}" readonly class="modal-input">
                <textarea readonly class="modal-textarea">${message}</textarea>
                <button class="modal-close">X</button>
            </div>`;
        document.body.appendChild(modal);
        modal.querySelector('.modal-close').addEventListener('click', () => modal.remove());
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.remove();
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const modal = document.querySelector('.modal-overlay');
            if (modal) modal.remove();
        }
    });
    
</script>
<style>
    :root { --primary-blue: #0207ba; --accent-orange: #ff7900; --white: #ffffff; --shadow: rgba(2, 7, 186, 0.15); }
    body { background: linear-gradient(135deg, var(--primary-blue), #1e3c72); display: flex; flex-direction: column; min-height: 100vh; margin: 0; font-family: 'Segoe UI', sans-serif; }
    .content-container { flex: 1; background: var(--white); border-radius: 20px; box-shadow: 0 20px 40px var(--shadow); padding: 3.5rem; max-width: 900px; margin: 0 auto; text-align: center; animation: slideUp 1s; }
    .leadership-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin: 20px 0; }
    .leadership-card { background: linear-gradient(135deg, #0207ba, #1e3c72); border-radius: 15px; padding: 15px; text-align: center; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2); animation: popIn 0.5s; position: relative; }
    .leadership-card img { width: 150px; height: auto; object-fit: contain; border-radius: 10px; cursor: pointer; }
    .leadership-card input, .leadership-card textarea { width: 100%; margin: 5px 0; border-radius: 5px; padding: 5px; background: #ffffff; }
    .leadership-card input:read-only, .leadership-card textarea:read-only { background: #f0f0f0; cursor: default; }
    .show-more-btn { background: var(--accent-orange); color: #fff000; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer; margin-top: 5px; }
    .show-more-btn:hover { background: #ff9f33; }
    .special-roles { display: flex; flex-wrap: wrap; gap: 20px; margin-top: 30px; justify-content: center; }
    .role-card { background: linear-gradient(135deg, #ff7900, #ff9f33); border-radius: 15px; padding: 20px; text-align: center; box-shadow: 0 10px 20px rgba(255, 121, 0, 0.3); width: 48%; animation: bounceIn 0.7s; position: relative; }
    .role-card img { width: 200px; height: auto; object-fit: contain; border-radius: 10px; cursor: pointer; }
    .role-card textarea { width: 100%; height: 100px; margin-top: 10px; border-radius: 5px; }
    .role-card input:read-only, .role-card textarea:read-only { background: #f0f0f0; cursor: default; }
    .btn-primary { background: linear-gradient(135deg, var(--primary-blue), var(--accent-orange)); color: #fff000; padding: 1.2rem 2.5rem; border-radius: 12px; font-size: 1.2rem; transition: transform 0.3s, box-shadow 0.3s; }
    .btn-primary:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(2, 7, 186, 0.4); }
    .btn-primary:disabled { background: #ccc; }
    #countdown { margin: 20px 0; font-size: 1.2rem; color: var(--primary-blue); }
    .leadership-roles { display: flex; flex-wrap: wrap; gap: 20px; margin-top: 30px; }
    .role-description { flex: 1; min-width: 200px; background: rgba(255, 255, 255, 0.9); border-radius: 10px; padding: 15px; box-shadow: 0 5px 15px var(--shadow); }
    .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.85); z-index: 2000; display: flex; justify-content: center; align-items: center; }
    .modal-content { position: relative; background: #fff; padding: 20px; border-radius: 10px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; text-align: center; }
    .modal-image { max-width: 100%; max-height: 60vh; object-fit: contain; }
    .modal-input { width: 100%; margin: 5px 0; border-radius: 5px; padding: 5px; background: #f0f0f0; border: 1px solid #ccc; font-size: 1rem; }
    .modal-textarea { width: 100%; margin: 5px 0; border-radius: 5px; padding: 5px; background: #f0f0f0; border: 1px solid #ccc; font-size: 1.3rem; min-height: 150px; }
    .modal-close { position: absolute; top: 10px; right: 10px; background: #0207ba; color: #fff000; border: none; padding: 8px 12px; border-radius: 50%; cursor: pointer; font-size: 1.2rem; font-weight: bold; z-index: 2001; line-height: 1; }
    .modal-close:hover { background: #fff000; color: #0207ba; }
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
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideUp { from { transform: translateY(50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    @keyframes popIn { from { transform: scale(0); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    @keyframes bounceIn { from { transform: scale(0.5); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    @media (max-width: 768px) { 
        .content-container { padding: 2rem; max-width: 90%; } 
        .leadership-grid { grid-template-columns: 1fr; } 
        .special-roles { flex-direction: column; } 
        .leadership-roles { flex-direction: column; } 
        .role-card { width: 100%; } 
        .modal-content { width: 95%; padding: 15px; }
        .modal-image { max-height: 50vh; }
        .modal-input, .modal-textarea { font-size: 0.9rem; }
        .modal-close { padding: 6px 10px; font-size: 1rem; }
    }
    @media (max-width: 480px) {
        .modal-content { width: 98%; padding: 10px; }
        .modal-image { max-height: 40vh; }
        .modal-input, .modal-textarea { font-size: 0.8rem; }
        .modal-close { top: 5px; right: 5px; padding: 5px 8px; font-size: 0.9rem; }
    }
</style>