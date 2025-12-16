<?php
session_start();
$_SESSION['success'] = "You have been logged out successfully!";
$_SESSION = []; // Clear all session variables
session_destroy(); // Destroy the session
header("Location: index.php");
exit();
?>