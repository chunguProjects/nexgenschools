<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

include_once(__DIR__ . '/../config/db.php');
include_once(__DIR__ . '/../model/student.php');

$classId = isset($_GET['class_id']) ? (int) $_GET['class_id'] : 0;

if (!$classId) {
    http_response_code(400);
    echo json_encode(['students' => [], 'message' => 'Missing class_id.']);
    exit;
}

$studentModel = new Student($conn);
$students = $studentModel->getStudentsByClass($classId);

// Keep only what the picker needs
$slim = array_map(function ($s) {
    return [
        'student_id' => $s['student_id'],
        'first_name' => $s['first_name'],
        'last_name'  => $s['last_name'],
    ];
}, $students ?: []);

echo json_encode(['students' => $slim]);
exit;