<?php
session_start();
require_once('../model/subject.php');
require_once('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subject'])) {
    // Sanitize and validate inputs
    $subjectName = trim($_POST['subject_name'] ?? '');
    $subjectCode = trim($_POST['subject_code'] ?? '');
    $teacherId   = !empty($_POST['teacher_id']) ? intval($_POST['teacher_id']) : null;
    $schoolId    = $_SESSION['user']['school_id'] ?? null;

    // Basic validation
    if (!$schoolId || empty($subjectName)) {
        header("Location: ../view/add-subject-form.php?error=Missing+Required+Fields");
        exit;
    }

    // Use null for subject code if empty (if you've removed it from your DB, remove this part completely)
    $subjectCode = !empty($subjectCode) ? $subjectCode : null;

    $subjectModel = new Subjects($conn);
    try {
        $result = $subjectModel->addSubject($subjectName, $subjectCode, $teacherId, $schoolId);
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
if (isset($_POST['update_subject'])) {
    $subjectId = (int)$_POST['subject_id'];
    $name      = trim($_POST['subject_name']);
    $code      = trim($_POST['subject_code']);
    $teacherId = (int)$_POST['teacher_id'];

    $model = new Subjects($conn);
    $success = $model->updateSubject($subjectId, $name, $code, $teacherId);

    if ($success) {
        header("Location: ../views/schooladmin.php?page=subject&message=Subject updated successfully.");
    } else {
        header("Location: ../views/schooladmin.php?page=subject&error=Update failed.");
    }
    exit;
}
if (isset($_POST['delete_subject'])) {
    $subjectId = $_POST['subject_id'];
    $schoolId = $_SESSION['user']['school_id'] ?? null;

    if (!$schoolId) {
        header("Location: ../views/schooladmin.php?page=subject&error=Unauthorized");
        exit;
    }

    $subjectModel = new Subjects($conn);

    if ($subjectModel->deleteSubject($subjectId, $schoolId)) {
        header("Location: ../views/schooladmin.php?page=subject&message=Subject deleted successfully");
    } else {
        header("Location: ../views/schooladmin.php?page=subject&error=Failed to delete subject");
    }
    exit;
}


