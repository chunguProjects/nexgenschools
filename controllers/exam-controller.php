<?php
include_once('../model/exam.php');
include_once('../config/db.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$examModel = new Exams($conn);

// ===== ADD EXAM =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_exam'])) {
    $schoolId = $_SESSION['user']['school_id'] ?? null;

    if (!$schoolId) {
        header("Location: ../view/add-exam-form.php?error=Unauthorized+Access");
        exit;
    }

    $examName = trim($_POST['exam_name'] ?? '');
    $term = trim($_POST['term'] ?? '');
    $academicYear = trim($_POST['academic_year'] ?? '');

    if (empty($examName) || empty($term) || empty($academicYear)) {
        header("Location: ../view/add-exam-form.php?error=Please+fill+all+required+fields");
        exit;
    }

    $result = $examModel->addExam($examName, $term, $academicYear, $schoolId);

    if ($result) {
        header("Location: ../view/add-exam-form.php?message=Exam+Added+Successfully");
    } else {
        header("Location: ../view/add-exam-form.php?error=Error+Adding+Exam");
    }
    exit;
}

// ===== UPDATE EXAM =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_exam'])) {
    $examId = (int)($_POST['exam_id'] ?? 0);
    $examName = trim($_POST['exam_name'] ?? '');
    $term = trim($_POST['term'] ?? '');
    $academicYear = trim($_POST['academic_year'] ?? '');

    if (!$examId || empty($examName) || empty($term) || empty($academicYear)) {
        header("Location: ../view/add-exam-form.php?exam_id=$examId&error=Missing+Required+Fields");
        exit;
    }

    $result = $examModel->updateExam($examId, $examName, $term, $academicYear);

    if ($result) {
        header("Location: ../view/add-exam-form.php?exam_id=$examId&message=Exam+Updated+Successfully");
    } else {
        header("Location: ../view/add-exam-form.php?exam_id=$examId&error=Failed+to+Update+Exam");
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_exam'])) {
    $examId = (int)($_POST['exam_id'] ?? 0);
    if ($examId) {
        $result = $examModel->deleteExam($examId);
        if ($result) {
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Exam Deleted'];
        } else {
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Delete Failed'];
        }
    } else {
        $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Invalid Exam ID'];
    }

    header("Location: /nexgenschools/dashboard/schooladmin.php?page=exam");
    exit;
}

