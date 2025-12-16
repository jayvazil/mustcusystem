<?php
require_once '../shared/config/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$errors = [];
$success = '';

// Handle promotion form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['member_id'])) {
    $member_id = filter_input(INPUT_POST, 'member_id', FILTER_SANITIZE_NUMBER_INT);
    $position = filter_input(INPUT_POST, 'position', FILTER_SANITIZE_STRING);
    $docket = filter_input(INPUT_POST, 'docket', FILTER_SANITIZE_STRING);

    if (empty($member_id) || empty($position) || empty($docket)) {
        $errors[] = 'All fields are required.';
    } else {
        $stmt = $pdo->prepare("SELECT name, email, phone, year, course, completion_year, ministry FROM members WHERE id = ?");
        $stmt->execute([$member_id]);
        $member = $stmt->fetch();

        if ($member) {
            $stmt = $pdo->prepare("INSERT INTO leaders (name, email, phone, year, course, completion_year, ministry, position, docket, start_date, submitted_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([$member['name'], $member['email'], $member['phone'], $member['year'], $member['course'], $member['completion_year'], $member['ministry'], $position, $docket]);
            $stmt = $pdo->prepare("INSERT INTO position_history (user_id, position, docket, start_date) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$member_id, $position, $docket]);
            $success = 'Leader promoted successfully.';
        } else {
            $errors[] = 'Member not found.';
        }
    }
}

// Pagination settings
$rows_per_page_options = [50, 100, 250, 300];
$rows_per_page = isset($_GET['rows']) && in_array((int)$_GET['rows'], $rows_per_page_options) ? (int)$_GET['rows'] : 50;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $rows_per_page;

// Fetch total number of promotion records
$stmt = $pdo->query("SELECT COUNT(*) FROM position_history");
$total_rows = $stmt->fetchColumn();
$total_pages = ceil($total_rows / $rows_per_page);

// Fetch promotion history with pagination
$stmt = $pdo->prepare("
    SELECT ph.user_id, ph.position, ph.docket, ph.start_date, m.name AS member_name 
    FROM position_history ph 
    JOIN members m ON ph.user_id = m.id 
    ORDER BY ph.start_date DESC 
    LIMIT ? OFFSET ?
");
$stmt->execute([$rows_per_page, $offset]);
$promotions = $stmt->fetchAll();

// Fetch members not already leaders for the form
$stmt = $pdo->query("SELECT id, name FROM members WHERE id NOT IN (SELECT id FROM leaders)");
$members = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promote Leader - MUST CU</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container mt-5">
        <h2 class="text-center mb-4"><i class="fas fa-user-tie"></i> Promote Leader</h2>
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

        <!-- Promotion Form -->
        <form method="POST" class="card p-4 slide-in mb-5">
            <div class="mb-3">
                <label for="member_id" class="form-label"><i class="fas fa-user"></i> Select Member</label>
                <select name="member_id" id="member_id" class="form-control" required>
                    <option value="">Select a member</option>
                    <?php foreach ($members as $member): ?>
                        <option value="<?php echo $member['id']; ?>"><?php echo htmlspecialchars($member['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div> 
            <div class="mb-3">
                <label for="position" class="form-label"><i class="fas fa-briefcase"></i> Position</label>
                <input type="text" name="position" id="position" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="docket" class="form-label"><i class="fas fa-folder"></i> Docket</label>
                <input type="text" name="docket" id="docket" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-bounce">Promote</button>
        </form>

        <!-- Promotion History Table -->
        <h3 class="mb-4"><i class="fas fa-history"></i> Recent Promotions</h3>
        <div class="mb-3">
            <label for="rows_per_page" class="form-label">Rows per page:</label>
            <select id="rows_per_page" class="form-select w-auto d-inline-block" onchange="window.location.href='?page=1&rows=' + this.value">
                <?php foreach ($rows_per_page_options as $option): ?>
                    <option value="<?php echo $option; ?>" <?php echo $rows_per_page == $option ? 'selected' : ''; ?>><?php echo $option; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead style="background-color: #0207ba; color: #fff000;">
                    <tr>
                        <th>Promoted Member</th>
                        <th>Position</th>
                        <th>Docket</th>
                        <th>Start Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($promotions)): ?>
                        <tr>
                            <td colspan="4" class="text-center">No promotion records found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($promotions as $promotion): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($promotion['member_name']); ?></td>
                                <td><?php echo htmlspecialchars($promotion['position']); ?></td>
                                <td><?php echo htmlspecialchars($promotion['docket']); ?></td>
                                <td><?php echo date('Y-m-d h:i A', strtotime($promotion['start_date'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&rows=<?php echo $rows_per_page; ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&rows=<?php echo $rows_per_page; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&rows=<?php echo $rows_per_page; ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>