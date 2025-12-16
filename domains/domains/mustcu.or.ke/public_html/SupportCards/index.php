<?php
// Database connection
$host = "localhost";
$dbname = "uvwehfds_Mustcumembersregitration2025sem2";
$username = "uvwehfds_Mustcumembersregitration2025sem2";
$password = "ZYubywsRwefMnsvdXJMs";

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS support_cards (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    card_number VARCHAR(20) NOT NULL,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);

// Process form submission
$showCard = false;
$userName = '';
$phoneNumber = '';
$cardNumber = '';

if ($_POST && isset($_POST['name'], $_POST['phone_number'])) {
    $userName = htmlspecialchars(trim($_POST['name']));
    $phoneNumber = htmlspecialchars(trim($_POST['phone_number']));
    
    // Basic validation
    if (!empty($userName) && !empty($phoneNumber) && preg_match('/^[0-9+\-]{7,20}$/', $phoneNumber)) {
        // Generate unique card number
        $cardNumber = 'MUSTCU' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        
        // Prepare and insert into database
        $stmt = $conn->prepare("INSERT INTO support_cards (name, phone_number, card_number) VALUES (?, ?, ?)");
        if ($stmt === false) {
            $error = "Prepare failed: " . $conn->connect_error;
        } else {
            $stmt->bind_param("sss", $userName, $phoneNumber, $cardNumber);
            if ($stmt->execute()) {
                $showCard = true;
            } else {
                $error = "Execute failed: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $error = "Please enter a valid name and phone number (7-20 characters, digits, +, or - only for phone).";
    }
}

// Built-in card details
$paybill = "400200";
$accountNumber = "1043428";
$amount = "Cynthia wekesa";
$projectName = "MUST CU Sound System Project";
$projectDate = "5TH OCTOBER 2025";
$creationDate = date('Y-m-d H:i:s');
$target = "371 000";

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music Sound System Support Card Generator</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #0207ba;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .form-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 30px;
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-header h1 {
            color: #333;
            font-size: 2.5em;
            margin-bottom: 10px;
            background: linear-gradient(45deg, #0207ba, #ff7900);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .form-header p {
            color: #666;
            font-size: 1.1em;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 1.1em;
        }

        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e1e1;
            border-radius: 10px;
            font-size: 1em;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
        }

        .form-group input:focus {
            outline: none;
            border-color: #0207ba;
            box-shadow: 0 0 0 3px rgba(2, 7, 186, 0.1);
            transform: translateY(-2px);
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(45deg, #0207ba, #ff7900);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.2em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(2, 7, 186, 0.3);
        }

        .submit-btn:active {
            transform: translateY(-1px);
        }

        .support-card {
            background: #FFFFFF;
            border: 3px solid #ff7900 !important;
            border-radius: 15px;
            padding: 0.5rem;
            color: white;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            position: relative;
            width: 100%;
            max-width: 500px;
            height: 800px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
        }

        .support-card::before {
            content: '';
            position: absolute;
            top: -40%;
            right: -40%;
            width: 150%;
            height: 150%;
            background: #FFFFFF;
            animation: shimmer 3s infinite;
            z-index: 0;
        }

        .card-header {
            text-align: center;
            margin-bottom: 0.3rem;
            position: relative;
            z-index: 1;
        }

        .logo-section {
            margin-bottom: 0.2rem;
        }

        .logo-placeholder img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #ccc;
            display: block;
            margin: auto;
        }

        .card-title {
            font-size: 1.5rem;
            margin-bottom: 0.2rem;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
            color: #ff7900;
            font-weight: bold;
        }

        .card-subtitle {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 0.1rem;
            color: #000000;
        }

        .card-body {
            position: relative;
            z-index: 1;
            padding: 0.3rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .supporter-info {
            background: #0207BA;
            border-radius: 8px;
            padding: 0.3rem;
            margin-bottom: 0.3rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .supporter-name {
            font-size: 1.2rem;
            font-weight: bold;
            text-align: center;
            margin-bottom: 0.2rem;
            color: #ffffff;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }

        .support-statement {
            background: #0207BA;
            border-radius: 8px;
            padding: 0.3rem;
            margin-bottom: 0.3rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            line-height: 1.4;
            font-size: 0.9rem;
            text-align: justify;
           
        }

        .support-statement .highlight {
            color: #ff7900;
            font-weight: bold;
        }

        .payment-item {
            background: #0207BA;
            border-radius: 6px;
            padding: 0.3rem;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease;
        }

        .payment-item:hover {
            transform: translateY(-3px);
        }

        .payment-label {
            font-size: 0.9rem;
            opacity: 0.9;
            font-weight: bold;
            margin-bottom: 0.1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .payment-value {
            font-size: 0.9rem;
            font-weight: bold;
            color: #ff7900;
        }

        .project-date {
            text-align: center;
            font-size: 0.9rem;
            margin-bottom: 0.2rem;
            color: #ff7900;
            font-weight: bold;
        }

        .thank-you {
            text-align: center;
            margin-bottom: 0.2rem;
            font-size: 0.7rem;
            font-style: italic;
            opacity: 0.9;
            color: #0207ba;
        }

        .card-number {
            text-align: center;
            font-size: 0.6rem;
            opacity: 0.8;
            margin-bottom: 0.2rem;
            font-family: monospace;
            color: #ff7900;
            font-weight: bold;
        }

        .card-header-extra {
            background: #FFFFFF;
            color: white;
            padding: 0.3rem;
            border-radius: 8px 8px 0 0;
            text-align: center;
            margin-bottom: 0.2rem;
            font-weight: bold;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            animation: pulse 2s infinite;
        }

        .card-header-extra h3 {
            margin: 0;
            font-size: 1rem;
        }

        .card-header-extra p {
            margin: 0.2rem 0 0;
            font-size: 0.7rem;
            opacity: 0.9;
        }

        .music-icons {
            position: absolute;
            top: 0.3rem;
            right: 0.3rem;
            font-size: 1rem;
            opacity: 0.3;
        }

        #downloadCanvas {
            display: none;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes shimmer {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }

        .exclude-from-download {
            border: 2px dashed rgba(255, 0, 0, 0.3);
            background: #cc0000;
        }

        @media (max-width: 576px) {
            .card-title { font-size: 1.3rem; }
            .card-subtitle { font-size: 0.9rem; }
            .supporter-name { font-size: 1.1rem; }
            .support-statement { font-size: 0.8rem; }
            .payment-value { font-size: 0.8rem; }
            .project-date { font-size: 0.8rem; }
            .thank-you { font-size: 0.6rem; }
            .card-number { font-size: 0.5rem; }
            .logo-placeholder img { width: 60px; height: 60px; }
        }
    </style>
</head>
<body>
    <div class="container my-4">
        <?php if (!$showCard): ?>
            <div class="form-container">
                <div class="form-header">
                    <h1>🎵 MUST CU Sound System</h1>
                    <p>Generate Your Support Card</p>
                </div>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Your Name</label>
                        <input type="text" id="name" name="name" required placeholder="Enter your full name">
                    </div>
                    <div class="form-group">
                        <label for="phone_number">Phone Number:</label>
                        <input type="text" id="phone_number" name="phone_number" placeholder="+1234567890" required>
                    </div>
                    <button type="submit" class="submit-btn">Generate Support Card</button>
                </form>
            </div>
            </div>
        <?php else: ?>
            <div class="support-card card shadow-lg" id="supportCard">
                <div class="card-header-extra exclude-from-download p-3">
                    <h3>🎉 Thank You for generating Your Support card!</h3>
                    <p>This card will help you share your fundraising goal with your friends, family members and others </p>
                    <p>We kindly request you to keep a record of the people who will support for the purposes of accounting for the money inflow please </p>
                </div>
                <div class="music-icons">🎵</div>
                
                <div class="card-header">
                    <div class="logo-section">
                        <div class="logo-placeholder">
                       
                            <img src="IMAGES/MUST CU OFFICIAL LOGO 22.jpg" alt="Logo">
                        </div>
                        <div style="font-size: 1.1em; color: #ff7900; font-weight: bold;">Meru University Christian Union</div>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="supporter-info">
                        <div class="supporter-name"> Sound System Project Support Card</div>
                    </div>
                    
                    <div class="support-statement">
                        I <span class="highlight"><?php echo $userName; ?></span> will be supporting the <span class="highlight">MUST CU 
                        Sound System Project</span> with <span class="highlight">KSH 1,000</span> for the purchase of a new sound system. 
                        I am kindly requesting your support to help me raise my target amount of KSH 1,000. A reliable sound system will 
                        enhance the effectiveness of our ministrations during fellowships, Sunday services, and outreach missions. 
                        The project will be held on <span class="highlight"><?php echo $projectDate; ?></span>. Your support will have a great impact on the work of God. This initiative aims to improve audio quality for all members and visitors, ensuring every voice is heard clearly.
                    </div>
                    
                    <div class="row row-cols-1 g-2 mb-2" style="flex-direction: column;">
                        <div class="col">
                            <div class="payment-item">
                                <div class="payment-label">Paybill Number</div>
                                <div class="payment-value"><?php echo $paybill; ?></div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="payment-item">
                                <div class="payment-label">Account Number</div>
                                <div class="payment-value"><?php echo $accountNumber; ?></div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="payment-item">
                                <div class="payment-label">Account Name</div>
                                <div class="payment-value"><?php echo $amount; ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="project-date">
                        The project target amount is <?php echo $target; ?> 
                    </div>
                    
                    <div class="thank-you">
                        "Your kind support will be of great impact to the work of God"
                        <br>
                        “Whoever brings blessing will be enriched, and one who waters will himself be watered.” <br>Proverbs 11:25
                    </div>

                    <div class="thank-you">
                        For more information, call or WhatsApp:<br><span class="highlight">0742 774365</span> 
                    </div>
                    
                    <div class="card-number">
                        Card No: <?php echo $cardNumber; ?> | Created: <?php echo date('d/m/Y H:i'); ?>
                    </div>
                    
                    <div class="d-flex flex-column flex-md-row justify-content-center gap-2">
                        <button class="download-btn btn btn-warning text-primary fw-bold" onclick="downloadCardAsImage()">📥 Download Card</button>
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="back-btn btn btn-primary fw-bold">🔄 Generate Another</a>
                    </div>
                </div>
            </div>
            <div class="music-icons">🎵</div>
            <canvas id="downloadCanvas"></canvas>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <!-- html2canvas -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        function downloadCardAsImage() {
            const card = document.getElementById('supportCard');
            const actionButtons = document.querySelector('.d-flex');
            const excludeElements = document.querySelectorAll('.exclude-from-download');
            
            const originalButtonText = document.querySelector('.download-btn').innerHTML;
            document.querySelector('.download-btn').innerHTML = '⏳ Generating...';
            document.querySelector('.download-btn').disabled = true;
            
            // Hide action buttons and excluded elements immediately
            actionButtons.style.display = 'none';
            excludeElements.forEach(el => {
                el.style.display = 'none';
            });
            
            // Scroll to top to ensure the card is fully visible
            window.scrollTo(0, 0);
            
            setTimeout(() => {
                const options = {
                    scale: 1,
                    useCORS: true,
                    allowTaint: false,
                    // Removed backgroundColor to preserve CSS background
                    width: 500,
                    height: 800,
                    scrollX: 0,
                    scrollY: -window.scrollY,
                    logging: true,
                    imageTimeout: 0,
                    removeContainer: false,
                    foreignObjectRendering: false,
                    ignoreElements: function(element) {
                        return element.classList.contains('exclude-from-download');
                    },
                    onclone: function(clonedDoc) {
                        const clonedCard = clonedDoc.getElementById('supportCard');
                        if (clonedCard) {
                            const excludedInClone = clonedCard.querySelectorAll('.exclude-from-download');
                            excludedInClone.forEach(el => {
                                el.remove();
                            });
                            
                            const clonedActionButtons = clonedCard.querySelector('.d-flex');
                            if (clonedActionButtons) {
                                clonedActionButtons.style.display = 'none !important';
                            }
                            
                            clonedCard.style.display = 'block';
                            clonedCard.style.visibility = 'visible';
                            clonedCard.style.opacity = '1';
                            clonedCard.style.transform = 'none';
                            clonedCard.style.position = 'relative';
                            clonedCard.style.top = '0';
                            clonedCard.style.left = '0';
                            clonedCard.style.border = '3px solid #ff7900 !important'; /* Ensure border is applied */
                            clonedCard.style.borderRadius = '15px';
                            clonedCard.style.overflow = 'visible';
                            clonedCard.style.width = '500px';
                            clonedCard.style.height = '800px';
                            clonedCard.style.padding = '0.5rem';
                            clonedCard.style.margin = '0';
                            
                            const style = clonedDoc.createElement('style');
                            style.textContent = `
                                .support-card::before {
                                    display: none !important;
                                }
                                .support-card {
                                    background: #FFFFFF !important;
                                    border: 3px solid #ff7900 !important;
                                    border-radius: 15px !important;
                                    overflow: visible !important;
                                    animation: none !important;
                                    box-sizing: border-box !important;
                                    padding: 0.5rem !important;
                                    margin: 0 !important;
                                    width: 500px !important;
                                    height: 800px !important;
                                    display: flex !important;
                                    flex-direction: column !important;
                                }
                                .supporter-info, .support-statement, .payment-item {
                                    background: #0207ba !important;
                                }
                                .exclude-from-download {
                                    display: none !important;
                                }
                                .d-flex {
                                    display: none !important;
                                }
                                .card-body {
                                    padding: 0.3rem !important;
                                    flex: 1 !important;
                                    display: flex !important;
                                    flex-direction: column !important;
                                    justify-content: space-between !important;
                                }
                                .row {
                                    flex-direction: column !important;
                                    margin: 0 !important;
                                }
                                .col {
                                    padding: 0 !important;
                                }
                            `;
                            clonedDoc.head.appendChild(style);
                        }
                    }
                };
                
                html2canvas(card, options).then(canvas => {
                    if (canvas.width === 0 || canvas.height === 0) {
                        throw new Error('Canvas is empty');
                    }
                    
                    const imageData = canvas.toDataURL('image/png', 1.0);
                    
                    if (imageData === 'data:,') {
                        throw new Error('Generated image is empty');
                    }
                    
                    const link = document.createElement('a');
                    const timestamp = new Date().toISOString().slice(0, 19).replace(/:/g, '-');
                    link.download = `MUST_Support_Card_<?php echo $cardNumber; ?>_${timestamp}.png`;
                    link.href = imageData;
                    
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    
                    actionButtons.style.display = 'flex';
                    excludeElements.forEach(el => {
                        el.style.display = 'block';
                    });
                    document.querySelector('.download-btn').innerHTML = originalButtonText;
                    document.querySelector('.download-btn').disabled = false;
                    
                    showSuccessMessage();
                    
                }).catch(error => {
                    console.error('Error generating image:', error);
                    
                    actionButtons.style.display = 'flex';
                    excludeElements.forEach(el => {
                        el.style.display = 'block';
                    });
                    document.querySelector('.download-btn').innerHTML = originalButtonText;
                    document.querySelector('.download-btn').disabled = false;
                    
                    showErrorMessage();
                });
            }, 500);
        }

        function showSuccessMessage() {
            const message = document.createElement('div');
            message.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3';
            message.style.zIndex = '10000';
            message.innerHTML = `
                ✅ Your Support card has been downloaded successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(message);
            
            setTimeout(() => {
                message.classList.remove('show');
                setTimeout(() => {
                    if (message.parentNode) {
                        document.body.removeChild(message);
                    }
                }, 500);
            }, 5000);
        }

        function showErrorMessage() {
            const message = document.createElement('div');
            message.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 end-0 m-3';
            message.style.zIndex = '10000';
            message.innerHTML = `
                ❌ There was an Error while generating your card. Please try again.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(message);
            
            setTimeout(() => {
                message.classList.remove('show');
                setTimeout(() => {
                    if (message.parentNode) {
                        document.body.removeChild(message);
                    }
                }, 500);
            }, 6000);
        }
    </script>
</body>
</html>