<?php
include_once('../model/class.php');
include_once('../config/db.php');

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_class'])) {
    // Check if required fields are set
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $teacher_id = $_POST['teacher_id'] ?? null;
    $school_id = $_SESSION['user']['school_id'] ?? null;

    if ($name && $teacher_id && $school_id) {
        $class = new ClassGroup($conn);
        $result = $class->addClass($name, $description, $teacher_id, $school_id);

        if ($result) {
            header("Location: ../views/add-class-form.php?message=Class+Added+Successfully");
            exit;
        } else {
            header("Location: ../views/add-class-form.php?error=Error+Adding+Class");
            exit;
        }
    } else {
        // Missing required inputs
        header("Location: ../views/add-class-form.php?error=Missing+required+fields");
        exit;
    }
}
?>
