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

// Check if members table exists
function checkMembersTable($pdo) {
    try {
        $stmt = $pdo->query("SELECT 1 FROM members LIMIT 1");
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Initialize database tables
function initializeDatabase($pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS dockets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS positions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            docket_id INT,
            name VARCHAR(100) NOT NULL,
            type ENUM('coordinator', 'secretary') NOT NULL,
            FOREIGN KEY (docket_id) REFERENCES dockets(id)
        )
    ");
    
    // Check if nominations1 table exists and modify columns if needed
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM nominations1");
        $existingColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // If table exists, modify columns to VARCHAR for storing names
        if (!empty($existingColumns)) {
            $columnsToModify = [
                'instrumentalist_coordinator', 'praise_worship_coordinator', 'choir_coordinator',
                'instrumentalist_secretary', 'praise_worship_secretary', 'choir_secretary',
                'it_coordinator', 'creative_coordinator', 'publicity_coordinator',
                'it_secretary', 'creative_secretary', 'publicity_secretary',
                'ushering_coordinator', 'catering_coordinator', 'decor_coordinator',
                'ushering_secretary', 'catering_secretary', 'decor_secretary'
            ];
            
            foreach ($columnsToModify as $column) {
                if (in_array($column, $existingColumns)) {
                    $pdo->exec("ALTER TABLE nominations1 MODIFY COLUMN `$column` VARCHAR(255) NULL");
                }
            }
        }
    } catch (Exception $e) {
        // Table doesn't exist, create it
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS nominations1 (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                docket_id INT NOT NULL,
                instrumentalist_coordinator VARCHAR(255) NULL,
                praise_worship_coordinator VARCHAR(255) NULL,
                choir_coordinator VARCHAR(255) NULL,
                instrumentalist_secretary VARCHAR(255) NULL,
                praise_worship_secretary VARCHAR(255) NULL,
                choir_secretary VARCHAR(255) NULL,
                it_coordinator VARCHAR(255) NULL,
                creative_coordinator VARCHAR(255) NULL,
                publicity_coordinator VARCHAR(255) NULL,
                it_secretary VARCHAR(255) NULL,
                creative_secretary VARCHAR(255) NULL,
                publicity_secretary VARCHAR(255) NULL,
                ushering_coordinator VARCHAR(255) NULL,
                catering_coordinator VARCHAR(255) NULL,
                decor_coordinator VARCHAR(255) NULL,
                ushering_secretary VARCHAR(255) NULL,
                catering_secretary VARCHAR(255) NULL,
                decor_secretary VARCHAR(255) NULL,
                nomination_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (docket_id) REFERENCES dockets(id)
            )
        ");
    }
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM dockets");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO dockets (name) VALUES 
            ('Music Docket'),
            ('Arts and Media Docket'),
            ('Hospitality Docket')");
        
        $pdo->exec("INSERT INTO positions (docket_id, name, type) VALUES 
            (1, 'Instrumentalist Coordinator', 'coordinator'),
            (1, 'Praise and Worship Coordinator', 'coordinator'),
            (1, 'Choir Coordinator', 'coordinator'),
            (1, 'Instrumentalist Secretary', 'secretary'),
            (1, 'Praise and Worship Secretary', 'secretary'),
            (1, 'Choir Secretary', 'secretary')");
        
        $pdo->exec("INSERT INTO positions (docket_id, name, type) VALUES 
            (2, 'IT Coordinator', 'coordinator'),
            (2, 'Creative Coordinator', 'coordinator'),
            (2, 'Publicity Coordinator', 'coordinator'),
            (2, 'IT Secretary', 'secretary'),
            (2, 'Creative Secretary', 'secretary'),
            (2, 'Publicity Secretary', 'secretary')");
        
        $pdo->exec("INSERT INTO positions (docket_id, name, type) VALUES 
            (3, 'Ushering Coordinator', 'coordinator'),
            (3, 'Catering Coordinator', 'coordinator'),
            (3, 'Decor Coordinator', 'coordinator'),
            (3, 'Ushering Secretary', 'secretary'),
            (3, 'Catering Secretary', 'secretary'),
            (3, 'Decor Secretary', 'secretary')");
    }
}

initializeDatabase($pdo);

$membersTableExists = checkMembersTable($pdo);

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$docket_id = isset($_GET['docket_id']) ? intval($_GET['docket_id']) : 0;

// Map position IDs to column names - exact mapping to match database
$positionColumns = [];
$stmt = $pdo->query("SELECT id, docket_id, name FROM positions ORDER BY docket_id, type, name");
$positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($positions as $position) {
    $name = strtolower($position['name']);
    // Replace spaces with underscores and remove 'and' completely
    $columnName = str_replace([' and ', ' '], ['_', '_'], $name);
    // Clean up any double underscores
    $columnName = preg_replace('/_+/', '_', $columnName);
    $positionColumns[$position['id']] = $columnName;
}

// Check if user has already nominated
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM nominations1 WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        if ($stmt->fetchColumn() > 0) {
            $showForm = false;
            $message = 'You have already submitted nominations for a docket.';
            $messageClass = 'error';
        }
    } catch (PDOException $e) {
        $message = 'Database error: ' . $e->getMessage();
        $messageClass = 'error';
    }
}

// Process user ID submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_user_id'])) {
    $user_id = trim($_POST['user_id']);
    if (!empty($user_id) && is_numeric($user_id)) {
        $user_id = intval($user_id);
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM members WHERE id = ?");
            $stmt->execute([$user_id]);
            if ($stmt->fetchColumn() > 0) {
                $_SESSION['user_id'] = $user_id;
                $_SESSION['role'] = 'member';
                $message = 'User ID set successfully.';
                $messageClass = 'success';
            } else {
                $message = 'Invalid Member ID.';
                $messageClass = 'error';
            }
        } catch (PDOException $e) {
            $message = 'Database error: ' . $e->getMessage();
            $messageClass = 'error';
        }
    } else {
        $message = 'Please enter a valid Member ID.';
        $messageClass = 'error';
    }
}

// Function to validate and clean nominee name
function validateNomineeName($name) {
    $name = trim($name);
    if (empty($name)) {
        return false;
    }
    // Allow letters, spaces, apostrophes, hyphens, and dots
    if (!preg_match("/^[a-zA-Z\s\'\-\.]+$/", $name)) {
        return false;
    }
    return $name;
}

// Process nominations submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_nominations']) && $showForm && isset($_SESSION['user_id'])) {
    try {
        $docket_id = intval($_POST['docket_id']);
        $user_id = $_SESSION['user_id'];
        
        $stmt = $pdo->prepare("SELECT * FROM positions WHERE docket_id = ? ORDER BY type, name");
        $stmt->execute([$docket_id]);
        $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $leaderData = array_fill_keys(array_values($positionColumns), null);
        $filledNominations = 0;
        $validationErrors = [];
        
        foreach ($positions as $position) {
            $nominee_name = isset($_POST['nominee_' . $position['id']]) ? trim($_POST['nominee_' . $position['id']]) : '';
            if (!empty($nominee_name)) {
                $validatedName = validateNomineeName($nominee_name);
                if ($validatedName !== false) {
                    $columnName = $positionColumns[$position['id']];
                    $leaderData[$columnName] = $validatedName;
                    $filledNominations++;
                } else {
                    $validationErrors[] = "Invalid name format for " . $position['name'] . ": " . htmlspecialchars($nominee_name);
                }
            }
        }
        
        if (!empty($validationErrors)) {
            $message = 'Please correct the following errors: ' . implode(', ', $validationErrors);
            $messageClass = 'error';
        } elseif ($filledNominations < 4) {
            $message = 'Please nominate at least 4 leaders.';
            $messageClass = 'error';
        } else {
            $columns = array_merge(['user_id', 'docket_id'], array_keys($leaderData));
            $placeholders = implode(',', array_fill(0, count($columns), '?'));
            $sql = "INSERT INTO nominations1 (" . implode(',', $columns) . ") VALUES ($placeholders)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_merge([$user_id, $docket_id], array_values($leaderData)));
            
            $showForm = false;
            $message = 'Nominations submitted successfully! You cannot submit again.';
            $messageClass = 'success';
            header("Location: ?page=thank_you");
            exit();
        }
    } catch (PDOException $e) {
        $message = 'Database error: ' . $e->getMessage();
        $messageClass = 'error';
    }
}

$members = [];
if ($membersTableExists) {
    $stmt = $pdo->query("SELECT id, name FROM members ORDER BY name");
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Christian Union Leader Nomination System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container1 {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        header {
            background: #0207ba;
            color: white;
            padding: 20px;
            text-align: center;
        }
        h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .subtitle {
            font-size: 16px;
            opacity: 0.8;
        }
        .content {
            padding: 30px;
        }
        .docket-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .docket-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
            border-left: 4px solid #0207ba;
        }
        .docket-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .docket-card h3 {
            color: #0207ba;
            margin-bottom: 10px;
        }
        .docket-card p {
            color: #7f8c8d;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .btn11 {
            display: inline-block;
            background: #0207ba;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.3s;
        }
        .btn11:hover {
            background: #ff7900;
        }
        .position-group {
            margin-bottom: 25px;
            padding: 15px;
            border-radius: 5px;
            background: #f8f9fa;
        }
        .coordinator {
            border-left: 5px solid #0207ba;
        }
        .secretary {
            border-left: 5px solid #ff7900;
        }
        .position-group h3 {
            margin-bottom: 15px;
            color: #0207ba;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #0207ba;
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .name-input {
            text-transform: capitalize;
        }
        .input-hint {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 3px;
            font-style: italic;
        }
        .submit-btn11 {
            background: #ff7900;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .submit-btn11:hover {
            background: #0207ba;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #0207ba;
            text-decoration: none;
            font-weight: bold;
        }
        .thank-you, .error-page {
            text-align: center;
            padding: 40px;
        }
        .thank-you h2, .error-page h2 {
            color: #0207ba;
            margin-bottom: 20px;
        }
        .success-icon {
            font-size: 60px;
            color: #ff7900;
            margin-bottom: 20px;
        }
        .error-icon {
            font-size: 60px;
            color: #0207ba;
            margin-bottom: 20px;
        }
        .message {
            margin: 20px 0;
            padding: 15px 20px;
            border-radius: 12px;
            font-size: 0.95em;
            font-weight: 500;
            text-align: center;
        }
        .error {
            background: #0207ba;
            color: white;
        }
        .success {
            background: #ff7900;
            color: white;
        }
        .instructions {
            background: #e8f4fd;
            border: 1px solid #bee5eb;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            color: #0c5460;
        }
        .instructions h4 {
            margin-bottom: 10px;
            color: #0207ba;
        }
        footer {
            text-align: center;
            padding: 20px;
            background: #ecf0f1;
            color: #7f8c8d;
            font-size: 14px;
        }
        @media (max-width: 768px) {
            .docket-grid {
                grid-template-columns: 1fr;
            }
            .content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>
    <div class="container1">
        <header>
            <h1>Christian Union Leader Nomination System</h1>
            <div class="subtitle">Select your docket and nominate coordinators and secretaries</div>
        </header>
        
        <div class="content">
            <?php if ($message): ?>
                <div class="message <?php echo htmlspecialchars($messageClass); ?>">
                    <?php if ($messageClass === 'success'): ?>
                        <span class="success-icon">✓</span>
                    <?php else: ?>
                        <span class="error-icon">✗</span>
                    <?php endif; ?>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!$membersTableExists): ?>
                <div class="message error">
                    <span class="error-icon">✗</span>
                    <strong>Error:</strong> The members table does not exist in the database. 
                    Please make sure your members table is properly set up.
                </div>
            <?php endif; ?>
            
            <?php if ($page == 'home'): ?>
                <?php if (isset($_SESSION['user_id']) && $showForm): ?>
                    <h2>Select a Docket to Nominate Leaders</h2>
                    <p>Choose one of the dockets below to nominate coordinators and secretaries for various roles.</p>
                    
                    <div class="docket-grid">
                        <div class="docket-card">
                            <h3>Music Docket</h3>
                            <p>Nominate leaders for Instrumentalist, Praise and Worship, and Choir sections</p>
                            <a href="?page=nominate&docket_id=1" class="btn11">Select Music Docket</a>
                        </div>
                        
                        <div class="docket-card">
                            <h3>Arts and Media Docket</h3>
                            <p>Nominate leaders for IT, Creative, and Publicity sections</p>
                            <a href="?page=nominate&docket_id=2" class="btn11">Select Arts & Media Docket</a>
                        </div>
                        
                        <div class="docket-card">
                            <h3>Hospitality Docket</h3>
                            <p>Nominate leaders for Ushering, Catering, and Decor sections</p>
                            <a href="?page=nominate&docket_id=3" class="btn11">Select Hospitality Docket</a>
                        </div>
                    </div>
                <?php elseif (isset($_SESSION['user_id']) && !$showForm): ?>
                    <div class="error-page">
                        <div class="error-icon">✗</div>
                        <h2>Nomination Already Submitted</h2>
                        <p>You have already submitted nominations for a docket. Each member can only nominate for one docket.</p>
                        <p><a href="?page=logout" class="btn11">Logout</a></p>
                    </div>
                <?php else: ?>
                    <h2>Enter Your Member ID</h2>
                    <p>Please enter your Member ID to proceed with nominations.</p>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="user_id">Member ID:</label>
                            <input type="text" id="user_id" name="user_id" placeholder="Enter your Member ID" required>
                        </div>
                        <button type="submit" name="set_user_id" class="submit-btn11">Set Member ID</button>
                    </form>
                <?php endif; ?>
                
            <?php elseif ($page == 'nominate' && $docket_id > 0 && $showForm && isset($_SESSION['user_id'])): ?>
                <?php
                if (!$membersTableExists) {
                    echo "<div class='message error'><span class='error-icon'>✗</span>Cannot load nomination form without members table. <a href='?page=home'>Return to homepage</a></div>";
                } else {
                    $stmt = $pdo->prepare("SELECT * FROM dockets WHERE id = ?");
                    $stmt->execute([$docket_id]);
                    $docket = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$docket) {
                        echo "<div class='message error'><span class='error-icon'>✗</span>Invalid docket selected. <a href='?page=home'>Return to docket selection</a></div>";
                    } else {
                        $stmt = $pdo->prepare("SELECT * FROM positions WHERE docket_id = ? ORDER BY type, name");
                        $stmt->execute([$docket_id]);
                        $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        $coordinators = array_filter($positions, function($pos) {
                            return $pos['type'] === 'coordinator';
                        });
                        
                        $secretaries = array_filter($positions, function($pos) {
                            return $pos['type'] === 'secretary';
                        });
                ?>
                <a href="?page=home" class="back-link">← Back to Docket Selection</a>
                <h2>Nominate Leaders for <?php echo htmlspecialchars($docket['name']); ?></h2>
                
                <div class="instructions">
                    <h4>Instructions:</h4>
                    <p>• Enter the full names of the people you wish to nominate for each position</p>
                    <p>• You must nominate at least 4 leaders</p>
                    <p>• Names should contain only letters, spaces, apostrophes, hyphens, and dots</p>
                    <p>• Example: "John Smith", "Mary O'Connor", "Jean-Pierre", "Dr. Johnson"</p>
                </div>
                
                <form method="POST" action="">
                    <input type="hidden" name="docket_id" value="<?php echo $docket_id; ?>">
                    
                    <div class="position-group coordinator">
                        <h3>Coordinators</h3>
                        <?php foreach($coordinators as $position): ?>
                            <div class="form-group">
                                <label for="nominee_<?php echo $position['id']; ?>">
                                    <?php echo htmlspecialchars($position['name']); ?>:
                                </label>
                                <input type="text" 
                                       id="nominee_<?php echo $position['id']; ?>" 
                                       name="nominee_<?php echo $position['id']; ?>" 
                                       class="name-input"
                                       placeholder="Enter full name (e.g., John Smith)"
                                       value="<?php echo isset($_POST['nominee_' . $position['id']]) ? htmlspecialchars($_POST['nominee_' . $position['id']]) : ''; ?>">
                                <div class="input-hint">Enter the full name of your nominee</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="position-group secretary">
                        <h3>Secretaries</h3>
                        <?php foreach($secretaries as $position): ?>
                            <div class="form-group">
                                <label for="nominee_<?php echo $position['id']; ?>">
                                    <?php echo htmlspecialchars($position['name']); ?>:
                                </label>
                                <input type="text" 
                                       id="nominee_<?php echo $position['id']; ?>" 
                                       name="nominee_<?php echo $position['id']; ?>" 
                                       class="name-input"
                                       placeholder="Enter full name (e.g., Jane Doe)"
                                       value="<?php echo isset($_POST['nominee_' . $position['id']]) ? htmlspecialchars($_POST['nominee_' . $position['id']]) : ''; ?>">
                                <div class="input-hint">Enter the full name of your nominee</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <button type="submit" name="submit_nominations" class="submit-btn11">Submit Nominations</button>
                </form>
                <?php 
                    } // End docket check
                } // End membersTableExists check
                ?>
                
            <?php elseif ($page == 'thank_you'): ?>
                <div class="thank-you">
                    <div class="success-icon">✓</div>
                    <h2>Thank You for Your Nominations!</h2>
                    <p>Your nominations have been successfully submitted to the nominations1 table in the database.</p>
                    <p>The names you provided will be stored and processed accordingly.</p>
                    <p><a href="dashboard.php" class="btn11">Return to Homepage</a></p>
                </div>
                
            <?php elseif ($page == 'dashboard.php'): ?>
                <?php
                session_destroy();
                header("Location: ?page=dashboard.php");
                exit();
                ?>
                
            <?php else: ?>
                <p>Page not found. <a href="?page=dashboard">Return to homepage</a></p>
            <?php endif; // End page selection ?>
        </div>
        
        <footer>
            <p>Christian Union Leader Nomination System &copy; <?php echo date('Y'); ?></p>
        </footer>
    </div>

    <script>
        // Auto-capitalize first letter of each word as user types
        document.addEventListener('DOMContentLoaded', function() {
            const nameInputs = document.querySelectorAll('.name-input');
            nameInputs.forEach(input => {
                input.addEventListener('input', function() {
                    // Capitalize first letter of each word
                    this.value = this.value.replace(/\b\w/g, function(char) {
                        return char.toUpperCase();
                    });
                });
            });
        });
    </script>
</body>
</html>
<?php // Explicitly close PHP
?>