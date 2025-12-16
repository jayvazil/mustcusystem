<?php
require_once 'includes/db_connect.php';
header('Content-Type: application/json');

$leader_id = isset($_GET['leader_id']) ? (int)$_GET['leader_id'] : 0;

if ($leader_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT position, docket FROM leaders WHERE id = ? AND status = 'active'");
        $stmt->execute([$leader_id]);
        $leader = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($leader) {
            echo json_encode([
                'success' => true,
                'position' => $leader['position'] ?? '',
                'docket' => $leader['docket'] ?? ''
            ]);
        } else {
            echo json_encode(['success' => false]);
        }
    } catch (PDOException $e) {
        error_log('Get leader details error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid leader ID']);
}
?>