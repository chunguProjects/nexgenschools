<?php
include('../config/db.php');
include('../model/student.php');

if (session_status() === PHP_SESSION_NONE) session_start();

$studentModel = new Student($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $classId = $_POST['class_id'] ?? null;
    $studentIds = $_POST['student_id'] ?? []; // This can contain empty string for new entries
    $firstNames = $_POST['first_name'] ?? [];
    $lastNames = $_POST['last_name'] ?? [];
    $genders = $_POST['gender'] ?? [];
    $dateOfBirths = $_POST['date_of_birth'] ?? [];
    $parentPhones = $_POST['parent_phone'] ?? [];
    $relationships = $_POST['relationship'] ?? [];
    $deleteIds = $_POST['delete_student'] ?? [];

    if (!$classId || empty($firstNames)) {
        header("Location: ../view/add-students-form.php?class_id=$classId&error=missing_data");
        exit();
    }

    $newStudents = [];

    foreach ($firstNames as $index => $firstName) {
        $id = trim($studentIds[$index] ?? '');
        $data = [
            'first_name'     => trim($firstName),
            'last_name'      => trim($lastNames[$index] ?? ''),
            'gender'         => $genders[$index] ?? '',
            'class_id'       => $classId,
            'date_of_birth'  => $dateOfBirths[$index] ?? null,
            'parent_phone'   => trim($parentPhones[$index] ?? ''),
            'relationship'   => $relationships[$index] ?? '',
            'contact_number' => trim($parentPhones[$index] ?? '')
        ];

        if ($id === '') {
            // New student
            $newStudents[] = $data;
        } else {
            // Check if marked for deletion
            if (in_array($id, $deleteIds)) {
                $studentModel->deleteStudent((int)$id);
            } else {
                // Update existing
                $studentModel->updateStudent((int)$id, $data);
            }
        }
    }

    // Add new students
    $result = ['success' => true];
    if (!empty($newStudents)) {
        $result = $studentModel->addStudentsWithParents($newStudents);
    }

    if ($result['success']) {
        header("Location: ../view/class.php?success=students_saved");
    } else {
        $errorMsg = urlencode($result['message'] ?? 'Unknown error');
        header("Location: ../view/add-students-form.php?class_id=$classId&error=$errorMsg");
    }

    exit();
}

// Handle GET request to fetch students by class (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['class_id'])) {
    $classId = (int)$_GET['class_id'];
    $students = $studentModel->getStudentsByClass($classId);

    header('Content-Type: application/json');
    echo json_encode($students ? ['success' => true, 'students' => $students] : ['success' => false, 'message' => 'No students found.']);
    exit();
}
