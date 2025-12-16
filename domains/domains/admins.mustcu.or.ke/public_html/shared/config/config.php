<?php
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
        session_start();
    } else {
        error_log("Session already started at " . date('Y-m-d H:i:s T') . " in " . __FILE__, 3, __DIR__ . '/session_errors.log');
    }
} catch (Exception $e) {
    error_log("Session setup error: " . $e->getMessage() . " at " . date('Y-m-d H:i:s T') . " in " . __FILE__, 3, __DIR__ . '/session_errors.log');
    die("Failed to initialize session: " . $e->getMessage());
}

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