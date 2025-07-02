<?php
session_start();
require_once('../config/db.php');

// Only superadmin can access this
if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'superadmin') {
    header("Location: ../views/login.php");
    exit;
}

if (isset($_POST['add_school'])) {
    // Sanitize inputs
    $schoolName = trim($_POST['school_name']);
    $status = $_POST['status'];

    $fullName = trim($_POST['admin_full_name']);
    $username = trim($_POST['admin_username']);
    $email = trim($_POST['admin_email']);
    $countryCode = trim($_POST['admin_country_code']);
    $phone = trim($_POST['admin_phone']);
    $password = $_POST['admin_password'];
    $confirmPassword = $_POST['admin_confirm_password'];

    // Basic validation
    if (empty($schoolName) || empty($fullName) || empty($username) || empty($email) || empty($phone) || empty($password)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: ../views/index.php?page=schools");
        exit;
    }

    if ($password !== $confirmPassword) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: ../views/index.php?page=schools");
        exit;
    }

    // Begin transaction
    $conn->begin_transaction();

    try {
        // 1. Insert school
        $stmt = $conn->prepare("INSERT INTO schools (name, status) VALUES (?, ?)");
        $stmt->bind_param("ss", $schoolName, $status);
        $stmt->execute();
        $schoolId = $stmt->insert_id;

        // 2. Get schooladmin role_id
        $roleQuery = $conn->prepare("SELECT role_id FROM roles WHERE name = 'schooladmin'");
        $roleQuery->execute();
        $roleResult = $roleQuery->get_result();
        $roleRow = $roleResult->fetch_assoc();
        $roleId = $roleRow['role_id'];

        // 3. Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // 4. Insert school admin
        $stmt2 = $conn->prepare("INSERT INTO users (school_id, full_name, country_code, phone_number, username, email, password, role_id, password_updated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)");
        $stmt2->bind_param("issssssi", $schoolId, $fullName, $countryCode, $phone, $username, $email, $hashedPassword, $roleId);
        $stmt2->execute();

        // 5. Commit
        $conn->commit();
        $_SESSION['modal_success'] = "School and school admin created successfully.";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['modal_error'] = "Error: " . $e->getMessage();
    }

    header("Location: ../dashboard/manage-schools.php");
    exit;
}
?>
