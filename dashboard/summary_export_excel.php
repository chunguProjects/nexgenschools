<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'admin') {
    header("Location: ../views/login.php");
    exit;
}

require_once __DIR__ . '/../config/db.php';           // gives us $conn
require_once __DIR__ . '/../model/ResultsCalculator.php';

$data = computeResultsSummary($conn, $_GET);

if ($data['dbError']) {
    http_response_code(500);
    echo 'Could not generate export: ' . htmlspecialchars($data['dbError']);
    exit;
}

$filename = 'results_' . preg_replace('/\W+/', '_', $data['selectedClassName'] ?: 'export') . '.xls';

header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

echo "\xEF\xBB\xBF"; // UTF-8 BOM so Excel reads accents/special chars correctly

echo '<table border="1">';
echo '<tr><th colspan="' . (4 + count($data['subjects'])) . '">'
    . htmlspecialchars($data['selectedExamName'] . ' — ' . $data['selectedYearName'] . ' — ' . $data['selectedClassName'])
    . '</th></tr>';
echo '<tr><th>Pos</th><th>Student Name</th>';
foreach ($data['subjects'] as $s) {
    echo '<th>' . htmlspecialchars($s['subject_code']) . '</th>';
}
echo '<th>' . htmlspecialchars($data['metricLabel']) . '</th><th>Result</th></tr>';

foreach ($data['students'] as $row) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars((string) ($row['position'] ?? '')) . '</td>';
    echo '<td>' . htmlspecialchars($row['name']) . '</td>';
    foreach ($row['marks'] as $m) {
        echo '<td>' . ($m['marks'] === null ? '' : (int) $m['marks']) . '</td>';
    }
    $metricVal = $data['isJCE'] ? $row['total_marks'] : $row['total_points'];
    echo '<td>' . ($row['has_marks'] ? (int) $metricVal : '') . '</td>';
    echo '<td>' . htmlspecialchars($row['result'] ?? '') . '</td>';
    echo '</tr>';
}
echo '</table>';