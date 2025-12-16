<?php
// admin_attendance.php – Admin view of last 10 days attendance
// Uses your existing session + $pdo from config
require_once '../shared/config/config.php';

// ---------------------------------------------------------------
// 1. CHECK IF USER IS LOGGED IN AS ADMIN (your existing logic)
// ---------------------------------------------------------------
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access denied. Admin only.");
}

// ---------------------------------------------------------------
// 2. EXPORT TO CSV (if requested)
// ---------------------------------------------------------------
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="cu_attendance_last10days_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, [
        'Date', 'Group', 'Event', 'Starting Time', 'Ending Time',
        'Total Attendees', 'Leader', 'Comments', 'Submitted At'
    ]);

    $ten_days_ago = date('Y-m-d', strtotime('-10 days'));
    $stmt = $pdo->prepare("
        SELECT a.activity_date, g.name, a.event_name, a.starting_time, a.ending_time,
               a.total_attendees, a.leader_name, a.comments, a.submitted_at
        FROM attendance a
        JOIN groups g ON a.group_id = g.id
        WHERE a.activity_date >= ?
        ORDER BY a.activity_date DESC, a.starting_time DESC
    ");
    $stmt->execute([$ten_days_ago]);
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $row[4] = $row[4] ?: '—'; // Replace null end time
        $row[7] = $row[7] ?: ''; // Replace null comments
        fputcsv($out, $row);
    }
    exit;
}

// ---------------------------------------------------------------
// 3. EXPORT TO EXCEL (Optional – requires PhpSpreadsheet)
// ---------------------------------------------------------------
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    require_once '../vendor/autoload.php';
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $headers = ['Date', 'Group', 'Event', 'Starting Time', 'Ending Time', 'Total Attendees', 'Leader', 'Comments', 'Submitted At'];
    $col = 'A';
    foreach ($headers as $h) {
        $sheet->setCellValue($col++ . '1', $h);
    }

    $ten_days_ago = date('Y-m-d', strtotime('-10 days'));
    $stmt = $pdo->prepare("
        SELECT a.activity_date, g.name, a.event_name, a.starting_time, a.ending_time,
               a.total_attendees, a.leader_name, a.comments, a.submitted_at
        FROM attendance a
        JOIN groups g ON a.group_id = g.id
        WHERE a.activity_date >= ?
        ORDER BY a.activity_date DESC
    ");
    $stmt->execute([$ten_days_ago]);
    $row_num = 2;
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $sheet->setCellValue('A' . $row_num, $r['activity_date']);
        $sheet->setCellValue('B' . $row_num, $r['name']);
        $sheet->setCellValue('C' . $row_num, $r['event_name']);
        $sheet->setCellValue('D' . $row_num, $r['starting_time']);
        $sheet->setCellValue('E' . $row_num, $r['ending_time'] ?: '—');
        $sheet->setCellValue('F' . $row_num, $r['total_attendees']);
        $sheet->setCellValue('G' . $row_num, $r['leader_name']);
        $sheet->setCellValue('H' . $row_num, $r['comments'] ?: '');
        $sheet->setCellValue('I' . $row_num, $r['submitted_at']);
        $row_num++;
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="cu_attendance_last10days_' . date('Y-m-d') . '.xlsx"');
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

// ---------------------------------------------------------------
// 4. FETCH LAST 10 DAYS DATA FOR DISPLAY
// ---------------------------------------------------------------
$ten_days_ago = date('Y-m-d', strtotime('-10 days'));
$stmt = $pdo->prepare("
    SELECT a.*, g.name as group_name
    FROM attendance a
    JOIN groups g ON a.group_id = g.id
    WHERE a.activity_date >= ?
    ORDER BY a.activity_date DESC, a.starting_time DESC
");
$stmt->execute([$ten_days_ago]);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_attendees = array_sum(array_column($records, 'total_attendees'));
$record_count = count($records);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Attendance (Last 10 Days)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 1.5rem; }
        .table th { font-size: 0.9rem; }
        .badge { font-size: 1rem; }
    </style>
</head>
<body>

<div class="container-fluid">
    <h2 class="mb-3">
        CU Attendance Dashboard 
        <small class="text-muted">(Last 10 Days)</small>
    </h2>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center border-primary">
                <div class="card-body">
                    <h5 class="card-title"><?= $record_count ?></h5>
                    <p class="card-text">Total Records</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-success">
                <div class="card-body">
                    <h5 class="card-title"><?= $total_attendees ?></h5>
                    <p class="card-text">Total Attendees</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Buttons -->
    <div class="mb-3">
        <a href="?export=csv" class="btn btn-outline-success me-2">
            Download CSV
        </a>
        <a href="?export=excel" class="btn btn-success">
            Download Excel
        </a>
    </div>

    <!-- Attendance Table -->
    <div class="table-responsive">
        <table class="table table-striped table-sm table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>Group</th>
                    <th>Event</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Attendees</th>
                    <th>Leader</th>
                    <th>Comments</th>
                    <th>Submitted</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($record_count === 0): ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            No attendance records in the last 10 days.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($records as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['activity_date']) ?></td>
                            <td><?= htmlspecialchars($r['group_name']) ?></td>
                            <td><?= htmlspecialchars($r['event_name']) ?></td>
                            <td><?= htmlspecialchars($r['starting_time']) ?></td>
                            <td><?= $r['ending_time'] ? htmlspecialchars($r['ending_time']) : '<span class="text-muted">—</span>' ?></td>
                            <td><strong class="text-success"><?= $r['total_attendees'] ?></strong></td>
                            <td><?= htmlspecialchars($r['leader_name']) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($r['comments'] ?: '—') ?></td>
                            <td class="text-muted small">
                                <?= date('M j, Y g:i A', strtotime($r['submitted_at'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <hr>
    <p class="text-muted small">
        Showing data from <strong><?= $ten_days_ago ?></strong> to <strong><?= date('Y-m-d') ?></strong>
    </p>
</div>

</body>
</html>