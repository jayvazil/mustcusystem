<?php
// Database connection
try {
    $pdo = new PDO('mysql:host=localhost;dbname=uvwehfds_mustcu', 'uvwehfds_mustcu', '7ZwV6yxXKGrD2LPn5eSH');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $year = filter_input(INPUT_POST, 'year', FILTER_SANITIZE_NUMBER_INT);
    $course = filter_input(INPUT_POST, 'course', FILTER_SANITIZE_STRING);
    $completion_month = filter_input(INPUT_POST, 'completion_month', FILTER_SANITIZE_STRING);
    $completion_year = filter_input(INPUT_POST, 'completion_year', FILTER_SANITIZE_STRING);
    $ministry = filter_input(INPUT_POST, 'ministry', FILTER_SANITIZE_STRING);

    if (empty($name) || empty($email) || empty($phone)) {
        $errors[] = 'Name, email, and phone are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    } else {
        // Check for existing email OR phone
        $stmt = $pdo->prepare("SELECT id, email, phone FROM members WHERE email = ? OR phone = ?");
        $stmt->execute([$email, $phone]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            if ($existing['email'] === $email) {
                $errors[] = 'Email already registered.';
            }
            if ($existing['phone'] === $phone) {
                $errors[] = 'Phone number already registered.';
            }
        } else {
            // Combine completion month and year if provided
            $completion_date = '';
            if (!empty($completion_month) && !empty($completion_year)) {
                $completion_date = $completion_month . '/' . $completion_year;
            }
            
            $stmt = $pdo->prepare("INSERT INTO members (name, email, phone, year, course, completion_year, ministry, submitted_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$name, $email, $phone, $year, $course, $completion_date, $ministry]);
            
            $success = 'Registration successful. You can now log in using your phone number as password in the members portal.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - MUST CU</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .form-control:focus, .form-select:focus {
            border-color: #0207ba;
            box-shadow: 0 0 0 0.2rem rgba(2, 7, 186, 0.25);
        }
        .btn-primary { 
            background-color: #0207ba; 
            border-color: #0207ba; 
        }
        .btn-primary:hover { 
            background-color: #ff7900; 
            border-color: #ff7900; 
        }
        .text-primary { 
            color: #0207ba !important; 
        }
        .completion-row { 
            display: flex; 
            gap: 10px; 
        }
        .completion-row > div { 
            flex: 1; 
        }
        .card {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border: none;
            border-radius: 10px;
        }
        .alert {
            border-radius: 8px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="text-center mb-4">
                    <h1 class="text-primary"><i class="fas fa-church"></i> MUST Christian Union</h1>
                    <p class="text-muted">Join our community of faith and fellowship</p>
                </div>
                
                <h2 class="text-center mb-4 text-primary">
                    <i class="fas fa-user-plus"></i> Register as a CU Member
                </h2>
                
                <?php if ($errors): ?>
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-exclamation-circle"></i> Registration Issues</h5>
                        <?php foreach ($errors as $error): ?>
                            <p class="mb-1"><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <h5><i class="fas fa-check-circle"></i> Success!</h5>
                        <p class="mb-0"><?php echo htmlspecialchars($success); ?></p>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="card p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-user"></i> Full Name *</label>
                                <input type="text" name="name" class="form-control" required 
                                       value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-envelope"></i> Your  Email *</label>
                                <input type="email" name="email" class="form-control" required
                                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-phone"></i> Phone Number *</label>
                                <input type="text" name="phone" class="form-control" required
                                       value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
                                <div class="form-text">Format: 07XX XXX XXX or +2547XX XXX XXX</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-calendar"></i> Current Year of Study</label>
                                <select name="year" class="form-control">
                                    <option value="" disabled selected>Select year of Study</option>
                                    <?php for ($y=1; $y<=5; $y++): ?>
                                        <option value="<?= $y ?>" <?= (isset($_POST['year']) && $_POST['year'] == $y) ? 'selected' : '' ?>>
                                            Year <?= $y ?>
                                        </option>
                                    <?php endfor; ?>
                                    <option value="6" <?= (isset($_POST['year']) && $_POST['year'] == 6) ? 'selected' : '' ?>>Graduate</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-book"></i> Course Of Study</label>
                        <input type="text" name="course" class="form-control" placeholder="e.g. Computer Science"
                               value="<?= isset($_POST['course']) ? htmlspecialchars($_POST['course']) : '' ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-graduation-cap"></i> Expected Course Completion time</label>
                        <div class="completion-row">
                            <div>
                                <select name="completion_month" class="form-control">
                                    <option value="" disabled selected>Month</option>
                                    <?php 
                                    $months = [
                                        '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
                                        '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
                                        '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
                                    ];
                                    foreach ($months as $value => $name): ?>
                                        <option value="<?= $value ?>" <?= (isset($_POST['completion_month']) && $_POST['completion_month'] == $value) ? 'selected' : '' ?>>
                                            <?= $name ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <select name="completion_year" class="form-control">
                                    <option value="" disabled selected>Year</option>
                                    <?php for ($y=date('Y'); $y<=date('Y')+5; $y++): ?>
                                        <option value="<?= $y ?>" <?= (isset($_POST['completion_year']) && $_POST['completion_year'] == $y) ? 'selected' : '' ?>>
                                            <?= $y ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label"><i class="fas fa-church"></i> Ministry</label>
                        <select name="ministry" class="form-control">
                            <option value="" selected>Select Ministry (Optional)</option>
                            <?php 
                            $ministries = [
                                "Praise and Worship", "IT and Publicity", "Ushering", "Creative",
                                "Choir", "Catering", "Decor", "Sunday School", "None"
                            ];
                            foreach ($ministries as $m): ?>
                                <option value="<?= $m ?>" <?= (isset($_POST['ministry']) && $_POST['ministry'] == $m) ? 'selected' : '' ?>>
                                    <?= $m ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="button" class="btn btn-outline-secondary me-md-2" onclick="window.location.href='https://members.mustcu.or.ke/members/index'">
                            <i class="fas fa-sign-in-alt"></i> Log in
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Register
                        </button>
                    </div>
                    
                    <div class="mt-3 text-center text-muted">
                        <small>* indicates required fields</small>
                    </div>
                </form>
                
                <div class="text-center mt-4">
                    <p class="text-muted">Already have an account? <a href="https://members.mustcu.or.ke/members/index">Log in here</a></p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>