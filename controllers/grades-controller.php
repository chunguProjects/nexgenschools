<?php
session_start();
include('../config/db.php'); // Database connection
include('../model/grades.php'); // Grades model

// Get and sanitize POST data
$exam_id = isset($_POST['exam_id']) ? (int)$_POST['exam_id'] : null;
$class_id = isset($_POST['class_id']) ? (int)$_POST['class_id'] : null;
$grades = $_POST['grades'] ?? [];

if (!$exam_id || !$class_id || empty($grades)) {
    die("Invalid request - Missing required fields.");
}

// Get school ID from session
$school_id = $_SESSION['user']['school_id'] ?? null;
if (!$school_id) {
    die("Unauthorized - School context missing.");
}

// Initialize Grades model
$gradesModel = new Grades($conn);

// Loop over the grades matrix: grades[student_id][subject_id] = mark
foreach ($grades as $student_id => $subjectGrades) {
    foreach ($subjectGrades as $subject_id => $mark) {
        $student_id = (int)$student_id;
        $subject_id = (int)$subject_id;
        $mark = trim($mark);

        // Skip empty entries
        if ($mark === '') continue;

        // Validate marks
        if (!is_numeric($mark) || $mark < 0 || $mark > 100) {
            die("Invalid mark ($mark) for student $student_id, subject $subject_id");
        }

        // Check if grade already exists
        $existing = $gradesModel->getGrade($school_id, $exam_id, $class_id, $subject_id, $student_id);

        if ($existing) {
            $success = $gradesModel->updateGrade($school_id, $exam_id, $class_id, $subject_id, $student_id, $mark);
        } else {
            $success = $gradesModel->insertGrade($school_id, $exam_id, $class_id, $subject_id, $student_id, $mark);
        }

        if (!$success) {
            die("Failed to save grade for student ID: $student_id, subject ID: $subject_id");
        }
    }
}

// Redirect with success message
header("Location: ../view/success.php?message=Grades saved successfully");
exit;
