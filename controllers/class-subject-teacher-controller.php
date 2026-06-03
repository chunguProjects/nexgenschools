<?php
session_start();
include('../config/db.php');
include('../model/classSubjectTeacher.php');

$classSubjectModel = new ClassSubjectTeacher($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_assignments'])) {

    $classId = intval($_POST['class_id'] ?? 0);
    $academicYearId = intval($_POST['academic_year_id'] ?? 0);
    $assignments = $_POST['assignments'] ?? [];

    // Basic validation
    if (!$classId || !$academicYearId) {
        $_SESSION['error'] = "Missing class or academic year.";
        header("Location: ../dashboard/admin.php?page=assign-subjects&class_id=$classId");
        exit;
    }

    try {

        // Ensure assignments is array
        if (!is_array($assignments)) {
            $assignments = [];
        }

        // Call your model method
        $classSubjectModel->assignTeachers($assignments);

        $_SESSION['success'] = "Teacher assignments saved successfully.";

    } catch (Exception $e) {
        $_SESSION['error'] = "Failed to save teacher assignments: " . $e->getMessage();
    }

    header("Location: ../dashboard/admin.php?page=assign-subjects&class_id=$classId");
    exit;
}

// fallback (prevents blank page if accessed directly)
$_SESSION['error'] = "Invalid request.";
header("Location: ../dashboard/admin.php?page=class");
exit;