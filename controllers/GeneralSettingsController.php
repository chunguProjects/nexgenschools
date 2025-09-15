<?php
session_start();
require_once('../config/db.php');
require_once('../model/GeneralSettingsModel.php');

$model = new GeneralSettingsModel($conn);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $schoolName   = trim($_POST['school_name'] ?? '');
    $motto        = trim($_POST['motto'] ?? '');
    $address      = trim($_POST['address'] ?? '');
    $contactEmail = trim($_POST['contact_email'] ?? '');
    $contactPhone = trim($_POST['contact_phone'] ?? '');

    // Validation
    if (empty($schoolName)) $errors[] = "School name is required.";
    if (empty($address)) $errors[] = "Address is required.";
    if (!filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email.";
    if (empty($contactPhone)) $errors[] = "Contact phone is required.";

    // Handle logo upload
    $logoPath = null;
    if (!empty($_FILES['logo']['name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/logos/'; // relative path
        $fullUploadDir = __DIR__ . '/../' . $uploadDir;

        if (!is_dir($fullUploadDir)) {
            mkdir($fullUploadDir, 0755, true);
        }

        $filename = uniqid() . '_' . basename($_FILES['logo']['name']);
        $fullUploadPath = $fullUploadDir . $filename;

        if (move_uploaded_file($_FILES['logo']['tmp_name'], $fullUploadPath)) {
            $logoPath = $uploadDir . $filename; // store relative path
        } else {
            $errors[] = 'Logo upload failed.';
        }
    }

    if (empty($errors)) {
        $data = [
            'school_name'   => $schoolName,
            'motto'         => $motto,
            'address'       => $address,
            'contact_email' => $contactEmail,
            'contact_phone' => $contactPhone,
        ];

        // Handle logo
        if ($logoPath) {
            $oldSettings = $model->getSettings();
            if (!empty($oldSettings['logo_path'])) {
                $oldLogoFullPath = __DIR__ . '/../' . $oldSettings['logo_path'];
                if (file_exists($oldLogoFullPath)) {
                    @unlink($oldLogoFullPath);
                }
            }
            $data['logo_path'] = $logoPath;
        }

        if ($model->exists()) {
            $model->updateSettings($data);
        } else {
            $model->insertSettings($data);
        }

        $_SESSION['settings_success'] = 'Settings updated successfully.';
    } else {
        $_SESSION['settings_errors'] = $errors;
    }

    // Redirect back to general settings page
    header('Location: ../dashboard/admin.php?page=settings-general');
    exit;
}
