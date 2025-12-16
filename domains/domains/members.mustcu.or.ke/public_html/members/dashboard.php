<?php
require_once '../shared/config/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header('Location: index.php');
    exit;
}

// Fetch user data
$stmt = $pdo->prepare("SELECT name, email, phone, ministry FROM members WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Fetch user roles
$roles = [];
$stmt = $pdo->prepare("
    SELECT 'leader' AS role FROM leaders WHERE email = ? UNION
    SELECT 'associate' AS role FROM associates WHERE email = ? UNION
    SELECT 'admin' AS role FROM admins WHERE email = ?
");
$stmt->execute([$user['email'], $user['email'], $user['email']]);
$roles = array_column($stmt->fetchAll(), 'role');

// Fetch scripture of the week
$stmt = $pdo->prepare("SELECT verse, reference FROM scriptures ORDER BY id DESC LIMIT 1");
$stmt->execute();
$scripture = $stmt->fetch();

// Fetch daily devotion
$stmt = $pdo->prepare("SELECT verse, reference, reflection FROM daily_devotions ORDER BY id DESC LIMIT 1");
$stmt->execute();
$devotion = $stmt->fetch();

// Fetch upcoming events
$stmt = $pdo->prepare("SELECT title, date, description FROM events WHERE date >= CURDATE() ORDER BY date ASC LIMIT 3");
$stmt->execute();
$events = $stmt->fetchAll();

// Fetch announcements
$stmt = $pdo->prepare("SELECT title, content FROM announcements ORDER BY id DESC LIMIT 3");
$stmt->execute();
$announcements = $stmt->fetchAll();

// Fetch prayer requests
$stmt = $pdo->prepare("SELECT content FROM prayer_requests ORDER BY id DESC LIMIT 3");
$stmt->execute();
$prayer_requests = $stmt->fetchAll();

// Fetch resources
$stmt = $pdo->prepare("SELECT title, url FROM resources ORDER BY id DESC LIMIT 3");
$stmt->execute();
$resources = $stmt->fetchAll();

// Check for success message
$message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard - MUST CU</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
   
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container mt-5">
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <div class="welcome-section text-center mb-5 slide-in">
            <h1>Dear <?php echo htmlspecialchars($user['name']); ?>  Welcome to MUST Christian Union</h1>
            <p class="lead">“For where two or three are gathered in my name, there am I among them.” – Matthew 18:20</p>
            <p><strong>Mission:</strong> To equip every student with Discipleship and Evangelism so as to live a Christ-like life. </p>
            <p><strong>Vision:</strong> To be faithful disciples and witnesses of Christ in and out of campus</p>
            <?php if ($scripture): ?>
                <div class="scripture card p-3 mt-3">
                    <h4>Scripture of this Week</h4>
                    <p><em><?php echo htmlspecialchars($scripture['verse']); ?></em> – <?php echo htmlspecialchars($scripture['reference']); ?></p>
                </div>
            <?php else: ?>
                <div class="scripture card p-3 mt-3">
                    <p>No scripture available this week.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="row">
            <!-- Profile Section -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card p-4 slide-in">
                    <h3><i class="fas fa-user"></i> Your Profile</h3>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
                    <p><strong>Ministry:</strong> <?php echo htmlspecialchars($user['ministry'] ?: 'None'); ?></p>
                    <?php if ($roles): ?>
                        <p><strong>Other Roles:</strong> <?php echo implode(', ', array_map('ucfirst', $roles)); ?></p>
                    <?php endif; ?>
                    <a href="profile.php" class="btn btn-primary mt-3">Edit Profile</a>
                </div>
            </div>

            <!-- Upcoming Events -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card p-4 slide-in">
                    <h3><i class="fas fa-calendar-alt"></i> Upcoming Events</h3>
                    <?php if ($events): ?>
                        <?php foreach ($events as $event): ?>
                            <div class="event mb-3">
                                <h5><?php echo htmlspecialchars($event['title']); ?></h5>
                                <p><strong>Date:</strong> <?php echo htmlspecialchars($event['date']); ?></p>
                                <p><?php echo htmlspecialchars($event['description']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No upcoming events.</p>
                    <?php endif; ?>
                    <a href="view_posts.php" class="btn btn-outline-primary mt-3">View All Events</a>
                </div>
            </div>

            <!-- Announcements -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card p-4 slide-in">
                    <h3><i class="fas fa-bullhorn"></i> Announcements</h3>
                    <?php if ($announcements): ?>
                        <?php foreach ($announcements as $announcement): ?>
                            <div class="announcement mb-3">
                                <h5><?php echo htmlspecialchars($announcement['title']); ?></h5>
                                <p><?php echo htmlspecialchars($announcement['content']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No recent announcements.</p>
                    <?php endif; ?>
                    <a href="dashboard.php" class="btn btn-outline-primary mt-3">View All Announcements</a>
                </div>
            </div>

            <!-- Ministry Participation -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card p-4 slide-in">
                    <h3><i class="fas fa-hands-helping"></i> Join a Ministry</h3>
                    <p>Explore our ministries: Worship, Prayer, Evangelism, and more.</p>
                    <a href="dashboard.php" class="btn btn-primary mt-3">Join Now</a>
                </div>
            </div>

            <!-- Prayer Wall -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card p-4 slide-in">
                    <h3><i class="fas fa-pray"></i> Prayer Wall</h3>
                    <?php if ($prayer_requests): ?>
                        <?php foreach ($prayer_requests as $request): ?>
                            <p class="mb-2"><?php echo htmlspecialchars($request['content']); ?></p>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No prayer requests at this time.</p>
                    <?php endif; ?>
                    <button type="button" class="btn btn-outline-primary mt-3" data-bs-toggle="modal" data-bs-target="#submissionModal">Submit a Prayer Request</button>
                </div>
            </div>

            <!-- Resources -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card p-4 slide-in">
                    <h3><i class="fas fa-book"></i> Resources</h3>
                    <?php if ($resources): ?>
                        <?php foreach ($resources as $resource): ?>
                            <p><a href="<?php echo htmlspecialchars($resource['url']); ?>" target="_blank"><?php echo htmlspecialchars($resource['title']); ?></a></p>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No resources available.</p>
                    <?php endif; ?>
                    <a href="dashboard.php" class="btn btn-outline-primary mt-3">View All Resources</a>
                </div>
            </div>

            <!-- Media & Livestream -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card p-4 slide-in">
                    <h3><i class="fas fa-video"></i> Media & Livestream</h3>
                    <p>Watch our latest sermon or view event photos.</p>
                    <a href="media.php" class="btn btn-primary mt-3">Watch Now</a>
                    <a href="dashboard.php" class="btn btn-outline-primary mt-2">Photo Gallery</a>
                </div>
            </div>

            <!-- Discipleship Tools -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card p-4 slide-in">
                    <h3><i class="fas fa-bible"></i> Discipleship Tools</h3>
                    <p>Track your Bible reading or connect with a mentor.</p>
                    <a href="dashboard.php" class="btn btn-primary mt-3">Get Started</a>
                </div>
            </div>

            <!-- Feedback & Suggestions -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card p-4 slide-in">
                    <h3><i class="fas fa-comment"></i> Feedback</h3>
                    <p>Share your feedback or submit a prayer request.</p>
                    <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#submissionModal">Submit Feedback</button>
                </div>
            </div>

            <!-- Outreach & Evangelism -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card p-4 slide-in">
                    <h3><i class="fas fa-globe"></i> Outreach</h3>
                    <p>Join our mission teams or download evangelism tools.</p>
                    <a href="dashboard.php" class="btn btn-primary mt-3">Get Involved</a>
                </div>
            </div>

            <!-- Member Forum -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card p-4 slide-in">
                    <h3><i class="fas fa-users"></i> Member Forum</h3>
                    <p>Connect with others in our discussion groups.</p>
                    <a href="member.php" class="btn btn-primary mt-3">Join Discussion</a>
                </div>
            </div>

            <!-- Spiritual Growth Challenge -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card p-4 slide-in">
                    <h3><i class="fas fa-fire"></i> 21-Day Prayer Challenge</h3>
                    <p>Join our spiritual growth challenge!</p>
                    <a href="dashboard.php" class="btn btn-primary mt-3">Start Now</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Devotion Pop-up -->
    <div class="modal fade" id="devotionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Daily Devotion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if ($devotion): ?>
                        <p><em>“<?php echo htmlspecialchars($devotion['verse']); ?>”</em> – <?php echo htmlspecialchars($devotion['reference']); ?></p>
                        <p><?php echo htmlspecialchars($devotion['reflection']); ?></p>
                    <?php else: ?>
                        <p>No devotion available today.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Unified Submission Modal -->
    <div class="modal fade" id="submissionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Submit Feedback or Prayer Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="submit_form.php" method="POST" id="submissionForm">
                        <div class="mb-3">
                            <label for="submission_type" class="form-label">Submission Type</label>
                            <select class="form-control" name="type" id="submission_type" required>
                                <option value="feedback">Feedback</option>
                                <option value="prayer">Prayer Request</option>
                            </select>
                        </div>
                        <div class="mb-3" id="title_field">
                            <label for="submission_title" class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" id="submission_title">
                        </div>
                        <div class="mb-3">
                            <label for="submission_content" class="form-label">Content</label>
                            <textarea class="form-control" name="content" id="submission_content" rows="4" placeholder="Enter your feedback or prayer request..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
    <script>
        // Show devotion modal on page load
        document.addEventListener('DOMContentLoaded', function () {
            var devotionModal = new bootstrap.Modal(document.getElementById('devotionModal'));
            devotionModal.show();

            // Toggle title field based on submission type
            const submissionType = document.getElementById('submission_type');
            const titleField = document.getElementById('title_field');
            const titleInput = document.getElementById('submission_title');
            
            submissionType.addEventListener('change', function () {
                if (this.value === 'feedback') {
                    titleField.style.display = 'block';
                    titleInput.setAttribute('required', 'required');
                } else {
                    titleField.style.display = 'none';
                    titleInput.removeAttribute('required');
                }
            });

            // Trigger change event on page load to set initial state
            submissionType.dispatchEvent(new Event('change'));
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
    background: linear-gradient(#ff7900, #0207ba, #ff7900);
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