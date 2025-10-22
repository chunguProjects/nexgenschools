<?php
include_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/../model/GeneralSettingsModel.php');

$model = new GeneralSettingsModel($conn);
$settings = $model->getSettings();

$schoolName = $settings['school_name'] ?? 'NextGen Schools';
$address = $settings['address'] ?? '';
$contactEmail = $settings['contact_email'] ?? '';
$contactPhone = $settings['contact_phone'] ?? '';
$motto = $settings['motto'] ?? '';
?>
<footer class="bg-primary text-light pt-5">
    <div class="container">
        <div class="row mb-4">
            <!-- School Info -->
            <div class="col-md-4 mb-3">
                <h5>School</h5>
                <p class="mb-1 fw-bold"><?= htmlspecialchars($schoolName) ?></p>
                <p class="mb-1"><?= htmlspecialchars($address) ?></p>
                <?php if ($contactEmail): ?>
                    <p><strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($contactEmail) ?>" class="text-light"><?= htmlspecialchars($contactEmail) ?></a></p>
                <?php endif; ?>
                <?php if ($contactPhone): ?>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($contactPhone) ?></p>
                <?php endif; ?>
            </div>

            <!-- Physical Address -->
            <div class="col-md-4 mb-3">
                <h5>Location</h5>
                <p class="mb-1"><?= htmlspecialchars($address) ?></p>
            </div>

            <!-- Office Contacts -->
            <div class="col-md-4 mb-3">
                <h5>Office Contacts</h5>
                <p class="mb-1">Principal: +265 (0) 988 301 840</p>
                <p class="mb-1">Deputy Principal: +265 (0) 888 382 503</p>
                <p class="mb-1">Bursar: +265 (0) 988 476 486</p>
                <p class="mb-1">Exam Officer: +265 (0) 983 338 578</p>
            </div>
        </div>

        <hr class="border-secondary">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center pb-3">
            <div class="mb-2 mb-md-0">
                &copy; <?= date('Y') ?> <span class="text-light">NextGen Schools</span> - Powered by <a href="#" class="fw-bold text-warning text-decoration-none">C-technologies Ltd</a>
            </div>
            <div>
                <a href="#" class="text-light me-2"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="text-light me-2"><i class="fab fa-instagram"></i></a>
                <a href="#" class="text-light me-2"><i class="fab fa-skype"></i></a>
                <a href="#" class="text-light"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>
    </div>
</footer>
