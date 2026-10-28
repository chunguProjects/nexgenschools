<?php
session_start();
header('Content-Type: application/json');

include('../config/db.php');
include('../model/grades.php');

$exam_id    = isset($_POST['exam_id']) ? (int)$_POST['exam_id'] : 0;
$class_id   = isset($_POST['class_id']) ? (int)$_POST['class_id'] : 0;
$student_id = isset($_POST['student_id']) ? (int)$_POST['student_id'] : 0;
$subject_id = isset($_POST['subject_id']) ? (int)$_POST['subject_id'] : 0;
$mark       = isset($_POST['mark']) ? trim($_POST['mark']) : '';

if (!$exam_id || !$class_id || !$student_id || !$subject_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

// A blank cell means "clear this mark" — delete any existing saved value.
if ($mark === '') {
    $gradesModel = new Grades($conn);
    try {
        $existing = $gradesModel->getGrade($exam_id, $class_id, $subject_id, $student_id);
        if ($existing) {
            $gradesModel->deleteGrade($exam_id, $class_id, $subject_id, $student_id);
        }
        echo json_encode(['success' => true, 'message' => 'Cleared.']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if (!is_numeric($mark) || $mark < 0 || $mark > 100) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => "Invalid mark ($mark). Must be 0-100."]);
    exit;
}

$gradesModel = new Grades($conn);

try {
    $existing = $gradesModel->getGrade($exam_id, $class_id, $subject_id, $student_id);

    $success = $existing
        ? $gradesModel->updateGrade($exam_id, $class_id, $subject_id, $student_id, $mark)
        : $gradesModel->insertGrade($exam_id, $class_id, $subject_id, $student_id, $mark);

    if (!$success) {
        throw new Exception('Failed to save grade.');
    }

    echo json_encode(['success' => true, 'message' => 'Saved.']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

exit;