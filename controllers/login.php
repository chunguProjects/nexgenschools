<?php
session_start();
include('../config/db.php');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/student-portal.php");
    exit;
}

// ----------------------
// Get POST data
// ----------------------
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? ''; // For admin/teacher
$studentNumber = trim($_POST['student_number'] ?? '');
$dob = $_POST['dob'] ?? '';

// ----------------------
// 1. Check Admin / Teacher
// ----------------------
if ($username && $password) {
    $stmt = $conn->prepare("
        SELECT u.user_id, u.full_name, u.password, u.role_id, r.name AS role_name
        FROM users u
        JOIN roles r ON u.role_id = r.role_id
        WHERE u.username = ?
        LIMIT 1
    ");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $loginSuccess = false;

        if (password_verify($password, $user['password'])) {
            $loginSuccess = true;
        } elseif ($password === $user['password']) { // Fallback for old plaintext
            $loginSuccess = true;
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $updateStmt = $conn->prepare("UPDATE users SET password = ?, password_updated = 1 WHERE user_id = ?");
            $updateStmt->bind_param("si", $hashed, $user['user_id']);
            $updateStmt->execute();
        }

        if ($loginSuccess) {
            $_SESSION['user'] = [
                'user_id' => $user['user_id'],
                'full_name' => $user['full_name'],
                'role_id' => $user['role_id'],
                'role_name' => $user['role_name']
            ];

            // Redirect based on role
            switch (strtolower($user['role_name'])) {
                case 'admin':
                    header("Location: ../dashboard/admin.php");
                    exit;
                case 'teacher':
                    header("Location: ../dashboard/teacher.php");
                    exit;
                default:
                    header("Location: ../views/student-portal.php?error=Unknown+Role");
                    exit;
            }
        } else {
            header("Location: ../views/login.php?error=Invalid+Password");
            exit;
        }
    }
}

// ----------------------
// 2. Check Student
// ----------------------
if ($studentNumber && $dob) {
    $stmt = $conn->prepare("SELECT student_id, first_name, last_name, password FROM students WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $studentNumber);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $student = $result->fetch_assoc();
        $loginSuccess = false;

        // Student password is hashed DOB: YYYYMMDD
        $dobHash = date('Ymd', strtotime($dob));

        if (password_verify($dobHash, $student['password'])) {
            $loginSuccess = true;
        } elseif ($dobHash === $student['password']) { // fallback
            $loginSuccess = true;
            $hashed = password_hash($dobHash, PASSWORD_DEFAULT);
            $updateStmt = $conn->prepare("UPDATE students SET password = ? WHERE student_id = ?");
            $updateStmt->bind_param("si", $hashed, $student['student_id']);
            $updateStmt->execute();
        }

        if ($loginSuccess) {
            // Fetch current academic year from settings
            $yearQuery = $conn->query("SELECT academic_year_id FROM academic_years WHERE is_current = 1 LIMIT 1");
            $currentYearRow = $yearQuery->fetch_assoc();
            $currentAcademicYearId = $currentYearRow['academic_year_id'] ?? 0;

            // Fetch student enrollment for current year
            $enrollStmt = $conn->prepare("
                SELECT e.class_id, c.name AS class_name
                FROM student_enrollments e
                JOIN classes c ON e.class_id = c.class_id
                WHERE e.student_id = ? AND e.academic_year_id = ?
            ");
            $enrollStmt->bind_param("ii", $student['student_id'], $currentAcademicYearId);
            $enrollStmt->execute();
            $enrollResult = $enrollStmt->get_result();
            $enrollment = $enrollResult->fetch_assoc();

            if (!$enrollment) {
                header("Location: ../views/student-portal.php?error=No+enrollment+found+for+current+year");
                exit;
            }

            // Set student session
            $_SESSION['user'] = [
                'student_id' => $student['student_id'],
                'full_name' => $student['first_name'] . ' ' . $student['last_name'],
                'role_id' => 3,
                'role_name' => 'Student',
                'class_id' => $enrollment['class_id'],
                'class_name' => $enrollment['class_name'],
                'academic_year_id' => $currentAcademicYearId
            ];

            // Redirect to student results page
            header("Location: ../dashboard/student.php");
            exit;

        } else {
            header("Location: ../views/student-portal.php?error=Invalid+Date+of+Birth");
            exit;
        }
    } else {
        header("Location: ../views/student-portal.php?error=Student+Not+Found");
        exit;
    }
}

// ----------------------
// 3. Default: No valid login attempt
// ----------------------
header("Location: ../views/student-portal.php?error=Please+provide+login+credentials");
exit;
?>
