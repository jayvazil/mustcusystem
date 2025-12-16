<?php
require_once '../shared/config/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$errors = [];
$success = '';

// Handle post creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $post_id = filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT);
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);

    if (empty($post_id) || !in_array($action, ['approve', 'reject'])) {
        $errors[] = 'Invalid post or action.';
    } else {
        $stmt = $pdo->prepare("UPDATE posts SET status = ?, approved_by = ?, approved_at = NOW() WHERE id = ?");
        $stmt->execute([$action === 'approve' ? 'approved' : 'rejected', $_SESSION['user_id'], $post_id]);
        $success = "Post $action successfully.";
    }
}

// Pagination settings
$posts_per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
if (!in_array($posts_per_page, [20, 50, 100])) {
    $posts_per_page = 20; // Default to 20 if invalid
}
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $posts_per_page;

// Fetch total count of approved posts for pagination
$stmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE status = 'approved'");
$stmt->execute();
$total_posts = $stmt->fetchColumn();
$total_pages = ceil($total_posts / $posts_per_page);

// Fetch posts with pagination
$stmt = $pdo->prepare("
    SELECT p.id, p.content, p.audience, p.submitted_at, p.status, 
           m.name AS creator_name, m.position, m.docket, 
           a.name AS approver_name 
    FROM posts p 
    JOIN leaders m ON p.creator_id = m.id 
    LEFT JOIN leaders a ON p.approved_by = a.id 
    WHERE p.status IN ('pending', 'approved')
    ORDER BY p.submitted_at DESC
    LIMIT ? OFFSET ?
");
$stmt->bindValue(1, $posts_per_page, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Posts - MUST CU</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .alert {
            border-radius: 12px;
            border: none;
            margin-bottom: 20px;
            font-size: 1.1rem;
            padding: 1.2rem;
            text-align: center;
            animation: slideIn 0.5s ease-in forwards, fadeOut 0.5s ease-out 4.5s forwards;
            opacity: 0;
            transform: translateY(-20px);
        }

        .alert-danger {
            background: linear-gradient(135deg, #fee2e2, #fce8e8);
            border-left: 4px solid #d63384;
            color: #dc2626;
        }

        .alert-success {
            background: linear-gradient(135deg, #d1fae5, #e6f7ed);
            border-left: 4px solid #a7f3d0;
            color: #065f46;
        }

        @keyframes slideIn {
            0% { opacity: 0; transform: translateY(-20px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeOut {
            0% { opacity: 1; }
            100% { opacity: 0; height: 0; margin: 0; padding: 0; }
        }

        .pagination {
            justify-content: center;
            margin-top: 20px;
        }

        .pagination .page-link {
            color: #0207ba;
            border-radius: 8px;
            margin: 0 5px;
            transition: all 0.3s ease;
        }

        .pagination .page-link:hover {
            background-color: #0207ba;
            color: #fff000;
        }

        .pagination .active .page-link {
            background-color: #0207ba;
            color: #fff000;
            border-color: #0207ba;
        }

        .pagination-select {
            margin-left: 20px;
            display: inline-flex;
            align-items: center;
        }

        .pagination-select label {
            margin-right: 10px;
            font-weight: 600;
        }

        .pagination-select select {
            border-radius: 8px;
            padding: 5px;
            border: 2px solid #0207ba;
        }

        .sender-column, .content-column, .date-column, .action-column {
            vertical-align: middle;
        }

        .btn-bounce {
            transition: transform 0.2s ease;
        }

        .btn-bounce:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container mt-5">
        <h2 class="text-center mb-4"><i class="fas fa-check"></i> Approve Posts</h2>
        <?php if ($errors): ?>
            <?php foreach ($errors as $error): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle me-2"></i>
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>
                <p><?php echo htmlspecialchars($success); ?></p>
            </div>
        <?php endif; ?>
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead style="background-color: #0207ba; color: #fff000;">
                            <tr>
                                <th>Sender</th>
                                <th>Content</th>
                                <th>Audience</th>
                                <th>Submitted At</th>
                                <th>Status</th>
                                <th>Approved By</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td class="sender-column">
                                        <?php 
                                        echo htmlspecialchars($post['creator_name']); 
                                        if ($post['position']) {
                                            echo '<br><small>Position: ' . htmlspecialchars($post['position']) . '</small>';
                                        }
                                        if ($post['docket']) {
                                            echo '<br><small>Docket: ' . htmlspecialchars($post['docket']) . '</small>';
                                        }
                                        ?>
                                    </td>
                                    <td class="content-column"><?php echo htmlspecialchars(substr($post['content'], 0, 100)); ?>...</td>
                                    <td><?php echo htmlspecialchars($post['audience']); ?></td>
                                    <td class="date-column"><?php echo date('Y-m-d h:i A', strtotime($post['submitted_at'])); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst($post['status'])); ?></td>
                                    <td><?php echo $post['approver_name'] ? htmlspecialchars($post['approver_name']) : '-'; ?></td>
                                    <td class="action-column">
                                        <?php if ($post['status'] === 'pending'): ?>
                                            <form method="POST" class="d-flex flex-wrap gap-2">
                                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                                <button type="submit" name="action" value="approve" class="btn btn-success btn-sm btn-bounce"><i class="fas fa-check"></i> Approve</button>
                                                <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm btn-bounce"><i class="fas fa-times"></i> Reject</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted">No actions available</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination Controls -->
        <nav aria-label="Post pagination" class="pagination">
            <ul class="pagination">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&per_page=<?php echo $posts_per_page; ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&per_page=<?php echo $posts_per_page; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&per_page=<?php echo $posts_per_page; ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
            <div class="pagination-select">
                <label for="posts_per_page">Posts per page:</label>
                <select id="posts_per_page" onchange="window.location.href='?page=1&per_page=' + this.value">
                    <option value="20" <?php echo $posts_per_page == 20 ? 'selected' : ''; ?>>20</option>
                    <option value="50" <?php echo $posts_per_page == 50 ? 'selected' : ''; ?>>50</option>
                    <option value="100" <?php echo $posts_per_page == 100 ? 'selected' : ''; ?>>100</option>
                </select>
            </div>
        </nav>
    </div>
    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-dismiss alerts after 4.5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    if (alert && alert.parentNode) {
                        alert.style.animation = 'fadeOut 0.5s ease-out forwards';
                        setTimeout(() => {
                            alert.remove();
                        }, 500);
                    }
                }, 4000);
            });
        });
    </script>
</body>
</html>