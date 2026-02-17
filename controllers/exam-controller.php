<?php
include_once('../config/db.php');
include_once('../model/exam.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$examModel = new Exams($conn);

// ===== ADD EXAM =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_exam'])) {
    $examName       = trim($_POST['exam_name'] ?? '');
    $term           = trim($_POST['term'] ?? '');
    $academicYearId = isset($_POST['academic_year_id']) ? intval($_POST['academic_year_id']) : null;

    // Validate required fields
    if (!$examName || !$term || !$academicYearId) {
        $_SESSION['error'] = "Please fill all required fields";
        header("Location: ../dashboard/admin.php?page=add-exam-form");
        exit;
    }

    if($examModel->addExam($examName, $term, $academicYearId)){

    $_SESSION['success'] = "Exam added successfully";
    }else{
    $_SESSION['error'] = "Exam already exists for this academic year";
    
    }
    header("Location: ../dashboard/admin.php?page=add-exam-form");
    exit;
    }


// ===== UPDATE EXAM =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_exam'])) {
    $examId         = intval($_POST['exam_id'] ?? 0);
    $examName       = trim($_POST['exam_name'] ?? '');
    $term           = trim($_POST['term'] ?? '');
    $academicYearId = isset($_POST['academic_year_id']) ? intval($_POST['academic_year_id']) : null;

    // Validate required fields
    if (!$examId || !$examName || !$term || !$academicYearId) {
        $_SESSION['error'] = "Missing required fields";
        header("Location: ../dashboard/admin.php?page=exam-form&exam_id=$examId");
        exit;
    }

    if($examModel->updateExam($examId, $examName, $term, $academicYearId)){
        $_SESSION['success'] = "Exam updated successfully";
    }else{
        $_SESSION['error'] = "Failed to update exam";
    }
        header("Location: ../dashboard/admin.php?page=exam");
    exit;
}

// ===== DELETE EXAM =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_exam'])) {
    $examId = intval($_POST['exam_id'] ?? 0);

    if ($examId) {
        $result = $examModel->deleteExam($examId);
        $_SESSION['success'] = "Exam deleted successfully";
           
    } else {
        $_SESSION['error'] = "Failed to delete the Exam";
    }

    header("Location: ../dashboard/admin.php?page=exam");
    exit;
}

?>
