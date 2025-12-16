<?php
// shared/config/config.php

// Safely start session
try {
    if (session_status() === PHP_SESSION_NONE) {
        // Use individual parameters for broader PHP version compatibility
        session_set_cookie_params(7200, '/', false, true, 'Lax');
        session_start();
    } else {
        // Log if session is already started to debug conflicts
        error_log("Session already started at " . date('Y-m-d H:i:s T') . " in " . __FILE__, 3, __DIR__ . '/session_errors.log');
    }
} catch (Exception $e) {
    error_log("Session setup error: " . $e->getMessage() . " at " . date('Y-m-d H:i:s T') . " in " . __FILE__, 3, __DIR__ . '/session_errors.log');
    die("Failed to initialize session: " . $e->getMessage());
}

// Define DB constants with explicit string type
define('DB_HOST', 'localhost');
define('DB_USER', 'uvwehfds_mustcu');
define('DB_PASS', '7ZwV6yxXKGrD2LPn5eSH');
define('DB_NAME', 'uvwehfds_mustcu'); // Replace with actual DB name

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
    $conn = require_once __DIR__ . '/../includes/db_connect.php';
    if ($conn === null) {
        throw new Exception("Database connection failed in db_connect.php");
    }
} catch (Exception $e) {
    error_log("Exception in config.php: " . $e->getMessage() . " at " . date('Y-m-d H:i:s T') . " in " . __FILE__, 3, __DIR__ . '/config_errors.log');
    die("Failed to establish database connection: " . $e->getMessage());
}

// Make $conn globally accessible
global $conn;