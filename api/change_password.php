<?php

require_once '../dbconnection.php';
require_once 'helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method.');
}

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    sendResponse(false, 'Unauthorized session. Please log in first.');
}

$userId = $_SESSION['user_id'];
$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
    sendResponse(false, 'Please fill in all password fields.');
}

if (strlen($newPassword) < 6) {
    sendResponse(false, 'New password must be at least 6 characters long.');
}

if ($newPassword !== $confirmPassword) {
    sendResponse(false, 'New password and confirmation do not match.');
}

// Fetch the user's current password hash from the database
$stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Verify the current password is correct before allowing a change
if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
    sendResponse(false, 'Your current password is incorrect.');
}

// Hash the new password and update the database record
$newHashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

$updateStmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
if ($updateStmt->execute([$newHashedPassword, $userId])) {
    sendResponse(true, 'Password updated successfully!');
} else {
    sendResponse(false, 'Failed to update password. Please try again.');
}

?>