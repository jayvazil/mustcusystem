<?php
// Choose a session directory (use custom directory if possible to avoid /tmp conflicts)
$sessionDir = '/home/uvwehfds/domains/mustcu.or.ke/public_html/sessions';
if (!is_dir($sessionDir)) {
    if (!mkdir($sessionDir, 0700, true) && !is_dir($sessionDir)) {
        error_log("Failed to create session directory: $sessionDir at " . date('Y-m-d H:i:s T') . " in " . __FILE__, 3, __DIR__ . '/session_errors.log');
        // Fallback to /tmp if custom directory creation fails
        $sessionDir = '/tmp';
    }
}
if (!is_writable($sessionDir)) {
    error_log("Session directory is not writable: $sessionDir at " . date('Y-m-d H:i:s T') . " in " . __FILE__, 3, __DIR__ . '/session_errors.log');
    // Fallback to /tmp if not writable
    $sessionDir = '/tmp';
}
session_save_path($sessionDir);

// Safely start session
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 7200,
            'path' => '/',
            'domain' => '.mustcu.or.ke', // Enable cross-subdomain session
            'secure' => true, // Use HTTPS
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        if (!session_start()) {
            error_log("Failed to start session with path: $sessionDir at " . date('Y-m-d H:i:s T') . " in " . __FILE__, 3, __DIR__ . '/session_errors.log');
            die("Internal server error: Failed to start session. Please contact support.");
        }
        // Log session file creation
        $sessionFile = $sessionDir . '/sess_' . session_id();
        error_log(
            "Session file: $sessionFile, Exists=" . (file_exists($sessionFile) ? 'Yes' : 'No') .
            ", Writable=" . (is_writable($sessionFile) || !file_exists($sessionFile) ? 'Yes' : 'No') .
            " at " . date('Y-m-d H:i:s T'),
            3,
            __DIR__ . '/session_errors.log'
        );
    } else {
        error_log("Session already started at " . date('Y-m-d H:i:s T') . " in " . __FILE__, 3, __DIR__ . '/session_errors.log');
    }
} catch (Exception $e) {
    error_log("Session setup error: " . $e->getMessage() . " at " . date('Y-m-d H:i:s T') . " in " . __FILE__, 3, __DIR__ . '/session_errors.log');
    die("Failed to initialize session: " . $e->getMessage());
}

// Log session configuration
$session_save_path = session_save_path();
error_log(
    "Session started: Session ID=" . session_id() .
    ", Save Path=$session_save_path, Writable=" . (is_writable($session_save_path) ? 'Yes' : 'No') .
    " at " . date('Y-m-d H:i:s T'),
    3,
    __DIR__ . '/session_errors.log'
);

// Define base URL for subdomains
define('BASE_URL', 'https://members.mustcu.or.ke');

// Define DB constants with explicit string type
define('DB_HOST', 'localhost'); // Replace with live host (e.g., mysql.mustcu.or.ke)
define('DB_USER', 'uvwehfds_mustcu'); // Replace with live username
define('DB_PASS', '7ZwV6yxXKGrD2LPn5eSH'); // Replace with live password
define('DB_NAME', 'uvwehfds_mustcu'); // Replace with live database name

// Validate constants
if (!defined('DB_HOST') || !is_string(DB_HOST) || empty(DB_HOST)) {
    error_log("Invalid DB_HOST constant at " . date('Y-m-d H:i:s T') . " in " . __FILE__, 3, __DIR__ . '/config_errors.log');
    die("Database configuration error: DB_HOST is invalid.");
}
if (!defined('DB_USER') || !is_string(DB_USER) || empty(DB_USER)) {
    error_log("Invalid DB_USER constant at " . date('Y-m-d H:i:s T') . " in " . __FILE__, 3, __DIR__ . '/config_errors.log');
    die("Database configuration error: DB_USER is invalid.");
}
if (!defined('DB_PASS') || !is_string(DB_PASS)) {
    error_log("Invalid DB_PASS constant at " . date('Y-m-d H:i:s T') . " in " . __FILE__, 3, __DIR__ . '/config_errors.log');
    die("Database configuration error: DB_PASS is invalid.");
}
if (!defined('DB_NAME') || !is_string(DB_NAME) || empty(DB_NAME)) {
    error_log("Invalid DB_NAME constant at " . date('Y-m-d H:i:s T') . " in " . __FILE__, 3, __DIR__ . '/config_errors.log');
    die("Database configuration error: DB_NAME is invalid.");
}

// Include DB connection with error handling
try {
    $pdo = require_once __DIR__ . '/../includes/db_connect.php';
    if ($pdo === null) {
        throw new Exception("Database connection failed in db_connect.php");
    }
} catch (Exception $e) {
    error_log("Exception in config.php: " . $e->getMessage() . " at " . date('Y-m-d H:i:s T') . " in " . __FILE__, 3, __DIR__ . '/config_errors.log');
    die("Failed to establish database connection: " . $e->getMessage());
}

// Make $pdo globally accessible
global $pdo;
?>