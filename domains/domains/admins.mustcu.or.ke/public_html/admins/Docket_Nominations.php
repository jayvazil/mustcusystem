<?php
require_once 'includes/db_connect.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Define position mappings - explicitly ordered
$positions = [
    1 => 'Instrumentalist Coordinator',
    2 => 'Praise & Worship Coordinator',
    3 => 'Choir Coordinator',
    4 => 'Instrumentalist Secretary',
    5 => 'Praise & Worship Secretary',
    6 => 'Choir Secretary',
    7 => 'IT Coordinator',
    8 => 'Creative Coordinator',
    9 => 'Publicity Coordinator',
    10 => 'IT Secretary',
    11 => 'Creative Secretary',
    12 => 'Publicity Secretary',
    13 => 'Ushering Coordinator',
    14 => 'Catering Coordinator',
    15 => 'Decor Coordinator',
    16 => 'Ushering Secretary',
    17 => 'Catering Secretary',
    18 => 'Decor Secretary'
];

// Database column mapping
$columnMapping = [
    1 => 'instrumentalist_coordinator',
    2 => 'praise_worship_coordinator',
    3 => 'choir_coordinator',
    4 => 'instrumentalist_secretary',
    5 => 'praise_worship_secretary',
    6 => 'choir_secretary',
    7 => 'it_coordinator',
    8 => 'creative_coordinator',
    9 => 'publicity_coordinator',
    10 => 'it_secretary',
    11 => 'creative_secretary',
    12 => 'publicity_secretary',
    13 => 'ushering_coordinator',
    14 => 'catering_coordinator',
    15 => 'decor_coordinator',
    16 => 'ushering_secretary',
    17 => 'catering_secretary',
    18 => 'decor_secretary'
];

// Fetch all nominations data
$query = "SELECT * FROM nominations1";
$stmt = $pdo->query($query);
$nominations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize position-wise analysis
$positionAnalysis = [];
foreach ($positions as $positionNum => $positionName) {
    $columnKey = $columnMapping[$positionNum];
    $positionAnalysis[$positionNum] = [
        'key' => $columnKey,
        'name' => $positionName,
        'nominees' => []
    ];
}

// Analyze each position
foreach ($nominations as $row) {
    foreach ($positionAnalysis as $positionNum => $positionData) {
        $columnKey = $positionData['key'];
        $name = trim($row[$columnKey]);
        if (!empty($name)) {
            if (!isset($positionAnalysis[$positionNum]['nominees'][$name])) {
                $positionAnalysis[$positionNum]['nominees'][$name] = 0;
            }
            $positionAnalysis[$positionNum]['nominees'][$name]++;
        }
    }
}

// Sort nominees within each position by count (descending)
foreach ($positionAnalysis as &$position) {
    arsort($position['nominees']);
}

// Calculate totals
$totalSubmissions = count($nominations);
$totalNominations = 0;
$uniqueNominees = [];
foreach ($positionAnalysis as $position) {
    foreach ($position['nominees'] as $name => $count) {
        $totalNominations += $count;
        $uniqueNominees[$name] = true;
    }
}
$uniqueCount = count($uniqueNominees);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Departmental Nominations Analysis</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(#0207BA 0%, #0207BA 100%);
            padding: 20px;
            min-height: 100vh;
        }
        
        .container1 {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #ff7900 0%, #ff7900 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 30px;
            background: #f8f9fa;
        }
        
        .summary-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .summary-card h3 {
            color: #0207BA;
            font-size: 2em;
            margin-bottom: 5px;
        }
        
        .summary-card p {
            color: #6c757d;
            font-size: 0.9em;
        }
        
        .content {
            padding: 30px;
        }
        
        .search-box {
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .search-box input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 1em;
            transition: border-color 0.3s;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: #0207BA;
        }
        
        .position-section {
            margin-bottom: 40px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .position-header {
            background: linear-gradient(135deg, #0207BA 0%, #0207ba 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .position-header h2 {
            font-size: 1.4em;
            font-weight: 600;
        }
        
        .position-count {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
        }
        
        .nominees-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .nominees-table thead {
            background: #f8f9fa;
        }
        
        .nominees-table th,
        .nominees-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        .nominees-table th {
            font-weight: 600;
            color: #495057;
            text-transform: uppercase;
            font-size: 0.85em;
            letter-spacing: 0.5px;
        }
        
        .nominees-table tbody tr {
            transition: background-color 0.2s;
        }
        
        .nominees-table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .rank-cell {
            font-weight: 700;
            color: #0207BA;
            font-size: 1.1em;
            width: 60px;
        }
        
        .name-cell {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .count-cell {
            font-weight: 700;
            color: #FF7900;
            font-size: 1.2em;
            width: 120px;
        }
        
        .count-badge {
            display: inline-block;
            background: linear-gradient(135deg, #0207BA 0%, #FF7900 100%);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.95em;
        }
        
        .no-nominations {
            padding: 30px;
            text-align: center;
            color: #6c757d;
            font-style: italic;
        }
        
        .top-badge {
            display: inline-block;
            background: #FFD700;
            color: #333;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75em;
            margin-left: 10px;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.8em;
            }
            
            .position-header {
                flex-direction: column;
                gap: 10px;
            }
            
            .nominees-table {
                font-size: 0.9em;
            }
            
            .nominees-table th,
            .nominees-table td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container1">
        <div class="header">
            <h1>Ministerial Nominations Analysis</h1>
            <p>Ministerial leadership nominations organized by position</p>
        </div>
        
        <div class="summary">
            <div class="summary-card">
                <h3><?php echo $uniqueCount; ?></h3>
                <p>Unique Nominees</p>
            </div>
            <div class="summary-card">
                <h3><?php echo $totalSubmissions; ?></h3>
                <p>Total Submissions</p>
            </div>
            <div class="summary-card">
                <h3><?php echo $totalNominations; ?></h3>
                <p>Total Nominations</p>
            </div>
            <div class="summary-card">
                <h3><?php echo count($positions); ?></h3>
                <p>Ministerial Positions</p>
            </div>
        </div>
        
        <div class="content">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="🔍 Search by name or position...">
            </div>
            
            <?php 
            // Display all 18 positions in order
            foreach ($positionAnalysis as $positionNum => $positionData):
            ?>
            <div class="position-section">
                <div class="position-header">
                    <h2><?php echo htmlspecialchars($positionData['name']); ?></h2>
                    <span class="position-count">
                        <?php echo count($positionData['nominees']); ?> Nominees
                    </span>
                </div>
                
                <?php if (count($positionData['nominees']) > 0): ?>
                <table class="nominees-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Nominee Name</th>
                            <th>Nominations</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rank = 1;
                        $maxCount = max($positionData['nominees']);
                        foreach ($positionData['nominees'] as $name => $count): 
                        ?>
                        <tr>
                            <td class="rank-cell">#<?php echo $rank; ?></td>
                            <td class="name-cell">
                                <?php echo htmlspecialchars($name); ?>
                                <?php if ($rank === 1 && $count > 1): ?>
                                <span class="top-badge">⭐ Top Nominee</span>
                                <?php endif; ?>
                            </td>
                            <td class="count-cell">
                                <span class="count-badge"><?php echo $count; ?></span>
                            </td>
                        </tr>
                        <?php 
                        $rank++;
                        endforeach; 
                        ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="no-nominations">
                    No nominations received for this position
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const sections = document.querySelectorAll('.position-section');
            
            sections.forEach(section => {
                const positionName = section.querySelector('.position-header h2').textContent.toLowerCase();
                const rows = section.querySelectorAll('.nominees-table tbody tr');
                let hasVisibleRow = false;
                
                rows.forEach(row => {
                    const name = row.cells[1].textContent.toLowerCase();
                    if (name.includes(searchTerm) || positionName.includes(searchTerm)) {
                        row.style.display = '';
                        hasVisibleRow = true;
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                // Show/hide entire section based on search
                if (positionName.includes(searchTerm) || hasVisibleRow) {
                    section.style.display = '';
                } else {
                    section.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>