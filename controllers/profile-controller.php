<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in.']);
    exit;
}

include_once('../config/db.php');
include_once('../model/user.php');
require_once('../helpers/ImageUpload.php');
require_once __DIR__ . '/../includes/session-check.php';


$userId = (int) $_SESSION['user']['user_id'];

$fullName     = trim($_POST['full_name'] ?? '');
$email        = trim($_POST['email'] ?? '');
$phone        = trim($_POST['phone_number'] ?? '');
$removePhoto  = isset($_POST['remove_photo']) && $_POST['remove_photo'] === '1';

if ($fullName === '' || $email === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Name and email are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

$userModel = new User($conn);

try {
    $success = $userModel->updateUser($userId, $fullName, $email, $phone, null);

    if (!$success) {
        throw new Exception('Failed to update profile.');
    }

    $existingUser  = $userModel->getUserById($userId);
    $existingPhoto = $existingUser['profile_photo'] ?? null;

    // --- Optional photo upload (takes priority over "remove photo") ---
    if (!empty($_FILES['profile_photo']['name'])) {
        $file = $_FILES['profile_photo'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Photo upload failed.');
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            throw new Exception('Photo must be under 2MB.');
        }

        $uploadDir = __DIR__ . '/../uploads/profile_photos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Resize + compress automatically (max 800px is plenty for an avatar,
        // it's only ever shown at 80px / 28px on screen)
        $relativePath = uploadOptimizedImage($file, $uploadDir, 800, 80, 'user_' . $userId);

        $userModel->updateProfilePhoto($userId, $relativePath);

        if (!empty($existingPhoto)) {
            $oldFullPath = __DIR__ . '/../' . $existingPhoto;
            if (file_exists($oldFullPath)) {
                @unlink($oldFullPath);
            }
        }

    } elseif ($removePhoto && !empty($existingPhoto)) {
        $oldFullPath = __DIR__ . '/../' . $existingPhoto;
        if (file_exists($oldFullPath)) {
            @unlink($oldFullPath);
        }
        $userModel->updateProfilePhoto($userId, null);
    }

    // Keep the session in sync so the navbar/name reflect the change immediately
    $_SESSION['user']['full_name'] = $fullName;
    $_SESSION['user']['email'] = $email;

    $freshUser = $userModel->getUserById($userId);
    $photoUrl = !empty($freshUser['profile_photo'])
        ? BASE_URL . '/' . ltrim($freshUser['profile_photo'], '/')
        : null;

    echo json_encode([
        'success'   => true,
        'message'   => 'Profile updated successfully.',
        'full_name' => $fullName,
        'photo_url' => $photoUrl,
        'photo_removed' => ($photoUrl === null)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

exit;