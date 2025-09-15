<?php
include_once('../config/db.php');
include_once('../model/User.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userModel = new User($conn);

// Get logged-in user info
$sessionUser = $_SESSION['user'] ?? null;

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

    $result = $userModel->addUser($fullName, $countryCode, $phoneNumber, $username, $email, $password, $role_id);

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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'], $_POST['user_id'])) {
    $userId      = (int) $_POST['user_id'];
    $fullName    = trim($_POST['full_name']);
    $email       = trim($_POST['email']);
    $phoneNumber = trim($_POST['phone_number']);
    $password    = !empty($_POST['password']) ? $_POST['password'] : null;

    $success = $userModel->updateUser($userId, $fullName, $email, $phoneNumber, $password);

    if ($success) {
        header("Location: ../views/index.php?page=user&success=User+updated+successfully");
    } else {
        header("Location: ../views/add-user-form.php?user_id=$userId&error=Failed+to+update+user");
    }
    exit;
}

// === DELETE USER ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'], $_POST['user_id'])) {
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
