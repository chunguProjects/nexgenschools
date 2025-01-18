<?php
include_once('../model/subject.php');
include_once('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subject'])) {
    $subject = new Subjects($conn);
    $result = $subject->addSubject($_POST['subject_name'], $_POST['subject_code'], $_POST['teacher_id']);
    
    if ($result) {
        // Redirect with success message
        header("Location: ../view/add-subject-form.php?message=Subject+Added+Successfully");
        exit;
    } else {
        // Handle error
        header("Location: ../view/add-subject-form.php?error=Error+Adding+Subject");
        exit;
    }
    
}
?>
