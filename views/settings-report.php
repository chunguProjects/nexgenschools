<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('../config/db.php');
require_once('../model/ReportSettingsModel.php');

$model = new ReportSettingsModel($conn);
$settings = $model->getSettings(); // single-tenant, no schoolId
?>

<div class="container-fluid py-3">

    <!-- PAGE HEADER -->
    <div class="lg-page-header">
        <a href="admin.php?page=dashboard" class="lg-btn-back text-decoration-none">
            <i class="fa fa-arrow-left"></i> Back
        </a>
        <h4 class="lg-page-title mb-0">Report Settings</h4>
        <div></div>
    </div>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash']) ?></div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <!-- FORM CARD -->
    <div class="lg-form-card">
        <h4>Report Card Configuration</h4>
        <div class="lg-form-divider"></div>

        <form action="../controllers/report-settings-controller.php" method="POST">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="opening_date" class="form-label">Opening Date</label>
                    <input type="date" class="form-control" id="opening_date" name="opening_date"
                           value="<?= htmlspecialchars($settings['opening_date'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="closing_date" class="form-label">Closing Date</label>
                    <input type="date" class="form-control" id="closing_date" name="closing_date"
                           value="<?= htmlspecialchars($settings['closing_date'] ?? '') ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="head_teacher_name" class="form-label">Head Teacher's Name</label>
                    <input type="text" class="form-control" id="head_teacher_name" name="head_teacher_name"
                           value="<?= htmlspecialchars($settings['head_teacher_name'] ?? '') ?>">
                </div>
            </div>

            <hr style="border-color: var(--rule);">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="fee_boarding" class="form-label">Boarding Fee (MWK)</label>
                    <input type="number" class="form-control" id="fee_boarding" name="fee_boarding"
                           value="<?= htmlspecialchars($settings['fee_boarding'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="fee_self_boarding" class="form-label">Self-Boarding Fee (MWK)</label>
                    <input type="number" class="form-control" id="fee_self_boarding" name="fee_self_boarding"
                           value="<?= htmlspecialchars($settings['fee_self_boarding'] ?? '') ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="fee_uniform" class="form-label">Uniform &amp; ID Card (MWK)</label>
                    <input type="number" class="form-control" id="fee_uniform" name="fee_uniform"
                           value="<?= htmlspecialchars($settings['fee_uniform'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="fee_registration" class="form-label">Registration Fee (MWK)</label>
                    <input type="number" class="form-control" id="fee_registration" name="fee_registration"
                           value="<?= htmlspecialchars($settings['fee_registration'] ?? '') ?>">
                </div>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="include_fees" name="include_fees"
                       <?= isset($settings['include_fees']) && $settings['include_fees'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="include_fees">
                    Include Fees in Report Card
                </label>
            </div>

            <button type="submit" class="lg-btn-add">Save Settings</button>
        </form>
    </div>
</div>