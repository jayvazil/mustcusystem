<?php
include '../includes/header.php';
include '../shared/includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read']) && isset($_POST['post_id'])) {
    $post_id = filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT);
    $stmt = $pdo->prepare("UPDATE posts SET read_status = 1 WHERE id = ? AND read_status = 0");
    $stmt->execute([$post_id]);
}

$stmt = $pdo->prepare("SELECT p.id, p.title, p.content, u.name AS author, p.created_at, p.read_status 
                       FROM posts p 
                       JOIN users u ON p.author_id = u.id 
                       WHERE u.role IN ('leader', 'admin') 
                       ORDER BY p.created_at DESC");
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content">
    <h2><i class="fas fa-newspaper"></i> Member Dashboard - Recent Posts</h2>
    <div class="row">
        <?php foreach ($posts as $post): ?>
            <div class="col-md-4 mb-4">
                <div class="card p-3" style="background: #ffffff; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); position: relative;">
                    <h5 class="card-title" style="color: #0207ba;"><?php echo htmlspecialchars($post['title']); ?></h5>
                    <p class="card-text" id="content-<?php echo $post['id']; ?>" style="color: #333; display: block; max-height: 100px; overflow: hidden; transition: max-height 0.3s ease;">
                        <?php echo htmlspecialchars(substr($post['content'], 0, 150)) . '...'; ?>
                    </p>
                    <?php if (strlen($post['content']) > 150): ?>
                        <button class="btn expand-btn" onclick="toggleExpand('content-<?php echo $post['id']; ?>', '<?php echo htmlspecialchars(addslashes($post['content'])); ?>')" style="background: #ff7900; color: #fff000; border-radius: 5px; border: none; padding: 5px 10px; margin-top: 5px;">Expand</button>
                    <?php endif; ?>
                    <?php if ($post['read_status'] == 0): ?>
                        <form method="POST" style="display: inline; margin-left: 10px;">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <button type="submit" name="mark_read" class="btn" style="background: #0207ba; color: #fff000; border-radius: 5px; border: none; padding: 5px 10px;">Mark as Read</button>
                        </form>
                    <?php else: ?>
                        <span style="color: #0207ba; font-size: 12px; margin-left: 10px;">Read</span>
                    <?php endif; ?>
                    <p class="text-muted" style="font-size: 12px;">By <?php echo htmlspecialchars($post['author']); ?> on <?php echo date('F d, Y', strtotime($post['created_at'])); ?></p>
                    <a href="view_post.php?id=<?php echo $post['id']; ?>" class="btn" style="background: #ff7900; color: #fff000; border-radius: 5px; text-decoration: none; margin-top: 5px;">Read More</a>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($posts)): ?>
            <p style="color: #fff000; text-align: center;">No posts available.</p>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleExpand(elementId, fullContent) {
    const element = document.getElementById(elementId);
    if (element.style.maxHeight === '100px' || element.style.maxHeight === '') {
        element.style.maxHeight = 'none';
        element.innerHTML = fullContent;
        element.parentElement.querySelector('.expand-btn').textContent = 'Collapse';
    } else {
        element.style.maxHeight = '100px';
        element.innerHTML = fullContent.substring(0, 150) + '...';
        element.parentElement.querySelector('.expand-btn').textContent = 'Expand';
    }
}
</script>

<?php include '../shared/includes/footer.php'; ?>