<?php
include_once('../config/db.php');
include_once('../model/user.php');

// Force school_id for schooladmins
if ($_SESSION['user']['role_name'] === 'schooladmin') {
    $school_id = $_SESSION['user']['school_id']; // enforce
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $user = new User($conn);

    // Get POST data
    $fullName     = trim($_POST['full_name']);
    $countryCode  = trim($_POST['country_code']);
    $phoneNumber  = trim($_POST['phone_number']);
    $username     = trim($_POST['username']);
    $email        = trim($_POST['email']);
    $password     = $_POST['password'];
    $role_id      = (int)$_POST['role_id'];
    $school_id    = isset($_POST['school_id']) ? (int)$_POST['school_id'] : 0;

    // Ensure school_id is required
    if ($school_id === 0) {
        header("Location: ../views/add-user-form.php?error=School+is+required");
        exit;
    }

    // Call the model
    $result = $user->addUser($fullName, $countryCode, $phoneNumber, $username, $email, $password, $role_id, $school_id);

    if ($result) {
        header("Location: ../views/index.php?page=user&success=User+added+successfully");
        exit;
    } else {
        header("Location: ../views/add-user-form.php?error=Failed+to+add+user");
        exit;
    }
}
?>
