<?php
include_once('../model/class.php');
include_once('../config/db.php');

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize model
$classModel = new ClassGroup($conn);

// ================= ADD CLASS =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_class'])) {
    $name           = trim($_POST['name'] ?? '');
    $description    = trim($_POST['description'] ?? '');
    $academicYearId = isset($_POST['academic_year_id']) ? intval($_POST['academic_year_id']) : null;
    $formTeacherId  = isset($_POST['teacher_id']) ? intval($_POST['teacher_id']) : null;

    if (!$name || !$academicYearId) {
        $_SESSION['error'] = "Class name and academic year are required.";
        header("Location: ../dashboard/admin.php?page=class");
        exit;
    }

    $added = $classModel->addClass($name, $description, $academicYearId, $formTeacherId);

    if ($added) {
        $_SESSION['success'] = "Class added successfully.";

        // Optionally, redirect to assign subjects immediately
        // header("Location: ../dashboard/admin.php?page=assign-subjects&class_id={$added}");
    } else {
        $_SESSION['error'] = "Class already exists in this academic year.";
    }

    header("Location: ../dashboard/admin.php?page=class");
    exit;
}

// ================= UPDATE CLASS =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_class'])) {
    $classId        = intval($_POST['class_id'] ?? 0);
    $name           = trim($_POST['name'] ?? '');
    $description    = trim($_POST['description'] ?? '');
    $academicYearId = isset($_POST['academic_year_id']) ? intval($_POST['academic_year_id']) : null;
    $formTeacherId  = isset($_POST['teacher_id']) ? intval($_POST['teacher_id']) : null;

    if (!$classId || !$name || !$academicYearId) {
        $_SESSION['error'] = "Missing required fields for update.";
        header("Location: ../dashboard/admin.php?page=class");
        exit;
    }

    $updated = $classModel->updateClass($classId, $name, $description, $academicYearId, $formTeacherId);

    if ($updated) {
        $_SESSION['success'] = "Class updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update class.";
    }

    header("Location: ../dashboard/admin.php?page=class");
    exit;
}

// ================= DELETE CLASS =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_class'])) {

    $classId = intval($_POST['class_id'] ?? 0);

    if (!$classId) {
        $_SESSION['error'] = "Invalid class ID.";
        header("Location: ../dashboard/admin.php?page=class");
        exit;
    }

    $result = $classModel->deleteClass($classId);

    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
    } else {
        $_SESSION['error'] = "Delete failed: " . $result['message'];
    }

    header("Location: ../dashboard/admin.php?page=class");
    exit;
}

// ================= ASSIGN SUBJECT-TEACHER =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_subjects'])) {
    $classId        = intval($_POST['class_id'] ?? 0);
    $academicYearId = intval($_POST['academic_year_id'] ?? 0);
    $assignments    = $_POST['subjects'] ?? []; // subjects[subject_id] => teacher_id

    if (!$classId || !$academicYearId || empty($assignments)) {
        $_SESSION['error'] = "Missing data for subject-teacher assignments.";
        header("Location: ../dashboard/admin.php?page=assign-subjects&class_id={$classId}");
        exit;
    }

    $success = true;
    foreach ($assignments as $subjectId => $teacherId) {
        $teacherId = intval($teacherId);
        $subjectId = intval($subjectId);

        if (!$classModel->assignSubjectTeacher($classId, $subjectId, $teacherId, $academicYearId)) {
            $success = false;
        }
    }

    $_SESSION['success'] = $success 
        ? "Subjects assigned to teachers successfully."
        : "Some assignments failed. Please try again.";

    header("Location: ../dashboard/admin.php?page=class-detail&class_id={$classId}");
    exit;
}
?>
