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
<style>
  .lp-footer{
    background: var(--green-dark, #124F32);
    color: #CFE3D6;
    border-top: 3px solid var(--gold, #C7962C);
    padding-top: 3rem;
  }
  .lp-footer h5{
    font-family: 'Fraunces', serif;
    font-weight: 600;
    color: #fff;
    font-size: 15px;
    letter-spacing: .02em;
    margin-bottom: 12px;
  }
  .lp-footer p{ color: #CFE3D6; font-size: 13.5px; }
  .lp-footer a{ color: #CFE3D6; text-decoration: none; }
  .lp-footer a:hover{ color: var(--gold, #C7962C); text-decoration: underline; }
  .lp-footer hr{ border-color: rgba(255,255,255,0.15); }
  .lp-footer .lp-brand{
    color: var(--gold, #C7962C);
    font-weight: 700;
  }
  .lp-footer .lp-social a{
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px; height: 30px;
    border: 1px solid rgba(255,255,255,0.25);
    border-radius: 50%;
    margin-left: 6px;
    color: #CFE3D6;
  }
  .lp-footer .lp-social a:hover{
    border-color: var(--gold, #C7962C);
    color: var(--gold, #C7962C);
    text-decoration: none;
  }
</style>
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

$facebookUrl  = $settings['facebook_url'] ?? '';
$instagramUrl = $settings['instagram_url'] ?? '';
$twitterUrl   = $settings['twitter_url'] ?? '';
$linkedinUrl  = $settings['linkedin_url'] ?? '';
?>
<footer class="lp-footer">
    <div class="container">
        <div class="row mb-4">
            <!-- School Info -->
            <div class="col-md-4 mb-3">
                <h5>School</h5>
                <p class="mb-1 fw-bold" style="color:#fff;"><?= htmlspecialchars($schoolName) ?></p>
                <p class="mb-1"><?= htmlspecialchars($address) ?></p>
                <?php if ($contactEmail): ?>
                    <p><strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($contactEmail) ?>"><?= htmlspecialchars($contactEmail) ?></a></p>
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

        <hr>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center pb-3">
            <div class="mb-2 mb-md-0">
                &copy; <?= date('Y') ?> <span style="color:#fff;">NextGen Schools</span> - Powered by <a href="#" class="lp-brand">C-technologies Ltd</a>
            </div>
            <?php if ($facebookUrl || $instagramUrl || $twitterUrl || $linkedinUrl): ?>
            <div class="lp-social">
                <?php if ($facebookUrl): ?>
                    <a href="<?= htmlspecialchars($facebookUrl) ?>" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a>
                <?php endif; ?>
                <?php if ($instagramUrl): ?>
                    <a href="<?= htmlspecialchars($instagramUrl) ?>" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>
                <?php endif; ?>
                <?php if ($twitterUrl): ?>
                    <a href="<?= htmlspecialchars($twitterUrl) ?>" target="_blank" rel="noopener"><i class="fab fa-x-twitter"></i></a>
                <?php endif; ?>
                <?php if ($linkedinUrl): ?>
                    <a href="<?= htmlspecialchars($linkedinUrl) ?>" target="_blank" rel="noopener"><i class="fab fa-linkedin-in"></i></a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</footer>