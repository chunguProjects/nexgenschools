<?php
require_once('../config/db.php');
require_once('../model/ReportSettingsModel.php');

// Check if user is authenticated
session_start();
if (!isset($_SESSION['user'])) {
    die('Unauthorized access');
}

$model = new ReportSettingsModel($conn);

// Get current settings
$settings = $model->getSettings();

// Save updated settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'opening_date'      => $_POST['opening_date'] ?? null,
        'closing_date'      => $_POST['closing_date'] ?? null,
        'head_teacher_name' => $_POST['head_teacher_name'] ?? '',
        'fee_boarding'      => (int) ($_POST['fee_boarding'] ?? 0),
        'fee_self_boarding' => (int) ($_POST['fee_self_boarding'] ?? 0),
        'fee_uniform'       => (int) ($_POST['fee_uniform'] ?? 0),
        'fee_registration'  => (int) ($_POST['fee_registration'] ?? 0),
        'include_fees'      => isset($_POST['include_fees']) ? 1 : 0
    ];

    $success = $model->saveSettings($data);

    if ($success) {
        $_SESSION['flash'] = "Report settings saved successfully.";
    } else {
        $_SESSION['flash'] = "Failed to save settings.";
    }

    // Reload to reflect changes
    header('Location: ../dashboard/admin.php?page=settings-report');
    exit;
}
