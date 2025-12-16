<?php
// C:\xampp\htdocs\SYSTEM PRO\leaders\dashboard.php
require_once '../shared/config/config.php';

// Session check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'leader') {
    header("Location: index.php");
    exit();
}

// Fetch user data including position
$stmt = $pdo->prepare("SELECT name, email, phone, ministry, position, docket FROM leaders WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['name' => 'Unknown', 'email' => '', 'phone' => '', 'ministry' => '', 'position' => '', 'docket' => ''];

// Store position in session for navigation control
$_SESSION['position'] = $user['position'];

// Fetch other roles (placeholder, adjust if needed)
$roles = [];

// Fetch members per ministry
$stmt_min = $pdo->prepare("SELECT ministry, COUNT(*) as count FROM members WHERE ministry IS NOT NULL AND ministry != '' GROUP BY ministry");
$stmt_min->execute();
$ministries_data = $stmt_min->fetchAll(PDO::FETCH_ASSOC) ?: [['ministry' => 'No Data', 'count' => 0]];

// Fetch members per year (using 'year' column)
$stmt_year = $pdo->prepare("SELECT year, COUNT(*) as count FROM members WHERE year IS NOT NULL GROUP BY year ORDER BY year");
$stmt_year->execute();
$years_data = $stmt_year->fetchAll(PDO::FETCH_ASSOC) ?: [['year' => 'No Data', 'count' => 0]];

// Fetch leaders per docket
$stmt_docket = $pdo->prepare("SELECT docket, COUNT(*) as count FROM leaders WHERE docket IS NOT NULL AND docket != '' GROUP BY docket");
$stmt_docket->execute();
$dockets_data = $stmt_docket->fetchAll(PDO::FETCH_ASSOC) ?: [['docket' => 'No Data', 'count' => 0]];

// Fetch leadership positions list with docket
$stmt_pos = $pdo->prepare("SELECT position, docket, COUNT(*) as count FROM leaders WHERE position IS NOT NULL AND position != '' GROUP BY position, docket");
$stmt_pos->execute();
$positions_data = $stmt_pos->fetchAll(PDO::FETCH_ASSOC) ?: [['position' => 'No Data', 'docket' => 'No Data', 'count' => 0]];

// Calculate remaining time
$target_date = new DateTime('2026-10-26'); // Replace with actual transition date
$current_date = new DateTime();
$interval = $current_date->diff($target_date);
$remaining_seconds = $interval->days * 86400 + $interval->h * 3600 + $interval->i * 60 + $interval->s;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leader Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .countdown {
            background: linear-gradient(135deg, #0207ba, #0207ba);
            color: white;
            text-align: center;
            padding: 1.5rem;
            font-size: 1.3rem;
            font-weight: bold;
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 3px solid #ffd700;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .countdown p {
            margin: 0;
        }
        .countdown .time {
            font-size: 1.8rem;
            color: #ffd700;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
        }
        .countdown .message {
            font-size: 1.1rem;
            color: #ffffff;
            margin-top: 0.5rem;
            font-style: italic;
        }
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 1rem;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1, h2, h3 {
            color: #0207ba;
            text-align: center;
        }
        .card {
            border: none;
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .slide-in {
            animation: slideIn 0.5s ease-out;
        }
        @keyframes slideIn {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .navbar-nav .nav-item a {
            color: #fff;
            margin-right: 1rem;
        }
        .navbar-nav .nav-item a:hover {
            color: #f97316;
        }
        .chart-container {
            position: relative;
            margin: auto;
            height: 300px;
            width: 100%;
            max-width: 500px;
        }
        .error-message {
            color: red;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="countdown">
        <?php if ($remaining_seconds > 0): ?>
            <p><strong>Time until position transition:</strong></p>
            <div class="time" id="countdown"></div>
            <p class="message">Keep up the good work <?php echo htmlspecialchars($user['position'] ?: 'None'); ?> <?php echo htmlspecialchars($user['name']); ?>!</p>
        <?php else: ?>
            <p><strong>Time until position transition:</strong> Transition has occurred!</p>
        <?php endif; ?>
    </div>

    <?php include 'includes/header.php'; ?>
    <div class="container mt-5">
        <h2 class="mb-4"><i class="fas fa-tachometer-alt"></i> Leader Dashboard</h2>
        <div class="row">
            <div class="col-md-6">
                <div class="card p-4 slide-in">
                    <h3>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h3>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
                    <p><strong>Ministry:</strong> <?php echo htmlspecialchars($user['ministry'] ?: 'None'); ?></p>
                    <p><strong>Position:</strong> <?php echo htmlspecialchars($user['position'] ?: 'None'); ?></p>
                    <p><strong>Docket:</strong> <?php echo htmlspecialchars($user['docket'] ?: 'None'); ?></p>
                    <?php if ($roles): ?>
                        <p><strong>Other Roles:</strong> <?php echo implode(', ', array_map('ucfirst', $roles)); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <h3>Members per Ministry</h3>
                <div class="chart-container">
                    <canvas id="ministriesChart"></canvas>
                </div>
                <?php if (empty($ministries_data) || $ministries_data[0]['ministry'] === 'No Data'): ?>
                    <p class="error-message">No ministry data available.</p>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <h3>Members per Year</h3>
                <div class="chart-container">
                    <canvas id="yearsChart"></canvas>
                </div>
                <?php if (empty($years_data) || $years_data[0]['year'] === 'No Data'): ?>
                    <p class="error-message">No year data available.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <h3>Leaders per Docket</h3>
                <div class="chart-container">
                    <canvas id="docketsChart"></canvas>
                </div>
                <?php if (empty($dockets_data) || $dockets_data[0]['docket'] === 'No Data'): ?>
                    <p class="error-message">No docket data available.</p>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <h3>Leadership Positions</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Position</th>
                            <th>Docket</th>
                            <th>Number of Leaders</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($positions_data as $pos): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($pos['position'] ?: 'None'); ?></td>
                                <td><?php echo htmlspecialchars($pos['docket'] ?: 'None'); ?></td>
                                <td><?php echo $pos['count']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
    <script>
        // Debug data
        console.log('Ministries Data:', <?php echo json_encode($ministries_data); ?>);
        console.log('Years Data:', <?php echo json_encode($years_data); ?>);
        console.log('Dockets Data:', <?php echo json_encode($dockets_data); ?>);

        // Check if Chart.js is loaded
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded. Please check the CDN or network connection.');
        } else {
            console.log('Chart.js is loaded successfully.');
        }

        // Ministries Chart
        const ministriesCtx = document.getElementById('ministriesChart').getContext('2d');
        try {
            new Chart(ministriesCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_column($ministries_data, 'ministry')); ?>,
                    datasets: [{
                        label: 'Number of Members',
                        data: <?php echo json_encode(array_column($ministries_data, 'count')); ?>,
                        backgroundColor: '#36A2EB',
                        borderColor: '#1E88E5',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Member Count' }
                        },
                        x: {
                            title: { display: true, text: 'Ministry' }
                        }
                    },
                    plugins: {
                        legend: { display: true, position: 'top' }
                    }
                }
            });
        } catch (e) {
            console.error('Error initializing Ministries Chart:', e);
        }

        // Years Chart
        const yearsCtx = document.getElementById('yearsChart').getContext('2d');
        try {
            new Chart(yearsCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_column($years_data, 'year')); ?>,
                    datasets: [{
                        label: 'Number of Members',
                        data: <?php echo json_encode(array_column($years_data, 'count')); ?>,
                        backgroundColor: '#4BC0C0',
                        borderColor: '#2E7D32',
                        borderWidth: 2,
                        fill: false
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Member Count' }
                        },
                        x: {
                            title: { display: true, text: 'Year' }
                        }
                    },
                    plugins: {
                        legend: { display: true, position: 'top' }
                    }
                }
            });
        } catch (e) {
            console.error('Error initializing Years Chart:', e);
        }

        // Dockets Chart
        const docketsCtx = document.getElementById('docketsChart').getContext('2d');
        try {
            new Chart(docketsCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_column($dockets_data, 'docket')); ?>,
                    datasets: [{
                        label: 'Number of Leaders',
                        data: <?php echo json_encode(array_column($dockets_data, 'count')); ?>,
                        backgroundColor: '#FF6384',
                        borderColor: '#D81B60',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Leader Count' }
                        },
                        x: {
                            title: { display: true, text: 'Docket' }
                        }
                    },
                    plugins: {
                        legend: { display: true, position: 'top' }
                    }
                }
            });
        } catch (e) {
            console.error('Error initializing Dockets Chart:', e);
        }

        // Countdown Timer
        let totalSeconds = <?php echo json_encode($remaining_seconds); ?>;
        if (totalSeconds > 0) {
            const countdownElement = document.getElementById('countdown');
            function updateCountdown() {
                totalSeconds--;
                if (totalSeconds <= 0) {
                    countdownElement.textContent = 'Transition has occurred!';
                    clearInterval(countdownInterval);
                    return;
                }
                const days = Math.floor(totalSeconds / (3600 * 24));
                const hours = Math.floor((totalSeconds % (3600 * 24)) / 3600);
                const minutes = Math.floor((totalSeconds % 3600) / 60);
                const seconds = totalSeconds % 60;
                countdownElement.textContent = `${days}d ${hours}h ${minutes}m ${seconds}s`;
            }
            const countdownInterval = setInterval(updateCountdown, 1000);
            updateCountdown();
        }
    </script>
</body>
</html>