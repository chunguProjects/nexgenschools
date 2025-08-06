<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('../config/db.php');
require_once('../model/ReportSettingsModel.php');

// Protect route
if (!isset($_SESSION['user']['school_id'])) {
    die("Unauthorized access.");
}

$schoolId = $_SESSION['user']['school_id'];
$model = new ReportSettingsModel($conn);
$settings = $model->getSettings($schoolId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Settings</title>
    <link rel="stylesheet" href="../assets/styles.css"> <!-- adjust if needed -->
    <style>
        .settings-container {
            max-width: 800px;
            margin: auto;
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
        .settings-container input[type="date"],
        .settings-container input[type="number"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .settings-container .checkbox {
            margin-bottom: 15px;
        }
        .settings-container button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .settings-container button:hover {
            background-color: #45a049;
        }
        .flash {
            background-color: #e0ffe0;
            padding: 10px;
            margin-bottom: 15px;
            border-left: 5px solid #2ecc71;
        }
    </style>
</head>
<body>
    <div class="settings-container">
        <div class="d-flex justify-content-between align-items-center mb-4" style="display: flex; justify-content: space-between; align-items: center;">
    <a href="schooladmin.php?page=dashboard" class="btn btn-outline-secondary" style="text-decoration: none; padding: 6px 12px; border: 1px solid #ccc; border-radius: 4px;">
        <i class="fa fa-arrow-left"></i> Back
    </a>
    <h2 style="flex: 1; text-align: center; margin: 0;">Report Settings</h2>
    <div style="width: 100px;"></div> <!-- Placeholder to balance space -->
</div>

        <?php if (isset($_SESSION['flash'])): ?>
            <div class="flash"><?= $_SESSION['flash'] ?></div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <form action="../controllers/report-settings-controller.php" method="POST">
            <label for="opening_date">Opening Date</label>
            <input type="date" id="opening_date" name="opening_date" value="<?= htmlspecialchars($settings['opening_date'] ?? '') ?>">

            <label for="closing_date">Closing Date</label>
            <input type="date" id="closing_date" name="closing_date" value="<?= htmlspecialchars($settings['closing_date'] ?? '') ?>">

            <label for="head_teacher_name">Head Teacher's Name</label>
            <input type="text" id="head_teacher_name" name="head_teacher_name" value="<?= htmlspecialchars($settings['head_teacher_name'] ?? '') ?>">

            <hr>

            <label for="fee_boarding">Boarding Fee (MWK)</label>
            <input type="number" id="fee_boarding" name="fee_boarding" value="<?= htmlspecialchars($settings['fee_boarding'] ?? '') ?>">

            <label for="fee_self_boarding">Self-Boarding Fee (MWK)</label>
            <input type="number" id="fee_self_boarding" name="fee_self_boarding" value="<?= htmlspecialchars($settings['fee_self_boarding'] ?? '') ?>">

            <label for="fee_uniform">Uniform & ID Card (MWK)</label>
            <input type="number" id="fee_uniform" name="fee_uniform" value="<?= htmlspecialchars($settings['fee_uniform'] ?? '') ?>">

            <label for="fee_registration">Registration Fee (MWK)</label>
            <input type="number" id="fee_registration" name="fee_registration" value="<?= htmlspecialchars($settings['fee_registration'] ?? '') ?>">

            <div class="checkbox">
                <label>
                    <input type="checkbox" name="include_fees" <?= isset($settings['include_fees']) && $settings['include_fees'] ? 'checked' : '' ?>>
                    Include Fees in Report Card
                </label>
            </div>

            <button type="submit">Save Settings</button>
        </form>
    </div>
</body>
</html>
