<?php
require_once '../shared/config/config.php';

// Fetch posts created by leaders and admins
$stmt = $pdo->prepare("SELECT id, creator_id, creator_type, content, audience, status, submitted_at, approved_by, approved_at, read_by 
                       FROM posts 
                       WHERE creator_type IN ('leader', 'admin') 
                       ORDER BY submitted_at DESC");
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$user_id = $_SESSION['user_id'] ?? null;
?>

<div class="posts-container">
    <h3 class="text-center mb-3">Recent Posts</h3>
    <?php foreach ($posts as $post): ?>
        <?php
        // Check if post is read by current user
        $read_by = json_decode($post['read_by'], true) ?: [];
        $is_read = in_array($user_id, $read_by);
        ?>
        <div class="post-card mb-3 p-3 bg-light rounded" data-post-id="<?php echo $post['id']; ?>" data-is-read="<?php echo $is_read ? 'true' : 'false'; ?>">
            <p class="post-author mb-1"><strong>Creator ID: <?php echo htmlspecialchars($post['creator_id']); ?></strong> (<?php echo htmlspecialchars($post['creator_type']); ?>) - <small><?php echo date('Y-m-d H:i', strtotime($post['submitted_at'])); ?></small></p>
            <?php if ($post['approved_by']): ?>
                <p class="text-muted mb-1"><small>Approved by <?php echo htmlspecialchars($post['approved_by']); ?> on <?php echo date('Y-m-d H:i', strtotime($post['approved_at'])); ?></small></p>
            <?php endif; ?>
            <div class="post-content">
                <?php 
                    $max_length = 100;
                    $content = htmlspecialchars($post['content']);
                    if (strlen($content) > $max_length) {
                        $short_content = substr($content, 0, $max_length) . '...';
                        echo '<span class="short-content">' . $short_content . '</span>';
                        echo '<a href="#" class="see-more" data-full-content="' . $content . '"> See More</a>';
                    } else {
                        echo '<span class="full-content">' . $content . '</span>';
                    }
                ?>
            </div>
            <?php if (!$is_read): ?>
                <button class="btn btn-sm btn-primary mark-as-read mt-2" data-post-id="<?php echo $post['id']; ?>">Mark as Read</button>
            <?php endif; ?>
            <p class="text-muted mt-1"><small>Audience: <?php echo htmlspecialchars($post['audience']); ?></small></p>
            <p class="text-muted"><small>Status: <?php echo htmlspecialchars($post['status']); ?></small></p>
        </div>
    <?php endforeach; ?>
    <?php if (empty($posts)): ?>
        <p class="text-center">No posts available.</p>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // See More functionality
    document.querySelectorAll('.see-more').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const fullContent = this.getAttribute('data-full-content');
            const contentDiv = this.parentElement;
            contentDiv.innerHTML = '<span class="full-content">' + fullContent + '</span> <a href="#" class="see-less"> See Less</a>';
        });
    });

    // See Less functionality
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('see-less')) {
            e.preventDefault();
            const contentDiv = e.target.parentElement;
            const postCard = contentDiv.closest('.post-card');
            const shortContent = contentDiv.querySelector('.short-content').textContent;
            contentDiv.innerHTML = '<span class="short-content">' + shortContent + '</span> <a href="#" class="see-more" data-full-content="' + contentDiv.querySelector('.full-content').textContent + '"> See More</a>';
        }
    });

    // Mark as Read functionality
    document.querySelectorAll('.mark-as-read').forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            fetch('mark_as_read_post.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'post_id=' + encodeURIComponent(postId) + '&user_id=' + encodeURIComponent(<?php echo json_encode($user_id); ?>)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const postCard = this.closest('.post-card');
                    postCard.setAttribute('data-is-read', 'true');
                    this.remove();
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
});
</script>

<style>
.posts-container {
    max-width: 800px;
    margin: 2rem auto;
}
.post-card {
    border: 1px solid #dee2e6;
    position: relative;
}
.post-content {
    margin-bottom: 0.5rem;
}
.see-more, .see-less {
    color: #007bff;
    text-decoration: none;
    margin-left: 0.5rem;
    cursor: pointer;
}
.see-more:hover, .see-less:hover {
    text-decoration: underline;
}
</style>