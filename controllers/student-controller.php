<?php
include('../config/db.php');
include('../model/student.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$studentModel = new Student($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect posted data
    $classId = $_POST['class_id'] ?? null;
    $firstNames = $_POST['first_name'] ?? [];
    $lastNames = $_POST['last_name'] ?? [];
    $genders = $_POST['gender'] ?? [];
    $parentPhones = $_POST['parent_phone'] ?? [];
    $relationships = $_POST['relationship'] ?? [];

    // Validate basic inputs
    if (!$classId || empty($firstNames) || empty($parentPhones)) {
        header('Location: ../view/add-students-form.php?class_id=' . $classId . '&error=missing_data');
        exit();
    }

    $students = [];

    // Build student array with parent info
    foreach ($firstNames as $index => $firstName) {
        $students[] = [
            'first_name' => trim($firstName),
            'last_name' => trim($lastNames[$index] ?? ''),
            'gender' => $genders[$index] ?? '',
            'parent_phone' => trim($parentPhones[$index] ?? ''),
            'relationship' => $relationships[$index] ?? '',
            'class_id' => $classId,
        ];
    }

    // Add students with parent creation/linking
    $result = $studentModel->addStudentsWithParents($students);

    if ($result['success']) {
        header('Location: ../view/class.php?success=students_added');
        exit();
    } else {
        // You can also pass error message in $result['message']
        header('Location: ../view/add-students-form.php?class_id=' . $classId . '&error=' . urlencode($result['message'] ?? 'unknown_error'));
        exit();
    }
}

// Handle GET request to fetch students by class (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['class_id'])) {
    $classId = (int)$_GET['class_id'];
    $students = $studentModel->getStudentsByClass($classId);

    if ($students) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'students' => $students]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No students found.']);
    }
    exit();
}
