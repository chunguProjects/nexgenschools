<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../views/login.php");
    exit;
}

include_once('../config/db.php');
include_once('../model/user.php');
include_once('../model/class.php');
require_once('../model/GeneralSettingsModel.php');

$userId = $_SESSION['user']['user_id'];
$roleName = $_SESSION['user']['role_name'] ?? '';

$userModel = new User($conn);
$user = $userModel->getUserById($userId);

if (!$user) {
    echo "<div class='alert alert-danger'>Could not load your profile.</div>";
    exit;
}

$photoUrl = $user['profile_photo']
    ? BASE_URL . '/' . ltrim($user['profile_photo'], '/')
    : null;

// --- Teacher assignments (only relevant for teachers) ---
$assignments = [];
if ($roleName === 'teacher') {
    $settingsModel = new GeneralSettingsModel($conn);
    $activeYear = $settingsModel->getActiveAcademicYear();

    if ($activeYear) {
        $classModel = new ClassGroup($conn);
        $teacherClasses = $classModel->getTeacherClasses($userId, $activeYear['academic_year_id']);

        foreach ($teacherClasses as $class) {
            $subjects = $classModel->getTeacherSubjectsByClass(
                $userId,
                $class['class_id'],
                $activeYear['academic_year_id']
            );
            $assignments[] = [
                'class_name' => $class['name'],
                'subjects'   => $subjects
            ];
        }
    }
}
?>

<div class="container-fluid py-3" style="max-width: 900px;">
    <div class="lg-page-header">
        <h4 class="lg-page-title mb-0">My Profile</h4>
        <div></div>
    </div>

    <div id="profileAlert" class="gs-toast-wrap"></div>

    <div class="row g-4">
        <!-- Profile photo + info form -->
        <div class="col-12">
            <div class="card">
                <div class="lg-card-header">
                    <h6 class="card-title mb-0">Profile Information</h6>
                </div>
                <div class="card-body">

                    <form id="profileForm" enctype="multipart/form-data">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <img id="photoPreview"
                                 src="<?= $photoUrl ? htmlspecialchars($photoUrl) : 'https://via.placeholder.com/80?text=Photo' ?>"
                                 alt="Profile photo"
                                 class="rounded-circle"
                                 style="width: 80px; height: 80px; object-fit: cover; border: 1.5px solid var(--gold);">
                            <div>
                                <label class="btn btn-sm btn-ledger-outline mb-1" for="profilePhotoInput">
                                    Change Photo
                                </label>
                                <input type="file" id="profilePhotoInput" name="profile_photo" accept="image/jpeg,image/png,image/webp" class="d-none">
                                <div class="form-text">JPG, PNG or WEBP, up to 2MB.</div>

                                <?php if ($photoUrl): ?>
                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox" id="removePhoto" name="remove_photo" value="1">
                                        <label class="form-check-label" for="removePhoto" style="font-size:13px;">
                                            Remove current photo
                                        </label>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="full_name" class="form-control"
                                       value="<?= htmlspecialchars($user['full_name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control"
                                       value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone_number" class="form-control"
                                       value="<?= htmlspecialchars($user['phone_number']) ?>">
                            </div>
                        </div>

                        <div class="text-end mt-3">
                            <button type="submit" class="lg-btn-add">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Change password -->
        <div class="col-12">
            <div class="card">
                <div class="lg-card-header">
                    <h6 class="card-title mb-0">Change Password</h6>
                </div>
                <div class="card-body">
                    <form id="passwordForm">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control" minlength="8" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" minlength="8" required>
                            </div>
                        </div>
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-ledger-outline">Update Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Account info -->
        <div class="col-12">
            <div class="card">
                <div class="lg-card-header">
                    <h6 class="card-title mb-0">Account</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">
                        Role: <strong><?= htmlspecialchars(ucfirst($roleName)) ?></strong><br>
                        Last login:
                        <strong>
                            <?= $user['last_login'] ? htmlspecialchars(date('d M Y, g:i A', strtotime($user['last_login']))) : 'This is your first recorded login' ?>
                        </strong>
                    </p>
                </div>
            </div>
        </div>

        <!-- Teacher assignments -->
        <?php if ($roleName === 'teacher'): ?>
            <div class="col-12">
                <div class="card">
                    <div class="lg-card-header">
                        <h6 class="card-title mb-0">My Assignments</h6>
                    </div>
                    <div class="card-body">
                        <?php if (empty($assignments)): ?>
                            <p class="text-muted mb-0">No classes assigned yet for the current academic year.</p>
                        <?php else: ?>
                            <?php foreach ($assignments as $a): ?>
                                <div class="mb-2">
                                    <strong><?= htmlspecialchars($a['class_name']) ?>:</strong>
                                    <?php if (empty($a['subjects'])): ?>
                                        <span class="text-muted">No subjects assigned</span>
                                    <?php else: ?>
                                        <?= htmlspecialchars(implode(', ', array_column($a['subjects'], 'subject_code'))) ?>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
(function () {
    const profileAlert = document.getElementById('profileAlert');
    const profileForm = document.getElementById('profileForm');
    const passwordForm = document.getElementById('passwordForm');
    const photoInput = document.getElementById('profilePhotoInput');
    const photoPreview = document.getElementById('photoPreview');
    const removePhotoCheckbox = document.getElementById('removePhoto');

    const PLACEHOLDER_PHOTO = 'https://via.placeholder.com/80?text=Photo';

    function showAlert(type, message, autoHideMs = 3500) {
        profileAlert.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        const alertNode = profileAlert.querySelector('.alert');
        if (autoHideMs && alertNode) {
            setTimeout(() => {
                alertNode.classList.remove('show');
                setTimeout(() => alertNode.remove(), 200);
            }, autoHideMs);
        }
    }

    // Live preview when a new photo is chosen — a new file always overrides "remove"
    photoInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        photoPreview.src = URL.createObjectURL(file);
        if (removePhotoCheckbox) removePhotoCheckbox.checked = false;
    });

    // Ticking "remove" clears any pending file selection and previews the placeholder
    if (removePhotoCheckbox) {
        removePhotoCheckbox.addEventListener('change', function () {
            if (this.checked) {
                photoInput.value = '';
                photoPreview.src = PLACEHOLDER_PHOTO;
            }
        });
    }

    // Save profile info + photo
    profileForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const submitBtn = profileForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Saving...';

        const formData = new FormData(profileForm);

        fetch('../controllers/profile-controller.php', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json().catch(() => {
                throw new Error('Unexpected server response.');
            }))
            .then(data => {
                showAlert(data.success ? 'success' : 'danger', data.message);
                if (data.success && data.full_name) {
                    // Reflect the new name in the navbar without a full reload
                    document.querySelectorAll('.navbar .fw-semibold').forEach(el => {
                        if (el.closest('.dropdown-toggle')) el.textContent = data.full_name;
                    });
                }
               if (data.success && data.photo_url) {
                    let navImg = document.getElementById('navProfilePhoto');
                    const navIcon = document.getElementById('navProfileIcon');
                    if (!navImg && navIcon) {
                        navImg = document.createElement('img');
                        navImg.id = 'navProfilePhoto';
                        navImg.alt = 'Profile photo';
                        navImg.className = 'rounded-circle me-2 nav-profile-photo';
                        navIcon.replaceWith(navImg);
                    }
                    if (navImg) navImg.src = data.photo_url + '?t=' + Date.now();
                } else if (data.success && data.photo_removed) {
                    // Reset the big preview back to the placeholder
                    photoPreview.src = PLACEHOLDER_PHOTO;
                    if (removePhotoCheckbox) removePhotoCheckbox.checked = false;

                    // Swap the small navbar avatar back to a plain icon
                    const navImg = document.getElementById('navProfilePhoto');
                    if (navImg) {
                        const navIcon = document.createElement('i');
                        navIcon.id = 'navProfileIcon';
                        navIcon.className = 'fa-solid fa-circle-user me-2';
                        navIcon.style.cssText = 'font-size:22px;';
                        navImg.replaceWith(navIcon);
                    }
                }
            })
            .catch(err => {
                console.error(err);
                showAlert('danger', 'Something went wrong while saving your profile.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
    });

    // Change password
    passwordForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const newPass = passwordForm.new_password.value;
        const confirmPass = passwordForm.confirm_password.value;

        if (newPass !== confirmPass) {
            showAlert('danger', 'New password and confirmation do not match.');
            return;
        }

        const submitBtn = passwordForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Updating...';

        const formData = new FormData(passwordForm);

        fetch('../controllers/change-password-controller.php', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json().catch(() => {
                throw new Error('Unexpected server response.');
            }))
            .then(data => {
                showAlert(data.success ? 'success' : 'danger', data.message);
                if (data.success) passwordForm.reset();
            })
            .catch(err => {
                console.error(err);
                showAlert('danger', 'Something went wrong while updating your password.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
    });
})();
</script>