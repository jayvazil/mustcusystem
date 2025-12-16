<?php
// Start session if not started (compatible with PHP 5.x)
if (!isset($_SESSION)) {
    session_start();
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include config for BASE_URL, $pdo, and session setup
require_once __DIR__ . '/shared/config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['role'])) {
    $role = strtolower(trim($_POST['role']));
    $base_dir = '/'; // Root path on live host
    $subdomains = [
        'member' => 'members.mustcu.or.ke/members',
        'leader' => 'leaders.mustcu.or.ke/leaders',
        'admin' => 'admins.mustcu.or.ke/admins',
        'associate' => 'associates.mustcu.or.ke/associates'
    ];

    

    if (array_key_exists($role, $subdomains)) {
        $redirect_url = 'https://' . $subdomains[$role] . $base_dir . 'index.php';
        // Optional: Store role in session for further use
        $_SESSION['role'] = $role;
        header("Location: " . $redirect_url);
        exit;
    } else {
        $_SESSION['error'] = "Invalid role selection. Please try again.";
        header("Location: https://mustcu.or.ke/Login_check");
        exit;
    }
} else {
    header("Location: https://mustcu.or.ke/Login_check");
    exit;
}
?>