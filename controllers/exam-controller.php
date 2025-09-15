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
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'text' => 'Please fill all required fields'
        ];
        header("Location: ../dashboard/schooladmin.php?page=exam-form");
        exit;
    }

    $result = $examModel->addExam($examName, $term, $academicYearId);

    $_SESSION['flash_message'] = $result
        ? ['type' => 'success', 'text' => 'Exam added successfully']
        : ['type' => 'error', 'text' => 'Exam already exists for this academic year'];

    header("Location: ../dashboard/schooladmin.php?page=exam");
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
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'text' => 'Missing required fields'
        ];
        header("Location: ../dashboard/schooladmin.php?page=exam-form&exam_id=$examId");
        exit;
    }

    $result = $examModel->updateExam($examId, $examName, $term, $academicYearId);

    $_SESSION['flash_message'] = $result
        ? ['type' => 'success', 'text' => 'Exam updated successfully']
        : ['type' => 'error', 'text' => 'Failed to update exam'];

    header("Location: ../dashboard/schooladmin.php?page=exam");
    exit;
}

// ===== DELETE EXAM =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_exam'])) {
    $examId = intval($_POST['exam_id'] ?? 0);

    if ($examId) {
        $result = $examModel->deleteExam($examId);
        $_SESSION['flash_message'] = $result
            ? ['type' => 'success', 'text' => 'Exam deleted successfully']
            : ['type' => 'error', 'text' => 'Failed to delete exam'];
    } else {
        $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Invalid Exam ID'];
    }

    header("Location: ../dashboard/schooladmin.php?page=exam");
    exit;
}
?>
