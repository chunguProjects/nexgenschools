<?php
if (session_status() === PHP_SESSION_NONE) session_start();

include('../config/db.php');
require_once('../model/GeneralSettingsModel.php');
require_once('../helpers/ImageUpload.php');

$model = new GeneralSettingsModel($conn);
$settings = $model->getSettings();
$settingsId = $settings['id'] ?? null;

$message = null;
$messageType = 'success';

// Pull any message left over from a redirect (Post/Redirect/Get)
if (isset($_SESSION['settings_flash'])) {
    $message = $_SESSION['settings_flash']['message'];
    $messageType = $_SESSION['settings_flash']['type'];
    unset($_SESSION['settings_flash']);
}

// ---------------------------------------------------------
// Small helper: raw upload (no resize/recompress). Used only
// for the logo, since logos are often PNG/SVG and need to keep
// transparency / vector format rather than being flattened to JPEG.
// ---------------------------------------------------------
function handleSettingsUpload(string $fieldName, string $prefix, array $allowedExt, string $uploadDir): ?string
{
    if (empty($_FILES[$fieldName]['name'])) {
        return null;
    }
    $ext = strtolower(pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        return null;
    }
    $newName = $prefix . '_' . time() . '.' . $ext;
    if (move_uploaded_file($_FILES[$fieldName]['tmp_name'], $uploadDir . $newName)) {
        return 'uploads/' . $newName;
    }
    return null;
}

// ---------------------------------------------------------
// Small helper: resolve the final path for one image field,
// handling three cases in priority order:
//   1. A new file was uploaded -> use it (optimized or raw), delete the old file
//   2. "Remove current image" was ticked, no new file -> clear it, delete the old file
//   3. Neither -> keep the existing path unchanged
//
// $useOptimized = true routes the upload through uploadOptimizedImage()
// (auto resize + JPEG compression, from helpers/ImageUpload.php).
// $useOptimized = false uses the raw handleSettingsUpload() above —
// used for the logo so PNG transparency / SVG survive untouched.
// Any upload error is appended to $errors (by reference) and the
// existing path is kept rather than the whole save failing.
// ---------------------------------------------------------
function resolveSettingsImage(
    string $fieldName,
    string $prefix,
    string $uploadDir,
    ?string $existingPath,
    bool $removeFlag,
    array &$errors,
    bool $useOptimized = true,
    array $allowedExt = ['jpg', 'jpeg', 'png', 'webp']
): ?string {
    if (!empty($_FILES[$fieldName]['name'])) {
        if ($useOptimized) {
            try {
                $newPath = uploadOptimizedImage($_FILES[$fieldName], $uploadDir, 1600, 78, $prefix);
            } catch (Exception $e) {
                $errors[] = ucwords(str_replace('_', ' ', $fieldName)) . ': ' . $e->getMessage();
                return $existingPath;
            }
        } else {
            $newPath = handleSettingsUpload($fieldName, $prefix, $allowedExt, $uploadDir);
            if ($newPath === null) {
                $errors[] = ucwords(str_replace('_', ' ', $fieldName)) . ': invalid file, or upload failed.';
                return $existingPath;
            }
        }

        if (!empty($existingPath)) {
            $oldFullPath = __DIR__ . '/../' . $existingPath;
            if (file_exists($oldFullPath)) {
                @unlink($oldFullPath);
            }
        }
        return $newPath;
    }

    if ($removeFlag && !empty($existingPath)) {
        $oldFullPath = __DIR__ . '/../' . $existingPath;
        if (file_exists($oldFullPath)) {
            @unlink($oldFullPath);
        }
        return null;
    }

    return $existingPath;
}

// ---------------------------------------------------------
// Save handler — one form, one submit, all sections at once.
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $settingsId) {
    // --- Profile & Hero ---
    $schoolName     = trim($_POST['school_name'] ?? '');
    $motto          = trim($_POST['motto'] ?? '');
    $heroHeading    = trim($_POST['hero_heading'] ?? '');
    $heroSubtext    = trim($_POST['hero_subtext'] ?? '');
    $foundedYearRaw = trim($_POST['founded_year'] ?? '');
    $foundedYear    = ($foundedYearRaw !== '') ? (int) $foundedYearRaw : null;
    $admissionsOpen = isset($_POST['admissions_open']) ? 1 : 0;
    $admissionsLink = trim($_POST['admissions_link'] ?? '');

    // --- About School ---
    $aboutText   = trim($_POST['about_text'] ?? '');
    $visionText  = trim($_POST['vision_text'] ?? '');
    $missionText = trim($_POST['mission_text'] ?? '');
    $valuesText  = trim($_POST['values_text'] ?? '');

    // --- Leadership ---
    $headTeacherName    = trim($_POST['head_teacher_name'] ?? '');
    $headTeacherMessage = trim($_POST['head_teacher_message'] ?? '');

    // --- Contact & Social ---
    $address      = trim($_POST['address'] ?? '');
    $contactEmail = trim($_POST['contact_email'] ?? '');
    $contactPhone = trim($_POST['contact_phone'] ?? '');
    $facebookUrl  = trim($_POST['facebook_url'] ?? '');
    $instagramUrl = trim($_POST['instagram_url'] ?? '');
    $twitterUrl   = trim($_POST['twitter_url'] ?? '');
    $linkedinUrl  = trim($_POST['linkedin_url'] ?? '');

    // --- File uploads / removals ---
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $imageErrors = [];

    // Logo: raw upload (keeps PNG transparency / SVG intact, not recompressed to JPEG)
    $logoPath = resolveSettingsImage(
        'logo', 'logo', $uploadDir,
        $settings['logo_path'] ?? null,
        isset($_POST['remove_logo']),
        $imageErrors,
        false,
        ['jpg', 'jpeg', 'png', 'webp', 'svg']
    );

    // Hero, About, Head Teacher photos: optimized (auto resize + compress)
    $heroImagePath = resolveSettingsImage(
        'hero_image', 'hero', $uploadDir,
        $settings['hero_image_path'] ?? null,
        isset($_POST['remove_hero_image']),
        $imageErrors,
        true
    );

    $aboutImagePath = resolveSettingsImage(
        'about_image', 'about', $uploadDir,
        $settings['about_image'] ?? null,
        isset($_POST['remove_about_image']),
        $imageErrors,
        true
    );

    $headTeacherPhotoPath = resolveSettingsImage(
        'head_teacher_photo', 'headteacher', $uploadDir,
        $settings['head_teacher_photo'] ?? null,
        isset($_POST['remove_head_teacher_photo']),
        $imageErrors,
        true
    );

    $stmt = $conn->prepare(
        'UPDATE settings SET
            school_name = ?, motto = ?, logo_path = ?,
            hero_heading = ?, hero_subtext = ?, hero_image_path = ?,
            founded_year = ?, admissions_open = ?, admissions_link = ?,
            about_text = ?, about_image = ?, vision_text = ?, mission_text = ?, values_text = ?,
            head_teacher_name = ?, head_teacher_message = ?, head_teacher_photo = ?,
            address = ?, contact_email = ?, contact_phone = ?,
            facebook_url = ?, instagram_url = ?, twitter_url = ?, linkedin_url = ?
         WHERE id = ?'
    );

    // Build the type string to match the param order exactly, rather than
    // hand-counting a literal (easy to get wrong by hand).
    $types = str_repeat('s', 6)   // school_name..hero_image_path
           . 'ii'                 // founded_year, admissions_open
           . str_repeat('s', 16)  // admissions_link..linkedin_url
           . 'i';                 // settingsId (WHERE id = ?)

    $stmt->bind_param(
        $types,
        $schoolName, $motto, $logoPath,
        $heroHeading, $heroSubtext, $heroImagePath,
        $foundedYear, $admissionsOpen, $admissionsLink,
        $aboutText, $aboutImagePath, $visionText, $missionText, $valuesText,
        $headTeacherName, $headTeacherMessage, $headTeacherPhotoPath,
        $address, $contactEmail, $contactPhone,
        $facebookUrl, $instagramUrl, $twitterUrl, $linkedinUrl,
        $settingsId
    );

    if ($stmt->execute()) {
        $message = 'Website settings saved successfully.';
        $messageType = 'success';
    } else {
        $message = 'Could not save settings: ' . $stmt->error;
        $messageType = 'danger';
    }

    if (!empty($imageErrors)) {
        $message .= ' However, some images were not updated — ' . implode(' ', $imageErrors);
        $messageType = 'danger';
    }

    // Redirect so a page reload doesn't resubmit the form
    $_SESSION['settings_flash'] = ['message' => $message, 'type' => $messageType];
    header('Location: admin.php?page=settings-general');
    exit;
}
?>

<style>
    /* Floating Save button — stays visible while scrolling a long form */
    .lg-floating-save-wrap{
        position: fixed;
        right: 28px;
        bottom: 28px;
        z-index: 1050;
    }
    .lg-floating-save{
        border-radius: 999px;
        padding: 14px 28px;
        box-shadow: 0 10px 28px rgba(0,0,0,.22);
        font-weight: 600;
    }
    /* Keep content from being hidden behind the floating button */
    .container-fluid.py-3{
        padding-bottom: 90px !important;
    }
</style>

<div class="container-fluid py-3">
    <div class="lg-page-header">
        <h4 class="lg-page-title mb-0"><i class="fa-solid fa-globe me-2" style="color:var(--gold);"></i>Website Management</h4>
        <div class="d-flex gap-2">
            <a href="admin.php?page=gallery" class="lg-btn-add">
                <i class="fa fa-images"></i> Gallery
            </a>
            <a href="admin.php?page=why-choose-us" class="lg-btn-add">
                <i class="fa fa-star"></i> Why Choose Us
            </a>
            <a href="admin.php?page=announcements" class="lg-btn-add">
                <i class="fa fa-bullhorn"></i> Announcements
            </a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <ul class="nav ledger-tabs nav-tabs mb-3" id="wmTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-profile" type="button">Profile &amp; Hero</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-about" type="button">About School</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-leadership" type="button">Leadership</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-contact" type="button">Contact &amp; Social</button>
            </li>
        </ul>

        <div class="tab-content">

            <!-- ===== Profile & Hero ===== -->
            <div class="tab-pane fade show active" id="tab-profile" role="tabpanel">
                <div class="lg-form-card">
                    <h4>School Profile</h4>
                    <div class="lg-form-divider"></div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">School Name</label>
                            <input type="text" name="school_name" class="form-control"
                                   value="<?= htmlspecialchars($settings['school_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Motto / Tagline</label>
                            <input type="text" name="motto" class="form-control"
                                   value="<?= htmlspecialchars($settings['motto'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Founded Year</label>
                            <input type="number" name="founded_year" class="form-control" min="1900" max="<?= date('Y') ?>"
                                   value="<?= htmlspecialchars($settings['founded_year'] ?? '') ?>"
                                   placeholder="e.g. 2005">
                            <div class="form-text">Used to show "Years of Excellence" on the homepage.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">School Logo</label>
                            <?php if (!empty($settings['logo_path'])): ?>
                                <div class="mb-2">
                                    <img src="../<?= htmlspecialchars($settings['logo_path']) ?>" alt="Current logo"
                                         style="max-height:48px;border:1.5px solid var(--gold);padding:2px;display:block;margin-bottom:6px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="removeLogo" name="remove_logo" value="1">
                                        <label class="form-check-label" for="removeLogo" style="font-size:13px;">
                                            Remove current logo
                                        </label>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="logo" class="form-control" accept="image/*">
                            <div class="form-text">Leave blank to keep the current logo. Uploading a new one replaces it automatically. PNG/SVG kept as-is to preserve transparency.</div>
                        </div>
                    </div>

                    <h4>Hero Section</h4>
                    <p class="text-muted" style="font-size:13.5px;">The banner at the top of the landing page.</p>
                    <div class="lg-form-divider"></div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Hero Heading</label>
                            <input type="text" name="hero_heading" class="form-control"
                                   value="<?= htmlspecialchars($settings['hero_heading'] ?? '') ?>"
                                   placeholder="e.g. Excellence in Education, Discipline and Character">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hero Image</label>
                            <?php if (!empty($settings['hero_image_path'])): ?>
                                <div class="mb-2">
                                    <img src="../<?= htmlspecialchars($settings['hero_image_path']) ?>" alt="Current hero image"
                                         style="max-height:70px;border:1px solid var(--rule);display:block;margin-bottom:6px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="removeHeroImage" name="remove_hero_image" value="1">
                                        <label class="form-check-label" for="removeHeroImage" style="font-size:13px;">
                                            Remove current hero image
                                        </label>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="hero_image" class="form-control" accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">Only used if no photos are in the Gallery yet. Large photos are automatically resized and compressed.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Hero Subtext</label>
                            <textarea name="hero_subtext" class="form-control" rows="2"
                                      placeholder="One or two sentences under the hero heading"><?= htmlspecialchars($settings['hero_subtext'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <h4>Admissions</h4>
                    <p class="text-muted" style="font-size:13.5px;">Controls the "Apply Now" button on the hero.</p>
                    <div class="lg-form-divider"></div>

                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="admissionsOpen"
                                       name="admissions_open" value="1"
                                       <?= !empty($settings['admissions_open']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="admissionsOpen">Admissions currently open</label>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Apply Now Link</label>
                            <input type="text" name="admissions_link" class="form-control"
                                   value="<?= htmlspecialchars($settings['admissions_link'] ?? '') ?>"
                                   placeholder="views/admissions.php">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== About ===== -->
            <div class="tab-pane fade" id="tab-about" role="tabpanel">
                <div class="lg-form-card">
                    <h4>About School</h4>
                    <p class="text-muted" style="font-size:13.5px;">
                        Shown together as one paragraph on the homepage About card, and as separate
                        sections on the full About Us page.
                    </p>
                    <div class="lg-form-divider"></div>

                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="form-label">About Text</label>
                            <textarea name="about_text" class="form-control" rows="4"><?= htmlspecialchars($settings['about_text'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Vision</label>
                            <textarea name="vision_text" class="form-control" rows="3"><?= htmlspecialchars($settings['vision_text'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mission</label>
                            <textarea name="mission_text" class="form-control" rows="3"><?= htmlspecialchars($settings['mission_text'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Core Values</label>
                            <input type="text" name="values_text" class="form-control"
                                   value="<?= htmlspecialchars($settings['values_text'] ?? '') ?>"
                                   placeholder="e.g. Integrity, Excellence, Respect, and Community">
                            <div class="form-text">Only shown on the full About Us page, not the homepage card.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">About Section Image</label>
                            <?php if (!empty($settings['about_image'])): ?>
                                <div class="mb-2">
                                    <img src="../<?= htmlspecialchars($settings['about_image']) ?>" alt="Current about image"
                                         style="max-height:80px;border:1px solid var(--rule);display:block;margin-bottom:6px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="removeAboutImage" name="remove_about_image" value="1">
                                        <label class="form-check-label" for="removeAboutImage" style="font-size:13px;">
                                            Remove current about image
                                        </label>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="about_image" class="form-control" accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">Falls back to the Hero Image if left blank. Large photos are automatically resized and compressed.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== Leadership ===== -->
            <div class="tab-pane fade" id="tab-leadership" role="tabpanel">
                <div class="lg-form-card">
                    <h4>Head Teacher's Message</h4>
                    <p class="text-muted" style="font-size:13.5px;">
                        Leave the name blank to hide this section from the homepage entirely.
                    </p>
                    <div class="lg-form-divider"></div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Head Teacher Name</label>
                            <input type="text" name="head_teacher_name" class="form-control"
                                   value="<?= htmlspecialchars($settings['head_teacher_name'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Head Teacher Photo</label>
                            <?php if (!empty($settings['head_teacher_photo'])): ?>
                                <div class="mb-2">
                                    <img src="../<?= htmlspecialchars($settings['head_teacher_photo']) ?>" alt="Current head teacher photo"
                                         class="rounded-circle" style="height:56px;width:56px;object-fit:cover;border:1.5px solid var(--gold);display:block;margin-bottom:6px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="removeHeadTeacherPhoto" name="remove_head_teacher_photo" value="1">
                                        <label class="form-check-label" for="removeHeadTeacherPhoto" style="font-size:13px;">
                                            Remove current photo
                                        </label>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="head_teacher_photo" class="form-control" accept="image/jpeg,image/png,image/webp">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Message</label>
                            <textarea name="head_teacher_message" class="form-control" rows="4"><?= htmlspecialchars($settings['head_teacher_message'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== Contact & Social ===== -->
            <div class="tab-pane fade" id="tab-contact" role="tabpanel">
                <div class="lg-form-card">
                    <h4>Contact Information</h4>
                    <p class="text-muted" style="font-size:13.5px;">Shown in the site footer.</p>
                    <div class="lg-form-divider"></div>

                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control"
                                   value="<?= htmlspecialchars($settings['address'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Email</label>
                            <input type="email" name="contact_email" class="form-control"
                                   value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Phone</label>
                            <input type="text" name="contact_phone" class="form-control"
                                   value="<?= htmlspecialchars($settings['contact_phone'] ?? '') ?>">
                        </div>
                    </div>

                    <h4>Social Media Links</h4>
                    <p class="text-muted" style="font-size:13.5px;">Leave any field blank to hide that icon in the footer.</p>
                    <div class="lg-form-divider"></div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label"><i class="fab fa-facebook-f me-1"></i> Facebook URL</label>
                            <input type="url" name="facebook_url" class="form-control"
                                   value="<?= htmlspecialchars($settings['facebook_url'] ?? '') ?>" placeholder="https://facebook.com/...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fab fa-instagram me-1"></i> Instagram URL</label>
                            <input type="url" name="instagram_url" class="form-control"
                                   value="<?= htmlspecialchars($settings['instagram_url'] ?? '') ?>" placeholder="https://instagram.com/...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fab fa-x-twitter me-1"></i> Twitter / X URL</label>
                            <input type="url" name="twitter_url" class="form-control"
                                   value="<?= htmlspecialchars($settings['twitter_url'] ?? '') ?>" placeholder="https://x.com/...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fab fa-linkedin-in me-1"></i> LinkedIn URL</label>
                            <input type="url" name="linkedin_url" class="form-control"
                                   value="<?= htmlspecialchars($settings['linkedin_url'] ?? '') ?>" placeholder="https://linkedin.com/...">
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="lg-floating-save-wrap">
            <button type="submit" class="btn btn-ledger-solid lg-floating-save">
                <i class="fa fa-save me-1"></i> Save All Changes
            </button>
        </div>
    </form>
</div>