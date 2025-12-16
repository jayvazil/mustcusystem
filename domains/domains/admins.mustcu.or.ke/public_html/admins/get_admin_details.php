<?php
require_once '../shared/config/config.php';

header('Content-Type: application/json');

if (!isset($_GET['admin_id']) || !is_numeric($_GET['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid admin ID']);
    exit;
}

$admin_id = (int)$_GET['admin_id'];
$stmt = $pdo->prepare("SELECT position, docket FROM admins WHERE id = ? AND role = 'admin' AND status = 'active'");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();

if ($admin) {
    echo json_encode(['success' => true, 'position' => $admin['position'], 'docket' => $admin['docket']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Admin not found']);
}
?>