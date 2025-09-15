<?php
include_once('../model/class.php');
include_once('../config/db.php');

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$classModel = new ClassGroup($conn);

// ================= Add Class =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_class'])) {
    $name           = trim($_POST['name'] ?? '');
    $description    = trim($_POST['description'] ?? '');
    $academicYearId = isset($_POST['academic_year_id']) ? intval($_POST['academic_year_id']) : null;
    $teacherId      = isset($_POST['teacher_id']) ? intval($_POST['teacher_id']) : null;

    if ($name && $academicYearId) {
        $result = $classModel->addClass($name, $description, $academicYearId, $teacherId);

        if ($result === true) {
            $_SESSION['success'] = "Class added and subjects assigned successfully.";
        } else {
            $_SESSION['error'] = "Class already exists in this academic year.";
        }
    } else {
        $_SESSION['error'] = "Missing required fields.";
    }

    header("Location: ../dashboard/admin.php?page=class");
    exit;
}

// ================= Update Class =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_class'])) {
    $classId        = intval($_POST['class_id'] ?? 0);
    $name           = trim($_POST['name'] ?? '');
    $description    = trim($_POST['description'] ?? '');
    $academicYearId = isset($_POST['academic_year_id']) ? intval($_POST['academic_year_id']) : null;
    $teacherId      = isset($_POST['teacher_id']) ? intval($_POST['teacher_id']) : null;

    if ($classId && $name && $academicYearId) {
        $updated = $classModel->updateClass($classId, $name, $description, $academicYearId, $teacherId);

        if ($updated) {
            $_SESSION['success'] = "Class updated successfully.";
        } else {
            $_SESSION['error'] = "Failed to update class.";
        }
    } else {
        $_SESSION['error'] = "Missing required fields.";
    }

    header("Location: ../dashboard/admin.php?page=class");
    exit;
}

// ================= Delete Class =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_class'])) {
    $classId = intval($_POST['class_id'] ?? 0);

    if ($classId) {
        if ($classModel->deleteClass($classId)) {
            $_SESSION['success'] = "Class deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete class.";
        }
    } else {
        $_SESSION['error'] = "Invalid class ID.";
    }

    header("Location: ../dashboard/admin.php?page=class");
    exit;
}
?>
