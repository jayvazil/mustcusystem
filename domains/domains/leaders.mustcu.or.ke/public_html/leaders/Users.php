<?php
// Enable error reporting for debugging


require_once '../shared/config/config.php';
require_once '../vendor/autoload.php'; // For PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'leader') {
    header('Location: index.php');
    exit;
}

$errors = [];
$success = '';
if (isset($_GET['success'])) {
    $success = 'User updated successfully.';
}
if (isset($_GET['error'])) {
    $errors[] = 'Error: ' . htmlspecialchars($_GET['error']);
}
$category = isset($_GET['category']) ? $_GET['category'] : 'members';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 50;
$per_page = in_array($per_page, [50, 100, 200, 300, 500]) ? $per_page : 50;
$offset = ($page - 1) * $per_page;

$categories = ['members', 'leaders', 'associates'];
if (!in_array($category, $categories)) {
    $category = 'members';
}

$table = $category;
$condition = "";
$search_condition = "";
$params = [];

if ($search) {
    $search_term = '%' . $search . '%';
    $search_condition = " WHERE (name LIKE :search OR email LIKE :search OR phone LIKE :search";
    $params[':search'] = $search_term;
    if (strtolower($search) === 'none') {
        $search_condition .= " OR ministry IS NULL";
    }
    $search_condition .= ")";
}

// Count total records
$query_count = "SELECT COUNT(*) FROM $table $condition" . $search_condition;
$stmt = $pdo->prepare($query_count);
if ($search) {
    $stmt->bindValue(':search', $params[':search'], PDO::PARAM_STR);
}
$stmt->execute();
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// Fetch records
$query = "SELECT * FROM $table $condition" . $search_condition . " LIMIT :per_page OFFSET :offset";
$stmt = $pdo->prepare($query);
if ($search) {
    $stmt->bindValue(':search', $params[':search'], PDO::PARAM_STR);
}
$stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
        $action = $_POST['action'];

        if ($action === 'send_message') {
            $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
            if ($message) {
                $stmt = $pdo->prepare("INSERT INTO messages (user_id, sender_id, content, sent_at, is_read) VALUES (?, ?, ?, NOW(), 0)");
                $stmt->execute([$user_id, $_SESSION['user_id'], $message]);
                $success = 'Message sent to dashboard successfully.';
            } else {
                $errors[] = 'Message content is required.';
            }
        } elseif ($action === 'send_invitation') {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $invite_message = filter_input(INPUT_POST, 'invite_message', FILTER_SANITIZE_STRING);
            if ($email) {
                try {
                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host = SMTP_HOST;
                    $mail->SMTPAuth = true;
                    $mail->Username = SMTP_USERNAME;
                    $mail->Password = SMTP_PASSWORD;
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = SMTP_PORT;

                    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
                    $mail->addAddress($email);
                    $mail->isHTML(true);
                    $mail->Subject = 'Invitation to Join MUST CU System';
                    $mail->Body = '
                        <h2>Welcome to MUST CU System</h2>
                        <p>' . htmlspecialchars($invite_message ?: 'You are invited to join our platform!') . '</p>
                        <p>Please visit <a href="' . SITE_URL . '/login">our platform</a> to get started.</p>
                        <p>Best regards,<br>' . SMTP_FROM_NAME . '</p>
                    ';
                    $mail->AltBody = strip_tags($invite_message ?: 'You are invited to join our platform!') . "\nVisit " . SITE_URL . "/login to get started.";

                    $mail->send();
                    $success = 'Invitation email sent successfully.';
                } catch (Exception $e) {
                    $errors[] = 'Failed to send invitation email: ' . $mail->ErrorInfo;
                }
            } else {
                $errors[] = 'Email address is required.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - MUST CU</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .table-container {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .table-responsive {
            overflow-x: auto;
        }
        .pagination .page-link {
            color: #0207ba;
        }
        .pagination .page-item.active .page-link {
            background-color: #0207ba;
            border-color: #0207ba;
            color: #fff000;
        }
        .action-btn {
            margin-right: 5px;
        }
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.5);
        }
    </style>
</head>
<body>
    <div id="loader" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.8); z-index: 9999; text-align: center; padding-top: 20%;">
        <i class="fas fa-spinner fa-spin fa-3x"></i>
        <p>Loading...</p>
    </div>
    <?php include 'includes/header.php'; ?>
    <div class="container mt-5 table-container">
        <h2 class="text-center mb-4"><i class="fas fa-users-cog"></i> Manage Users</h2>
        <?php if ($errors): ?>
            <div class="alert alert-danger fade-in">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success fade-in"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <div class="mb-4">
            <form method="GET" id="searchForm" class="row g-3 align-items-center">
                <div class="col-auto">
                    <label for="category" class="form-label"><i class="fas fa-filter"></i> Category</label>
                </div>
                <div class="col-auto">
                    <select name="category" id="category" class="form-select" onchange="this.form.submit()">
                        <option value="members" <?php echo $category === 'members' ? 'selected' : ''; ?>>Members</option>
                        <option value="leaders" <?php echo $category === 'leaders' ? 'selected' : ''; ?>>Leaders</option>
                        <option value="associates" <?php echo $category === 'associates' ? 'selected' : ''; ?>>Associates</option>
                    </select>
                </div>
                <div class="col-auto">
                    <label for="per_page" class="form-label"><i class="fas fa-list"></i> Per Page</label>
                </div>
                <div class="col-auto">
                    <select name="per_page" id="per_page" class="form-select" onchange="this.form.submit()">
                        <option value="50" <?php echo $per_page == 50 ? 'selected' : ''; ?>>50</option>
                        <option value="100" <?php echo $per_page == 100 ? 'selected' : ''; ?>>100</option>
                        <option value="200" <?php echo $per_page == 200 ? 'selected' : ''; ?>>200</option>
                        <option value="300" <?php echo $per_page == 300 ? 'selected' : ''; ?>>300</option>
                        <option value="500" <?php echo $per_page == 500 ? 'selected' : ''; ?>>500</option>
                    </select>
                </div>
                <div class="col-auto">
                    <label for="search" class="form-label"><i class="fas fa-search"></i> Search</label>
                </div>
                <div class="col-auto">
                    <input type="text" name="search" id="search" class="form-control" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name, email, phone, or ministry">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead style="background-color: #0207ba; color: #fff000;">
                    <tr>
                        <?php if ($category === 'members'): ?>
                            <th>Ministry</th>
                            <th>Email</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Year</th>
                            <th>Course</th>
                        <?php elseif ($category === 'leaders'): ?>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Year</th>
                            <th>Course</th>
                            <th>Completion Year</th>
                            <th>Ministry</th>
                            <th>Position</th>
                            <th>Docket</th>
                        <?php elseif ($category === 'associates'): ?>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Completion Year</th>
                            <th>Ministry</th>
                            <th>Course</th>
                            <th>Position</th>
                            <th>Docket</th>
                            <th>Previous Role</th>
                        <?php endif; ?>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($records)): ?>
                        <tr>
                            <td colspan="<?php echo $category === 'members' ? 7 : 10; ?>" class="text-center">No records found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($records as $record): ?>
                            <tr>
                                <?php if ($category === 'members'): ?>
                                    <td><?php echo htmlspecialchars($record['ministry'] ?? 'None'); ?></td>
                                    <td><?php echo htmlspecialchars($record['email'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($record['name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($record['phone'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($record['year'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($record['course'] ?? 'N/A'); ?></td>
                                <?php elseif ($category === 'leaders'): ?>
                                    <td><?php echo htmlspecialchars($record['name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($record['email'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($record['phone'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($record['year'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($record['course'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($record['completion_year'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($record['ministry'] ?? 'None'); ?></td>
                                    <td><?php echo htmlspecialchars($record['position'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($record['docket'] ?? 'N/A'); ?></td>
                                <?php elseif ($category === 'associates'): ?>
                                    <td><?php echo htmlspecialchars($record['name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($record['email'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($record['phone'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($record['completion_year'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($record['ministry'] ?? 'None'); ?></td>
                                    <td><?php echo htmlspecialchars($record['course'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($record['position'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($record['docket'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($record['previous_role'] ?? 'N/A'); ?></td>
                                <?php endif; ?>
                                <td>
                                    <button class="btn btn-sm btn-primary action-btn" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $record['id']; ?>"><i class="fas fa-edit"></i> Edit</button>
                                    <button class="btn btn-sm btn-success action-btn" data-bs-toggle="modal" data-bs-target="#messageModal<?php echo $record['id']; ?>"><i class="fas fa-envelope"></i> Message</button>
                                    <button class="btn btn-sm btn-info action-btn" data-bs-toggle="modal" data-bs-target="#inviteModal<?php echo $record['id']; ?>"><i class="fas fa-paper-plane"></i> Invite</button>
                                </td>
                            </tr>
                            <!-- Edit Modal -->
                            <div class="modal fade" id="editModal<?php echo $record['id']; ?>" tabindex="-1" aria-labelledby="editModal<?php echo $record['id']; ?>Label" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editModal<?php echo $record['id']; ?>Label">Edit <?php echo htmlspecialchars($record['name']); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="POST" action="edit_user.php">
                                                <input type="hidden" name="user_id" value="<?php echo $record['id']; ?>">
                                                <input type="hidden" name="category" value="<?php echo $category; ?>">
                                                <div class="mb-3">
                                                    <label for="name" class="form-label">Name</label>
                                                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($record['name'] ?? ''); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="email" class="form-label">Email</label>
                                                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($record['email'] ?? ''); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="phone" class="form-label">Phone</label>
                                                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($record['phone'] ?? ''); ?>">
                                                </div>
                                                <?php if ($category === 'members'): ?>
                                                    <div class="mb-3">
                                                        <label for="ministry" class="form-label">Ministry</label>
                                                        <input type="text" name="ministry" class="form-control" value="<?php echo htmlspecialchars($record['ministry'] ?? ''); ?>">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="year" class="form-label">Year</label>
                                                        <input type="text" name="year" class="form-control" value="<?php echo htmlspecialchars($record['year'] ?? ''); ?>">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="course" class="form-label">Course</label>
                                                        <input type="text" name="course" class="form-control" value="<?php echo htmlspecialchars($record['course'] ?? ''); ?>">
                                                    </div>
                                                <?php elseif ($category === 'leaders'): ?>
                                                    <div class="mb-3">
                                                        <label for="year" class="form-label">Year</label>
                                                        <input type="text" name="year" class="form-control" value="<?php echo htmlspecialchars($record['year'] ?? ''); ?>">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="course" class="form-label">Course</label>
                                                        <input type="text" name="course" class="form-control" value="<?php echo htmlspecialchars($record['course'] ?? ''); ?>">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="completion_year" class="form-label">Completion Year</label>
                                                        <input type="text" name="completion_year" class="form-control" value="<?php echo htmlspecialchars($record['completion_year'] ?? ''); ?>">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="ministry" class="form-label">Ministry</label>
                                                        <input type="text" name="ministry" class="form-control" value="<?php echo htmlspecialchars($record['ministry'] ?? ''); ?>">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="position" class="form-label">Position</label>
                                                        <input type="text" name="position" class="form-control" value="<?php echo htmlspecialchars($record['position'] ?? ''); ?>">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="docket" class="form-label">Docket</label>
                                                        <input type="text" name="docket" class="form-control" value="<?php echo htmlspecialchars($record['docket'] ?? ''); ?>">
                                                    </div>
                                                <?php elseif ($category === 'associates'): ?>
                                                    <div class="mb-3">
                                                        <label for="completion_year" class="form-label">Completion Year</label>
                                                        <input type="text" name="completion_year" class="form-control" value="<?php echo htmlspecialchars($record['completion_year'] ?? ''); ?>">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="ministry" class="form-label">Ministry</label>
                                                        <input type="text" name="ministry" class="form-control" value="<?php echo htmlspecialchars($record['ministry'] ?? ''); ?>">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="course" class="form-label">Course</label>
                                                        <input type="text" name="course" class="form-control" value="<?php echo htmlspecialchars($record['course'] ?? ''); ?>">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="position" class="form-label">Position</label>
                                                        <input type="text" name="position" class="form-control" value="<?php echo htmlspecialchars($record['position'] ?? ''); ?>">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="docket" class="form-label">Docket</label>
                                                        <input type="text" name="docket" class="form-control" value="<?php echo htmlspecialchars($record['docket'] ?? ''); ?>">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="previous_role" class="form-label">Previous Role</label>
                                                        <input type="text" name="previous_role" class="form-control" value="<?php echo htmlspecialchars($record['previous_role'] ?? ''); ?>">
                                                    </div>
                                                <?php endif; ?>
                                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Message Modal -->
                            <div class="modal fade" id="messageModal<?php echo $record['id']; ?>" tabindex="-1" aria-labelledby="messageModal<?php echo $record['id']; ?>Label" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="messageModal<?php echo $record['id']; ?>Label">Send Message to <?php echo htmlspecialchars($record['name']); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="POST">
                                                <input type="hidden" name="user_id" value="<?php echo $record['id']; ?>">
                                                <input type="hidden" name="action" value="send_message">
                                                <div class="mb-3">
                                                    <label for="message" class="form-label">Message</label>
                                                    <textarea name="message" class="form-control" rows="4" required></textarea>
                                                </div>
                                                <button type="submit" class="btn btn-success">Send Message</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Invite Modal -->
                            <div class="modal fade" id="inviteModal<?php echo $record['id']; ?>" tabindex="-1" aria-labelledby="inviteModal<?php echo $record['id']; ?>Label" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="inviteModal<?php echo $record['id']; ?>Label">Send Invitation to <?php echo htmlspecialchars($record['name']); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="POST">
                                                <input type="hidden" name="user_id" value="<?php echo $record['id']; ?>">
                                                <input type="hidden" name="action" value="send_invitation">
                                                <input type="hidden" name="email" value="<?php echo htmlspecialchars($record['email']); ?>">
                                                <div class="mb-3">
                                                    <label for="invite_message" class="form-label">Invitation Message</label>
                                                    <textarea name="invite_message" class="form-control" rows="4" placeholder="Enter a custom invitation message"></textarea>
                                                </div>
                                                <p>Send an email invitation to <?php echo htmlspecialchars($record['email']); ?>?</p>
                                                <button type="submit" class="btn btn-info">Send Invitation</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?category=<?php echo $category; ?>&search=<?php echo urlencode($search); ?>&per_page=<?php echo $per_page; ?>&page=<?php echo $page - 1; ?>">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?category=<?php echo $category; ?>&search=<?php echo urlencode($search); ?>&per_page=<?php echo $per_page; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?category=<?php echo $category; ?>&search=<?php echo urlencode($search); ?>&per_page=<?php echo $per_page; ?>&page=<?php echo $page + 1; ?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>
    <?php include 'includes/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
    <script>
        $(document).ready(function() {
            $('#loader').hide();
            $('#searchForm').on('submit', function() {
                $('#loader').show();
            });
            $('#search').on('keyup', function() {
                $('#loader').show();
                clearTimeout(window.searchTimer);
                window.searchTimer = setTimeout(function() {
                    $('#searchForm').submit();
                }, 500);
            });
        });
    </script>
</body>
</html>