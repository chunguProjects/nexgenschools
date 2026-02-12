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
        $_SESSION['error'] = "Missing subject name";
        header("Location: ../dashboard/admin.php?page=add-subject-form");
        exit;
    }

    // Allow null for subject code if empty
    $subjectCode = !empty($subjectCode) ? $subjectCode : null;

    try {
        if($subjectModel->addSubject($subjectName, $subjectCode, $teacherId)){
            $_SESSION['success'] = "Subject added successfully.";
        }else{
            $_SESSION['error'] = "Subject already exists.";
        }
            header("Location: ../dashboard/admin.php?page=add-subject-form");
            exit;
    } catch (Exception $e) {
        // Optional: log $e->getMessage()
        $_SESSION['error'] = "Error adding subject";
        header("Location: ../dashboard/admin.php?page=add-subject-form");
        exit;
    }
}

// Update Subject
if (isset($_POST['update_subject'])) {
    $subjectId = (int)$_POST['subject_id'];
    $name      = trim($_POST['subject_name']);
    $code      = trim($_POST['subject_code']);
    $teacherId = (int)$_POST['teacher_id'];

   if($subjectModel->updateSubject($subjectId, $name, $code, $teacherId)){
        $_SESSION['success'] = "Subject updated successfully.";

   }else{
        $_SESSION['error'] = "Subject failed to update.";
   }
        header("Location: ../dashboard/admin.php?page=subject");
    exit;
}

// Delete Subject
if (isset($_POST['delete_subject'])) {
    $subjectId = (int)$_POST['subject_id'];

    if ($subjectModel->deleteSubject($subjectId)) {
         $_SESSION['success'] = "Subject deleted successfully.";

    } else {
        $_SESSION['error'] = "Failed to delete subject.";
    }
     header("Location: ../dashboard/admin.php?page=subject");
    exit;
}
?>
