<?php
require_once '../shared/config/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_scripture'])) {
        $verse = $_POST['verse'];
        $reference = $_POST['reference'];
        $stmt = $pdo->prepare("INSERT INTO scriptures (verse, reference, admin_id) VALUES (?, ?, ?)");
        $stmt->execute([$verse, $reference, $_SESSION['user_id']]);
    } elseif (isset($_POST['add_event'])) {
        $title = $_POST['title'];
        $date = $_POST['date'];
        $description = $_POST['description'];
        $stmt = $pdo->prepare("INSERT INTO events (title, date, description, admin_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $date, $description, $_SESSION['user_id']]);
    } elseif (isset($_POST['add_announcement'])) {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $stmt = $pdo->prepare("INSERT INTO announcements (title, content, admin_id) VALUES (?, ?, ?)");
        $stmt->execute([$title, $content, $_SESSION['user_id']]);
    } elseif (isset($_POST['add_prayer'])) {
        $content = $_POST['content'];
        $stmt = $pdo->prepare("INSERT INTO prayer_requests (content, admin_id) VALUES (?, ?)");
        $stmt->execute([$content, $_SESSION['user_id']]);
    } elseif (isset($_POST['add_resource'])) {
        $title = $_POST['title'];
        $url = $_POST['url'];
        $stmt = $pdo->prepare("INSERT INTO resources (title, url, admin_id) VALUES (?, ?, ?)");
        $stmt->execute([$title, $url, $_SESSION['user_id']]);
    } elseif (isset($_POST['add_devotion'])) {
        $verse = $_POST['verse'];
        $reference = $_POST['reference'];
        $reflection = $_POST['reflection'];
        $stmt = $pdo->prepare("INSERT INTO daily_devotions (verse, reference, reflection, admin_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$verse, $reference, $reflection, $_SESSION['user_id']]);
    }
}

// Success/error message handling
$message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : '';
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';

// Pagination settings for Recent Updates
$updates_per_page = isset($_GET['updates_per_page']) ? (int)$_GET['updates_per_page'] : 20;
$updates_page = isset($_GET['updates_page']) ? (int)$_GET['updates_page'] : 1;
$updates_offset = ($updates_page - 1) * $updates_per_page;

// Validate pagination parameters
$updates_per_page = in_array($updates_per_page, [20, 50, 100, 150, 250, 500]) ? $updates_per_page : 20;
$updates_page = max(1, $updates_page);

// Fetch total number of updates for pagination
$total_updates_stmt = $pdo->query("
    SELECT COUNT(*) FROM (
        SELECT id FROM scriptures
        UNION ALL SELECT id FROM daily_devotions
        UNION ALL SELECT id FROM events
        UNION ALL SELECT id FROM announcements
        UNION ALL SELECT id FROM resources
    ) AS total
");
$total_updates = $total_updates_stmt->fetchColumn();
$total_updates_pages = ceil($total_updates / $updates_per_page);

// Fetch recent updates with pagination
$updates_query = "
    SELECT id, 'Scripture' AS type, verse AS title, reference AS content, admin_id, NULL AS member_id, created_at FROM scriptures
    UNION ALL
    SELECT id, 'Devotion' AS type, verse AS title, CONCAT(reference, ': ', reflection) AS content, admin_id, NULL AS member_id, created_at FROM daily_devotions
    UNION ALL
    SELECT id, 'Event' AS type, title, description AS content, admin_id, NULL AS member_id, created_at FROM events
    UNION ALL
    SELECT id, 'Announcement' AS type, title, content, admin_id, NULL AS member_id, created_at FROM announcements
    UNION ALL
    SELECT id, 'Resource' AS type, title, url AS content, admin_id, NULL AS member_id, created_at FROM resources
    ORDER BY created_at DESC
    LIMIT " . intval($updates_per_page) . " OFFSET " . intval($updates_offset);
$updates_stmt = $pdo->query($updates_query);
$updates = $updates_stmt->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MUST CU</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container mt-5">
        <h1 class="text-center mb-4"><i class="fas fa-user-shield"></i> Admin Dashboard</h1>
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Daily Devotion Management -->
        <div class="card p-4 mb-4 slide-in">
            <h3><i class="fas fa-bible"></i> Manage Daily Devotions</h3>
            <form action="admin_dashboard.php" method="POST" class="mb-4">
                <div class="mb-3">
                    <label for="devotion_verse" class="form-label">Verse</label>
                    <textarea class="form-control" name="verse" id="devotion_verse" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="devotion_reference" class="form-label">Reference</label>
                    <input type="text" class="form-control" name="reference" id="devotion_reference" required>
                </div>
                <div class="mb-3">
                    <label for="devotion_reflection" class="form-label">Reflection</label>
                    <textarea class="form-control" name="reflection" id="devotion_reflection" required></textarea>
                </div>
                <button type="submit" name="add_devotion" class="btn btn-primary">Add Devotion</button>
            </form>
        </div>

        <!-- Scripture Management -->
        <div class="card p-4 mb-4 slide-in">
            <h3><i class="fas fa-bible"></i> Manage Scriptures</h3>
            <form action="admin_dashboard.php" method="POST" class="mb-4">
                <div class="mb-3">
                    <label for="verse" class="form-label">Verse</label>
                    <textarea class="form-control" name="verse" id="verse" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="reference" class="form-label">Reference</label>
                    <input type="text" class="form-control" name="reference" id="reference" required>
                </div>
                <button type="submit" name="add_scripture" class="btn btn-primary">Add Scripture</button>
            </form>
        </div>

        <!-- Event Management -->
        <div class="card p-4 mb-4 slide-in">
            <h3><i class="fas fa-calendar-alt"></i> Manage Events</h3>
            <form action="admin_dashboard.php" method="POST" class="mb-4">
                <div class="mb-3">
                    <label for="event_title" class="form-label">Title</label>
                    <input type="text" class="form-control" name="title" id="event_title" required>
                </div>
                <div class="mb-3">
                    <label for="event_date" class="form-label">Date</label>
                    <input type="date" class="form-control" name="date" id="event_date" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" name="description" id="description" required></textarea>
                </div>
                <button type="submit" name="add_event" class="btn btn-primary">Add Event</button>
            </form>
        </div>

        <!-- Announcement Management -->
        <div class="card p-4 mb-4 slide-in">
            <h3><i class="fas fa-bullhorn"></i> Manage Announcements</h3>
            <form action="admin_dashboard.php" method="POST" class="mb-4">
                <div class="mb-3">
                    <label for="announcement_title" class="form-label">Title</label>
                    <input type="text" class="form-control" name="title" id="announcement_title" required>
                </div>
                <div class="mb-3">
                    <label for="announcement_content" class="form-label">Content</label>
                    <textarea class="form-control" name="content" id="announcement_content" required></textarea>
                </div>
                <button type="submit" name="add_announcement" class="btn btn-primary">Add Announcement</button>
            </form>
        </div>

        <!-- Prayer Request Management (Admin-Submitted) -->
        <div class="card p-4 mb-4 slide-in">
            <h3><i class="fas fa-pray"></i> Manage Admin Prayer Requests</h3>
            <form action="admin_dashboard.php" method="POST" class="mb-4">
                <div class="mb-3">
                    <label for="prayer_content" class="form-label">Prayer Request</label>
                    <textarea class="form-control" name="content" id="prayer_content" required></textarea>
                </div>
                <button type="submit" name="add_prayer" class="btn btn-primary">Add Prayer Request</button>
            </form>
        </div>

        <!-- Resource Management -->
        <div class="card p-4 mb-4 slide-in">
            <h3><i class="fas fa-book"></i> Manage Resources</h3>
            <form action="admin_dashboard.php" method="POST" class="mb-4">
                <div class="mb-3">
                    <label for="resource_title" class="form-label">Title</label>
                    <input type="text" class="form-control" name="title" id="resource_title" required>
                </div>
                <div class="mb-3">
                    <label for="resource_url" class="form-label">URL</label>
                    <input type="url" class="form-control" name="url" id="resource_url" required>
                </div>
                <button type="submit" name="add_resource" class="btn btn-primary">Add Resource</button>
            </form>
        </div>

        <!-- Recent Updates -->
        <div class="card p-4 mb-4 slide-in">
            <h3><i class="fas fa-history"></i> Recent Updates</h3>
            <form action="delete_updates.php" method="POST" id="updatesForm">
                <div class="mb-3">
                    <label for="updates_per_page" class="form-label">Records per page:</label>
                    <select name="updates_per_page" id="updates_per_page" class="form-select w-auto d-inline-block" onchange="this.form.submit()">
                        <option value="20" <?php echo $updates_per_page == 20 ? 'selected' : ''; ?>>20</option>
                        <option value="50" <?php echo $updates_per_page == 50 ? 'selected' : ''; ?>>50</option>
                        <option value="100" <?php echo $updates_per_page == 100 ? 'selected' : ''; ?>>100</option>
                        <option value="150" <?php echo $updates_per_page == 150 ? 'selected' : ''; ?>>150</option>
                        <option value="250" <?php echo $updates_per_page == 250 ? 'selected' : ''; ?>>250</option>
                        <option value="500" <?php echo $updates_per_page == 500 ? 'selected' : ''; ?>>500</option>
                    </select>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAllUpdates"></th>
                                <th>Type</th>
                                <th>Title</th>
                                <th>Content</th>
                                <th>Submitter</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($updates): ?>
                                <?php foreach ($updates as $update): ?>
                                    <tr>
                                        <td><input type="checkbox" name="updates[]" value="<?php echo htmlspecialchars($update['type'] . '|' . $update['id']); ?>" class="update-checkbox"></td>
                                        <td><?php echo htmlspecialchars($update['type']); ?></td>
                                        <td><?php echo htmlspecialchars($update['title'] ?: 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars(substr($update['content'], 0, 100) . (strlen($update['content']) > 100 ? '...' : '')); ?></td>
                                        <td>
                                            <?php
                                            if ($update['admin_id']) {
                                                $stmt = $pdo->prepare("SELECT name FROM admins WHERE id = ?");
                                                $stmt->execute([$update['admin_id']]);
                                                $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                                                echo htmlspecialchars($admin['name'] ?? 'Unknown Admin');
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($update['created_at']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6">No recent updates.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="btn btn-danger mt-3" id="deleteUpdatesButton" disabled>Delete Selected</button>
            </form>
            <!-- Pagination for Updates -->
            <nav aria-label="Updates navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $updates_page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?updates_page=<?php echo $updates_page - 1; ?>&updates_per_page=<?php echo $updates_per_page; ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_updates_pages; $i++): ?>
                        <li class="page-item <?php echo $updates_page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?updates_page=<?php echo $i; ?>&updates_per_page=<?php echo $updates_per_page; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $updates_page >= $total_updates_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?updates_page=<?php echo $updates_page + 1; ?>&updates_per_page=<?php echo $updates_per_page; ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>

        

        
    </div>
    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
    <script>
        // Enable/disable delete buttons based on checkbox selection
        document.addEventListener('DOMContentLoaded', function () {
            // Recent Updates
            const updatesCheckboxes = document.querySelectorAll('.update-checkbox');
            const deleteUpdatesButton = document.getElementById('deleteUpdatesButton');
            const selectAllUpdates = document.getElementById('selectAllUpdates');

            function updateUpdatesDeleteButton() {
                deleteUpdatesButton.disabled = !Array.from(updatesCheckboxes).some(checkbox => checkbox.checked);
            }

            updatesCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateUpdatesDeleteButton);
            });

            selectAllUpdates.addEventListener('change', function () {
                updatesCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAllUpdates.checked;
                });
                updateUpdatesDeleteButton();
            });

            // Feedback
            const feedbackCheckboxes = document.querySelectorAll('.feedback-checkbox');
            const deleteFeedbackButton = document.getElementById('deleteFeedbackButton');
            const selectAllFeedback = document.getElementById('selectAllFeedback');

            function updateFeedbackDeleteButton() {
                deleteFeedbackButton.disabled = !Array.from(feedbackCheckboxes).some(checkbox => checkbox.checked);
            }

            feedbackCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateFeedbackDeleteButton);
            });

            selectAllFeedback.addEventListener('change', function () {
                feedbackCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAllFeedback.checked;
                });
                updateFeedbackDeleteButton();
            });

            // Prayer Requests
            const prayerCheckboxes = document.querySelectorAll('.prayer-checkbox');
            const deletePrayerButton = document.getElementById('deletePrayerButton');
            const selectAllPrayer = document.getElementById('selectAllPrayer');

            function updatePrayerDeleteButton() {
                deletePrayerButton.disabled = !Array.from(prayerCheckboxes).some(checkbox => checkbox.checked);
            }

            prayerCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updatePrayerDeleteButton);
            });

            selectAllPrayer.addEventListener('change', function () {
                prayerCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAllPrayer.checked;
                });
                updatePrayerDeleteButton();
            });

            // Update button states on page load
            updateUpdatesDeleteButton();
            updateFeedbackDeleteButton();
            updatePrayerDeleteButton();
        });
    </script>
</body>
<style>
body {
    background-color: #ffffff;
    font-family: 'Arial', sans-serif;
    color: #333;
}

.container {
    max-width: 1400px;
}

.welcome-section {
    background: linear-gradient(#0207ba, #0207ba, #0207ba);
    color: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
}

.card h3 {
    color: #0207ba;
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.card i {
    margin-right: 0.5rem;
}

.btn-primary {
    background-color: #0207ba;
    border-color: #0207ba;
    transition: background-color 0.3s ease;
}

.btn-primary:hover {
    background-color: #ff7900;
}

.btn-outline-primary {
    border-color: #0207ba;
    color: #0207ba;
}

.btn-outline-primary:hover {
    background-color: #0207ba;
    color: white;
}

.scripture {
    background-color: #e9ecef;
    border-left: 5px solid #0207ba;
}

.event, .announcement {
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 1rem;
}

.slide-in {
    animation: slideIn 0.5s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .welcome-section {
        padding: 1.5rem;
    }
    .card {
        margin-bottom: 1.5rem;
    }
    .card h3 {
        font-size: 1.3rem;
    }
}
</style>
</html>