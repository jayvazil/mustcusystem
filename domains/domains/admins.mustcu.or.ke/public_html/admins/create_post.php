<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'includes/db_connect.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$errors = [];
$success = '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$limit_options = [20, 50, 100, 200, 300];

// Handle file upload path
$upload_dir = '../Uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_post'])) {
        $post_id = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
        if ($post_id) {
            try {
                // Delete associated file if exists
                $stmt = $pdo->prepare("SELECT file_path FROM posts WHERE id = ?");
                $stmt->execute([$post_id]);
                $post = $stmt->fetch();
                if ($post['file_path'] && file_exists($upload_dir . $post['file_path'])) {
                    unlink($upload_dir . $post['file_path']);
                }

                $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
                $stmt->execute([$post_id]);
                $success = 'Post deleted successfully.';
                $_SESSION['success'] = $success;
                header("Location: create_post.php");
                exit();
            } catch (PDOException $e) {
                $errors[] = 'Failed to delete post: ' . $e->getMessage();
                $_SESSION['error'] = $errors[0];
                header("Location: create_post.php");
                exit();
            }
        } else {
            $errors[] = 'Invalid post ID.';
            $_SESSION['error'] = $errors[0];
            header("Location: create_post.php");
            exit();
        }
    } elseif (isset($_POST['edit_post'])) {
        $post_id = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
        $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING);
        $audience = filter_input(INPUT_POST, 'audience', FILTER_SANITIZE_STRING);

        if (empty($content) || empty($audience) || !$post_id) {
            $errors[] = 'Content, audience, and post ID are required.';
            $_SESSION['error'] = $errors[0];
            header("Location: create_post.php");
            exit();
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE posts SET content = ?, audience = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$content, $audience, $post_id]);
                $success = 'Post updated successfully.';
                $_SESSION['success'] = $success;
                header("Location: create_post.php");
                exit();
            } catch (PDOException $e) {
                $errors[] = 'Failed to update post: ' . $e->getMessage();
                $_SESSION['error'] = $errors[0];
                header("Location: create_post.php");
                exit();
            }
        }
    } else {
        // Handle post creation
        $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING);
        $audience = filter_input(INPUT_POST, 'audience', FILTER_SANITIZE_STRING);
        $file_path = null;

        if (empty($content) || empty($audience)) {
            $errors[] = 'Content and audience are required.';
            $_SESSION['error'] = $errors[0];
            header("Location: create_post.php");
            exit();
        }

        // Handle file upload
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];
            $file_type = $_FILES['attachment']['type'];
            $file_size = $_FILES['attachment']['size'];
            $max_size = 5 * 1024 * 1024; // 5MB

            if (!in_array($file_type, $allowed_types)) {
                $errors[] = 'Invalid file type. Allowed types: PDF, DOC, DOCX, JPEG, PNG.';
            } elseif ($file_size > $max_size) {
                $errors[] = 'File size exceeds 5MB limit.';
            } else {
                $file_name = uniqid() . '_' . basename($_FILES['attachment']['name']);
                $file_path = $file_name;
                if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_dir . $file_name)) {
                    $errors[] = 'Failed to upload file.';
                }
            }
        }

        if (!empty($errors)) {
            $_SESSION['error'] = $errors[0];
            header("Location: create_post.php");
            exit();
        }

        try {
            // Check for duplicate post
            $stmt = $pdo->prepare("SELECT id FROM posts WHERE content = ? AND audience = ? AND creator_type = 'admin'");
            $stmt->execute([$content, $audience]);
            if ($stmt->fetch()) {
                $errors[] = 'A post with the same content and audience already exists.';
                $_SESSION['error'] = $errors[0];
                header("Location: create_post.php");
                exit();
            }

            // Insert the post
            $stmt = $pdo->prepare("INSERT INTO posts (creator_id, creator_type, content, audience, status, submitted_at, approved_by, approved_at, file_path) VALUES (?, 'admin', ?, ?, 'approved', NOW(), ?, NOW(), ?)");
            $stmt->execute([$_SESSION['user_id'], $content, $audience, $_SESSION['user_id'], $file_path]);
            $success = 'Post created successfully.';
            $_SESSION['success'] = $success;
            header("Location: create_post.php");
            exit();
        } catch (PDOException $e) {
            $errors[] = 'Failed to create post: ' . $e->getMessage();
            $_SESSION['error'] = $errors[0];
            header("Location: create_post.php");
            exit();
        }
    }
}

// Fetch all leaders
$stmt = $pdo->query("SELECT id, name FROM leaders ORDER BY name ASC");
$leaders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch distinct ministries and years with counts
$ministries = [];
$stmt = $pdo->query("SELECT ministry, COUNT(*) as count FROM members WHERE ministry IS NOT NULL GROUP BY ministry ORDER BY ministry");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $ministries[] = ['name' => $row['ministry'], 'count' => $row['count']];
}

$years = [];
$stmt = $pdo->query("SELECT year, COUNT(*) as count FROM members WHERE year IS NOT NULL GROUP BY year ORDER BY year");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $years[] = ['name' => $row['year'], 'count' => $row['count']];
}

// Fetch member counts for general categories
$counts = [
    'all_members' => $pdo->query("SELECT COUNT(*) FROM members")->fetchColumn(),
    'all_associates' => $pdo->query("SELECT COUNT(*) FROM associates")->fetchColumn(),
    'all_leaders' => $pdo->query("SELECT COUNT(*) FROM leaders")->fetchColumn()
];

// Fetch recent posts with search and pagination
$search_query = $search ? "WHERE p.content LIKE :search OR p.audience LIKE :search" : "";
$stmt = $pdo->prepare("
    SELECT p.id, p.content, p.audience, p.status, p.submitted_at, p.approved_at, p.file_path,
           CASE 
               WHEN p.creator_type = 'admin' THEN a.name 
               WHEN p.creator_type = 'leader' THEN l.name 
               ELSE 'Unknown' 
           END AS creator_name,
           a2.name AS approver_name
    FROM posts p 
    LEFT JOIN admins a ON p.creator_id = a.id AND p.creator_type = 'admin'
    LEFT JOIN leaders l ON p.creator_id = l.id AND p.creator_type = 'leader'
    LEFT JOIN admins a2 ON p.approved_by = a2.id
    $search_query
    ORDER BY p.submitted_at DESC
    LIMIT :limit OFFSET :offset
");
if ($search) {
    $stmt->bindValue(':search', '%' . $search . '%');
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll();

// Get total posts for pagination
$stmt = $pdo->prepare("SELECT COUNT(*) FROM posts $search_query");
if ($search) {
    $stmt->bindValue(':search', '%' . $search . '%');
}
$stmt->execute();
$total_posts = $stmt->fetchColumn();
$total_pages = ceil($total_posts / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post - MUST CU Admin Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f3f4f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            margin: 0;
        }
        .create-post-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2.5rem;
            background-color: #ffffff;
            border-radius: 0.75rem;
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        h2 {
            font-size: 1.75rem;
            font-weight: 600;
            color: #0207ba;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        h3 {
            font-size: 1.5rem;
            color: #0207ba;
            margin-top: 2rem;
            margin-bottom: 1rem;
            text-align: center;
        }
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
            max-width: 600px;
            width: 100%;
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
        .form-control, .form-select {
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.75rem;
            font-size: 0.9rem;
            transition: border-color 0.2s, box-shadow 0.2s;
            max-width: 600px;
            width: 100%;
        }
        .form-control:focus, .form-select:focus {
            border-color: #0207ba;
            box-shadow: 0 0 0 2px rgba(2, 7, 186, 0.2);
            outline: none;
        }
        .form-label {
            font-size: 0.9rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        .btn-primary {
            background-color: #0207ba;
            border: none;
            padding: 0.75rem;
            font-weight: 500;
            border-radius: 0.375rem;
            transition: background-color 0.2s;
        }
        .btn-primary:hover {
            background-color: #001f7a;
        }
        .btn-danger {
            background-color: #dc2626;
            border: none;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            border-radius: 0.375rem;
            transition: background-color 0.2s;
        }
        .btn-danger:hover {
            background-color: #b91c1c;
        }
        .btn-warning {
            background-color: #ff7900;
            border: none;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            border-radius: 0.375rem;
            transition: background-color 0.2s;
        }
        .btn-warning:hover {
            background-color: #e66900;
        }
        .preloader {
            display: none;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        .preloader.active {
            display: flex;
        }
        .spinner {
            border: 4px solid #f3f4f6;
            border-top: 4px solid #fff000;
            border-radius: 50%;
            width: 2rem;
            height: 2rem;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .content-column {
            max-width: 300px;
        }
        .content-text {
            word-wrap: break-word;
        }
        .content-text.truncated {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .toggle-btn {
            color: #ff7900;
            cursor: pointer;
            text-decoration: underline;
        }
        .toggle-btn:hover {
            color: #e66900;
        }
        .table-container {
            display: flex;
            justify-content: center;
            width: 100%;
        }
        .table {
            background-color: #ffffff;
            border-radius: 0.375rem;
            overflow: hidden;
            max-width: 1000px;
            width: 100%;
            text-align: center;
        }
        .table thead {
            background-color: #0207ba;
            color: #fff000;
        }
        .table-bordered td, .table-bordered th {
            border: 1px solid #d1d5db;
            text-align: center;
        }
        .table tbody tr:hover {
            background-color: #f1f1f1;
        }
        .modal-content {
            border-radius: 0.75rem;
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        .modal-header {
            background-color: #0207ba;
            color: #ffffff;
        }
        .modal-footer .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .search-container {
            max-width: 400px;
            margin: 1.5rem auto;
            display: flex;
            justify-content: center;
        }
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
            max-width: 1000px;
            width: 100%;
            margin-left: auto;
            margin-right: auto;
        }
        .limit-selector {
            max-width: 120px;
        }
        .attachment-link {
            color: #0207ba;
            text-decoration: none;
        }
        .attachment-link:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .create-post-container {
                max-width: 100%;
                padding: 1.5rem;
            }
            .content-column {
                max-width: 200px;
            }
            .table {
                max-width: 100%;
            }
        }
        @media (max-width: 576px) {
            .create-post-container {
                padding: 1rem;
            }
            .content-column {
                max-width: 150px;
            }
            .table-responsive {
                font-size: 0.9rem;
            }
            .btn-danger, .btn-warning {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
            }
            .table {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container mt-5">
        <div class="create-post-container">
            <h2><i class="fas fa-edit"></i> Create Post</h2>
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <p><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
                </div>
            <?php endif; ?>
            <?php if (!empty($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <p><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
                </div>
            <?php endif; ?>
            <form method="POST" class="card p-4" id="postForm" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="content" class="form-label"><i class="fas fa-comment"></i> Post Content</label>
                    <textarea name="content" id="content" class="form-control" rows="6" required placeholder="Enter post content"></textarea>
                </div>
                <div class="mb-3">
                    <label for="audience" class="form-label"><i class="fas fa-users"></i> Audience</label>
                    <select name="audience" id="audience" class="form-select" required>
                        <option value="" disabled selected>Select audience</option>
                        <optgroup label="Members by Ministry">
                            <?php foreach ($ministries as $ministry): ?>
                                <option value="ministry:<?php echo htmlspecialchars($ministry['name']); ?>">
                                    <?php echo htmlspecialchars($ministry['name']) . ' (' . $ministry['count'] . ')'; ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Members by Year of Study">
                            <?php foreach ($years as $year): ?>
                                <option value="year:<?php echo htmlspecialchars($year['name']); ?>">
                                    Year <?php echo htmlspecialchars($year['name']) . ' (' . $year['count'] . ')'; ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="General">
                            <option value="all_members">All Members (<?php echo $counts['all_members']; ?>)</option>
                            <option value="all_associates">All Associates (<?php echo $counts['all_associates']; ?>)</option>
                            <option value="all_leaders">All Leaders (<?php echo $counts['all_leaders']; ?>)</option>
                        </optgroup>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="attachment" class="form-label"><i class="fas fa-paperclip"></i> Attachment (PDF, DOC, DOCX, JPEG, PNG, max 5MB)</label>
                    <input type="file" name="attachment" id="attachment" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                </div>
                <button type="submit" class="btn btn-primary">Create Post</button>
                <div class="preloader" id="preloader">
                    <div class="spinner"></div>
                </div>
            </form>
        </div>
        <h3><i class="fas fa-history"></i> Recent Posts</h3>
        <div class="search-container">
            <form method="GET" class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search posts..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
            </form>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Content</th>
                                    <th>Audience</th>
                                    <th>Creator</th>
                                    <th>Approved By</th>
                                    <th>Status</th>
                                    <th>Submitted At</th>
                                    <th>Attachment</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($posts)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No posts found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($posts as $index => $post): ?>
                                        <tr>
                                            <td class="content-column">
                                                <div class="content-text truncated" id="content-<?php echo $index; ?>">
                                                    <?php echo htmlspecialchars($post['content']); ?>
                                                </div>
                                                <?php if (strlen($post['content']) > 100): ?>
                                                    <span class="toggle-btn show-more" data-index="<?php echo $index; ?>">Show More</span>
                                                    <span class="toggle-btn show-less" data-index="<?php echo $index; ?>" style="display: none;">Show Less</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($post['audience']); ?></td>
                                            <td><?php echo htmlspecialchars($post['creator_name']); ?></td>
                                            <td><?php echo htmlspecialchars($post['approver_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars(ucfirst($post['status'])); ?></td>
                                            <td class="date-column"><?php echo date('Y-m-d h:i A', strtotime($post['submitted_at'])); ?></td>
                                            <td>
                                                <?php if ($post['file_path']): ?>
                                                    <a href="../Uploads/<?php echo htmlspecialchars($post['file_path']); ?>" class="attachment-link" target="_blank">View Attachment</a>
                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $post['id']; ?>"><i class="fas fa-edit"></i> Edit</button>
                                                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $post['id']; ?>"><i class="fas fa-trash"></i> Delete</button>
                                            </td>
                                        </tr>
                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editModal<?php echo $post['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $post['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editModalLabel<?php echo $post['id']; ?>">Edit Post</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label for="edit_content<?php echo $post['id']; ?>" class="form-label">Post Content</label>
                                                                <textarea name="content" id="edit_content<?php echo $post['id']; ?>" class="form-control" rows="6" required><?php echo htmlspecialchars($post['content']); ?></textarea>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="edit_audience<?php echo $post['id']; ?>" class="form-label">Audience</label>
                                                                <select name="audience" id="edit_audience<?php echo $post['id']; ?>" class="form-select" required>
                                                                    <option value="" disabled>Select audience</option>
                                                                    <optgroup label="Members by Ministry">
                                                                        <?php foreach ($ministries as $ministry): ?>
                                                                            <option value="ministry:<?php echo htmlspecialchars($ministry['name']); ?>" <?php echo $post['audience'] === "ministry:{$ministry['name']}" ? 'selected' : ''; ?>>
                                                                                <?php echo htmlspecialchars($ministry['name']) . ' (' . $ministry['count'] . ')'; ?>
                                                                            </option>
                                                                        <?php endforeach; ?>
                                                                    </optgroup>
                                                                    <optgroup label="Members by Year of Study">
                                                                        <?php foreach ($years as $year): ?>
                                                                            <option value="year:<?php echo htmlspecialchars($year['name']); ?>" <?php echo $post['audience'] === "year:{$year['name']}" ? 'selected' : ''; ?>>
                                                                                Year <?php echo htmlspecialchars($year['name']) . ' (' . $year['count'] . ')'; ?>
                                                                            </option>
                                                                        <?php endforeach; ?>
                                                                    </optgroup>
                                                                    <optgroup label="General">
                                                                        <option value="all_members" <?php echo $post['audience'] === 'all_members' ? 'selected' : ''; ?>>All Members (<?php echo $counts['all_members']; ?>)</option>
                                                                        <option value="all_associates" <?php echo $post['audience'] === 'all_associates' ? 'selected' : ''; ?>>All Associates (<?php echo $counts['all_associates']; ?>)</option>
                                                                        <option value="all_leaders" <?php echo $post['audience'] === 'all_leaders' ? 'selected' : ''; ?>>All Leaders (<?php echo $counts['all_leaders']; ?>)</option>
                                                                    </optgroup>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3 form-check">
                                                                <input type="checkbox" class="form-check-input" id="confirm_edit<?php echo $post['id']; ?>">
                                                                <label class="form-check-label" for="confirm_edit<?php echo $post['id']; ?>">Confirm Edit</label>
                                                            </div>
                                                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="edit_post" class="btn btn-primary" id="submit_edit<?php echo $post['id']; ?>" disabled>Save Changes</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="deleteModal<?php echo $post['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $post['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteModalLabel<?php echo $post['id']; ?>">Delete Post</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body">
                                                            <p>Are you sure you want to delete this post?</p>
                                                            <div class="mb-3 form-check">
                                                                <input type="checkbox" class="form-check-input" id="confirm_delete<?php echo $post['id']; ?>">
                                                                <label class="form-check-label" for="confirm_delete<?php echo $post['id']; ?>">Confirm Delete</label>
                                                            </div>
                                                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="delete_post" class="btn btn-danger" id="submit_delete<?php echo $post['id']; ?>" disabled>Delete</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="pagination-container">
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&limit=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                            </li>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&limit=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&limit=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                    <div class="limit-selector">
                        <form method="GET">
                            <select name="limit" class="form-select" onchange="this.form.submit()">
                                <?php foreach ($limit_options as $option): ?>
                                    <option value="<?php echo $option; ?>" <?php echo $limit === $option ? 'selected' : ''; ?>><?php echo $option; ?> per page</option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                            <input type="hidden" name="page" value="1">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const showMoreButtons = document.querySelectorAll('.show-more');
            const showLessButtons = document.querySelectorAll('.show-less');
            const form = document.getElementById('postForm');
            const preloader = document.getElementById('preloader');

            showMoreButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const index = this.getAttribute('data-index');
                    const content = document.getElementById(`content-${index}`);
                    content.classList.remove('truncated');
                    this.style.display = 'none';
                    document.querySelector(`.show-less[data-index="${index}"]`).style.display = 'inline';
                });
            });

            showLessButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const index = this.getAttribute('data-index');
                    const content = document.getElementById(`content-${index}`);
                    content.classList.add('truncated');
                    this.style.display = 'none';
                    document.querySelector(`.show-more[data-index="${index}"]`).style.display = 'inline';
                });
            });

            form.addEventListener('submit', function() {
                preloader.classList.add('active');
            });

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

            // Handle checkbox confirmation for modals
            document.querySelectorAll('.form-check-input').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const postId = this.id.replace('confirm_delete', '').replace('confirm_edit', '');
                    const submitButton = document.getElementById(`submit_${this.id.includes('delete') ? 'delete' : 'edit'}${postId}`);
                    submitButton.disabled = !this.checked;
                });
            });
        });
    </script>
</body>
</html>