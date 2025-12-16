<?php

require_once '../shared/config/config.php';

// Authentication check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'member') {
    header('Location: index.php');
    exit;
}

// Initialize variables
$message = '';
$messageClass = '';
$showForm = true;
$userId = (int)$_SESSION['user_id']; // Cast to integer for security

try {
    // Ensure PDO is available (add check if needed)
    if (!isset($pdo)) {
        throw new Exception('PDO connection not established in config.php');
    }
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if user has already submitted
    //$stmt = $pdo->prepare('SELECT COUNT(*) FROM nominations WHERE user_id = ?');
    //$stmt->execute([$userId]);
    //if ($stmt->fetchColumn() > 0) {
       // $showForm = false;
       // $message = 'You have already submitted a nomination.';
       // $messageClass = 'error';
    

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $showForm) {
        $leaders = [];
        for ($i = 1; $i <= 13; $i++) {
            $leaders[$i] = isset($_POST["leader$i"]) ? trim($_POST["leader$i"]) : '';
            // Sanitize input
            $leaders[$i] = htmlspecialchars($leaders[$i], ENT_QUOTES, 'UTF-8');
        }

        // Validate input (at least 5 fields required)
        $filledLeaders = array_filter($leaders, fn($leader) => !empty($leader));
        if (count($filledLeaders) < 5) {
            $message = 'Please fill at least 5 leader fields.';
            $messageClass = 'error';
        } else {
            // Prepare leader data
            $leaderData = array_fill(1, 13, null);
            foreach ($leaders as $index => $leader) {
                $leaderData[$index] = !empty($leader) ? $leader : null;
            }

            // Insert into database
            $sql = 'INSERT INTO nominations (user_id, leader1, leader2, leader3, leader4, leader5, leader6, leader7, leader8, leader9, leader10, leader11, leader12, leader13)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_merge([$userId], $leaderData));

            $showForm = false;
            $message = 'Submission successful! You cannot submit again.';
            $messageClass = 'success';
        }
    }
} catch (PDOException $e) {
    // For debugging: Show full error (change back to user-friendly on production)
    $message = 'Database error: ' . $e->getMessage();
    $messageClass = 'error';
    error_log('PDOException in Nomination.php: ' . $e->getMessage());
} catch (Exception $e) {
    // For debugging: Show full error
    $message = 'Error: ' . $e->getMessage();
    $messageClass = 'error';
    error_log('Exception in Nomination.php: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Leader Nomination Portal</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #0207ba 0%, #0207ba 50%, #0207BA 100%);
      min-height: 100vh;
      padding: 20px;
      position: relative;
      overflow-x: hidden;
    }

    /* Animated background particles */
    .bg-animation {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: -1;
      overflow: hidden;
    }

    .particle {
      position: absolute;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      animation: float 20s infinite linear;
    }

    @keyframes float {
      0% {
        transform: translateY(100vh) rotate(0deg);
        opacity: 0;
      }
      10% {
        opacity: 1;
      }
      90% {
        opacity: 1;
      }
      100% {
        transform: translateY(-100px) rotate(360deg);
        opacity: 0;
      }
    }

    .form-container {
      max-width: 700px;
      margin: 0 auto;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      border-radius: 20px;
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
      padding: 40px 30px;
      border: 1px solid rgba(255, 255, 255, 0.2);
      animation: slideIn 0.8s ease-out;
      position: relative;
      overflow: hidden;
    }

    .form-container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient( #0207ba, #ff7900, #fff000);
      background-size: 200% 100%;
      animation: shimmer 3s ease-in-out infinite;
    }

    @keyframes shimmer {
      0%, 100% { background-position: 200% 0; }
      50% { background-position: -200% 0; }
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(50px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .header-section {
      text-align: center;
      margin-bottom: 35px;
    }

    .form-container h2 {
      font-size: 2em;
      font-weight: 1200;
      background: linear-gradient( #0207ba, #ff7900);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 10px;
      position: relative;
    }

    .subtitle {
      color: #666;
      font-size: 0.95em;
      margin-bottom: 20px;
    }

    .icon-container {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 80px;
      height: 80px;
      background: linear-gradient(135deg, #0207ba, #ff7900);
      border-radius: 50%;
      margin-bottom: 20px;
      box-shadow: 0 10px 30px rgba(2, 7, 186, 0.3);
      animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.05); }
    }

    .icon-container i {
      font-size: 2em;
      color: white;
    }

    .form-group {
      margin-bottom: 20px;
      position: relative;
    }

    .input-container {
      position: relative;
      overflow: hidden;
    }

    label {
      display: block;
      font-size: 0.9em;
      font-weight: 600;
      color: #555;
      margin-bottom: 8px;
      transition: all 0.3s ease;
    }

    input[type="text"] {
      width: 100%;
      padding: 15px 20px;
      font-size: 1em;
      border: 2px solid #e1e8ed;
      border-radius: 12px;
      background: #f8fafc;
      transition: all 0.3s ease;
      position: relative;
    }

    input[type="text"]:focus {
      outline: none;
      border-color: #0207ba;
      background: white;
      box-shadow: 0 0 0 3px rgba(2, 7, 186, 0.1);
      transform: translateY(-2px);
    }

    input[type="text"]:not(:placeholder-shown) {
      background: white;
      border-color: #0207ba;
    }

    /* Input animation effect */
    .input-container::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      height: 2px;
      width: 0;
      background: linear-gradient(90deg, #0207ba, #ff7900);
      transition: width 0.3s ease;
    }

    input[type="text"]:focus + .input-container::after,
    input[type="text"]:not(:placeholder-shown) + .input-container::after {
      width: 100%;
    }

    .progress-bar {
      width: 100%;
      height: 8px;
      background: #e1e8ed;
      border-radius: 10px;
      margin: 25px 0;
      overflow: hidden;
    }

    .progress-fill {
      height: 100%;
      background: linear-gradient(90deg, #0207ba, #ff7900);
      border-radius: 10px;
      width: 0%;
      transition: width 0.5s ease;
      position: relative;
    }

    .progress-fill::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
      animation: progressShine 2s ease-in-out infinite;
    }

    @keyframes progressShine {
      0% { transform: translateX(-100%); }
      100% { transform: translateX(100%); }
    }

    .progress-text {
      text-align: center;
      font-size: 0.85em;
      color: #666;
      margin-top: 5px;
    }

    button {
      width: 100%;
      padding: 16px 20px;
      background: linear-gradient(135deg, #FF7900, #ff7900);
      color: white;
      border: none;
      border-radius: 12px;
      font-size: 1.1em;
      font-weight: 600;
      cursor: pointer;
      position: relative;
      overflow: hidden;
      transition: all 0.3s ease;
      margin-top: 10px;
    }

    button:not(:disabled):hover {
      transform: translateY(-2px);
      box-shadow: 0 15px 35px rgba(2, 7, 186, 0.4);
    }

    button:not(:disabled):active {
      transform: translateY(0);
    }

    button:disabled {
      background: #ccc;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }

    /* Button ripple effect */
    button::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 0;
      height: 0;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.3);
      transform: translate(-50%, -50%);
      transition: width 0.6s, height 0.6s;
    }

    button:not(:disabled):active::before {
      width: 300px;
      height: 300px;
    }

    .message {
      margin: 20px 0;
      padding: 15px 20px;
      border-radius: 12px;
      font-size: 0.95em;
      font-weight: 500;
      text-align: center;
      animation: messageSlide 0.5s ease-out;
      position: relative;
    }

    @keyframes messageSlide {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .error {
      background: linear-gradient(135deg, #ff6b6b, #ee5a52);
      color: white;
      box-shadow: 0 10px 25px rgba(255, 107, 107, 0.3);
    }

    .success {
      background: linear-gradient(135deg, #51cf66, #40c057);
      color: white;
      box-shadow: 0 10px 25px rgba(81, 207, 102, 0.3);
    }

    .message::before {
      content: '';
      position: absolute;
      top: -2px;
      left: -2px;
      right: -2px;
      bottom: -2px;
      background: inherit;
      border-radius: 12px;
      z-index: -1;
      filter: blur(8px);
      opacity: 0.7;
    }

    /* Success checkmark animation */
    .success-icon {
      display: inline-block;
      margin-right: 10px;
      animation: bounceIn 0.8s ease-out;
    }

    @keyframes bounceIn {
      0% { transform: scale(0.3); opacity: 0; }
      50% { transform: scale(1.05); }
      70% { transform: scale(0.9); }
      100% { transform: scale(1); opacity: 1; }
    }

    .hidden {
      display: none;
    }

    /* Responsive design */
    @media (max-width: 480px) {
      body {
        padding: 10px;
      }
      
      .form-container {
        padding: 30px 20px;
        margin: 10px auto;
      }
      
      .form-container h2 {
        font-size: 1.6em;
      }
      
      input[type="text"] {
        padding: 12px 15px;
      }
      
      button {
        padding: 14px 20px;
        font-size: 1em;
      }
    }

    /* Form field focus states with colors */
    .form-group:nth-child(1) input:focus { border-color: #0207ba; box-shadow: 0 0 0 3px rgba(2, 7, 186, 0.1); }
    .form-group:nth-child(2) input:focus { border-color: #ff7900; box-shadow: 0 0 0 3px rgba(255, 121, 0, 0.1); }
    .form-group:nth-child(3) input:focus { border-color: #fff000; box-shadow: 0 0 0 3px rgba(255, 240, 0, 0.1); }
    .form-group:nth-child(4) input:focus { border-color: #0207ba; box-shadow: 0 0 0 3px rgba(2, 7, 186, 0.1); }
    .form-group:nth-child(5) input:focus { border-color: #ff7900; box-shadow: 0 0 0 3px rgba(255, 121, 0, 0.1); }
    .form-group:nth-child(6) input:focus { border-color: #fff000; box-shadow: 0 0 0 3px rgba(255, 240, 0, 0.1); }
    .form-group:nth-child(7) input:focus { border-color: #0207ba; box-shadow: 0 0 0 3px rgba(2, 7, 186, 0.1); }
    .form-group:nth-child(8) input:focus { border-color: #ff7900; box-shadow: 0 0 0 3px rgba(255, 121, 0, 0.1); }
    .form-group:nth-child(9) input:focus { border-color: #fff000; box-shadow: 0 0 0 3px rgba(255, 240, 0, 0.1); }
    .form-group:nth-child(10) input:focus { border-color: #0207ba; box-shadow: 0 0 0 3px rgba(2, 7, 186, 0.1); }
    .form-group:nth-child(11) input:focus { border-color: #ff7900; box-shadow: 0 0 0 3px rgba(255, 121, 0, 0.1); }
    .form-group:nth-child(12) input:focus { border-color: #fff000; box-shadow: 0 0 0 3px rgba(255, 240, 0, 0.1); }
    .form-group:nth-child(13) input:focus { border-color: #0207ba; box-shadow: 0 0 0 3px rgba(2, 7, 186, 0.1); }
    .form-group:nth-child(14) input:focus { border-color: #ff7900; box-shadow: 0 0 0 3px rgba(255, 121, 0, 0.1); }

    /* Tooltip for minimum requirement */
    .requirement-hint {
      font-size: 0.8em;
      color: #333;
      text-align: center;
      margin: 10px 0;
      padding: 10px;
      background: rgba(2, 7, 186, 0.1);
      border-radius: 8px;
      border-left: 4px solid #0207ba;
    }

    /* Debug info */
    .debug-info {
      background: #f0f0f0;
      padding: 10px;
      border-radius: 5px;
      margin-bottom: 20px;
      font-family: monospace;
      font-size: 0.8em;
    }
  </style>
</head>
<body>
<?php include 'includes/header.php'; ?>
  <!-- Background animation -->
  <div class="bg-animation" id="bgAnimation"></div>

  <div class="form-container">
    <div class="header-section">
      <div class="icon-container">
        <i class="fas fa-users"></i>
      </div>
      <h2>Executive Leaders Nomination Form</h2>
      <p class="subtitle">Submit your nominations for Exec leadership positions</p>
    </div>

   

    <?php if ($message): ?>
      <div class="message <?php echo htmlspecialchars($messageClass); ?>">
        <?php if ($messageClass === 'success'): ?>
          <i class="fas fa-check-circle success-icon"></i>
        <?php endif; ?>
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <?php if ($showForm): ?>
      <div class="requirement-hint">
        <i class="fas fa-info-circle"></i> Please fill at least 5 leader fields to proceed
      </div>

      <div class="progress-bar">
        <div class="progress-fill" id="progressFill"></div>
      </div>
      <div class="progress-text" id="progressText">0 of 5 minimum fields completed</div>

      <form id="leaderForm" method="post" action="">
        <?php
        $labels = [
            1 => 'CU Chairperson *',
            2 => 'CU Vice Chairperson *',
            3 => 'CU Secretary *',
            4 => 'CU Treasurer *',
            5 => 'CU Vice Secretary *',
            6 => 'Organizing Secretary',
            7 => 'Mission and Evangelism Coordinator',
            8 => 'Discipleship Coordinator',
            9 => 'Music Coordinator',
            10 => 'Prayer Coordinator',
            11 => 'Bible Study Coordinator',
            12 => 'Non Resident Coordinator',
            13 => 'Arts and Media Coordinator'
        ];
        $iconMap = [
            1 => 'crown',
            2 => 'user-shield',
            3 => 'pen-fancy',
            4 => 'coins',
            5 => 'users-cog',
            6 => 'bullhorn'
        ];
        for ($i = 1; $i <= 13; $i++): 
        ?>
          <div class="form-group">
            <label for="leader<?php echo $i; ?>">
              <i class="fas fa-<?php echo $iconMap[$i] ?? 'clipboard-list'; ?>" style="margin-right: 5px; color: #0207ba;"></i>
              <?php echo $labels[$i]; ?>
            </label>
            <div class="input-container">
              <input 
                type="text" 
                id="leader<?php echo $i; ?>" 
                name="leader<?php echo $i; ?>" 
                placeholder="Enter the <?php echo str_replace(' *', '', $labels[$i]); ?> name"
                value="<?php echo isset($_POST["leader$i"]) ? htmlspecialchars($_POST["leader$i"]) : ''; ?>"
                maxlength="100"
              >
            </div>
          </div>
        <?php endfor; ?>

        <button type="submit" id="submitBtn" disabled>
          <i class="fas fa-paper-plane" style="margin-right: 8px;"></i>
          Submit Nominations
        </button>
      </form>
    <?php endif; ?>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const form = document.getElementById('leaderForm');
      const submitBtn = document.getElementById('submitBtn');
      const progressFill = document.getElementById('progressFill');
      const progressText = document.getElementById('progressText');
      const leaderInputs = Array.from({ length: 13 }, (_, i) => document.getElementById(`leader${i + 1}`));

      // Create background particles
      function createParticles() {
        const bgAnimation = document.getElementById('bgAnimation');
        for (let i = 0; i < 15; i++) {
          const particle = document.createElement('div');
          particle.classList.add('particle');
          particle.style.width = particle.style.height = Math.random() * 6 + 2 + 'px';
          particle.style.left = Math.random() * 100 + '%';
          particle.style.animationDelay = Math.random() * 20 + 's';
          particle.style.animationDuration = (Math.random() * 10 + 15) + 's';
          bgAnimation.appendChild(particle);
        }
      }

      createParticles();

      // Validate form inputs and update progress
      const validateForm = () => {
        const filledFields = leaderInputs.filter(input => input.value.trim() !== '').length;
        const isValid = filledFields >= 5; // Match server-side requirement
        
        submitBtn.disabled = !isValid;
        
        // Update progress bar
        const progress = Math.min((filledFields / 5) * 100, 100);
        progressFill.style.width = progress + '%';
        
        // Update progress text
        if (filledFields >= 5) {
          progressText.textContent = `✓ ${filledFields} fields completed - Ready to submit!`;
          progressText.style.color = '#51cf66';
        } else {
          progressText.textContent = `${filledFields} of 5 minimum fields completed`;
          progressText.style.color = '#666';
        }
        
        // Add visual feedback to submit button
        if (isValid && !submitBtn.classList.contains('ready')) {
          submitBtn.classList.add('ready');
          submitBtn.style.animation = 'bounceIn 0.6s ease-out';
        } else if (!isValid && submitBtn.classList.contains('ready')) {
          submitBtn.classList.remove('ready');
          submitBtn.style.animation = '';
        }
      };

      // Add input animations and event listeners
      leaderInputs.forEach((input, index) => {
        input.addEventListener('input', validateForm);
        
        input.addEventListener('focus', function() {
          this.parentElement.parentElement.style.transform = 'scale(1.02)';
          this.parentElement.parentElement.style.transition = 'transform 0.2s ease';
        });
        
        input.addEventListener('blur', function() {
          this.parentElement.parentElement.style.transform = 'scale(1)';
        });

        // Add stagger animation to form fields
        input.parentElement.parentElement.style.animation = `slideIn 0.6s ease-out ${index * 0.1}s both`;
      });

      // Initial validation
      validateForm();

      // Form submission with loading state
      form.addEventListener('submit', function(e) {
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i>Submitting...';
        submitBtn.disabled = true;
      });
    });
  </script>
</body>
</html>