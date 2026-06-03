<?php
session_start();

include('../config/db.php');
include('../model/classSubjectTeacher.php');
include('../model/subject.php');
include('../model/user.php');
include('../model/academic_year.php');

// Initialize models
$cstModel     = new ClassSubjectTeacher($conn);
$subjectModel = new Subjects($conn);
$teacherModel = new User($conn);
$ayModel      = new AcademicYear($conn);

// Get class ID from query string
$classId = intval($_GET['class_id'] ?? 0);

// Fetch current academic year
$academicYear = $ayModel->getCurrentYear();
$academicYearId = $academicYear['academic_year_id'] ?? 0;

// Fetch all subjects and teachers
$subjects = $subjectModel->getAllSubjects();
$teachers = $teacherModel->getUsersByRoleId(2); // array of teachers

// Fetch existing assignments
$existingAssignments = [];
foreach ($cstModel->getByClass($classId, $academicYearId) as $assign) {
    $existingAssignments[$assign['subject_id']] = $assign['teacher_id'];
}

// Include view
include('../views/assign-subjects.php');
