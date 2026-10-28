<?php
session_start();
require_once('../config/db.php');
require_once('../model/school.php');
require_once __DIR__ . '/../includes/session-check.php';


// Only superadmin can access this
if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'superadmin') {
    header("Location: ../views/login.php");
    exit;
}

if (isset($_POST['add_school'])) {
    // Sanitize inputs
    $schoolName     = trim($_POST['school_name']);
    $status         = trim($_POST['status']);
    $fullName       = trim($_POST['admin_full_name']);
    $username       = trim($_POST['admin_username']);
    $email          = trim($_POST['admin_email']);
    $countryCode    = trim($_POST['admin_country_code']);
    $phone          = trim($_POST['admin_phone']);
    $password       = $_POST['admin_password'];
    $confirmPassword = $_POST['admin_confirm_password'];

    // Validation
    if (
        empty($schoolName) || empty($status) || empty($fullName) || empty($username) ||
        empty($email) || empty($countryCode) || empty($phone) || empty($password)
    ) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: ../views/index.php?page=schools");
        exit;
    }

    if ($password !== $confirmPassword) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: ../views/index.php?page=schools");
        exit;
    }

    try {
        $schoolModel = new School($conn);

        $schoolModel->createSchoolWithAdmin($schoolName, $status, [
            'full_name'     => $fullName,
            'username'      => $username,
            'email'         => $email,
            'country_code'  => $countryCode,
            'phone_number'  => $phone,
            'password'      => $password
        ]);

        $_SESSION['modal_success'] = "School and school admin created successfully.";
    } catch (Exception $e) {
        $_SESSION['modal_error'] = "Error: " . $e->getMessage();
    }

    header("Location: ../dashboard/manage-schools.php");
    exit;
}
