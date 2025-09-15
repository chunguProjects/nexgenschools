<?php
session_start();
require_once('../model/subject.php');
require_once('../config/db.php');

$subjectModel = new Subjects($conn);

// Add Subject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subject'])) {
    // Sanitize inputs
    $subjectName = trim($_POST['subject_name'] ?? '');
    $subjectCode = trim($_POST['subject_code'] ?? '');
    $teacherId   = !empty($_POST['teacher_id']) ? intval($_POST['teacher_id']) : null;

    // Basic validation
    if (empty($subjectName)) {
        header("Location: ../view/add-subject-form.php?error=Missing+Subject+Name");
        exit;
    }

    // Allow null for subject code if empty
    $subjectCode = !empty($subjectCode) ? $subjectCode : null;

    try {
        $result = $subjectModel->addSubject($subjectName, $subjectCode, $teacherId);
        if ($result) {
            header("Location: ../view/add-subject-form.php?message=Subject+Added+Successfully");
            exit;
        } else {
            header("Location: ../view/add-subject-form.php?error=Subject+Already+Exists+or+DB+Error");
            exit;
        }
    } catch (Exception $e) {
        // Optional: log $e->getMessage()
        header("Location: ../view/add-subject-form.php?error=Internal+Error");
        exit;
    }
}

// Update Subject
if (isset($_POST['update_subject'])) {
    $subjectId = (int)$_POST['subject_id'];
    $name      = trim($_POST['subject_name']);
    $code      = trim($_POST['subject_code']);
    $teacherId = (int)$_POST['teacher_id'];

    $success = $subjectModel->updateSubject($subjectId, $name, $code, $teacherId);

    if ($success) {
        header("Location: ../views/schooladmin.php?page=subject&message=Subject+updated+successfully");
    } else {
        header("Location: ../views/schooladmin.php?page=subject&error=Update+failed");
    }
    exit;
}

// Delete Subject
if (isset($_POST['delete_subject'])) {
    $subjectId = (int)$_POST['subject_id'];

    if ($subjectModel->deleteSubject($subjectId)) {
        header("Location: ../views/schooladmin.php?page=subject&message=Subject+deleted+successfully");
    } else {
        header("Location: ../views/schooladmin.php?page=subject&error=Failed+to+delete+subject");
    }
    exit;
}
?>
