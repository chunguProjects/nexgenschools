<?php
include('../config/db.php');
include('../model/student.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$studentModel = new Student($conn);

# ================= HANDLE POST =================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $classId        = $_POST['class_id'] ?? null;
    $studentIds     = $_POST['student_id'] ?? [];
    $firstNames     = $_POST['first_name'] ?? [];
    $lastNames      = $_POST['last_name'] ?? [];
    $genders        = $_POST['gender'] ?? [];
    $dateOfBirths   = $_POST['date_of_birth'] ?? [];
    $contactNumbers = $_POST['contact_number'] ?? [];
    $deleteIds      = $_POST['delete_student'] ?? [];

    if (!$classId || empty($firstNames)) {
        $_SESSION['error'] = "Missing required student data.";
        header("Location: ../dashboard/admin.php?page=add-student-form?class_id=$classId");
        exit();
    }

    $newStudents = [];
    $errors = [];

    foreach ($firstNames as $index => $firstName) {

        $id = trim($studentIds[$index] ?? '');

        $data = [
            'first_name'     => trim($firstName),
            'last_name'      => trim($lastNames[$index] ?? ''),
            'gender'         => $genders[$index] ?? '',
            'class_id'       => $classId,
            'date_of_birth'  => $dateOfBirths[$index] ?? null,
            'contact_number' => trim($contactNumbers[$index] ?? '')
        ];

        # -------- VALIDATION --------
        $rowErrors = [];

        if (empty($data['first_name'])) {
            $rowErrors[] = "First name required";
        }

        if (empty($data['gender']) || !in_array($data['gender'], ['Male', 'Female'])) {
            $rowErrors[] = "Gender must be Male or Female";
        }

        if (empty($data['contact_number'])) {
            $rowErrors[] = "Contact number required";
        }

        if (!empty($data['date_of_birth']) &&
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date_of_birth'])) {
            $rowErrors[] = "Invalid DOB format";
        }

        if ($rowErrors) {
            $errors[] = "Row " . ($index + 1) . ": " . implode(", ", $rowErrors);
            continue;
        }

        # -------- NEW STUDENT --------
        if ($id === '') {
            $newStudents[] = $data;
            continue;
        }

        # -------- DELETE STUDENT --------
        if (in_array($id, $deleteIds)) {

            if (!$studentModel->deleteStudent((int)$id)) {
                $errors[] = "Failed to delete student ID $id";
            }

        } else {
            # -------- UPDATE STUDENT --------
            if (!$studentModel->updateStudent((int)$id, $data)) {
                $errors[] = "Failed to update student ID $id";
            }
        }
    }

    # -------- ADD NEW STUDENTS --------
    if (!empty($newStudents)) {
        $result = $studentModel->addStudents($newStudents);

        if (!$result['success']) {
            $errors[] = "Adding new students failed: " . ($result['message'] ?? "Unknown error");
        }
    }

    # -------- FINAL RESPONSE --------
    if (!empty($errors)) {
        $_SESSION['error'] = implode(" | ", $errors);
        
        header("Location: ../dashboard/admin.php?page=add-student-form?class_id=$classId");
       
    } else {
        $_SESSION['success'] = "Students saved successfully.";
        header("Location: ../dashboard/admin.php?page=add-student-form&class_id=$classId");    }

    exit();
}

# ================= HANDLE AJAX =================
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['class_id'])) {

    $classId  = (int)$_GET['class_id'];
    $students = $studentModel->getStudentsByClass($classId);

    header('Content-Type: application/json');

    echo json_encode(
        $students
            ? ['success' => true, 'students' => $students]
            : ['success' => false, 'message' => 'No students found']
    );

    exit();
}

?>