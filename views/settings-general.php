<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once('../config/db.php');
require_once('../model/GeneralSettingsModel.php');

$model = new GeneralSettingsModel($conn);
$settings = $model->getSettings();

$successMessage = $_SESSION['settings_success'] ?? null;
$errors = $_SESSION['settings_errors'] ?? [];
unset($_SESSION['settings_success'], $_SESSION['settings_errors']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>General Settings</title>
    <link rel="stylesheet" href="../assets/styles.css"> <!-- Adjust path if needed -->
    <style>
        .settings-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .settings-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .settings-container label {
            font-weight: bold;
        }
        .settings-container input[type="text"],
        .settings-container input[type="email"],
        .settings-container input[type="file"],
        .settings-container textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .settings-container button {
            background-color: #007BFF;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .settings-container button:hover {
            background-color: #0069d9;
        }
        .flash-success {
            background-color: #e0ffe0;
            padding: 10px;
            border-left: 5px solid #2ecc71;
            margin-bottom: 15px;
        }
        .flash-error {
            background-color: #ffe0e0;
            padding: 10px;
            border-left: 5px solid #e74c3c;
            margin-bottom: 15px;
        }
        .settings-container img {
            max-height: 100px;
            margin-bottom: 10px;
            display: block;
        }
    </style>
</head>
<body>

<div class="settings-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <a href="admin.php?page=dashboard" class="btn btn-outline-secondary" style="text-decoration: none; padding: 6px 12px; border: 1px solid #ccc; border-radius: 4px;">
            <i class="fa fa-arrow-left"></i> Back
        </a>
        <h2 style="flex: 1; text-align: center; margin: 0;">General School Settings</h2>
        <div style="width: 100px;"></div>
    </div>

    <?php if ($successMessage): ?>
        <div class="flash-success"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="flash-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="../controllers/GeneralSettingsController.php" method="POST" enctype="multipart/form-data">

        <label for="school_name">School Name</label>
        <input type="text" id="school_name" name="school_name" value="<?= htmlspecialchars($settings['school_name'] ?? '') ?>" required>

        <label for="motto">School Motto</label>
        <input type="text" id="motto" name="motto" value="<?= htmlspecialchars($settings['motto'] ?? '') ?>">

        <label for="address">School Address</label>
        <textarea id="address" name="address" rows="2"><?= htmlspecialchars($settings['address'] ?? '') ?></textarea>

        <label for="contact_phone">Phone Number</label>
        <input type="text" id="contact_phone" name="contact_phone" value="<?= htmlspecialchars($settings['contact_phone'] ?? '') ?>">

        <label for="contact_email">Email</label>
        <input type="email" id="contact_email" name="contact_email" value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>">

        <label for="logo">School Logo</label>
        <?php
        $logoPath = $settings['logo_path'] ?? '';
        if (!empty($logoPath) && file_exists($logoPath)):
        ?>
            <img src="<?= htmlspecialchars($logoPath) ?>" alt="Current Logo">
        <?php endif; ?>
        <input type="file" id="logo" name="logo">

        <button type="submit">Save Settings</button>
    </form>
</div>

</body>
</html>
