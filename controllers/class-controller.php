<?php
include_once('../model/class.php');
include_once('../config/db.php');

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$schoolId = $_SESSION['user']['school_id'] ?? null;
$classModel = new ClassGroup($conn);

// Add Class
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_class'])) {
    // Sanitize and prepare inputs
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $teacherId   = isset($_POST['teacher_id']) ? intval($_POST['teacher_id']) : null;

    // ✅ Validate required fields
    if ($name && $teacherId && $schoolId) {
        $result = $classModel->addClass($name, $description, $teacherId, $schoolId);

        if ($result === true) {
            header("Location: ../views/add-class-form.php?message=Class+Added+and+Subjects+Assigned");
            exit;
        } else {
            // Class might already exist
            header("Location: ../views/add-class-form.php?error=Class+already+exists+in+this+school");
            exit;
        }
    } else {
        // Missing required fields
        header("Location: ../views/add-class-form.php?error=Missing+required+fields");
        exit;
    }
}

// Update Class
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_class'])) {
    $classId     = intval($_POST['class_id'] ?? 0);
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $teacherId   = isset($_POST['teacher_id']) ? intval($_POST['teacher_id']) : null;

    if ($classId && $name && $teacherId && $schoolId) {
        $updated = $classModel->updateClass($classId, $name, $description, $teacherId, $schoolId);
        if ($updated) {
            header("Location: ../views/schooladmin.php?page=class&message=Class+updated+successfully");
        } else {
            header("Location: ../views/schooladmin.php?page=class&error=Failed+to+update+class");
        }
        exit;
    } else {
        header("Location: ../views/schooladmin.php?page=class&error=Missing+required+fields");
        exit;
    }
}

// Delete Class
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_class'])) {
    $classId = intval($_POST['class_id'] ?? 0);

    if ($classId && $schoolId) {
        if ($classModel->deleteClass($classId, $schoolId)) {
            header("Location: ../views/schooladmin.php?page=class&message=Class+deleted+successfully");
        } else {
            header("Location: ../views/schooladmin.php?page=class&error=Failed+to+delete+class");
        }
    } else {
        header("Location: ../views/schooladmin.php?page=class&error=Invalid+class+ID");
    }
    exit;
}
