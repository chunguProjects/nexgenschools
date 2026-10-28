<?php
session_start();
include('../config/db.php');

/* ----------------------
   FLASH MESSAGE FUNCTION
---------------------- */
function setFlash(string $type, string $message): void {
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_message'] = $message;
}

/* ----------------------
   ONLY POST REQUESTS
---------------------- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/student-portal.php");
    exit;
}

/* ----------------------
   GET INPUTS
---------------------- */
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$studentNumber = trim($_POST['student_number'] ?? '');
$dob = $_POST['dob'] ?? '';

/* =====================================================
   1. ADMIN / TEACHER LOGIN
===================================================== */
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

    if ($result->num_rows !== 1) {
        setFlash("error", "Invalid username or password.");
        header("Location: ../views/login.php");
        exit;
    }

    $user = $result->fetch_assoc();
    $loginSuccess = false;

    // Password check
    if (password_verify($password, $user['password'])) {
        $loginSuccess = true;
    } elseif ($password === $user['password']) {
        $loginSuccess = true;

        // auto-upgrade old password
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $updateStmt = $conn->prepare("UPDATE users SET password = ?, password_updated = 1 WHERE user_id = ?");
        $updateStmt->bind_param("si", $hashed, $user['user_id']);
        $updateStmt->execute();
    }

    if (!$loginSuccess) {
        setFlash("error", "Invalid username or password.");
        header("Location: ../views/login.php");
        exit;
    }

    /* SESSION */
    $_SESSION['user'] = [
        'user_id' => $user['user_id'],
        'full_name' => $user['full_name'],
        'role_id' => $user['role_id'],
        'role_name' => $user['role_name']
    ];
    $_SESSION['last_activity'] = time(); // enables session-check.php's inactivity timeout

    // Record this login's timestamp
    $lastLoginStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
    $lastLoginStmt->bind_param("i", $user['user_id']);
    $lastLoginStmt->execute();

    setFlash("success", "Welcome back, " . $user['full_name'] . "!");

    // Redirect by role
    if (strtolower($user['role_name']) === 'admin') {
        header("Location: ../dashboard/admin.php");
    } elseif (strtolower($user['role_name']) === 'teacher') {
        header("Location: ../dashboard/teacher.php");
    } else {
        setFlash("error", "Unknown role assigned.");
        header("Location: ../views/login.php");
    }

    exit;
}

/* =====================================================
   2. STUDENT LOGIN
===================================================== */
if ($studentNumber && $dob) {

    $stmt = $conn->prepare("
        SELECT student_id, first_name, last_name, password
        FROM students
        WHERE username = ?
        LIMIT 1
    ");

    $stmt->bind_param("s", $studentNumber);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        setFlash("error", "Student not found.");
        header("Location: ../views/student-portal.php");
        exit;
    }

    $student = $result->fetch_assoc();

    $dobHash = date('Ymd', strtotime($dob));
    $loginSuccess = false;

    if (password_verify($dobHash, $student['password'])) {
        $loginSuccess = true;
    } elseif ($dobHash === $student['password']) {
        $loginSuccess = true;

        // upgrade password hash
        $hashed = password_hash($dobHash, PASSWORD_DEFAULT);
        $updateStmt = $conn->prepare("UPDATE students SET password = ? WHERE student_id = ?");
        $updateStmt->bind_param("si", $hashed, $student['student_id']);
        $updateStmt->execute();
    }

    if (!$loginSuccess) {
        setFlash("error", "Incorrect date of birth.");
        header("Location: ../views/student-portal.php");
        exit;
    }

    /* GET CURRENT ACADEMIC YEAR */
    $yearQuery = $conn->query("SELECT academic_year_id FROM academic_years WHERE is_current = 1 LIMIT 1");
    $currentYearRow = $yearQuery->fetch_assoc();
    $currentAcademicYearId = $currentYearRow['academic_year_id'] ?? 0;

    /* CHECK ENROLLMENT */
    $enrollStmt = $conn->prepare("
        SELECT e.class_id, c.name AS class_name
        FROM student_enrollments e
        JOIN classes c ON e.class_id = c.class_id
        WHERE e.student_id = ? AND e.academic_year_id = ?
    ");

    $enrollStmt->bind_param("ii", $student['student_id'], $currentAcademicYearId);
    $enrollStmt->execute();
    $enrollment = $enrollStmt->get_result()->fetch_assoc();

    if (!$enrollment) {
        setFlash("error", "No class enrollment found for current academic year.");
        header("Location: ../views/student-portal.php");
        exit;
    }

    /* SESSION */
    $_SESSION['user'] = [
        'student_id' => $student['student_id'],
        'full_name' => $student['first_name'] . ' ' . $student['last_name'],
        'role_id' => 3,
        'role_name' => 'Student',
        'class_id' => $enrollment['class_id'],
        'class_name' => $enrollment['class_name'],
        'academic_year_id' => $currentAcademicYearId
    ];
    $_SESSION['last_activity'] = time(); // enables session-check.php's inactivity timeout

    setFlash("success", "Welcome " . $student['first_name'] . "!");

    header("Location: ../dashboard/student.php");
    exit;
}

/* =====================================================
   DEFAULT FAIL
===================================================== */
setFlash("error", "Please enter valid login details.");
header("Location: ../views/student-portal.php");
exit;
?>