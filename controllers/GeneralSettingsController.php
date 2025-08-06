<?php
session_start();
require_once('../config/db.php');
require_once('../model/GeneralSettingsModel.php');

if (!isset($_SESSION['user']['school_id'])) {
    die("Unauthorized access.");
}

$schoolId = $_SESSION['user']['school_id'];

$model = new GeneralSettingsModel($conn);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $motto        = trim($_POST['motto'] ?? '');
    $address      = trim($_POST['address'] ?? '');
    $contactEmail = trim($_POST['contact_email'] ?? '');
    $contactPhone = trim($_POST['contact_phone'] ?? '');

    // Validation
    if (empty($address)) $errors[] = "Address is required.";
    if (!filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email.";
    if (empty($contactPhone)) $errors[] = "Contact phone is required.";

    // Handle logo upload
    $logoPath = null;
    if (!empty($_FILES['logo']['name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/logos/'; // relative path to save in DB
        $fullUploadDir = __DIR__ . '/../' . $uploadDir;

        if (!is_dir($fullUploadDir)) {
            mkdir($fullUploadDir, 0755, true);
        }

        $filename = uniqid() . '_' . basename($_FILES['logo']['name']);
        $fullUploadPath = $fullUploadDir . $filename;

        if (move_uploaded_file($_FILES['logo']['tmp_name'], $fullUploadPath)) {
            $logoPath = $uploadDir . $filename; // store relative path in DB
        } else {
            $errors[] = 'Logo upload failed.';
        }
    }

    if (empty($errors)) {
        $data = [
            'school_id'     => $schoolId,
            'motto'         => $motto,
            'address'       => $address,
            'contact_email' => $contactEmail,
            'contact_phone' => $contactPhone
        ];

        // If logo uploaded, handle old logo deletion and set new path
        if ($logoPath) {
            if ($model->exists($schoolId)) {
                $oldSettings = $model->getSettings($schoolId);
                if (!empty($oldSettings['logo_path'])) {
                    $oldLogoFullPath = __DIR__ . '/../' . $oldSettings['logo_path'];
                    if (file_exists($oldLogoFullPath)) {
                        @unlink($oldLogoFullPath); // suppress errors if any
                    }
                }
            }
            $data['logo_path'] = $logoPath;
        }

        if ($model->exists($schoolId)) {
            $model->updateSettings($data);
        } else {
            $model->insertSettings($data);
        }

        $_SESSION['settings_success'] = 'Settings updated successfully.';
    } else {
        $_SESSION['settings_errors'] = $errors;
    }

    // Redirect back to the general settings page inside dashboard layout
    header('Location: ../dashboard/schooladmin.php?page=settings-general');
    exit;
}
