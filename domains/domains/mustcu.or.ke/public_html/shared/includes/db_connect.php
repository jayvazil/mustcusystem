<?php
// db_connect.php (PDO version)
function logDbError($message, $error_details = null) {
    $log_file = __DIR__ . '/database_errors.log';
    $timestamp = date('Y-m-d H:i:s T');
    $log_message = "[$timestamp] ERROR: $message";
    if ($error_details) {
        $log_message .= " - Details: " . json_encode($error_details, JSON_PRETTY_PRINT);
    }
    error_log($log_message . PHP_EOL, 3, $log_file);
}

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    return $pdo;
} catch (PDOException $e) {
    logDbError("Database connection failed", [
        'message' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
    return null;
}