<?php
require_once '../shared/config/config.php';

header('Content-Type: application/json');

if (!isset($_GET['search']) || strlen($_GET['search']) < 2) {
    echo json_encode([]);
    exit;
}

$search = '%' . filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) . '%';
$stmt = $pdo->prepare("
    SELECT id, name, email 
    FROM members 
    WHERE (name LIKE ? OR email LIKE ?) 
    AND id NOT IN (SELECT id FROM admins)
    LIMIT 10
");
$stmt->execute([$search, $search]);
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($members);
?>