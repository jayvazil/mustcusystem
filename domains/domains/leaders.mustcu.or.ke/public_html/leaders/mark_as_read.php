<?php
require_once '../shared/config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id']) && isset($_POST['user_id'])) {
    $post_id = $_POST['post_id'];
    $user_id = $_POST['user_id'];
    $stmt = $pdo->prepare("SELECT read_by FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $read_by = json_decode($post['read_by'], true) ?: [];
    if (!in_array($user_id, $read_by)) {
        $read_by[] = $user_id;
        $stmt = $pdo->prepare("UPDATE posts SET read_by = ? WHERE id = ?");
        $stmt->execute([json_encode($read_by), $post_id]);
    }
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>