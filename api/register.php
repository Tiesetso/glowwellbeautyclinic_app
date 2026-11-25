<?php

require_once '../dbconnection.php';
require_once 'helpers.php';

// Only enables POST requests from the form
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method.');
}

$fullName = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = trim($_POST['password'] ?? '');

// Input validation 
if (empty($fullName) || empty($email) || empty($phone) || empty($password)) {
    sendResponse(false, 'All registration fields are required.');
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(false, 'Please provide a valid email address.');
}

// Check if email is already registered
$stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->execute([$email]);

if ($stmt->fetch()) {
    sendResponse(false, 'An account with this email address already exists.');
}

// Encrypt the password before storing it
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Insert the new customer into the database
$insertStmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password_hash, role) VALUES (?, ?, ?, ?, 'customer')");

if ($insertStmt->execute([$fullName, $email, $phone, $hashedPassword])) {
    sendResponse(true, 'Registration successful! You may now sign in.');
} else {
    sendResponse(false, 'Unable to register account. Please try again.');
}

?>