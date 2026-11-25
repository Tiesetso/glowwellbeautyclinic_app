<?php

require_once '../dbconnection.php';
require_once 'helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method.');
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

// Validate that neither field is blank
if (empty($email) || empty($password)) {
    sendResponse(false, 'Email and password are required.');
}

// Finding the user by email 
$stmt = $pdo->prepare("SELECT user_id, full_name, email, password_hash, role FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

// Verify the user exists and the password matches the stored hash
if ($user && password_verify($password, $user['password_hash'])) {

    // Save user details in the PHP session so they stay logged in across pages
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];

    // Decide which dashboard to send them to based on their role
    $redirectUrl = ($user['role'] === 'admin') ? 'admin_dashboard.php' : 'customer_dashboard.php';
    
    // Send success message and the redirect URL back to JavaScript
    sendResponse(true, 'Login successful!', ['redirect' => $redirectUrl]);
    
} else {
    sendResponse(false, 'Invalid email or password credentials provided.');
}

?>