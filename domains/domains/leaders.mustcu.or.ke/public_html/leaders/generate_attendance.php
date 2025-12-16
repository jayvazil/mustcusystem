<?php
// Session check


require_once 'tcpdf/tcpdf.php'; // For PDF
require_once 'vendor/autoload.php'; // For PHPWord (after Composer install)

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

// Database connection (same as above)
$dsn = 'mysql:host=localhost;dbname=uvwehfds_mustcu';
$username = 'uvwehfds_mustcu';
$password = '7ZwV6yxXKGrD2LPn5eSH';
try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get form inputs
$table = isset($_POST['table']) ? $_POST['table'] : '';
$ministry = isset($_POST['ministry']) ? trim($_POST['ministry']) : '';
$year_of_study = isset($_POST['year_of_study']) ? trim($_POST['year_of_study']) : '';
$format = isset($_POST['format']) ? $_POST['format'] : 'pdf';

if (!in_array($table, ['members', 'leaders', 'associates'])) {
    die("Invalid table selected.");
}

// Build query
$sql = "SELECT name, phone FROM $table WHERE 1=1";
$params = [];
if ($ministry) {
    $sql .= " AND ministry LIKE ?";
    $params[] = "%$ministry%";
}
if ($year_of_study) {
    $sql .= " AND year_of_study LIKE ?";
    $params[] = "%$year_of_study%";
}
$sql .= " ORDER BY name";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Build title summary
$title_parts = [];
if ($ministry) $title_parts[] = "Ministry: $ministry";
if ($year_of_study) $title_parts[] = "Year: $year_of_study";
$search_title = 'Attendance Form' . (!empty($title_parts) ? ' for ' . implode(', ', $title_parts) : '') . " in " . ucfirst($table) . " Table";

// Organization details
$org_name = 'Your Organization';
$org_address = '123 Main St, City, State';
$logo_path = 'logo.png'; // Your logo file
$org_vision = 'Our vision is to empower communities through education and collaboration.';
$generation_datetime = date('Y-m-d H:i:s');

if (empty($members)) {
    die("No matching members found.");
}

if ($format === 'pdf') {
    // PDF Generation with TCPDF
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false); // Portrait A4

    $pdf->SetCreator($org_name);
    $pdf->SetAuthor('Attendance System');
    $pdf->SetTitle($search_title);

    // Custom header with logo
    $pdf->SetHeaderData($logo_path, 30, "$org_name\n$org_address", $search_title);

    $pdf->SetMargins(10, 30, 10); // Increased top margin for header
    $pdf->SetHeaderMargin(10);
    $pdf->SetFooterMargin(10);

    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 10);

    // Table HTML
    $html = '<table border="1" cellpadding="5">
        <thead>
            <tr>
                <th>Name</th>
                <th>Phone Number</th>
                <th>Date 1</th>
                <th>Date 2</th>
                <th>Date 3</th>
                <th>Date 4</th>
                <th>Date 5</th>
            </tr>
        </thead>
        <tbody>';
    foreach ($members as $member) {
        $html .= '<tr>
            <td>' . htmlspecialchars($member['name']) . '</td>
            <td>' . htmlspecialchars($member['phone']) . '</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>';
    }
    $html .= '</tbody></table>';

    $pdf->writeHTML($html, true, false, true, false, '');

    // Bottom content
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'I', 9);
    $pdf->Cell(0, 10, "Organization Vision: $org_vision", 0, 1);
    $pdf->Cell(0, 10, "Generated on: $generation_datetime", 0, 1);

    // Output
    $pdf->Output('attendance_form.pdf', 'D');

} elseif ($format === 'word') {
    // Word Generation with PHPWord
    $phpWord = new PhpWord();
    $section = $phpWord->addSection(['paperSize' => 'A4', 'marginLeft' => 600, 'marginRight' => 600, 'marginTop' => 600, 'marginBottom' => 600]);

    // Add logo at top
    $header = $section->addHeader();
    $header->addImage($logo_path, ['width' => 100, 'height' => 50, 'alignment' => 'center']);

    // Org details and title
    $section->addText("$org_name", ['bold' => true, 'size' => 14], ['alignment' => 'center']);
    $section->addText($org_address, ['size' => 10], ['alignment' => 'center']);
    $section->addText($search_title, ['bold' => true, 'size' => 12], ['alignment' => 'center']);
    $section->addTextBreak(1);

    // Table
    $table = $section->addTable(['borderSize' => 1, 'borderColor' => '000000', 'alignment' => 'center']);
    $table->addRow();
    $table->addCell(2000)->addText('Name');
    $table->addCell(2000)->addText('Phone Number');
    $table->addCell(1500)->addText('Date 1');
    $table->addCell(1500)->addText('Date 2');
    $table->addCell(1500)->addText('Date 3');
    $table->addCell(1500)->addText('Date 4');
    $table->addCell(1500)->addText('Date 5');

    foreach ($members as $member) {
        $table->addRow();
        $table->addCell(2000)->addText(htmlspecialchars($member['name']));
        $table->addCell(2000)->addText(htmlspecialchars($member['phone']));
        $table->addCell(1500)->addText(''); // Blank
        $table->addCell(1500)->addText('');
        $table->addCell(1500)->addText('');
        $table->addCell(1500)->addText('');
        $table->addCell(1500)->addText('');
    }

    // Bottom content
    $section->addTextBreak(1);
    $section->addText("Organization Vision: $org_vision", ['italic' => true, 'size' => 9]);
    $section->addText("Generated on: $generation_datetime", ['italic' => true, 'size' => 9]);

    // Output
    $writer = IOFactory::createWriter($phpWord, 'Word2007');
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="attendance_form.docx"');
    $writer->save('php://output');
    exit;
}
?>