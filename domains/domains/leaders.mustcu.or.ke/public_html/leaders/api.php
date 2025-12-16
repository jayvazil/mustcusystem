<?php
// api.php – Dynamic group loader (Ministry, Forum, Fellowship)
// Works with your existing $pdo from config

require_once '../shared/config/config.php'; // <-- SAME CONFIG AS Attendance.php

header('Content-Type: application/json');

// Get the requested type (Ministry, Forum, Fellowship)
$type = $_GET['type'] ?? '';

// Validate allowed types
$allowed = ['Ministry', 'Forum', 'Fellowship'];
if (!in_array($type, $allowed)) {
    echo json_encode([]);
    exit;
}

// Fetch groups of that type
try {
    $stmt = $pdo->prepare("SELECT id, name FROM groups WHERE type = ? ORDER BY name");
    $stmt->execute([$type]);
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($groups);

} catch (Exception $e) {
    // Never expose raw errors in production
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}