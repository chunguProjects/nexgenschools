<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

include('../config/db.php');
include('../model/student.php');
include('../model/class.php');
include('../model/academic_year.php');

try {
    $studentIds = $_POST['student_ids'] ?? [];
    $currentClassId = (int)($_POST['current_class_id'] ?? 0);
    $targetClassId = (int)($_POST['target_class_id'] ?? 0);
    $targetAcademicYearId = (int)($_POST['target_academic_year_id'] ?? 0);

    if (empty($studentIds) || !$currentClassId || !$targetAcademicYearId) {
        throw new Exception('Missing required data.');
    }

    $studentModel = new Student($conn);
    $classGroup = new ClassGroup($conn);
    $yearModel = new AcademicYear($conn);

    // Auto-generate target class if none selected
    if ($targetClassId === 0) {
        if (!method_exists($classGroup, 'getOrCreateNextClass')) {
            throw new Exception('Method getOrCreateNextClass not found in ClassGroup.');
        }
        $targetClass = $classGroup->getOrCreateNextClass($currentClassId, $targetAcademicYearId);
        if (!$targetClass) throw new Exception('Failed to create target class.');
        $targetClassId = $targetClass['class_id'];
    }

    if (!method_exists($studentModel, 'promoteStudents')) {
        throw new Exception('Method promoteStudents not found in Student model.');
    }

    $result = $studentModel->promoteStudents($studentIds, $targetClassId, $targetAcademicYearId);

    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
exit;

