<?php
// Logging function




require_once '../shared/config/config.php';
// Attempt connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        logDbError("Connection failed", ['error' => $conn->connect_error]);
        throw new Exception("Connection error: " . $conn->connect_error);
    }

    if (!$conn->set_charset("utf8mb4")) {
        logDbError("Charset set failed", ['error' => $conn->error]);
        throw new Exception("Charset error: " . $conn->error);
    }

    if (!$conn->ping()) {
        logDbError("Connection not alive", ['error' => $conn->error]);
        throw new Exception("Ping failed: " . $conn->error);
    }

} catch (Exception $e) {
    logDbError("Exception", ['message' => $e->getMessage()]);
    $conn = null;
}

// ✅ Return connection object so it can be captured
return $conn;
