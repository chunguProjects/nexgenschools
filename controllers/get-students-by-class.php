<?php
include('../config/db.php');

// Get class ID
$classId = (int)($_GET['class_id'] ?? 0);
if (!$classId) {
    echo json_encode([]);
    exit;
}

// Fetch current academic year
$currentYearResult = $conn->query("SELECT academic_year_id FROM academic_years WHERE is_current = 1 LIMIT 1");
$currentYear = $currentYearResult->fetch_assoc();
$currentYearId = $currentYear['academic_year_id'] ?? 0;

if (!$currentYearId) {
    echo json_encode([]);
    exit;
}

// Get students from enrollments
$stmt = $conn->prepare("
    SELECT s.student_id, s.first_name, s.last_name, e.promoted
    FROM student_enrollments e
    JOIN students s ON s.student_id = e.student_id
    WHERE e.class_id = ? AND e.academic_year_id = ? AND e.status = 'active'
    ORDER BY s.first_name, s.last_name
");
$stmt->bind_param("ii", $classId, $currentYearId);
$stmt->execute();
$result = $stmt->get_result();

$students = [];
while ($row = $result->fetch_assoc()) {
    $row['already_promoted'] = (bool)$row['promoted'];
    $students[] = $row;
}

$stmt->close();

header('Content-Type: application/json');
echo json_encode($students);
