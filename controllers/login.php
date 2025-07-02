<?php
session_start();
include('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("
        SELECT u.user_id, u.full_name, u.password, u.role_id, r.name AS role_name, u.school_id
        FROM users u
        JOIN roles r ON u.role_id = r.role_id
        WHERE u.username = ?
    ");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user'] = [
                'user_id'   => $user['user_id'],
                'full_name' => $user['full_name'],
                'role_id'   => $user['role_id'],
                'role_name' => $user['role_name'],
                'school_id' => $user['school_id'] ?? null
            ];

            // Redirect based on role
            switch ($user['role_id']) {
                case 1: header("Location: ../dashboard/superadmin.php"); break;
                case 2: header("Location: ../dashboard/schooladmin.php"); break; // schooladmin
                case 3: header("Location: ../dashboard/teacher.php"); break;
                case 4: header("Location: ../dashboard/parent.php"); break;
                default: header("Location: ../views/login.php?error=Unknown+Role");
            }
            exit;
        } else {
            header("Location: ../views/login.php?error=Invalid+Password");
            exit;
        }
    } else {
        header("Location: ../views/login.php?error=User+Not+Found");
        exit;
    }
}
?>
