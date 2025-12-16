<?php
// Start the session
session_start();

// Enable error reporting for debugging


// Restrict access to members only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'member') {
    header("Location: ../index.php");
    exit();
}

include 'includes/db_connect.php';

// Handle marking a post as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read']) && isset($_POST['post_id'])) {
    $post_id = filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT);
    try {
        $stmt = $pdo->prepare("UPDATE posts SET read_status = 1 WHERE id = ? AND read_status = 0");
        $stmt->execute([$post_id]);
        // Refresh the page to reflect the updated status
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (PDOException $e) {
        error_log("Mark as read failed: " . $e->getMessage(), 3, __DIR__ . '/debug.log');
        $error = "Error marking post as read. Please try again.";
    }
}

// Fetch posts directly from the posts table
try {
    $stmt = $pdo->prepare("
        SELECT id, title, content, created_at, read_status
        FROM posts
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Query error: " . $e->getMessage(), 3, __DIR__ . '/debug.log');
    $error = "Error fetching posts. Please try again.";
}
?>
<?php include 'includes/header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posts for Members</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }
        .container1 {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .post {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #fff;
        }
        .post.unread {
            background-color: #fff3e0; /* Light orange for unread */
            border-color: #ff9800;
        }
        .post.read {
            background-color: #e8f5e9; /* Light green for read */
            border-color: #4caf50;
        }
        .error {
            padding: 10px;
            margin-bottom: 10px;
            background-color: #ffebee;
            color: #c62828;
            border-radius: 4px;
        }
        h2 {
            color: #333;
        }
        p {
            margin: 5px 0;
        }
        .mark-read {
            background-color: #4CAF50;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .mark-read:hover {
            background-color: #45a049;
        }
        .mark-read:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="container1">
        <h2>Posts for Members</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (empty($posts)): ?>
            <p>No posts found.</p>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <div class="post <?php echo $post['read_status'] ? 'read' : 'unread'; ?>">
                    <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                    <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                    <p><small>Posted on: <?php echo $post['created_at']; ?></small></p>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <button type="submit" name="mark_read" class="mark-read" <?php echo $post['read_status'] ? 'disabled' : ''; ?>>
                            <?php echo $post['read_status'] ? 'Read' : 'Mark as Read'; ?>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
<?php include 'includes/footer.php'; ?>
</html>