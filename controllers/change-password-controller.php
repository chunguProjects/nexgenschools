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
require_once __DIR__ . '/../includes/session-check.php';


$userId = (int) $_SESSION['user']['user_id'];

$currentPassword = $_POST['current_password'] ?? '';
$newPassword     = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'All password fields are required.']);
    exit;
}

if ($newPassword !== $confirmPassword) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'New password and confirmation do not match.']);
    exit;
}

if (strlen($newPassword) < 8) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'New password must be at least 8 characters.']);
    exit;
}

$userModel = new User($conn);

try {
    if (!$userModel->verifyPassword($userId, $currentPassword)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
        exit;
    }

    $success = $userModel->changePassword($userId, $newPassword);

    if (!$success) {
        throw new Exception('Failed to update password.');
    }

    echo json_encode(['success' => true, 'message' => 'Password updated successfully.']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

exit;