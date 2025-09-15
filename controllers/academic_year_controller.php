<?php
include('../config/db.php');
include('../model/academic_year.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$ayModel = new AcademicYear($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ✅ Match form input names with DB column names
    $name       = trim($_POST['name'] ?? '');
    $start_date = $_POST['start_date'] ?? '';
    $end_date   = $_POST['end_date'] ?? '';
    $is_current = isset($_POST['is_current']) ? 1 : 0;

    if (empty($name) || empty($start_date) || empty($end_date)) {
        $_SESSION['error'] = "All fields are required.";
         header("Location: ../dashboard/admin.php?page=academic_years");
        exit;
    }

    try {
        if (!empty($_POST['academic_year_id'])) {
            $ayModel->update($_POST['academic_year_id'], $name, $start_date, $end_date, $is_current);
            $_SESSION['success'] = "Academic year updated successfully!";
        } else {
            $ayModel->create($name, $start_date, $end_date, $is_current);
            $_SESSION['success'] = "Academic year created successfully!";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }

    header("Location: ../dashboard/admin.php?page=academic_years");
    exit;
}

// ✅ Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    try {
        $ayModel->delete($id);
        $_SESSION['success'] = "Academic year deleted successfully!";
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }

       header("Location: ../dashboard/admin.php?page=academic_years");

    exit;
}
 