<?php
session_start();
header('Content-Type: application/json');

include('../config/db.php');
include('../model/grades.php');

$exam_id = isset($_POST['exam_id']) ? (int)$_POST['exam_id'] : 0;
$class_id = isset($_POST['class_id']) ? (int)$_POST['class_id'] : 0;
$grades = $_POST['grades'] ?? [];

if (!$exam_id || !$class_id || empty($grades)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields.'
    ]);
    exit;
}

$gradesModel = new Grades($conn);

try {

    foreach ($grades as $student_id => $subjectGrades) {

        foreach ($subjectGrades as $subject_id => $mark) {

            $student_id = (int)$student_id;
            $subject_id = (int)$subject_id;
            $mark = trim($mark);

            // Skip blank grades
            if ($mark === '') {
                continue;
            }

            if (!is_numeric($mark) || $mark < 0 || $mark > 100) {
                throw new Exception(
                    "Invalid mark ($mark) for student $student_id"
                );
            }

            $existing = $gradesModel->getGrade(
                $exam_id,
                $class_id,
                $subject_id,
                $student_id
            );

            if ($existing) {

                $success = $gradesModel->updateGrade(
                    $exam_id,
                    $class_id,
                    $subject_id,
                    $student_id,
                    $mark
                );

            } else {

                $success = $gradesModel->insertGrade(
                    $exam_id,
                    $class_id,
                    $subject_id,
                    $student_id,
                    $mark
                );
            }

            if (!$success) {
                throw new Exception(
                    "Failed to save grade for student ID {$student_id}"
                );
            }
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Grades saved successfully.'
    ]);

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

exit;