<?php
include('../config/db.php');

$year = $_GET['academic_year_id'];

$data = [];

/* Classes */
$stmt = $conn->prepare("
SELECT class_id, name 
FROM classes 
WHERE academic_year_id = ?
ORDER BY name
");
$stmt->bind_param("i",$year);
$stmt->execute();
$data['classes'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* Terms */
$stmt = $conn->prepare("
SELECT DISTINCT term 
FROM exams 
WHERE academic_year_id = ?
ORDER BY term
");
$stmt->bind_param("i",$year);
$stmt->execute();
$data['terms'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* Exams */
$stmt = $conn->prepare("
SELECT id, exam_name 
FROM exams 
WHERE academic_year_id = ?
ORDER BY exam_name
");
$stmt->bind_param("i",$year);
$stmt->execute();
$data['exams'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode($data);
