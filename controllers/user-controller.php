<?php
include_once('../config/db.php');
include_once('../model/user.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userModel = new User($conn);

// Get logged-in user info
$sessionUser = $_SESSION['user'] ?? null;
$school_id = 0;

// Determine school_id from session or POST
if ($sessionUser && $sessionUser['role_name'] === 'schooladmin') {
    $school_id = (int) $sessionUser['school_id'];
} elseif (isset($_POST['school_id'])) {
    $school_id = (int) $_POST['school_id'];
}

// === ADD NEW USER ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $requiredFields = ['full_name', 'country_code', 'phone_number', 'username', 'email', 'password', 'role_id'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            header("Location: ../views/add-user-form.php?error=Missing+required+field+$field");
            exit;
        }
    }

    $fullName     = trim($_POST['full_name']);
    $countryCode  = trim($_POST['country_code']);
    $phoneNumber  = trim($_POST['phone_number']);
    $username     = trim($_POST['username']);
    $email        = trim($_POST['email']);
    $password     = $_POST['password'];
    $role_id      = (int) $_POST['role_id'];

    if ($school_id === 0) {
        header("Location: ../views/add-user-form.php?error=School+is+required");
        exit;
    }

    $result = $userModel->addUser($fullName, $countryCode, $phoneNumber, $username, $email, $password, $role_id, $school_id);

    if ($result) {
        $redirectPage = match ($role_id) {
            2 => 'user-admins',
            3 => 'user-teachers',
            4 => 'user-students',
            default => 'user',
        };
        header("Location: ../views/index.php?page=$redirectPage&success=User+added+successfully");
    } else {
        header("Location: ../views/add-user-form.php?error=Failed+to+add+user");
    }
    exit;
}

// === UPDATE EXISTING USER ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user']) && isset($_POST['user_id'])) {
    $userId      = (int) $_POST['user_id'];
    $fullName    = trim($_POST['full_name']);
    $email       = trim($_POST['email']);
    $phoneNumber = trim($_POST['phone_number']);
    // Check if a new password was provided
if (!empty($_POST['password'])) {
    $newPassword = $_POST['password'];
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Optional: You can create a method for this or use inline SQL
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $stmt->bind_param("si", $hashedPassword, $userId);
    $stmt->execute();
}
    $success = $userModel->updateUser($userId, $fullName, $email, $phoneNumber);

    if ($success) {
        header("Location: ../views/schooladmin.php?page=user&success=User+updated+successfully");
    } else {
        header("Location: ../views/add-user-form.php?user_id=$userId&error=Failed+to+update+user");
    }
    exit;
}

// Delete user logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user']) && isset($_POST['user_id'])) {
    $userId = (int) $_POST['user_id'];

    $deleted = $userModel->deleteUser($userId);

    if ($deleted) {
        header("Location: ../views/index.php?page=user&success=User+deleted+successfully");
    } else {
        header("Location: ../views/index.php?page=user&error=Failed+to+delete+user");
    }
    exit;
}

?>
