<?php
include_once('../config/db.php');
include_once('../model/user.php');
require_once __DIR__ . '/../includes/session-check.php';


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
            $_SESSION['error'] = "Please fill in all required fields.";
            header("Location: ../dashboard/admin.php?page=add-user-form");
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

    if($userModel->addUser($fullName, $countryCode, $phoneNumber, $username, $email, $password, $role_id)){
       $_SESSION['success'] ="User added successfully";
    }else{
        $_SESSION['error'] ="Failed to add user";
    }
    
        header("Location: ../dashboard/admin.php?page=add-user-form");
    exit;
}

// === UPDATE EXISTING USER ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'], $_POST['user_id'])) {
    $userId      = (int) $_POST['user_id'];
    $fullName    = trim($_POST['full_name']);
    $email       = trim($_POST['email']);
    $phoneNumber = trim($_POST['phone_number']);
    $password    = !empty($_POST['password']) ? $_POST['password'] : null;

    if($userModel->updateUser($userId, $fullName, $email, $phoneNumber, $password)){
        $_SESSION['success'] ="User updated successfully";
    }else{
        $_SESSION['error'] = "Failed to update the user.";
    }
         header("Location: ../dashboard/admin.php?page=add-user-form");
    exit;
}

// === DELETE USER ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'], $_POST['user_id'])) {
    $userId = (int) $_POST['user_id'];

    if($userModel->deleteUser($userId)){
        $_SESSION['success'] = "User deleted successfully.";
    }else{
        $_SESSION['error'] = "Failed to delete user.";
    }
    header("Location: ../dashboard/admin.php?page=user");
    exit;
}
?>
