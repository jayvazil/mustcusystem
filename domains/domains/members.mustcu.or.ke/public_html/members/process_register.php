<?php
// Start output buffering to capture any unintended output
ob_start();

// Disable display errors and enable logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/php_errors.log'); // Adjust path as needed

// AJAX Handler - Process AJAX requests FIRST
if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
    // Clear all output buffers
    while (ob_get_level()) ob_end_clean();
    
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-cache, must-revalidate');
    
    $response = ['success' => '', 'errors' => [], 'form_data' => []];
    
    try {
        // Try to connect to database
        if (file_exists('includes/db_connect.php')) {
            require_once 'includes/db_connect.php';
        } elseif (file_exists('../shared/config/config.php')) {
            require_once '../shared/config/config.php';
        } else {
            throw new Exception('Database config not found');
        }
        
        if (!isset($pdo)) {
            throw new Exception('Database connection failed');
        }
        
        // Get and validate form data
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $year = $_POST['year'] ?? '';
        $course = trim($_POST['course'] ?? '');
        $completion_month = $_POST['completion_month'] ?? '';
        $completion_year = $_POST['completion_year'] ?? '';
        $ministry = $_POST['ministry'] ?? '';
        
        $response['form_data'] = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'year' => $year,
            'course' => $course,
            'completion_month' => $completion_month,
            'completion_year' => $completion_year,
            'ministry' => $ministry
        ];
        
        // Validation
        if (empty($name) || empty($email) || empty($phone)) {
            $response['errors'][] = 'Name, email, and phone are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['errors'][] = 'Invalid email format.';
        } elseif (!preg_match('/^\+?\d{10,12}$/', $phone)) {
            $response['errors'][] = 'Invalid phone number format (10-12 digits).';
        } else {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM members WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $response['errors'][] = 'Email already registered.';
            } else {
                // Combine completion date
                $completion_date = '';
                if (!empty($completion_year) && !empty($completion_month)) {
                    $completion_date = $completion_month . '/' . $completion_year;
                }
                
                // Insert new member
                $stmt = $pdo->prepare("INSERT INTO members (name, email, phone, year, course, completion_year, ministry, submitted_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$name, $email, $phone, $year, $course, $completion_date, $ministry]);
                
                $response['success'] = 'Registration successful. Use your phone number to log in and set a password.';
                $response['form_data'] = []; // Clear form data on success
            }
        }
        
    } catch (Exception $e) {
        $response['errors'][] = 'Error: ' . $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    while (ob_get_level()) ob_end_clean();
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
    header('Access-Control-Max-Age: 86400');
    http_response_code(200);
    exit;
}

// For regular page loads, handle form submission normally
$errors = [];
$success = '';
$form_data = [];
$db_error = null;

// Try to connect for regular form submissions
try {
    if (file_exists('includes/db_connect.php')) {
        require_once 'includes/db_connect.php';
    } elseif (file_exists('../shared/config/config.php')) {
        require_once '../shared/config/config.php';
    } else {
        throw new Exception('Database config not found');
    }
    
    if (!isset($pdo)) {
        throw new Exception('Database connection not available');
    }
} catch (Exception $e) {
    $db_error = $e->getMessage();
}

// Handle regular form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['ajax']) && !$db_error) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $year = $_POST['year'] ?? '';
    $course = trim($_POST['course'] ?? '');
    $completion_month = $_POST['completion_month'] ?? '';
    $completion_year = $_POST['completion_year'] ?? '';
    $ministry = $_POST['ministry'] ?? '';
    
    $form_data = [
        'name' => $name, 'email' => $email, 'phone' => $phone, 'year' => $year,
        'course' => $course, 'completion_month' => $completion_month,
        'completion_year' => $completion_year, 'ministry' => $ministry
    ];
    
    if (empty($name) || empty($email) || empty($phone)) {
        $errors[] = 'Name, email, and phone are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    } elseif (!preg_match('/^\+?\d{10,12}$/', $phone)) {
        $errors[] = 'Invalid phone number format (10-12 digits).';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM members WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $errors[] = 'Email already registered.';
            } else {
                $completion_date = '';
                if (!empty($completion_year) && !empty($completion_month)) {
                    $completion_date = $completion_month . '/' . $completion_year;
                }
                
                $stmt = $pdo->prepare("INSERT INTO members (name, email, phone, year, course, completion_year, ministry, submitted_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$name, $email, $phone, $year, $course, $completion_date, $ministry]);
                
                $success = 'Registration successful. Use your phone number to log in and set a password.';
                $form_data = [];
            }
        } catch (Exception $e) {
            $errors[] = 'Error: ' . $e->getMessage();
        }
    }
}

// Clear output buffer for HTML output
ob_end_flush();
?>