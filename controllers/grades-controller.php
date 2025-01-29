<?php
include('../config/db.php'); // Database connection
include('../model/grades.php'); // Grades model

// Get data from the form
$exam_id = $_POST['exam_id'] ?? null;
$class_id = $_POST['class_id'] ?? null;
$subject_id = $_POST['subject_id'] ?? null;
$student_ids = $_POST['student_ids'] ?? [];
$grades = $_POST['grades'] ?? [];

if (!$exam_id || !$class_id || !$subject_id || empty($student_ids) || empty($grades)) {
    echo "<pre>";
    echo "Exam ID: " . htmlspecialchars($exam_id) . "\n";
    echo "Class ID: " . htmlspecialchars($class_id) . "\n";
    echo "Subject ID: " . htmlspecialchars($subject_id) . "\n";
    echo "Student IDs: " . print_r($student_ids, true) . "\n";
    echo "Grades: " . print_r($grades, true) . "\n";
    die("Invalid request - Missing required fields.");
}

// Initialize the Grades model
$gradesModel = new Grades($conn);

// Insert grades into the database
foreach ($student_ids as $index => $student_id) {
    $grade = $grades[$index];
    $success = $gradesModel->insertGrade($exam_id, $class_id, $subject_id, $student_id, $grade);
    
    if (!$success) {
        die("Failed to insert grade for student ID: $student_id");
    }
}

// Redirect to a success page or display a success message
header("Location: ../view/success.php");
exit;
?>
