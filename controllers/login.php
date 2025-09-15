<?php
session_start();
include('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // ----------------------
    // 1. Check Staff (admin / teacher)
    // ----------------------
    $stmt = $conn->prepare("
        SELECT u.user_id, u.full_name, u.password, u.role_id, r.name AS role_name
        FROM users u
        JOIN roles r ON u.role_id = r.role_id
        WHERE u.username = ?
    ");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $login_success = false;

// Try hashed password first
if (password_verify($password, $user['password'])) {
    $login_success = true;
} else {
    // fallback for plaintext passwords (old entries)
    if ($password === $user['password']) {
        $login_success = true;

        // Hash and update password
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $updateStmt = $conn->prepare("UPDATE users SET password = ?, password_updated = 1 WHERE user_id = ?");
        $updateStmt->bind_param("si", $hashed, $user['user_id']);
        $updateStmt->execute();
    }
}


        if ($login_success) {
            $_SESSION['user'] = [
                'user_id'   => $user['user_id'],
                'full_name' => $user['full_name'],
                'role_id'   => $user['role_id'],
                'role_name' => $user['role_name']
            ];

            // Redirect based on role
            switch ($user['role_name']) {
                case 'admin':
                    header("Location: ../dashboard/admin.php");
                    break;
                case 'teacher':
                    header("Location: ../dashboard/teacher.php");
                    break;
                default:
                    header("Location: ../views/login.php?error=Unknown+Role");
            }
            exit;
        } else {
            header("Location: ../views/login.php?error=Invalid+Password");
            exit;
        }
    }

    // ----------------------
    // 2. Check Student
    // ----------------------
    $stmt = $conn->prepare("
        SELECT s.student_id, s.first_name, s.last_name, s.password
        FROM students s
        WHERE s.username = ?
    ");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $student = $result->fetch_assoc();
        $login_success = false;

        if (password_get_info($student['password'])['algo'] === 0) {
            if ($password === $student['password']) {
                $login_success = true;

                // Hash and update password
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $updateStmt = $conn->prepare("UPDATE students SET password = ? WHERE student_id = ?");
                $updateStmt->bind_param("si", $hashed, $student['student_id']);
                $updateStmt->execute();
            }
        } else {
            if (password_verify($password, $student['password'])) {
                $login_success = true;
            }
        }

        if ($login_success) {
            // Fetch current academic year
            $yearQuery = $conn->query("SELECT opening_date, closing_date FROM settings LIMIT 1");
            $settings = $yearQuery->fetch_assoc();
            $currentYear = date('Y', strtotime($settings['opening_date'])) . '-' . date('Y', strtotime($settings['closing_date']));

            // Fetch enrollment for current year
            $enrollStmt = $conn->prepare("
                SELECT e.class_id, c.name AS class_name
                FROM student_enrollments e
                JOIN classes c ON e.class_id = c.class_id
                WHERE e.student_id = ? AND e.academic_year = ?
            ");
            $enrollStmt->bind_param("is", $student['student_id'], $currentYear);
            $enrollStmt->execute();
            $enrollResult = $enrollStmt->get_result();
            $enrollment = $enrollResult->fetch_assoc();

            if (!$enrollment) {
                header("Location: ../views/login.php?error=No+enrollment+for+$currentYear");
                exit;
            }

            $_SESSION['user'] = [
                'student_id'   => $student['student_id'],
                'full_name'    => $student['first_name'] . " " . $student['last_name'],
                'role_id'      => 3,
                'role_name'    => 'Student',
                'class_id'     => $enrollment['class_id'],
                'class_name'   => $enrollment['class_name'],
                'academic_year'=> $currentYear
            ];

            header("Location: ../dashboard/student.php");
            exit;
        } else {
            header("Location: ../views/login.php?error=Invalid+Password");
            exit;
        }
    }

    // ----------------------
    // 3. Not found
    // ----------------------
    header("Location: ../views/login.php?error=User+Not+Found");
    exit;
}
?>
