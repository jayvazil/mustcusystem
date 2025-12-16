<?php
require_once '../shared/config/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updates'])) {
    foreach ($_POST['updates'] as $update) {
        list($type, $id) = explode('|', $update);
        $table = '';
        switch ($type) {
            case 'Scripture':
                $table = 'scriptures';
                break;
            case 'Devotion':
                $table = 'daily_devotions';
                break;
            case 'Event':
                $table = 'events';
                break;
            case 'Announcement':
                $table = 'announcements';
                break;
            case 'Resource':
                $table = 'resources';
                break;
            default:
                continue;
        }
        $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
        $stmt->execute([(int)$id]);
    }
    header('Location: admin_dashboard.php?message=Selected updates deleted successfully');
    exit;
} else {
    header('Location: admin_dashboard.php?error=No updates selected for deletion');
    exit;
}
?>