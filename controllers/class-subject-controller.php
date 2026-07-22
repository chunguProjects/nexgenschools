<?php
session_start();
include('../config/db.php');
include('../model/classSubject.php');

$classSubjectModel = new ClassSubject($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_class_subjects'])) {
    $classId = intval($_POST['class_id'] ?? 0);
    $academicYearId = intval($_POST['academic_year_id'] ?? 0);
    $subjectIds = $_POST['subjects'] ?? [];

    if ($classId && $academicYearId) {
        $classSubjectModel->saveClassSubjects($classId, $academicYearId, $subjectIds);
        $_SESSION['success'] = "Subjects saved for class.";
    } else {
        $_SESSION['error'] = "Missing class or academic year.";
    }
    header("Location: ../dashboard/admin.php?page=assign-subjects&class_id=$classId");
    exit;
}