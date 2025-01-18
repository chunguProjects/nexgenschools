<?php
include_once('../model/exam.php');
include_once('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_exam'])) {
    $exam = new Exams($conn);
    $result = $exam->addExam($_POST['exam_name'], $_POST['term'], $_POST['academic_year']);
    
    if ($result) {
        // Redirect with success message
        header("Location: ../view/add-exam-form.php?message=Exam+Added+Successfully");
        exit;
    } else {
        // Handle error
        header("Location: ../view/add-exam-form.php?error=Error+Adding+Exam");
        exit;
    }
    
}
?>
    